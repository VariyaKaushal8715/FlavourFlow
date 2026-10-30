<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

test('checkout page hides demo simulated outcome UI by default', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 100.00,
        'quantity' => 10,
        'is_active' => true,
    ]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => 100.00,
        'line_total' => 200.00,
    ]);

    $response = $this->actingAs($user)
        ->get(route('checkout.index'));

    $response->assertOk();

    // Verify #demo-payment-ui has class hidden
    $response->assertSee('id="demo-payment-ui" class="hidden', false);
});

test('checkout page reveals demo outcome UI only when demo payment method is explicitly selected via old input', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 100.00,
        'quantity' => 10,
        'is_active' => true,
    ]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 100.00,
        'line_total' => 100.00,
    ]);

    // Simulate returning to checkout with old('payment_method' => 'demo')
    $response = $this->actingAs($user)
        ->withSession(['_old_input' => ['payment_method' => 'demo']])
        ->get(route('checkout.index'));

    $response->assertOk();

    // When demo is explicitly old-selected, demo UI is visible without hidden class
    $response->assertDontSee('id="demo-payment-ui" class="hidden', false);
    $response->assertSee('id="demo-payment-ui" class=" rounded-2xl', false);
});
