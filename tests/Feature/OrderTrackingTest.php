<?php

use App\Models\Order;
use App\Models\User;

test('guests are redirected to login for orders and tracking pages', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_id' => 'ORD-12345678',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total' => 250,
    ]);

    $this->get(route('account.orders'))
        ->assertRedirect(route('login'));

    $this->get(route('account.orders.show', $order->order_id))
        ->assertRedirect(route('login'));

    $this->get(route('account.orders.track', $order->order_id))
        ->assertRedirect(route('login'));
});

test('authenticated user can view their own orders', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_id' => 'ORD-20260823-111111',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total' => 250,
    ]);

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertSuccessful()
        ->assertSee('ORD-20260823-111111')
        ->assertSee('Rs. 250.00');

    $this->actingAs($user)
        ->get(route('account.orders.show', $order->order_id))
        ->assertSuccessful()
        ->assertSee('ORD-20260823-111111')
        ->assertSee('Rs. 250.00')
        ->assertSee('123 Main St');
});

test('user cannot view or track another users order', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $order = Order::create([
        'order_id' => 'ORD-20260823-222222',
        'user_id' => $user1->id, // Owned by user 1
        'status' => 'Confirmed',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total' => 250,
    ]);

    // User 2 trying to show or track should be forbidden (403)
    $this->actingAs($user2)
        ->get(route('account.orders.show', $order->order_id))
        ->assertForbidden();

    $this->actingAs($user2)
        ->get(route('account.orders.track', $order->order_id))
        ->assertForbidden();
});

test('tracking page renders timeline statuses', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_id' => 'ORD-20260823-333333',
        'user_id' => $user->id,
        'status' => 'Shipped', // Current state
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total' => 250,
    ]);

    $this->actingAs($user)
        ->get(route('account.orders.track', $order->order_id))
        ->assertSuccessful()
        ->assertSee('Confirmed')
        ->assertSee('Shipped')
        ->assertSee('Out for Delivery')
        ->assertSee('Current Status:')
        ->assertSee('Shipped');
});
