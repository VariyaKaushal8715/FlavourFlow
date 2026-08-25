<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

test('unauthenticated guest cannot access reviews API', function () {
    $this->get(route('reviews.pending'))
        ->assertRedirect(route('login'));

    $this->post(route('reviews.store'), [
        'order_id' => 1,
        'product_id' => 1,
        'rating' => 5,
        'review_text' => 'Loved it!',
    ])->assertRedirect(route('login'));
});

test('user without orders has no pending reviews', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('reviews.pending'))
        ->assertSuccessful()
        ->assertJson([
            'has_pending' => false,
        ]);
});

test('user with order has pending reviews and can submit successfully with correct database persistence', function () {
    $user = User::factory()->create(['name' => 'Alice Tester']);
    $product = Product::factory()->create(['name' => 'Kashmiri Chili Powder', 'price' => 120, 'quantity' => 10, 'rating' => 0.0]);

    $order = Order::create([
        'order_number' => 'ORD-COMPLETED-999',
        'user_id' => $user->id,
        'status' => 'pending',
        'name' => 'Alice Tester',
        'mobile' => '9999999999',
        'email' => 'alice@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 120,
        'delivery_charge' => 50,
        'total_amount' => 170,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Kashmiri Chili Powder',
        'product_slug' => $product->slug,
        'unit' => '250g',
        'quantity' => 1,
        'unit_price' => 120,
        'total_price' => 120,
    ]);

    // Submit review
    $this->actingAs($user)
        ->postJson(route('reviews.store'), [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review_text' => 'High quality and very red!',
        ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);

    // Verify correct fields in the database
    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'user_name' => 'Alice Tester',
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Kashmiri Chili Powder',
        'unit' => '250g',
        'rating' => 5,
        'review_text' => 'High quality and very red!',
    ]);
});

test('cannot submit duplicate review for the same product and order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Turmeric Powder', 'price' => 80, 'quantity' => 10]);

    $order = Order::create([
        'order_number' => 'ORD-DUPLICATE',
        'user_id' => $user->id,
        'status' => 'pending',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 80,
        'delivery_charge' => 50,
        'total_amount' => 130,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'unit' => '100g',
        'quantity' => 1,
        'unit_price' => 80,
        'total_price' => 80,
    ]);

    // Submit first review
    $this->actingAs($user)
        ->postJson(route('reviews.store'), [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 4,
            'review_text' => 'Good high quality turmeric spice.',
        ])
        ->assertSuccessful();

    // Try submitting second duplicate review
    $this->actingAs($user)
        ->postJson(route('reviews.store'), [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review_text' => 'Trying to submit duplicate review.',
        ])
        ->assertStatus(400);
});

test('cannot review product that was not purchased in the order', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['name' => 'Coriander Powder', 'price' => 80]);
    $product2 = Product::factory()->create(['name' => 'Black Pepper', 'price' => 150]);

    $order = Order::create([
        'order_number' => 'ORD-UNPURCHASED',
        'user_id' => $user->id,
        'status' => 'pending',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 80,
        'delivery_charge' => 50,
        'total_amount' => 130,
    ]);

    // Purchased only product 1
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'product_name' => $product1->name,
        'product_slug' => $product1->slug,
        'unit' => '100g',
        'quantity' => 1,
        'unit_price' => 80,
        'total_price' => 80,
    ]);

    // Attempting to review product 2 (not in the order)
    $this->actingAs($user)
        ->postJson(route('reviews.store'), [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'rating' => 4,
            'review_text' => 'Trying to review product I did not buy.',
        ])
        ->assertStatus(403);
});

