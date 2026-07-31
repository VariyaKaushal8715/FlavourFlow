<?php

use App\Models\Product;

test('the session cart page is available without authentication', function () {
    $product = Product::factory()->create();

    $this->get(route('cart.index'))->assertSuccessful();
    $this->postJson(route('cart.store', $product))->assertSuccessful();
});

test('adding a product increases its session cart quantity', function () {
    $product = Product::factory()->create();

    $this->postJson(route('cart.store', $product), [
        'quantity' => 2,
        'selected_options' => ['grind' => 'fine'],
    ])->assertSuccessful()->assertJsonPath('count', 2);

    $this->postJson(route('cart.store', $product))
        ->assertSuccessful()
        ->assertJsonPath('count', 3);

    expect(session('cart.'.$product->slug.'.quantity'))->toBe(3);
});

test('cart page displays products stored in the session', function () {
    $product = Product::factory()->create(['name' => 'Customer Masala', 'price' => 120]);
    $this->withSession(['cart' => [$product->slug => ['quantity' => 2, 'selected_options' => null]]]);

    $this->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee('Customer Masala')
        ->assertSee('Rs. 240.00');
});
