<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

test('guests can add, update, and remove cart items with persistence after refresh', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $this->get(route('cart.index'))
        ->assertSuccessful();

    $this->postJson(route('cart.store', $product), [
        'quantity' => 2,
        'selected_options' => ['grind' => 'fine'],
    ])
        ->assertSuccessful()
        ->assertJsonPath('count', 2);

    $this->postJson(route('cart.store', $product))
        ->assertSuccessful()
        ->assertJsonPath('count', 3);

    $this->get(route('cart.summary'))
        ->assertSuccessful()
        ->assertJsonPath('count', 3);

    $this->patchJson(route('cart.update', $product), [
        'quantity' => 4,
    ])
        ->assertSuccessful()
        ->assertJsonPath('quantity', 4)
        ->assertJsonPath('line_total', number_format((float) $product->price * 4, 2, '.', ''));

    $this->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee($product->name)
        ->assertSee('Rs. '.number_format((float) $product->price * 4, 2));

    $this->deleteJson(route('cart.destroy', $product))
        ->assertSuccessful()
        ->assertJsonPath('count', 0);

    $this->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee('Your cart is empty.');
});

test('an authenticated user can add products to their saved cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);

    $this->actingAs($user)->postJson(route('cart.store', $product), [
        'quantity' => 2,
        'selected_options' => ['grind' => 'fine'],
    ])
        ->assertSuccessful()
        ->assertJsonPath('count', 2);

    $this->actingAs($user)->postJson(route('cart.store', $product))
        ->assertSuccessful()
        ->assertJsonPath('count', 3);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);
});

test('an authenticated user can update and remove saved cart items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 120, 'quantity' => 10]);
    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)->patchJson(route('cart.update', $product), [
        'quantity' => 4,
    ])
        ->assertSuccessful()
        ->assertJsonPath('quantity', 4)
        ->assertJsonPath('line_total', '480.00');

    $this->actingAs($user)->deleteJson(route('cart.destroy', $product))
        ->assertSuccessful()
        ->assertJsonPath('count', 0);

    $this->assertDatabaseMissing('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

test('the cart page displays saved database items for the authenticated user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Customer Masala', 'price' => 120, 'quantity' => 10]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee('Customer Masala')
        ->assertSee('Rs. 240.00');
});
