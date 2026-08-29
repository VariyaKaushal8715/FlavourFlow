<?php

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Models\User;

test('guest cannot update order status', function () {
    $order = Order::factory()->create();

    $this->patch(route('admin.orders.updateStatus', $order), [
        'status' => 'Shipped',
    ])->assertRedirect(route('login'));
});

test('admin can update order status and timestamps are recorded', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'Confirmed']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Shipped',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Shipped');
    expect($order->shipped_at)->not->toBeNull();
});

test('admin cannot update order to invalid status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'Confirmed']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Delivered-and-Signed',
        ])
        ->assertSessionHasErrors(['status']);
});

test('admin can fetch latest orders for notifications including cancellations and requests', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // 1. New Pending Order
    $order1 = Order::factory()->create(['status' => 'Pending']);

    // 2. Cancelled Order
    $order2 = Order::factory()->create([
        'status' => 'Cancelled',
        'cancelled_at' => now(),
        'cancellation_reason' => 'User cancelled',
    ]);

    // 3. Return Request
    $order3 = Order::factory()->create(['status' => 'Delivered', 'delivered_at' => now()]);
    $returnReq = ReturnRequest::create([
        'order_id' => $order3->id,
        'reason' => 'Bad quality',
        'status' => 'Pending',
    ]);

    // 4. Refund Request
    $order4 = Order::factory()->create(['status' => 'Delivered', 'delivered_at' => now()]);
    $refundReq = RefundRequest::create([
        'order_id' => $order4->id,
        'amount' => $order4->total_amount,
        'reason' => 'Damaged',
        'status' => 'Pending',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('admin.api.newOrders'))
        ->assertSuccessful();

    $data = $response->json('orders');
    expect($data)->toHaveCount(4);

    // Verify type tags are prepended to names
    $types = array_column($data, 'name');
    expect($types)->toContain('[New Order] Placed by '.$order1->name);
    expect($types)->toContain('[Cancelled] '.$order2->name.' - User cancelled');
    expect($types)->toContain('[Return Request] (Pending) '.$order3->name.' - Bad quality');
    expect($types)->toContain('[Refund Request] (Pending) '.$order4->name.' - Damaged');
});

test('admin can accept order', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'Pending']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Confirmed',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Confirmed');
    expect($order->confirmed_at)->not->toBeNull();
});

test('admin can cancel pending order with reason', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'Pending']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Cancelled',
            'cancellation_reason' => 'Out of stock items',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Cancelled');
    expect($order->cancellation_reason)->toBe('Out of stock items');
    expect($order->cancelled_at)->not->toBeNull();
});

test('admin cannot change status of cancelled order', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create([
        'status' => 'Cancelled',
        'cancelled_at' => now(),
        'cancellation_reason' => 'Out of stock',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Shipped',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Cancelled'); // Remains Cancelled
});

test('admin can approve or deny return request', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'Delivered', 'delivered_at' => now()]);
    $returnReq = ReturnRequest::create([
        'order_id' => $order->id,
        'reason' => 'Bad quality',
        'status' => 'Pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.returnRequests.updateStatus', $returnReq), [
            'status' => 'Approved',
        ])
        ->assertRedirect();

    $returnReq->refresh();
    expect($returnReq->status)->toBe('Approved');
});

test('admin can approve or deny refund request', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create(['status' => 'Delivered', 'delivered_at' => now()]);
    $refundReq = RefundRequest::create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'reason' => 'Damaged',
        'status' => 'Pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.refundRequests.updateStatus', $refundReq), [
            'status' => 'Completed',
        ])
        ->assertRedirect();

    $refundReq->refresh();
    expect($refundReq->status)->toBe('Completed');
});
