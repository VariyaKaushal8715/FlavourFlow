<?php

use App\Models\Product;
use App\Models\User;

test('guests are redirected to the public login page before saving cart items', function () {
    $product = Product::factory()->create([
        'name' => 'Sunrise Chilli Blend',
        'slug' => 'sunrise-chilli-blend',
    ]);

    $this->post(route('cart.store', $product), [
        'quantity' => 2,
    ])->assertRedirect(route('login'));
});

test('authenticated users can add a product to the cart with a saved snapshot', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Royal Garam Masala',
        'sku' => 'FF-RGM-100',
        'category' => 'Signature blend',
        'unit' => '100 g',
        'price' => '149.00',
        'slug' => 'royal-garam-masala',
    ]);

    $this->actingAs($user)
        ->post(route('cart.store', $product), [
            'quantity' => 3,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->getKey(),
        'product_id' => $product->getKey(),
        'product_name' => 'Royal Garam Masala',
        'product_slug' => 'royal-garam-masala',
        'sku' => 'FF-RGM-100',
        'category' => 'Signature blend',
        'unit' => '100 g',
        'quantity' => 3,
        'unit_price' => '149.00',
        'line_total' => '447.00',
    ]);
});

test('authenticated users can save a product to the wishlist with product details', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Monsoon Chai Spice',
        'sku' => 'FF-MCS-100',
        'category' => 'Everyday blend',
        'unit' => '100 g',
        'price' => '129.00',
        'slug' => 'monsoon-chai-spice',
    ]);

    $this->actingAs($user)
        ->post(route('wishlist.store', $product))
        ->assertRedirect();

    $this->assertDatabaseHas('wishlist_items', [
        'user_id' => $user->getKey(),
        'product_id' => $product->getKey(),
        'product_name' => 'Monsoon Chai Spice',
        'product_slug' => 'monsoon-chai-spice',
        'sku' => 'FF-MCS-100',
        'category' => 'Everyday blend',
        'unit' => '100 g',
        'unit_price' => '129.00',
    ]);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
