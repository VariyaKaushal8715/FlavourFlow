<?php

use App\Models\Order;
use App\Models\User;

test('non admin users cannot access unread summary or mark viewed endpoints', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson(route('admin.orders.mark_viewed'))
        ->assertForbidden();
});

test('it returns unread summary with correct counts and formatted message for 1 order from 1 customer', function () {
    $admin = User::factory()->admin()->create([
        'admin_orders_last_viewed_at' => now()->subHour(),
    ]);

    $customer = User::factory()->create();
    Order::factory()->create([
        'user_id' => $customer->id,
        'created_at' => now()->subMinutes(10),
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertSuccessful()
        ->assertJson([
            'has_unread' => true,
            'order_count' => 1,
            'customer_count' => 1,
            'message' => 'You have 1 new order from 1 customer. Tap to view orders.',
            'orders_url' => route('admin.orders.index'),
        ]);
});

test('it returns unread summary with correct counts and formatted message for 3 orders from 3 customers', function () {
    $admin = User::factory()->admin()->create([
        'admin_orders_last_viewed_at' => now()->subHour(),
    ]);

    $customer1 = User::factory()->create();
    $customer2 = User::factory()->create();
    $customer3 = User::factory()->create();

    Order::factory()->create(['user_id' => $customer1->id, 'created_at' => now()->subMinutes(15)]);
    Order::factory()->create(['user_id' => $customer2->id, 'created_at' => now()->subMinutes(10)]);
    Order::factory()->create(['user_id' => $customer3->id, 'created_at' => now()->subMinutes(5)]);

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertSuccessful()
        ->assertJson([
            'has_unread' => true,
            'order_count' => 3,
            'customer_count' => 3,
            'message' => 'You have 3 new orders from 3 customers. Tap to view orders.',
            'orders_url' => route('admin.orders.index'),
        ]);
});

test('it returns unread summary with correct counts and formatted message for 3 orders from 1 customer', function () {
    $admin = User::factory()->admin()->create([
        'admin_orders_last_viewed_at' => now()->subHour(),
    ]);

    $customer = User::factory()->create();

    Order::factory()->create(['user_id' => $customer->id, 'created_at' => now()->subMinutes(15)]);
    Order::factory()->create(['user_id' => $customer->id, 'created_at' => now()->subMinutes(10)]);
    Order::factory()->create(['user_id' => $customer->id, 'created_at' => now()->subMinutes(5)]);

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertSuccessful()
        ->assertJson([
            'has_unread' => true,
            'order_count' => 3,
            'customer_count' => 1,
            'message' => 'You have 3 new orders from 1 customer. Tap to view orders.',
            'orders_url' => route('admin.orders.index'),
        ]);
});

test('it returns no unread orders when no new orders exist since last viewed', function () {
    $admin = User::factory()->admin()->create([
        'admin_orders_last_viewed_at' => now(),
    ]);

    $customer = User::factory()->create();
    Order::factory()->create([
        'user_id' => $customer->id,
        'created_at' => now()->subHour(),
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertSuccessful()
        ->assertJson([
            'has_unread' => false,
            'order_count' => 0,
            'customer_count' => 0,
            'message' => null,
            'orders_url' => route('admin.orders.index'),
        ]);
});

test('visiting the admin orders index page updates admin_orders_last_viewed_at and clears unread summary', function () {
    $admin = User::factory()->admin()->create([
        'admin_orders_last_viewed_at' => now()->subHour(),
    ]);

    $customer = User::factory()->create();
    Order::factory()->create([
        'user_id' => $customer->id,
        'created_at' => now()->subMinutes(10),
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertJson([
            'has_unread' => true,
            'order_count' => 1,
        ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful();

    expect($admin->fresh()->admin_orders_last_viewed_at)->not->toBeNull();

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertJson([
            'has_unread' => false,
            'order_count' => 0,
        ]);
});

test('it allows marking unread orders as viewed via mark-viewed endpoint', function () {
    $admin = User::factory()->admin()->create([
        'admin_orders_last_viewed_at' => now()->subHour(),
    ]);

    $customer = User::factory()->create();
    Order::factory()->create([
        'user_id' => $customer->id,
        'created_at' => now()->subMinutes(10),
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.orders.mark_viewed'))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);

    $this->actingAs($admin)
        ->getJson(route('admin.orders.unread_summary'))
        ->assertJson([
            'has_unread' => false,
            'order_count' => 0,
        ]);
});
