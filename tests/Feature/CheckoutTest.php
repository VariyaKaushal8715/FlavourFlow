<?php

use App\Models\CartItem;
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

test('successful checkout clears cart and redirects to success page', function () {
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
        ->assertSessionHas('order_details');

    $this->assertDatabaseMissing('cart_items', [
        'user_id' => $user->id,
    ]);
});

