<?php

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Models\User;

test('guest cannot cancel, return, or refund orders', function () {
    $order = Order::factory()->create();

    $this->post(route('account.orders.cancel', $order->order_number), ['reason' => 'Changed my mind'])
        ->assertRedirect(route('login'));

    $this->post(route('account.orders.return', $order->order_number), ['reason' => 'Damaged item'])
        ->assertRedirect(route('login'));

    $this->post(route('account.orders.refund', $order->order_number), ['reason' => 'Never received'])
        ->assertRedirect(route('login'));
});

test('user can cancel their own confirmed order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Confirmed',
    ]);

    $this->actingAs($user)
        ->post(route('account.orders.cancel', $order->order_number), [
            'reason' => 'No longer need it',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Cancelled');
    expect($order->cancellation_reason)->toBe('No longer need it');
    expect($order->cancelled_at)->not->toBeNull();
});

test('user cannot cancel shipped, out for delivery, or delivered orders', function () {
    $user = User::factory()->create();

    foreach (['Shipped', 'Out for Delivery', 'Delivered'] as $status) {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
        ]);

        $this->actingAs($user)
            ->post(route('account.orders.cancel', $order->order_number), [
                'reason' => 'Cancel request',
            ])
            ->assertRedirect();

        $order->refresh();
        expect($order->status)->toBe($status); // Status remains unchanged
    }
});

test('user can request return or refund within 7 days of delivery, but not both', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now()->subDays(5),
    ]);

    // Return request
    $this->actingAs($user)
        ->post(route('account.orders.return', $order->order_number), [
            'reason' => 'Defective item',
        ])
        ->assertRedirect();

    expect(ReturnRequest::where('order_id', $order->id)->exists())->toBeTrue();
    expect(ReturnRequest::where('order_id', $order->id)->first()->reason)->toBe('Defective item');

    // Refund request should now fail
    $this->actingAs($user)
        ->post(route('account.orders.refund', $order->order_number), [
            'reason' => 'Item is bad',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(RefundRequest::where('order_id', $order->id)->exists())->toBeFalse();
});

test('user cannot request return or refund after 7 days of delivery', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now()->subDays(8),
    ]);

    // Return request
    $this->actingAs($user)
        ->post(route('account.orders.return', $order->order_number), [
            'reason' => 'Too late return',
        ])
        ->assertRedirect();

    expect(ReturnRequest::where('order_id', $order->id)->exists())->toBeFalse();

    // Refund request
    $this->actingAs($user)
        ->post(route('account.orders.refund', $order->order_number), [
            'reason' => 'Too late refund',
        ])
        ->assertRedirect();

    expect(RefundRequest::where('order_id', $order->id)->exists())->toBeFalse();
});

test('user cannot manage another users orders', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user1->id,
        'status' => 'Confirmed',
    ]);

    $this->actingAs($user2)
        ->post(route('account.orders.cancel', $order->order_number), [
            'reason' => 'Malicious cancel',
        ])
        ->assertStatus(403);
});
