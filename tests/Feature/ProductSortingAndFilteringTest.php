<?php

use App\Models\Product;

test('home page sorts products by featured by default', function () {
    Product::factory()->create(['name' => 'Cardamom Regular', 'price' => 200, 'priority' => 10, 'is_featured' => false, 'is_active' => true]);
    Product::factory()->create(['name' => 'Kashmiri Saffron', 'price' => 500, 'priority' => 99, 'is_featured' => true, 'is_active' => true]);

    $response = $this->get(route('home'));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Kashmiri Saffron');
});

test('home page sorts products by top rated', function () {
    Product::factory()->create(['name' => 'Low Rated Pepper', 'rating' => 3.2, 'is_active' => true]);
    Product::factory()->create(['name' => 'High Rated Turmeric', 'rating' => 4.9, 'is_active' => true]);

    $response = $this->get(route('home', ['sort' => 'rating']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('High Rated Turmeric');
});

test('home page sorts products by price low to high', function () {
    Product::factory()->create(['name' => 'Expensive Spice', 'price' => 900, 'is_active' => true]);
    Product::factory()->create(['name' => 'Affordable Cumin', 'price' => 80, 'is_active' => true]);

    $response = $this->get(route('home', ['sort' => 'price_asc']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Affordable Cumin');
});

test('home page sorts products by price high to low', function () {
    Product::factory()->create(['name' => 'Affordable Cumin', 'price' => 80, 'is_active' => true]);
    Product::factory()->create(['name' => 'Expensive Spice', 'price' => 900, 'is_active' => true]);

    $response = $this->get(route('home', ['sort' => 'price_desc']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Expensive Spice');
});

test('home page sorts products by name A to Z', function () {
    Product::factory()->create(['name' => 'Zaffron Special', 'price' => 300, 'is_active' => true]);
    Product::factory()->create(['name' => 'Anise Star', 'price' => 200, 'is_active' => true]);

    $response = $this->get(route('home', ['sort' => 'name']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Anise Star');
});

test('home page sorts products by newest arrivals', function () {
    $old = Product::factory()->create([
        'name' => 'Old Legacy Spice',
        'created_at' => now()->subDays(10),
        'is_active' => true,
    ]);
    $new = Product::factory()->create([
        'name' => 'Brand New Arrival',
        'created_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->get(route('home', ['sort' => 'newest']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Brand New Arrival');
});

test('home page filters products by price range', function () {
    Product::factory()->create(['name' => 'Budget Chilli', 'price' => 50, 'is_active' => true]);
    Product::factory()->create(['name' => 'Mid Cinnamon', 'price' => 250, 'is_active' => true]);
    Product::factory()->create(['name' => 'Luxury Saffron', 'price' => 950, 'is_active' => true]);

    $response = $this->get(route('home', ['min_price' => 100, 'max_price' => 500]));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products)->toHaveCount(1);
    expect($products[0]['name'])->toBe('Mid Cinnamon');
});

test('home page handles combination of sort and price range filtering', function () {
    Product::factory()->create(['name' => 'Mid Cinnamon High Rated', 'price' => 300, 'rating' => 4.9, 'is_active' => true]);
    Product::factory()->create(['name' => 'Mid Cardamom Lower Rated', 'price' => 200, 'rating' => 4.0, 'is_active' => true]);
    Product::factory()->create(['name' => 'Out of Range Luxury', 'price' => 1200, 'rating' => 5.0, 'is_active' => true]);

    $response = $this->get(route('home', [
        'sort' => 'rating',
        'min_price' => 100,
        'max_price' => 500,
    ]));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products)->toHaveCount(2);
    expect($products[0]['name'])->toBe('Mid Cinnamon High Rated');
    expect($products[1]['name'])->toBe('Mid Cardamom Lower Rated');
});

test('home page handles sort option price_range with min and max bounds', function () {
    Product::factory()->create(['name' => 'Cheap Star', 'price' => 50, 'is_active' => true]);
    Product::factory()->create(['name' => 'In Range Cumin', 'price' => 220, 'is_active' => true]);
    Product::factory()->create(['name' => 'In Range Fennel', 'price' => 180, 'is_active' => true]);
    Product::factory()->create(['name' => 'Expensive Gold', 'price' => 900, 'is_active' => true]);

    $response = $this->get(route('home', [
        'sort' => 'price_range',
        'min_price' => 100,
        'max_price' => 300,
    ]));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products)->toHaveCount(2);
    expect($products[0]['name'])->toBe('In Range Fennel'); // 180 before 220 (price ascending)
    expect($products[1]['name'])->toBe('In Range Cumin');
});

test('home page shows empty state when no products match price range', function () {
    Product::factory()->create(['name' => 'Standard Product', 'price' => 100, 'is_active' => true]);

    $response = $this->get(route('home', ['min_price' => 800, 'max_price' => 1000]));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products)->toBeEmpty();
    $response->assertSee(__('ui.no_products_found'));
});
