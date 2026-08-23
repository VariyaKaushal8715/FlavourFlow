<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

test('administrator can access executive dashboard with real metrics', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $product = Product::factory()->create([
        'name' => 'Royal Saffron Blend',
        'category' => 'Signature blend',
        'price' => 299.00,
        'quantity' => 25,
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => 'ORD-TEST1234',
        'total_amount' => 598.00,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 299.00,
        'total_price' => 598.00,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Executive Dashboard')
        ->assertSee('Total Revenue')
        ->assertSee('Royal Saffron Blend')
        ->assertSee('ORD-TEST1234')
        ->assertSee('Signature blend')
        ->assertSee('flavourflow-mark.png');
});

test('administrator can view sales analytics and product performance leaderboard', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $product = Product::factory()->create([
        'name' => 'Kashmiri Saffron Masala',
        'sku' => 'FF-KSM-100',
        'category' => 'Pure spice',
        'price' => 350.00,
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => 'ORD-LEADER1',
        'total_amount' => 700.00,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 350.00,
        'total_price' => 700.00,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.analytics.sales'))
        ->assertSuccessful()
        ->assertSee('Product Sales Leaderboard')
        ->assertSee('Kashmiri Saffron Masala')
        ->assertSee('FF-KSM-100')
        ->assertSee('700');
});

test('administrator can view dedicated product analytics page', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['name' => 'Aarav Patel']);

    $product = Product::factory()->create([
        'name' => 'Single Origin Black Pepper',
        'slug' => 'single-origin-black-pepper',
        'sku' => 'FF-BP-100',
        'category' => 'Pure spice',
        'price' => 199.00,
        'quantity' => 18,
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => 'ORD-PEPPER1',
        'total_amount' => 398.00,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 199.00,
        'total_price' => 398.00,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.analytics.products.show', $product))
        ->assertSuccessful()
        ->assertSee('Single Origin Black Pepper Analytics')
        ->assertSee('Lifetime Revenue')
        ->assertSee('Aarav Patel')
        ->assertSee('ORD-PEPPER1')
        ->assertSee('Edit Product');
});

test('administrator can view orders list and individual order detail', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create([
        'name' => 'Diya Sharma',
        'email' => 'diya@example.com',
    ]);

    $product = Product::factory()->create([
        'name' => 'Cardamom Pods',
        'price' => 240.00,
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => 'ORD-DETAIL-999',
        'total_amount' => 480.00,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 240.00,
        'total_price' => 480.00,
    ]);

    // List
    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful()
        ->assertSee('Customer Orders')
        ->assertSee('ORD-DETAIL-999')
        ->assertSee('Diya Sharma');

    // Show
    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertSuccessful()
        ->assertSee('ORD-DETAIL-999')
        ->assertSee('Diya Sharma')
        ->assertSee('diya@example.com')
        ->assertSee('Cardamom Pods')
        ->assertSee('480');
});

test('administrator can view inventory control and stock alerts', function () {
    $admin = User::factory()->admin()->create();

    $healthyProduct = Product::factory()->create([
        'name' => 'Healthy Coriander',
        'quantity' => 50,
        'low_stock_threshold' => 10,
    ]);

    $lowStockProduct = Product::factory()->lowStock()->create([
        'name' => 'Alert Cumin Seeds',
        'quantity' => 3,
        'low_stock_threshold' => 10,
    ]);

    $outOfStockProduct = Product::factory()->outOfStock()->create([
        'name' => 'Exhausted Star Anise',
        'quantity' => 0,
        'low_stock_threshold' => 5,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.inventory.index'))
        ->assertSuccessful()
        ->assertSee('Inventory Control')
        ->assertSee('Healthy Coriander')
        ->assertSee('Alert Cumin Seeds')
        ->assertSee('Exhausted Star Anise')
        ->assertSee('Out of Stock')
        ->assertSee('Low Stock');
});

test('administrator can view categories overview and category detail analytics', function () {
    $admin = User::factory()->admin()->create();

    Product::factory()->create([
        'name' => 'Panch Phoron Blend',
        'category' => 'Regional Specialty',
        'price' => 180.00,
    ]);

    // Categories Index
    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertSuccessful()
        ->assertSee('Categories Performance')
        ->assertSee('Regional Specialty');

    // Category Show
    $this->actingAs($admin)
        ->get(route('admin.categories.show', ['category' => 'Regional Specialty']))
        ->assertSuccessful()
        ->assertSee('Regional Specialty')
        ->assertSee('Panch Phoron Blend');
});

test('non-admin user is forbidden from accessing analytics and management routes', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $product = Product::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-SEC-01',
        'total_amount' => 100.00,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    $this->actingAs($user)->get(route('admin.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.analytics.sales'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.analytics.products.show', $product))->assertForbidden();
    $this->actingAs($user)->get(route('admin.orders.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.orders.show', $order))->assertForbidden();
    $this->actingAs($user)->get(route('admin.inventory.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
});

test('storefront and admin pages contain the FlavourFlow favicon', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('flavourflow-mark.png');

    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('flavourflow-mark.png');
});

test('opening or redirecting to /admin always shows dashboard overview and does not redirect to products', function () {
    $admin = User::factory()->admin()->create();

    // Direct open
    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Executive Dashboard')
        ->assertSee('Store Pulse')
        ->assertSee('Top Selling Products')
        ->assertDontSee('Add to catalog');

    // Refresh
    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Executive Dashboard');
});

test('admin login redirects directly to /admin dashboard overview', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin-dashboard-test@flavourflow.test',
        'password' => 'secret-password',
    ]);

    $this->post(route('admin.login'), [
        'email' => 'admin-dashboard-test@flavourflow.test',
        'password' => 'secret-password',
    ])
        ->assertRedirect(route('admin.index'))
        ->assertRedirect('/admin');

    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Executive Dashboard');
});

test('sidebar subsections open normally without redirecting to dashboard', function () {
    $admin = User::factory()->admin()->create();

    // Products
    $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertSuccessful()
        ->assertSee('Catalog control')
        ->assertSee('Add to catalog');

    // Orders
    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful()
        ->assertSee('Customer Orders');

    // Sales Analytics
    $this->actingAs($admin)
        ->get(route('admin.analytics.sales'))
        ->assertSuccessful()
        ->assertSee('Product Sales Leaderboard');

    // Inventory
    $this->actingAs($admin)
        ->get(route('admin.inventory.index'))
        ->assertSuccessful()
        ->assertSee('Inventory Control');

    // Categories
    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertSuccessful()
        ->assertSee('Categories Performance');

    // Offers
    $this->actingAs($admin)
        ->get(route('admin.offers.index'))
        ->assertSuccessful()
        ->assertSee('Campaign control');
});
