<?php

use App\Models\Order;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

uses(TestCase::class);

test('creates mock order with correct amount in paise when using dummy credentials', function () {
    $service = new RazorpayService;
    $order = new Order(['total_amount' => 249.50]);

    $result = $service->createOrder($order);

    expect($result['success'])->toBeTrue()
        ->and($result['id'])->toStartWith('order_')
        ->and($result['amount'])->toBe(24950);
});

test('verifies signature in mock mode with non-empty signature', function () {
    Config::set('services.razorpay.key_secret', 'rzp_test_dummy_key_secret');
    $service = new RazorpayService;

    expect($service->verifySignature('order_123', 'pay_123', 'any-sig'))->toBeTrue()
        ->and($service->verifySignature('order_123', 'pay_123', ''))->toBeFalse();
});

test('verifies valid HMAC SHA256 signature when real secret is configured', function () {
    $secret = 'super_secret_key_123';
    Config::set('services.razorpay.key_secret', $secret);
    $service = new RazorpayService;

    $orderId = 'order_ABC123';
    $paymentId = 'pay_XYZ789';
    $validSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

    expect($service->verifySignature($orderId, $paymentId, $validSignature))->toBeTrue()
        ->and($service->verifySignature($orderId, $paymentId, 'invalid_sig'))->toBeFalse();
});

test('correctly identifies real vs dummy credentials', function () {
    Config::set('services.razorpay.key_id', 'rzp_test_dummy_key_id');
    Config::set('services.razorpay.key_secret', 'rzp_test_dummy_key_secret');
    $service = new RazorpayService;

    expect($service->hasRealCredentials())->toBeFalse();

    Config::set('services.razorpay.key_id', 'rzp_test_real123');
    Config::set('services.razorpay.key_secret', 'real_secret_456');

    expect($service->hasRealCredentials())->toBeTrue();
});
