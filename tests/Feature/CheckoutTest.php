<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('guests are redirected to login before viewing checkout', function () {
    $this->get(route('checkout.index'))
        ->assertRedirect(route('login'));

    $this->post(route('checkout.store'))
        ->assertRedirect(route('login'));
});

test('authenticated user with empty cart is redirected to cart page with error', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('checkout.index'))
        ->assertRedirect(route('cart.index'));

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'name' => 'John Doe',
            'mobile' => '+91 9999999999',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'country' => 'India',
            'payment_method' => 'cod',
        ])
        ->assertRedirect(route('cart.index'));
});

test('authenticated user with items can view checkout page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => $product->price,
        'line_total' => $product->price * 2,
        'image_path' => $product->image_path,
    ]);

    $this->actingAs($user)
        ->get(route('checkout.index'))
        ->assertSuccessful()
        ->assertSee($product->name)
        ->assertSee('Rs. 200.00');
});

test('checkout validation rules are enforced', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => $product->price,
        'line_total' => $product->price,
        'image_path' => $product->image_path,
    ]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [])
        ->assertSessionHasErrors(['name', 'mobile', 'email', 'address', 'city', 'state', 'pincode', 'country', 'payment_method']);
});

test('successful checkout saves order, clear cart, reduces stock, and redirects', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => $product->price,
        'line_total' => $product->price * 2,
        'image_path' => $product->image_path,
    ]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'name' => 'John Doe',
            'mobile' => '9999999999',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'country' => 'India',
            'payment_method' => 'cod',
        ])
        ->assertRedirect(route('checkout.success'))
        ->assertSessionHas('placed_order_id');

    // Assert cart cleared
    $this->assertDatabaseMissing('cart_items', [
        'user_id' => $user->id,
    ]);

    // Assert order inserted
    $order = Order::query()->where('user_id', $user->id)->first();
    expect($order)->not->toBeNull();
    expect($order->status)->toBe('Confirmed');
    expect($order->name)->toBe('John Doe');

    // Assert order items inserted
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    // Assert product stock reduced (10 - 2 = 8)
    $product->refresh();
    expect($product->quantity)->toBe(8);
});

test('checkout fails and redirects back to cart if product is out of stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 1]); // Only 1 in stock

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 3, // Requesting 3 (greater than 1)
        'unit_price' => $product->price,
        'line_total' => $product->price * 3,
        'image_path' => $product->image_path,
    ]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'name' => 'John Doe',
            'mobile' => '9999999999',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'country' => 'India',
            'payment_method' => 'cod',
        ])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors(['cart']);

    // Assert stock remains unchanged
    $product->refresh();
    expect($product->quantity)->toBe(1);

    // Assert cart items are not cleared
    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});
