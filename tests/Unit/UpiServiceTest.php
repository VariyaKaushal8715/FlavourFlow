<?php

use App\Models\Order;
use App\Models\PaymentGatewaySetting;
use App\Services\QrCodeService;
use App\Services\UpiService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->qrService = new QrCodeService;
    $this->upiService = new UpiService($this->qrService);
});

test('validates valid and invalid UPI IDs correctly', function () {
    // Valid UPI IDs
    expect($this->upiService->isValidUpiId('merchant@upi'))->toBeTrue()
        ->and($this->upiService->isValidUpiId('flavourflow@okhdfcbank'))->toBeTrue()
        ->and($this->upiService->isValidUpiId('store.spices_123@icici'))->toBeTrue()
        ->and($this->upiService->isValidUpiId('9876543210@paytm'))->toBeTrue()
        ->and($this->upiService->isValidUpiId('user-name@axl'))->toBeTrue();

    // Invalid UPI IDs
    expect($this->upiService->isValidUpiId(''))->toBeFalse()
        ->and($this->upiService->isValidUpiId(null))->toBeFalse()
        ->and($this->upiService->isValidUpiId('noupiid'))->toBeFalse()
        ->and($this->upiService->isValidUpiId('@bank'))->toBeFalse()
        ->and($this->upiService->isValidUpiId('user@'))->toBeFalse()
        ->and($this->upiService->isValidUpiId('user@bank@extra'))->toBeFalse()
        ->and($this->upiService->isValidUpiId('user name@bank'))->toBeFalse()
        ->and($this->upiService->isValidUpiId('<script>alert(1)</script>@bank'))->toBeFalse();
});

test('builds standard UPI payment URI with exact amount and order reference', function () {
    $uri = $this->upiService->buildPaymentUri(
        upiId: 'merchant@okhdfcbank',
        payeeName: 'FlavourFlow Spices',
        amount: 450.00,
        orderReference: 'ORD-20261029-ABC123',
        note: 'Order ORD-20261029-ABC123'
    );

    expect($uri)->toStartWith('upi://pay?')
        ->and($uri)->toContain('pa=merchant@okhdfcbank')
        ->and($uri)->toContain('pn=FlavourFlow%20Spices')
        ->and($uri)->toContain('am=450.00')
        ->and($uri)->toContain('cu=INR')
        ->and($uri)->toContain('tn=Order%20ORD-20261029-ABC123')
        ->and($uri)->toContain('tr=ORD-20261029-ABC123');
});

test('safely encodes special characters in payee name and note against injection', function () {
    $uri = $this->upiService->buildPaymentUri(
        upiId: 'merchant@upi',
        payeeName: 'Flavour & Flow <Spices> = Premium',
        amount: 1250.50,
        orderReference: 'ORD-TEST-1',
        note: 'Spices & Herbs? amount=0&free=true'
    );

    expect($uri)->not->toContain(' ')
        ->and($uri)->not->toContain('&free=true')
        ->and($uri)->not->toContain('<Spices>')
        ->and($uri)->toContain('pn=Flavour%20%26%20Flow%20%3CSpices%3E%20%3D%20Premium')
        ->and($uri)->toContain('tn=Spices%20%26%20Herbs%3F%20amount%3D0%26free%3Dtrue');
});

test('rejects zero or negative payment amounts', function () {
    $this->upiService->buildPaymentUri(
        upiId: 'merchant@upi',
        payeeName: 'FlavourFlow',
        amount: 0.00,
        orderReference: 'ORD-123'
    );
})->throws(InvalidArgumentException::class);

test('generates scannable SVG and data URI', function () {
    $uri = 'upi://pay?pa=merchant@upi&pn=FlavourFlow&am=100.00&cu=INR&tn=Test&tr=ORD-1';

    $svg = $this->upiService->generateQrSvg($uri, 250, 4);
    expect($svg)->toContain('<svg')
        ->and($svg)->toContain('viewBox=')
        ->and($svg)->toContain('width="250"')
        ->and($svg)->toContain('height="250"')
        ->and($svg)->toContain('<rect')
        ->and($svg)->toContain('<path');

    $dataUri = $this->upiService->generateQrDataUri($uri, 250, 4);
    expect($dataUri)->toStartWith('data:image/svg+xml;base64,')
        ->and(base64_decode(substr($dataUri, strlen('data:image/svg+xml;base64,'))))->toContain('<svg');
});

test('getPaymentDetailsForOrder dynamically reads authoritative order data', function () {
    $setting = PaymentGatewaySetting::current();
    $setting->update([
        'upi_id' => 'flavourflow@okhdfcbank',
        'upi_name' => 'FlavourFlow Store',
        'upi_note' => 'Spices Order',
    ]);

    $order = new Order([
        'order_number' => 'ORD-20261029-XYZ999',
        'total_amount' => 699.00,
    ]);

    $details = $this->upiService->getPaymentDetailsForOrder($order);

    expect($details['upi_id'])->toBe('flavourflow@okhdfcbank')
        ->and($details['payee_name'])->toBe('FlavourFlow Store')
        ->and($details['amount'])->toBe(699.0)
        ->and($details['formatted_amount'])->toBe('₹699.00')
        ->and($details['order_reference'])->toBe('ORD-20261029-XYZ999')
        ->and($details['payment_uri'])->toContain('am=699.00')
        ->and($details['payment_uri'])->toContain('tr=ORD-20261029-XYZ999')
        ->and($details['qr_svg'])->toContain('<svg');
});
