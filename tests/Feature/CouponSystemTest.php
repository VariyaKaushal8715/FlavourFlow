<?php

use App\Models\Coupon;
use App\Models\CouponRewardRule;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CouponService;

beforeEach(function () {
    // Set up default reward rules
    CouponRewardRule::create([
        'name' => '10% Online Reward (₹1,000+)',
        'min_purchase_amount' => 1000.00,
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'max_discount' => 300.00,
        'min_next_order_amount' => 500.00,
        'validity_days' => 30,
        'is_active' => true,
    ]);

    CouponRewardRule::create([
        'name' => '15% Online Reward (₹2,000+)',
        'min_purchase_amount' => 2000.00,
        'discount_type' => 'percent',
        'discount_value' => 15.00,
        'max_discount' => 500.00,
        'min_next_order_amount' => 800.00,
        'validity_days' => 30,
        'is_active' => true,
    ]);
});

test('online order above 1000 automatically generates a unique 10% reward coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 20, 'price' => 1200.00]);

    // Add to cart
    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    // Place online checkout
    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Kaushal Test',
        'mobile' => '9876543210',
        'email' => 'kaushal@example.com',
        'address' => 'Flat 101, Spice Garden',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'online',
        'delivery_option' => 'standard',
    ]);

    $response->assertRedirect(route('checkout.success'));

    $order = Order::where('user_id', $user->id)->latest()->first();
    expect($order)->not->toBeNull();
    expect($order->payment_method)->toBe('online');
    expect($order->earned_coupon_id)->not->toBeNull();

    $coupon = Coupon::find($order->earned_coupon_id);
    expect($coupon)->not->toBeNull();
    expect($coupon->user_id)->toBe($user->id);
    expect((float) $coupon->discount_value)->toBe(10.00);
    expect($coupon->discount_type)->toBe('percent');
    expect($coupon->status)->toBe('available');
    expect($coupon->is_active)->toBeTrue();
    expect($coupon->source_order_id)->toBe($order->id);
});

test('online order above 2000 automatically generates a 15% tier reward coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 20, 'price' => 2500.00]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Kaushal Test',
        'mobile' => '9876543210',
        'email' => 'kaushal@example.com',
        'address' => 'Flat 101, Spice Garden',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'online',
        'delivery_option' => 'standard',
    ]);

    $order = Order::where('user_id', $user->id)->latest()->first();
    $coupon = Coupon::find($order->earned_coupon_id);

    expect((float) $coupon->discount_value)->toBe(15.00);
});

test('cod orders must not receive reward coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 20, 'price' => 1500.00]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Kaushal COD',
        'mobile' => '9876543210',
        'email' => 'kaushal@example.com',
        'address' => 'Flat 101, Spice Garden',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'cod',
        'delivery_option' => 'standard',
    ]);

    $response->assertRedirect(route('checkout.success'));

    $order = Order::where('user_id', $user->id)->latest()->first();
    expect($order->earned_coupon_id)->toBeNull();
    expect(Coupon::where('source_order_id', $order->id)->exists())->toBeFalse();
});

test('reward coupon generation is idempotent and prevents duplicates', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'subtotal' => 1500.00,
        'payment_method' => 'online',
    ]);

    $service = app(CouponService::class);
    $coupon1 = $service->generateRewardCouponForOrder($order);
    $coupon2 = $service->generateRewardCouponForOrder($order);

    expect($coupon1->id)->toBe($coupon2->id);
    expect(Coupon::where('source_order_id', $order->id)->count())->toBe(1);
});

test('payment webhook confirms order and generates reward coupon idempotently', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'order_number' => 'ORD-TEST-999',
        'user_id' => $user->id,
        'subtotal' => 1200.00,
        'payment_method' => 'online',
        'status' => 'Pending',
        'confirmed_at' => null,
    ]);

    $response = $this->postJson(route('payment.webhook'), [
        'order_number' => 'ORD-TEST-999',
        'event' => 'payment.captured',
        'transaction_id' => 'TXN_123456',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);

    $order->refresh();
    expect($order->status)->toBe('Confirmed');
    expect($order->earned_coupon_id)->not->toBeNull();

    // Call webhook second time (duplicate call)
    $response2 = $this->postJson(route('payment.webhook'), [
        'order_number' => 'ORD-TEST-999',
        'event' => 'payment.captured',
        'transaction_id' => 'TXN_123456',
    ]);

    $response2->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Webhook already processed (idempotent).',
        ]);
});

test('automatically rejects expired coupons and displays proper message', function () {
    $user = User::factory()->create();
    Coupon::create([
        'code' => 'EXPIRED20',
        'discount_type' => 'percent',
        'discount_value' => 20.00,
        'expires_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->postJson(route('checkout.coupon.apply'), [
            'coupon_code' => 'EXPIRED20',
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Sorry, this coupon has expired.',
        ]);
});

test('rejects coupon if subtotal is below minimum order amount', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 300.00]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    Coupon::create([
        'code' => 'MIN500',
        'discount_type' => 'fixed',
        'discount_value' => 50.00,
        'min_order_amount' => 500.00,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->postJson(route('checkout.coupon.apply'), [
            'coupon_code' => 'MIN500',
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Minimum order amount to use this coupon is Rs. 500.00',
        ]);
});

test('cannot use a coupon assigned to another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Coupon::create([
        'code' => 'USER1ONLY',
        'user_id' => $user1->id,
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'is_active' => true,
    ]);

    $this->actingAs($user2)
        ->postJson(route('checkout.coupon.apply'), [
            'coupon_code' => 'USER1ONLY',
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This coupon is not valid for your account.',
        ]);
});

test('cannot reuse single-use coupon after successful checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 10, 'price' => 500.00]);

    $coupon = Coupon::create([
        'code' => 'ONETIME10',
        'user_id' => $user->id,
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'usage_limit_per_user' => 1,
        'is_active' => true,
    ]);

    // 1st purchase using coupon
    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Kaushal Reuse Test',
        'mobile' => '9876543210',
        'email' => 'kaushal@example.com',
        'address' => 'Flat 101, Spice Garden',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'cod',
        'coupon_code' => 'ONETIME10',
    ]);

    expect(CouponUsage::where('coupon_id', $coupon->id)->count())->toBe(1);

    // Attempt 2nd purchase with same coupon
    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'ONETIME10',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You have already used this coupon.',
        ]);
});

test('user can view available, used, and expired coupons in profile', function () {
    $user = User::factory()->create();

    // Available coupon
    Coupon::create([
        'code' => 'AVAIL10',
        'user_id' => $user->id,
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'is_active' => true,
        'expires_at' => now()->addDays(10),
    ]);

    // Expired coupon
    Coupon::create([
        'code' => 'EXP10',
        'user_id' => $user->id,
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'is_active' => true,
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->get(route('account.coupons'));
    $response->assertSuccessful();
    $response->assertSee('AVAIL10');
    $response->assertSee('EXP10');
    $response->assertSee('My Coupons');
});

test('admin can create, toggle status, and delete coupons', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // Admin index
    $this->actingAs($admin)->get(route('admin.coupons.index'))->assertSuccessful();

    // Create coupon
    $this->actingAs($admin)->post(route('admin.coupons.store'), [
        'code' => 'PROMO50',
        'title' => 'Festive 50 Off',
        'discount_type' => 'fixed',
        'discount_value' => 50.00,
        'payment_method_eligibility' => 'both',
        'min_order_amount' => 250.00,
        'is_active' => true,
    ])->assertRedirect(route('admin.coupons.index'));

    $coupon = Coupon::where('code', 'PROMO50')->first();
    expect($coupon)->not->toBeNull();
    expect((float) $coupon->discount_value)->toBe(50.00);

    // Toggle status
    $this->actingAs($admin)->patch(route('admin.coupons.toggle', $coupon))
        ->assertRedirect(route('admin.coupons.index'));
    $coupon->refresh();
    expect($coupon->is_active)->toBeFalse();

    // Delete coupon
    $this->actingAs($admin)->delete(route('admin.coupons.destroy', $coupon))
        ->assertRedirect(route('admin.coupons.index'));
    expect(Coupon::where('code', 'PROMO50')->exists())->toBeFalse();
});

test('admin can manage reward offer rules', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post(route('admin.coupons.rewardRules.store'), [
        'name' => '20% Gold Tier (₹5,000+)',
        'min_purchase_amount' => 5000.00,
        'discount_type' => 'percent',
        'discount_value' => 20.00,
        'max_discount' => 1000.00,
        'min_next_order_amount' => 1000.00,
        'validity_days' => 45,
        'is_active' => true,
    ])->assertRedirect(route('admin.coupons.index'));

    $rule = CouponRewardRule::where('name', '20% Gold Tier (₹5,000+)')->first();
    expect($rule)->not->toBeNull();
    expect((float) $rule->min_purchase_amount)->toBe(5000.00);

    // Toggle rule
    $this->actingAs($admin)->patch(route('admin.coupons.rewardRules.toggle', $rule))
        ->assertRedirect(route('admin.coupons.index'));
    $rule->refresh();
    expect($rule->is_active)->toBeFalse();
});

test('online payment only coupon is rejected when customer selects cash on delivery', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 600.00]);

    $coupon = Coupon::create([
        'code' => 'ONLINEONLY10',
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'payment_method_eligibility' => 'online',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    // Apply with COD
    $response = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'ONLINEONLY10',
        'payment_method' => 'cod',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This coupon is valid only for online payment. Please select online payment to use this coupon.',
        ]);

    // Apply with Online
    $onlineResponse = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'ONLINEONLY10',
        'payment_method' => 'online',
    ]);

    $onlineResponse->assertSuccessful()
        ->assertJson([
            'success' => true,
            'discount' => 60.00,
            'coupon' => [
                'code' => 'ONLINEONLY10',
                'payment_method_eligibility' => 'online',
            ],
        ]);
});

test('cash on delivery only coupon is rejected when customer selects online payment', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 800.00]);

    $coupon = Coupon::create([
        'code' => 'CODONLY20',
        'discount_type' => 'fixed',
        'discount_value' => 50.00,
        'payment_method_eligibility' => 'cod',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    // Apply with Online Payment
    $response = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'CODONLY20',
        'payment_method' => 'online',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This coupon is valid only for Cash on Delivery. Please select Cash on Delivery to use this coupon.',
        ]);

    // Apply with COD
    $codResponse = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'CODONLY20',
        'payment_method' => 'cod',
    ]);

    $codResponse->assertSuccessful()
        ->assertJson([
            'success' => true,
            'discount' => 50.00,
            'coupon' => [
                'code' => 'CODONLY20',
                'payment_method_eligibility' => 'cod',
            ],
        ]);
});

test('both payment eligible coupon works with both online and cod payments', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 500.00]);

    $coupon = Coupon::create([
        'code' => 'BOTHWAYS15',
        'discount_type' => 'percent',
        'discount_value' => 15.00,
        'payment_method_eligibility' => 'both',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    // Apply with COD
    $codResponse = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'BOTHWAYS15',
        'payment_method' => 'cod',
    ]);
    $codResponse->assertSuccessful()
        ->assertJson(['success' => true]);

    // Apply with Online
    $onlineResponse = $this->actingAs($user)->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'BOTHWAYS15',
        'payment_method' => 'online',
    ]);
    $onlineResponse->assertSuccessful()
        ->assertJson(['success' => true]);
});

test('server-side validation rejects checkout store when online-only coupon is placed as COD', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 1000.00]);

    Coupon::create([
        'code' => 'ONLINESTRICT',
        'discount_type' => 'percent',
        'discount_value' => 20.00,
        'payment_method_eligibility' => 'online',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('cart.store', $product->slug), [
        'quantity' => 1,
        'unit' => '100g',
    ]);

    // Attempt to checkout with COD using online-only coupon
    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Kaushal Test',
        'mobile' => '9876543210',
        'email' => 'kaushal@example.com',
        'address' => 'Flat 101, Spice Garden',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'cod',
        'delivery_option' => 'standard',
        'coupon_code' => 'ONLINESTRICT',
    ]);

    $response->assertSessionHasErrors('coupon_code');
    expect(Order::where('user_id', $user->id)->count())->toBe(0);
});

test('admin can create and edit coupons with payment method eligibility', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // Create online-only coupon
    $this->actingAs($admin)->post(route('admin.coupons.store'), [
        'code' => 'PAYONLINE25',
        'title' => 'Online Only Special',
        'discount_type' => 'percent',
        'discount_value' => 25.00,
        'payment_method_eligibility' => 'online',
        'is_active' => true,
    ])->assertRedirect(route('admin.coupons.index'));

    $coupon = Coupon::where('code', 'PAYONLINE25')->first();
    expect($coupon)->not->toBeNull();
    expect($coupon->payment_method_eligibility)->toBe('online');
    expect($coupon->paymentMethodLabel())->toBe('Online Payment Only');

    // Edit coupon to COD only
    $this->actingAs($admin)->put(route('admin.coupons.update', $coupon), [
        'code' => 'PAYONLINE25',
        'title' => 'COD Only Modified',
        'discount_type' => 'percent',
        'discount_value' => 25.00,
        'payment_method_eligibility' => 'cod',
        'is_active' => true,
    ])->assertRedirect(route('admin.coupons.index'));

    $coupon->refresh();
    expect($coupon->payment_method_eligibility)->toBe('cod');
    expect($coupon->paymentMethodLabel())->toBe('Cash on Delivery Only');
});

test('admin can filter coupons by payment method eligibility in admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Coupon::create([
        'code' => 'FILTERONLINE',
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'payment_method_eligibility' => 'online',
        'is_active' => true,
    ]);

    Coupon::create([
        'code' => 'FILTERCOD',
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'payment_method_eligibility' => 'cod',
        'is_active' => true,
    ]);

    $responseOnline = $this->actingAs($admin)->get(route('admin.coupons.index', ['payment_method' => 'online']));
    $responseOnline->assertSuccessful();
    $responseOnline->assertSee('FILTERONLINE');
    $responseOnline->assertDontSee('FILTERCOD');

    $responseCod = $this->actingAs($admin)->get(route('admin.coupons.index', ['payment_method' => 'cod']));
    $responseCod->assertSuccessful();
    $responseCod->assertSee('FILTERCOD');
    $responseCod->assertDontSee('FILTERONLINE');
});
