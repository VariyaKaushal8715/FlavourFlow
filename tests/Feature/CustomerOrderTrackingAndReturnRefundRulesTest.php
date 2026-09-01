<?php

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Models\User;

test('cancelled order track page displays cancelled state and stops timeline', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Cancelled',
        'cancellation_reason' => 'Out of stock',
    ]);

    $response = $this->actingAs($user)
        ->get(route('account.orders.track', $order->order_number))
        ->assertSuccessful();

    $steps = $response->viewData('steps');
    expect($steps)->toHaveCount(2);
    expect($steps[0]['name'])->toBe('Confirmed');
    expect($steps[1]['name'])->toBe('Cancelled');
    expect($steps[1]['state'])->toBe('active');
});

test('delivered order track page shows all steps as completed', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('account.orders.track', $order->order_number))
        ->assertSuccessful();

    $steps = $response->viewData('steps');
    foreach ($steps as $step) {
        expect($step['state'])->toBe('completed');
    }
});

test('customer can submit either return or refund but not both', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
    ]);

    // Submit Return request
    $this->actingAs($user)
        ->post(route('account.orders.return', $order->order_number), [
            'reason' => 'Defective',
        ])
        ->assertRedirect();

    expect(ReturnRequest::where('order_id', $order->id)->exists())->toBeTrue();

    // Try to submit Refund request -> should fail
    $this->actingAs($user)
        ->post(route('account.orders.refund', $order->order_number), [
            'reason' => 'Refund wanted',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(RefundRequest::where('order_id', $order->id)->exists())->toBeFalse();
});

test('accepted return or refund locks order status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create([
        'status' => 'Delivered',
    ]);

    $returnReq = ReturnRequest::create([
        'order_id' => $order->id,
        'reason' => 'Bad scent',
        'status' => 'Approved', // Approved by admin
    ]);

    // Try to update status -> should be locked and rejected
    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Shipped',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    $order->refresh();
    expect($order->status)->toBe('Delivered');
});
