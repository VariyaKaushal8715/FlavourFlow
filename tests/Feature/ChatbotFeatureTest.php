<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

test('chatbot endpoint responds to product inquiries and price queries', function (): void {
    Product::factory()->create([
        'name' => 'Royal Garam Masala',
        'price' => 149.00,
        'quantity' => 20,
        'is_active' => true,
        'description' => 'Rich aromatic blend for curries.',
    ]);

    $response = $this->postJson(route('api.chatbot'), [
        'message' => 'Tell me about Royal Garam Masala',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('reply'))->toContain('Royal Garam Masala');
});

test('chatbot recommends products under a budget constraint', function (): void {
    Product::factory()->create([
        'name' => 'Kashmiri Red Chilli Powder',
        'price' => 199.00,
        'quantity' => 15,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'name' => 'Expensive Saffron Jar',
        'price' => 899.00,
        'quantity' => 5,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('api.chatbot'), [
        'message' => 'Suggest something under 300',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('reply'))
        ->toContain('Kashmiri Red Chilli Powder')
        ->not->toContain('Expensive Saffron Jar');
});

test('authenticated user can query their own order status and delivery updates', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Turmeric Powder',
        'price' => 150.00,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-20260912-TESTAI',
        'status' => 'Shipped',
        'total_amount' => 350.00,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Turmeric Powder',
        'unit_price' => 150.00,
        'total_price' => 300.00,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($user)->postJson(route('api.chatbot'), [
        'message' => 'Where is my order ORD-20260912-TESTAI',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    expect($response->json('reply'))
        ->toContain('ORD-20260912-TESTAI')
        ->toContain('Shipped');
});

test('authenticated user cannot query another user order details', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $orderB = Order::factory()->create([
        'user_id' => $userB->id,
        'order_number' => 'ORD-SECRET-USERB',
        'status' => 'Delivered',
        'total_amount' => 500.00,
    ]);

    $response = $this->actingAs($userA)->postJson(route('api.chatbot'), [
        'message' => 'Show me status for ORD-SECRET-USERB',
    ]);

    $response->assertOk();

    // User A should NOT be able to view user B's order details
    expect($response->json('reply'))->not->toContain('ORD-SECRET-USERB');
});

test('guest user is instructed to sign in when asking about order status', function (): void {
    $response = $this->postJson(route('api.chatbot'), [
        'message' => 'What is my order delivery status?',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    expect($response->json('reply'))->toContain('sign in');
});

test('chatbot answers delivery charges intent directly without listing orders', function (): void {
    $user = User::factory()->create();
    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-DELIVERY-TEST',
        'status' => 'Shipped',
    ]);

    $response = $this->actingAs($user)->postJson(route('api.chatbot'), [
        'message' => 'Any delivery charges on product?',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    expect($response->json('reply'))
        ->toContain('FREE')
        ->toContain('500')
        ->not->toContain('ORD-DELIVERY-TEST');
});

test('chatbot prompts selection when multiple active orders exist', function (): void {
    $user = User::factory()->create();

    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-ACTIVE-1',
        'status' => 'Shipped',
        'created_at' => now()->subMinutes(10),
    ]);

    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-ACTIVE-2',
        'status' => 'Out for Delivery',
        'created_at' => now(),
    ]);

    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-OLD-DELIVERED',
        'status' => 'Delivered',
        'created_at' => now()->subDays(5),
    ]);

    $response = $this->actingAs($user)->postJson(route('api.chatbot'), [
        'message' => 'order status',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $reply = $response->json('reply');

    expect($reply)
        ->toContain('Which order would you like to check?')
        ->toContain('ORD-ACTIVE-1')
        ->toContain('ORD-ACTIVE-2')
        ->not->toContain('ORD-OLD-DELIVERED');
});

test('chatbot indicates no active orders if all orders are delivered or cancelled', function (): void {
    $user = User::factory()->create();

    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-DELIVERED-ONLY',
        'status' => 'Delivered',
    ]);

    $response = $this->actingAs($user)->postJson(route('api.chatbot'), [
        'message' => 'where is my order',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    expect($response->json('reply'))->toContain("You don't have any active orders to track.");
});
