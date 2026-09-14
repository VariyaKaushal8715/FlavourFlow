<?php

use App\Models\CartItem;
use App\Models\DeliverySetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('admin can create a product with default deliver everywhere mode', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.products.store'), [
            'name' => 'Everywhere Masala',
            'sku' => 'FF-EVE-01',
            'category' => 'Blended Spices',
            'unit' => '100 g',
            'description' => 'Available for delivery across all destinations.',
            'badge' => 'New',
            'price' => '149.00',
            'quantity' => '50',
            'low_stock_threshold' => '5',
            'rating' => '4.8',
            'priority' => '80',
            'is_featured' => '0',
            'is_active' => '1',
            'delivery_mode' => 'all',
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status');

    $product = Product::query()->where('sku', 'FF-EVE-01')->firstOrFail();
    expect($product->delivery_mode)->toBe('all')
        ->and($product->deliverable_locations)->toBeNull()
        ->and($product->isDeliverableTo('India', 'Gujarat', 'Ahmedabad'))->toBeTrue()
        ->and($product->isDeliverableTo('United States'))->toBeTrue();
});

test('admin can create a product with specific deliverable locations', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $locations = [
        ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Surat'],
        ['country' => 'India', 'state' => 'Maharashtra', 'city' => 'Mumbai'],
        ['country' => 'United States', 'state' => null, 'city' => null],
    ];

    $this->actingAs($admin)
        ->post(route('admin.products.store'), [
            'name' => 'Surat Special Masala',
            'sku' => 'FF-SUR-01',
            'category' => 'Regional Specialty',
            'unit' => '250 g',
            'description' => 'Delivered exclusively to select cities and countries.',
            'badge' => 'Special',
            'price' => '299.00',
            'quantity' => '30',
            'low_stock_threshold' => '5',
            'rating' => '4.9',
            'priority' => '90',
            'is_featured' => '0',
            'is_active' => '1',
            'delivery_mode' => 'specific',
            'deliverable_locations' => $locations,
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status');

    $product = Product::query()->where('sku', 'FF-SUR-01')->firstOrFail();
    expect($product->delivery_mode)->toBe('specific')
        ->and($product->deliverable_locations)->toHaveCount(3)
        ->and($product->isDeliverableTo('India', 'Gujarat', 'Surat'))->toBeTrue()
        ->and($product->isDeliverableTo('India', 'Maharashtra', 'Mumbai'))->toBeTrue()
        ->and($product->isDeliverableTo('United States'))->toBeTrue()
        ->and($product->isDeliverableTo('India', 'Gujarat', 'Ahmedabad'))->toBeFalse()
        ->and($product->isDeliverableTo('United Kingdom'))->toBeFalse();
});

test('admin can update deliverable locations on an existing product', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'name' => 'Cardamom Pods',
        'sku' => 'FF-CRD-01',
        'delivery_mode' => 'all',
        'deliverable_locations' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.products.edit', $product))
        ->assertSuccessful()
        ->assertSee('Deliverable Locations')
        ->assertSee('Deliver Everywhere')
        ->assertSee('Specific Locations');

    // Update to specific locations
    $updatedLocations = [
        ['country' => 'India', 'state' => 'Karnataka', 'city' => 'Bengaluru'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product), [
            'name' => 'Premium Cardamom Pods',
            'sku' => 'FF-CRD-01',
            'category' => 'Whole Spices',
            'unit' => '100 g',
            'description' => 'Aromatic whole green cardamom pods.',
            'badge' => 'Premium',
            'price' => '450.00',
            'quantity' => '20',
            'low_stock_threshold' => '3',
            'rating' => '5.0',
            'priority' => '95',
            'is_featured' => '0',
            'is_active' => '1',
            'delivery_mode' => 'specific',
            'deliverable_locations' => $updatedLocations,
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status');

    $product->refresh();
    expect($product->name)->toBe('Premium Cardamom Pods')
        ->and($product->delivery_mode)->toBe('specific')
        ->and($product->deliverable_locations)->toHaveCount(1)
        ->and($product->isDeliverableTo('India', 'Karnataka', 'Bengaluru'))->toBeTrue()
        ->and($product->isDeliverableTo('India', 'Gujarat', 'Surat'))->toBeFalse();
});

test('checkout succeeds when customer address is inside product deliverable locations', function () {
    DeliverySetting::current()->update(['mode' => 'all']);

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 200.00,
        'quantity' => 10,
        'delivery_mode' => 'specific',
        'deliverable_locations' => [
            ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Ahmedabad'],
        ],
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
        'unit_price' => $product->price,
        'line_total' => $product->price * 2,
        'image_path' => $product->image_path,
    ]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'name' => 'Local Buyer',
            'mobile' => '9998887776',
            'email' => 'local@example.com',
            'address' => '101 Navrangpura',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'country' => 'India',
            'payment_method' => 'cod',
            'delivery_option' => 'standard',
        ])
        ->assertRedirect(route('checkout.success'))
        ->assertSessionHas('placed_order_id');

    $this->assertDatabaseHas('orders', [
        'name' => 'Local Buyer',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
    ]);
});

test('checkout is blocked when customer address is outside product deliverable locations', function () {
    DeliverySetting::current()->update(['mode' => 'all']);

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Ahmedabad Exclusive Garam Masala',
        'price' => 250.00,
        'quantity' => 10,
        'delivery_mode' => 'specific',
        'deliverable_locations' => [
            ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Ahmedabad'],
        ],
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
        'unit_price' => $product->price,
        'line_total' => $product->price,
        'image_path' => $product->image_path,
    ]);

    // Customer tries to order to Delhi
    $response = $this->actingAs($user)
        ->from(route('checkout.index'))
        ->post(route('checkout.store'), [
            'name' => 'Delhi Buyer',
            'mobile' => '9998887776',
            'email' => 'delhi@example.com',
            'address' => 'Connaught Place',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'country' => 'India',
            'payment_method' => 'cod',
            'delivery_option' => 'standard',
        ]);

    $response->assertSessionHasErrors(['country']);
    $this->assertDatabaseMissing('orders', [
        'name' => 'Delhi Buyer',
    ]);
});

test('real time delivery check verifies product level restrictions', function () {
    DeliverySetting::current()->update(['mode' => 'all']);

    $product = Product::factory()->create([
        'name' => 'Kashmir Saffron',
        'delivery_mode' => 'specific',
        'deliverable_locations' => [
            ['country' => 'India', 'state' => 'Jammu and Kashmir', 'city' => 'Srinagar'],
            ['country' => 'United Arab Emirates', 'state' => null, 'city' => null],
        ],
    ]);

    // Check specific product query
    $res1 = $this->postJson(route('delivery.check'), [
        'country' => 'India',
        'state' => 'Jammu and Kashmir',
        'city' => 'Srinagar',
        'product_id' => $product->id,
    ]);
    $res1->assertOk()->assertJson(['deliverable' => true]);

    $res2 = $this->postJson(route('delivery.check'), [
        'country' => 'India',
        'state' => 'Gujarat',
        'city' => 'Surat',
        'product_id' => $product->id,
    ]);
    $res2->assertOk()->assertJson([
        'deliverable' => false,
        'message' => "Sorry, 'Kashmir Saffron' is not deliverable to this location.",
    ]);

    $res3 = $this->postJson(route('delivery.check'), [
        'country' => 'United Arab Emirates',
        'product_slug' => $product->slug,
    ]);
    $res3->assertOk()->assertJson(['deliverable' => true]);
});

test('product details page passes saved user profile location and renders delivery availability UI', function () {
    $user = User::factory()->create();
    $user->profile()->create([
        'full_name' => 'Profile User',
        'mobile_number' => '9999999999',
        'email' => 'profile@example.com',
        'address' => '123 Test St',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'country' => 'India',
        'postal_code' => '395001',
    ]);

    $product = Product::factory()->create([
        'name' => 'Surat Locho Spice Mix',
        'delivery_mode' => 'specific',
        'deliverable_locations' => [
            ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Surat'],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee('Delivery Availability')
        ->assertSee('Check in Other Areas')
        ->assertViewHas('userLocation', [
            'country' => 'India',
            'state' => 'Gujarat',
            'city' => 'Surat',
        ]);
});
