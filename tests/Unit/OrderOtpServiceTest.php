<?php

use App\Mail\OrderConfirmationOtp;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OrderOtpService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

test('cannot generate or send order confirmation OTP for unverified pending order', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-OTP-PENDING-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Aarav Patel',
        'mobile' => '9876543210',
        'email' => 'aarav@example.com',
        'address' => 'Heritage Square',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $service = app(OrderOtpService::class);

    expect(fn () => $service->generateAndSendOrderOtp($order))
        ->toThrow(DomainException::class, 'Cannot generate or send order confirmation OTP: payment is not yet verified.');

    Mail::assertNothingSent();
    expect($order->fresh()->order_confirmation_otp)->toBeNull();
});

test('generates 6 digit numeric OTP and sends email when order is verified and paid', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-OTP-CONFIRMED-01',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'payment_status' => 'paid',
        'name' => 'Diya Shah',
        'mobile' => '9888877777',
        'email' => 'diya@example.com',
        'address' => 'Green Heights',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 600,
        'delivery_charge' => 0,
        'total_amount' => 600,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'captured',
        'amount' => 600,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $service = app(OrderOtpService::class);
    $result = $service->generateAndSendOrderOtp($order);

    expect($result)->toBeTrue();

    $refreshedOrder = $order->fresh();
    expect($refreshedOrder->order_confirmation_otp)->not->toBeNull()
        ->and(strlen($refreshedOrder->order_confirmation_otp))->toBe(6)
        ->and(ctype_digit($refreshedOrder->order_confirmation_otp))->toBeTrue()
        ->and($refreshedOrder->otp_expires_at)->not->toBeNull()
        ->and($refreshedOrder->otp_expires_at->isFuture())->toBeTrue()
        ->and($refreshedOrder->otp_sent_at)->not->toBeNull();

    Mail::assertSent(OrderConfirmationOtp::class, function ($mail) use ($refreshedOrder) {
        return $mail->hasTo('diya@example.com')
            && $mail->otp === $refreshedOrder->order_confirmation_otp
            && $mail->order->id === $refreshedOrder->id;
    });
});

test('verifyOtp validates correct code and rejects incorrect code', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-OTP-VERIFY-01',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'payment_status' => 'paid',
        'name' => 'Diya Shah',
        'mobile' => '9888877777',
        'email' => 'diya@example.com',
        'address' => 'Green Heights',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 600,
        'delivery_charge' => 0,
        'total_amount' => 600,
        'order_confirmation_otp' => '543210',
        'otp_expires_at' => now()->addMinutes(15),
        'otp_sent_at' => now(),
    ]);

    $service = app(OrderOtpService::class);

    // Incorrect code
    expect($service->verifyOtp($order, '123456'))->toBeFalse()
        ->and($order->fresh()->otp_verified_at)->toBeNull();

    // Correct code
    expect($service->verifyOtp($order, '543210'))->toBeTrue()
        ->and($order->fresh()->otp_verified_at)->not->toBeNull();
});

test('verifyOtp rejects expired code', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-OTP-EXPIRED-01',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'payment_status' => 'paid',
        'name' => 'Diya Shah',
        'mobile' => '9888877777',
        'email' => 'diya@example.com',
        'address' => 'Green Heights',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 600,
        'delivery_charge' => 0,
        'total_amount' => 600,
        'order_confirmation_otp' => '654321',
        'otp_expires_at' => now()->subMinutes(5), // Expired
        'otp_sent_at' => now()->subMinutes(20),
    ]);

    $service = app(OrderOtpService::class);

    expect($service->verifyOtp($order, '654321'))->toBeFalse()
        ->and($order->fresh()->otp_verified_at)->toBeNull();
});
