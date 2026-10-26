<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\CashfreeService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function () {
    Config::set('services.cashfree.app_id', 'test_cf_app_123');
    Config::set('services.cashfree.secret_key', 'test_cf_secret_456');
    Config::set('services.cashfree.env', 'TEST');
    Config::set('services.cashfree.api_version', '2023-08-01');
});

test('cashfree service defaults to sandbox mode', function () {
    Config::set('services.cashfree.env', 'TEST');
    $service = new CashfreeService;

    expect($service->isSandbox())->toBeTrue()
        ->and($service->getSdkMode())->toBe('sandbox')
        ->and($service->getBaseUrl())->toBe('https://sandbox.cashfree.com/pg');

    // Default when env is empty
    Config::set('services.cashfree.env', '');
    expect($service->isSandbox())->toBeTrue();
});

test('cashfree service detects when credentials are missing or configured', function () {
    $service = new CashfreeService;
    expect($service->isConfigured())->toBeTrue()
        ->and($service->getMaskedSecret())->toBe('••••••••••••••••');

    Config::set('services.cashfree.app_id', '');
    Config::set('services.cashfree.secret_key', '');
    expect($service->isConfigured())->toBeFalse()
        ->and($service->getMaskedSecret())->toBe('');
});

test('createOrder returns error when credentials are not configured', function () {
    Config::set('services.cashfree.app_id', '');
    Config::set('services.cashfree.secret_key', '');

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-20261024-TEST01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Test St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    $service = new CashfreeService;
    $result = $service->createOrder($order);

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('not configured');
});

test('createOrder contacts Cashfree API and returns session id', function () {
    Http::fake([
        'https://sandbox.cashfree.com/pg/orders' => Http::response([
            'cf_order_id' => 'cf_order_12345678',
            'order_id' => 'ORD-20261024-TEST02',
            'order_amount' => 500.00,
            'order_currency' => 'INR',
            'payment_session_id' => 'session_test_abc123xyz',
            'order_status' => 'ACTIVE',
        ], 200),
    ]);

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-20261024-TEST02',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Jane Doe',
        'mobile' => '+91 9888888888',
        'email' => 'jane@example.com',
        'address' => '456 Test Ave',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    $service = new CashfreeService;
    $result = $service->createOrder($order);

    expect($result['success'])->toBeTrue()
        ->and($result['payment_session_id'])->toBe('session_test_abc123xyz')
        ->and($result['cf_order_id'])->toBe('cf_order_12345678')
        ->and($result['order_id'])->toBe('ORD-20261024-TEST02')
        ->and($result['environment'])->toBe('sandbox');
});

test('getOrderStatus authoritatively fetches order and payment details from Cashfree', function () {
    Http::fake([
        'https://sandbox.cashfree.com/pg/orders/ORD-20261024-TEST03' => Http::response([
            'order_id' => 'ORD-20261024-TEST03',
            'cf_order_id' => 'cf_333444',
            'order_status' => 'PAID',
            'order_amount' => 750.00,
            'order_currency' => 'INR',
        ], 200),
        'https://sandbox.cashfree.com/pg/orders/ORD-20261024-TEST03/payments' => Http::response([
            [
                'cf_payment_id' => 'cf_pay_999888',
                'payment_status' => 'SUCCESS',
                'payment_amount' => 750.00,
                'payment_group' => 'upi',
                'payment_time' => '2026-10-24T12:00:00Z',
            ],
        ], 200),
    ]);

    $service = new CashfreeService;
    $result = $service->getOrderStatus('ORD-20261024-TEST03');

    expect($result['success'])->toBeTrue()
        ->and($result['is_paid'])->toBeTrue()
        ->and($result['order_status'])->toBe('PAID')
        ->and($result['cf_payment_id'])->toBe('cf_pay_999888')
        ->and($result['payment_method'])->toBe('upi')
        ->and($result['order_amount'])->toBe(750.0);
});

test('verifyWebhookSignature correctly verifies valid signature and rejects tampered payload', function () {
    $secret = 'test_cf_secret_456';
    Config::set('services.cashfree.secret_key', $secret);

    $service = new CashfreeService;
    $timestamp = '1698150000';
    $rawPayload = json_encode(['data' => ['order' => ['order_id' => 'ORD-TEST']]]);

    $validSignature = base64_encode(hash_hmac('sha256', $timestamp.$rawPayload, $secret, true));

    // Valid signature
    expect($service->verifyWebhookSignature($rawPayload, $timestamp, $validSignature))->toBeTrue();

    // Tampered payload
    expect($service->verifyWebhookSignature($rawPayload.'tampered', $timestamp, $validSignature))->toBeFalse();

    // Wrong timestamp
    expect($service->verifyWebhookSignature($rawPayload, '1698159999', $validSignature))->toBeFalse();

    // Empty signature
    expect($service->verifyWebhookSignature($rawPayload, $timestamp, ''))->toBeFalse();
});

test('initiateRefund contacts Cashfree API and records refund status', function () {
    Http::fake([
        'https://sandbox.cashfree.com/pg/orders/ORD-REFUND-01/refunds' => Http::response([
            'cf_refund_id' => 'cf_ref_555666',
            'refund_id' => 'ref_local_777',
            'refund_status' => 'SUCCESS',
            'refund_amount' => 200.00,
        ], 200),
    ]);

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-REFUND-01',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'name' => 'John',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'cashfree',
        'cashfree_order_id' => 'ORD-REFUND-01',
        'cashfree_payment_id' => 'cf_pay_111222',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'captured',
        'payment_method' => 'online',
    ]);

    $service = new CashfreeService;
    $result = $service->initiateRefund($payment, 200.00, 'Customer requested cancellation');

    expect($result['success'])->toBeTrue()
        ->and($result['refund_status'])->toBe('SUCCESS')
        ->and($result['refund_id'])->toBe('cf_ref_555666');
});

test('initiateRefund rejects zero, negative, or amounts exceeding refundable balance', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-REFUND-02',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'name' => 'John',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 150.00,
        'delivery_charge' => 0.00,
        'total_amount' => 150.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'cashfree',
        'cashfree_order_id' => 'ORD-REFUND-02',
        'amount' => 150.00,
        'refunded_amount' => 50.00,
        'currency' => 'INR',
        'status' => 'captured',
        'payment_method' => 'online',
    ]);

    $service = new CashfreeService;

    // Reject <= 0
    $resZero = $service->initiateRefund($payment, 0.0, 'Zero refund');
    expect($resZero['success'])->toBeFalse()
        ->and($resZero['message'])->toContain('greater than zero');

    // Reject exceeding refundable balance (150 - 50 = 100 available)
    $resExcess = $service->initiateRefund($payment, 105.00, 'Too much');
    expect($resExcess['success'])->toBeFalse()
        ->and($resExcess['message'])->toContain('exceeds available refundable balance');
});
