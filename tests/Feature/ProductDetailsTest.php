<?php

use App\Models\Product;

test('a customer can open a product and see its full details', function () {
    $product = Product::factory()->create([
        'name' => 'Royal Test Masala',
        'slug' => 'royal-test-masala',
        'description' => 'Short attractive card copy.',
        'long_description' => 'A detailed story shown only on the complete product page.',
        'highlights' => ['Freshly packed', 'No artificial colours'],
        'ingredients' => 'Coriander, cumin, and aromatic spices.',
        'usage_instructions' => 'Add one teaspoon near the end of cooking.',
        'origin' => 'Gujarat, India',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('products.show', $product))
        ->assertSee('Short attractive card copy.')
        ->assertDontSee('A detailed story shown only on the complete product page.');

    $this->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee('data-refresh-policy="preserve"', false)
        ->assertSee('Royal Test Masala')
        ->assertSee('A detailed story shown only on the complete product page.')
        ->assertSee('Freshly packed')
        ->assertSee('Coriander, cumin, and aromatic spices.')
        ->assertSee('Add one teaspoon near the end of cooking.')
        ->assertSee('Gujarat, India');
});

test('inactive products cannot be opened publicly', function () {
    $product = Product::factory()->inactive()->create();

    $this->get(route('products.show', $product))
        ->assertNotFound();
});
