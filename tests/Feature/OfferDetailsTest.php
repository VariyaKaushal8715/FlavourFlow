<?php

use App\Models\Offer;
use App\Models\Product;

test('a customer can open an active offer from the homepage', function () {
    $offer = Offer::factory()->create([
        'title' => 'Monsoon kitchen savings',
        'description' => 'Full campaign details for rainy-day cooking.',
        'terms' => 'Valid while stocks last.',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('offers.show', $offer));

    $this->get(route('offers.show', $offer))
        ->assertSuccessful()
        ->assertSee('data-refresh-policy="home"', false)
        ->assertSee('Monsoon kitchen savings')
        ->assertSee('Full campaign details for rainy-day cooking.')
        ->assertSee('Valid while stocks last.');
});

test('a pick three offer recommends the three best active in-stock products', function () {
    $offer = Offer::factory()->create([
        'title' => 'Build your everyday shelf',
        'description' => 'Pick any three spices for the week.',
        'discount_label' => 'Pick 3 & save',
    ]);

    Product::factory()->create([
        'name' => 'Featured Choice',
        'is_featured' => true,
        'rating' => 4.1,
        'priority' => 10,
        'quantity' => 10,
    ]);
    Product::factory()->create([
        'name' => 'Highest Rated Choice',
        'is_featured' => false,
        'rating' => 4.9,
        'priority' => 20,
        'quantity' => 10,
    ]);
    Product::factory()->create([
        'name' => 'Priority Choice',
        'is_featured' => false,
        'rating' => 4.8,
        'priority' => 90,
        'quantity' => 10,
    ]);
    Product::factory()->create([
        'name' => 'Fourth Choice',
        'is_featured' => false,
        'rating' => 4.7,
        'priority' => 100,
        'quantity' => 10,
    ]);
    Product::factory()->create([
        'name' => 'Out Of Stock Choice',
        'is_featured' => true,
        'rating' => 5,
        'priority' => 100,
        'quantity' => 0,
    ]);

    $this->get(route('offers.show', $offer))
        ->assertSuccessful()
        ->assertSee('Pick these 3 products.')
        ->assertSee('Featured Choice')
        ->assertSee('Highest Rated Choice')
        ->assertSee('Priority Choice')
        ->assertDontSee('Fourth Choice')
        ->assertDontSee('Out Of Stock Choice');
});

test('inactive and expired offers cannot be opened publicly', function () {
    $inactiveOffer = Offer::factory()->inactive()->create();
    $expiredOffer = Offer::factory()->create([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subDay(),
    ]);

    $this->get(route('offers.show', $inactiveOffer))->assertNotFound();
    $this->get(route('offers.show', $expiredOffer))->assertNotFound();
});
