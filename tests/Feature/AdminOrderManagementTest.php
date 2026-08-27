<?php

use App\Models\Order;
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

test('admin can fetch latest orders for notifications', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Order::factory()->create();

    $this->actingAs($admin)
        ->getJson(route('admin.api.newOrders'))
        ->assertSuccessful()
        ->assertJsonStructure([
            'orders' => [
                '*' => [
                    'id',
                    'order_number',
                    'name',
                    'total_amount',
                    'status',
                    'created_at',
                ]
            ]
        ]);
});

