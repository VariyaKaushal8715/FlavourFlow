<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSortOption;

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

test('home page loads sort options dynamically from database', function () {
    $response = $this->get(route('home'));
    $response->assertSuccessful();

    $sortOptions = $response->viewData('sortOptions');
    expect($sortOptions)->not->toBeEmpty();
    expect($sortOptions->pluck('key')->all())->toContain('featured', 'price_asc', 'price_desc', 'rating');
});

test('disabled sort option disappears from home page and falls back to default', function () {
    ProductSortOption::where('key', 'newest')->update(['is_active' => false]);

    $response = $this->get(route('home'));
    $response->assertSuccessful();

    $sortOptions = $response->viewData('sortOptions');
    expect($sortOptions->pluck('key')->all())->not->toContain('newest');

    // Accessing disabled sort falls back to default
    $responseWithDisabled = $this->get(route('home', ['sort' => 'newest']));
    $responseWithDisabled->assertSuccessful();
    expect($responseWithDisabled->viewData('sort'))->toBe('featured');
});

test('newly created sort option appears on home page and sorts correctly', function () {
    Product::factory()->create(['name' => 'Low Priority Herb', 'priority' => 10, 'is_active' => true]);
    Product::factory()->create(['name' => 'High Priority Spice', 'priority' => 95, 'is_active' => true]);

    ProductSortOption::create([
        'key' => 'staff_priority',
        'label' => 'Staff Priority Picks',
        'sort_field' => 'priority',
        'sort_direction' => 'desc',
        'display_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->get(route('home', ['sort' => 'staff_priority']));
    $response->assertSuccessful();

    $sortOptions = $response->viewData('sortOptions');
    expect($sortOptions->pluck('key')->all())->toContain('staff_priority');

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('High Priority Spice');
});

test('best selling sort option sorts products by order item quantity sold', function () {
    $product1 = Product::factory()->create(['name' => 'Low Seller Clove', 'is_active' => true]);
    $product2 = Product::factory()->create(['name' => 'Top Seller Cardamom', 'is_active' => true]);

    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'quantity' => 25,
    ]);

    $response = $this->get(route('home', ['sort' => 'best_selling']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Top Seller Cardamom');
});

test('discount sort option sorts products by biggest discount amount', function () {
    Product::factory()->create([
        'name' => 'Small Discount Pepper',
        'price' => 200,
        'compare_at_price' => 220, // 20 discount
        'is_active' => true,
    ]);
    Product::factory()->create([
        'name' => 'Huge Discount Saffron',
        'price' => 500,
        'compare_at_price' => 1000, // 500 discount
        'is_active' => true,
    ]);

    $response = $this->get(route('home', ['sort' => 'discount']));
    $response->assertSuccessful();

    $products = $response->viewData('products');
    expect($products[0]['name'])->toBe('Huge Discount Saffron');
});

test('display order in database controls sort options ordering on home page', function () {
    ProductSortOption::where('key', 'price_desc')->update(['display_order' => 1]);
    ProductSortOption::where('key', 'featured')->update(['display_order' => 99]);

    $response = $this->get(route('home'));
    $response->assertSuccessful();

    $sortOptions = $response->viewData('sortOptions');
    expect($sortOptions->first()->key)->toBe('price_desc');
});

test('sort products UI contains filter form, custom trigger, options, sliders, and apply button', function () {
    $response = $this->get(route('home'));
    $response->assertSuccessful();

    $response->assertSee('id="product-filter-form"', false);
    $response->assertSee('id="custom-sort-trigger"', false);
    $response->assertSee('id="product-sort-input"', false);
    $response->assertSee('id="sort-dropdown-menu"', false);
    $response->assertSee('id="filter-apply-btn"', false);
    $response->assertSee('id="range-min-slider"', false);
    $response->assertSee('id="range-max-slider"', false);
    $response->assertSee(__('ui.apply'));
    $response->assertSee(__('ui.sort_products'));
});

test('all nine standard sorting options correctly order multiple products', function () {
    ProductSortOption::seedDefaults();

    $p1 = Product::factory()->create([
        'name' => 'A Kashmiri Saffron',
        'price' => 500,
        'compare_at_price' => 700, // discount: 200
        'rating' => 4.9,
        'is_featured' => true,
        'priority' => 10,
        'created_at' => now()->subDays(5),
        'is_active' => true,
    ]);
    $p2 = Product::factory()->create([
        'name' => 'B Organic Turmeric',
        'price' => 100,
        'compare_at_price' => 110, // discount: 10
        'rating' => 4.2,
        'is_featured' => false,
        'priority' => 20,
        'created_at' => now()->subDays(1),
        'is_active' => true,
    ]);
    $p3 = Product::factory()->create([
        'name' => 'C Black Pepper',
        'price' => 300,
        'compare_at_price' => 600, // discount: 300
        'rating' => 3.8,
        'is_featured' => false,
        'priority' => 5,
        'created_at' => now()->subDays(10),
        'is_active' => true,
    ]);

    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $p3->id, 'quantity' => 50]);
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $p1->id, 'quantity' => 20]);
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $p2->id, 'quantity' => 5]);

    // 1. featured
    $resFeatured = $this->get(route('home', ['sort' => 'featured']));
    $namesFeatured = collect($resFeatured->viewData('products'))->pluck('name')->all();
    expect($namesFeatured[0])->toBe('A Kashmiri Saffron');

    // 2. rating (desc)
    $resRating = $this->get(route('home', ['sort' => 'rating']));
    $namesRating = collect($resRating->viewData('products'))->pluck('name')->all();
    expect($namesRating)->toBe(['A Kashmiri Saffron', 'B Organic Turmeric', 'C Black Pepper']);

    // 3. price_asc
    $resPriceAsc = $this->get(route('home', ['sort' => 'price_asc']));
    $namesPriceAsc = collect($resPriceAsc->viewData('products'))->pluck('name')->all();
    expect($namesPriceAsc)->toBe(['B Organic Turmeric', 'C Black Pepper', 'A Kashmiri Saffron']);

    // 4. price_desc
    $resPriceDesc = $this->get(route('home', ['sort' => 'price_desc']));
    $namesPriceDesc = collect($resPriceDesc->viewData('products'))->pluck('name')->all();
    expect($namesPriceDesc)->toBe(['A Kashmiri Saffron', 'C Black Pepper', 'B Organic Turmeric']);

    // 5. name (A-Z)
    $resName = $this->get(route('home', ['sort' => 'name']));
    $namesName = collect($resName->viewData('products'))->pluck('name')->all();
    expect($namesName)->toBe(['A Kashmiri Saffron', 'B Organic Turmeric', 'C Black Pepper']);

    // 6. newest
    $resNewest = $this->get(route('home', ['sort' => 'newest']));
    $namesNewest = collect($resNewest->viewData('products'))->pluck('name')->all();
    expect($namesNewest)->toBe(['B Organic Turmeric', 'A Kashmiri Saffron', 'C Black Pepper']);

    // 7. best_selling
    $resBestSelling = $this->get(route('home', ['sort' => 'best_selling']));
    $namesBestSelling = collect($resBestSelling->viewData('products'))->pluck('name')->all();
    expect($namesBestSelling)->toBe(['C Black Pepper', 'A Kashmiri Saffron', 'B Organic Turmeric']);

    // 8. discount
    $resDiscount = $this->get(route('home', ['sort' => 'discount']));
    $namesDiscount = collect($resDiscount->viewData('products'))->pluck('name')->all();
    expect($namesDiscount)->toBe(['C Black Pepper', 'A Kashmiri Saffron', 'B Organic Turmeric']);

    // 9. price_range with bounds
    $resPriceRange = $this->get(route('home', ['sort' => 'price_range', 'min_price' => 150, 'max_price' => 550]));
    $namesPriceRange = collect($resPriceRange->viewData('products'))->pluck('name')->all();
    expect($namesPriceRange)->toBe(['C Black Pepper', 'A Kashmiri Saffron']);
});
