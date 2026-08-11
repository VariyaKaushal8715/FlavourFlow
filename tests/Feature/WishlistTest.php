<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

test('guests are redirected to login before viewing or changing wishlist products', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $this->get(route('wishlist.index'))
        ->assertRedirect(route('login'));

    $this->post(route('wishlist.store', $product))
        ->assertRedirect(route('login'));

    $this->assertDatabaseMissing('wishlists', [
        'product_id' => $product->id,
    ]);
});

test('an authenticated user can add a product to their wishlist only once', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);

    $this->actingAs($user)->postJson(route('wishlist.store', $product))
        ->assertSuccessful()
        ->assertJsonPath('wishlisted', true);

    $this->actingAs($user)->postJson(route('wishlist.store', $product))
        ->assertSuccessful()
        ->assertJsonPath('wishlisted', true);

    expect(Wishlist::query()
        ->where('user_id', $user->id)
        ->where('product_id', $product->id)
        ->count())->toBe(1);
});

test('an authenticated user can remove a product from their wishlist', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);
    Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)->deleteJson(route('wishlist.destroy', $product))
        ->assertSuccessful()
        ->assertJsonPath('wishlisted', false);

    $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
});

test('the wishlist page displays the authenticated users saved products', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Saved Masala', 'quantity' => 10]);
    Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)->get(route('wishlist.index'))
        ->assertSuccessful()
        ->assertSee('Saved Masala')
        ->assertSee('data-wishlisted="true"', false);
});
