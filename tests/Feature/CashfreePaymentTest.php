<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('services.cashfree.app_id', 'test_cf_app_123');
    Config::set('services.cashfree.secret_key', 'test_cf_secret_456');
    Config::set('services.cashfree.env', 'TEST');
    Config::set('services.cashfree.api_version', '2023-08-01');
});

test('online checkout initiates Cashfree payment and creates pending order and payment record', function () {
    Http::fake([
        'https://sandbox.cashfree.com/pg/orders' => Http::response([
            'cf_order_id' => 'cf_order_998877',
            'order_id' => 'ORD-20261024-TEST99',
            'order_amount' => 200.00,
            'order_currency' => 'INR',
            'payment_session_id' => 'session_real_998877',
            'order_status' => 'ACTIVE',
        ], 200),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::create([
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

    $response = $this->actingAs($user)->postJson(route('checkout.store'), [
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'payment_method' => 'online',
            'gateway' => 'cashfree',
            'payment_session_id' => 'session_real_998877',
            'environment' => 'sandbox',
        ]);

    // Order must be Pending
    $order = Order::where('user_id', $user->id)->latest()->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('Pending')
        ->and($order->payment_method)->toBe('online');

    // Payment record must be created with cashfree provider
    $payment = Payment::where('order_id', $order->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->provider)->toBe('cashfree')
        ->and($payment->payment_session_id)->toBe('session_real_998877')
        ->and($payment->status)->toBe('created');

    // Inventory must NOT be decremented before payment verification
    $product->refresh();
    expect($product->quantity)->toBe(10);

    // Cart must NOT be cleared before payment verification
    expect(CartItem::where('user_id', $user->id)->count())->toBe(1);
});

test('online checkout handles missing Cashfree credentials safely with clear validation error', function () {
    Config::set('services.cashfree.app_id', '');
    Config::set('services.cashfree.secret_key', '');

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::create([
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

    $response = $this->actingAs($user)->postJson(route('checkout.store'), [
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment']);
});

test('verifyCashfreePayment confirms order, decrements inventory, and clears cart on successful payment', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::create([
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

    $order = Order::create([
        'order_number' => 'ORD-20261024-VERIFY01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => 100.00,
        'total_price' => 200.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'cashfree',
        'cashfree_order_id' => $order->order_number,
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
        'payment_method' => 'online',
    ]);

    Http::fake([
        'https://sandbox.cashfree.com/pg/orders/'.$order->order_number => Http::response([
            'order_id' => $order->order_number,
            'cf_order_id' => 'cf_112233',
            'order_status' => 'PAID',
            'order_amount' => 200.00,
            'order_currency' => 'INR',
        ], 200),
        'https://sandbox.cashfree.com/pg/orders/'.$order->order_number.'/payments' => Http::response([
            [
                'cf_payment_id' => 'cf_pay_445566',
                'payment_status' => 'SUCCESS',
                'payment_amount' => 200.00,
                'payment_group' => 'upi',
            ],
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.cashfree.verify'), [
        'order_id' => $order->order_number,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'redirect_url' => route('checkout.success'),
        ]);

    // Order confirmed
    $order->refresh();
    expect($order->status)->toBe('Confirmed')
        ->and($order->confirmed_at)->not->toBeNull();

    // Payment captured
    $payment->refresh();
    expect($payment->status)->toBe('captured')
        ->and($payment->cashfree_payment_id)->toBe('cf_pay_445566')
        ->and($payment->paid_at)->not->toBeNull();

    // Inventory decremented (10 - 2 = 8)
    $product->refresh();
    expect($product->quantity)->toBe(8);

    // Cart cleared
    expect(CartItem::where('user_id', $user->id)->count())->toBe(0);
});

test('verifyCashfreePayment forbids unauthorized user from verifying someone elses order', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-FORBIDDEN-01',
        'user_id' => $owner->id,
        'status' => 'Pending',
        'name' => 'Owner',
        'mobile' => '9999999999',
        'email' => 'owner@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 100.00,
        'delivery_charge' => 0.00,
        'total_amount' => 100.00,
    ]);

    $this->actingAs($attacker)
        ->postJson(route('checkout.cashfree.verify'), ['order_id' => $order->order_number])
        ->assertForbidden();
});

test('cashfree webhook processes valid payment event idempotently', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    $order = Order::create([
        'order_number' => 'ORD-WEBHOOK-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Webhook Customer',
        'mobile' => '9999999999',
        'email' => 'webhook@example.com',
        'address' => '123 Webhook St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => 100.00,
        'total_price' => 200.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'cashfree',
        'cashfree_order_id' => $order->order_number,
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
        'payment_method' => 'online',
    ]);

    $rawPayload = json_encode([
        'data' => [
            'order' => [
                'order_id' => $order->order_number,
                'order_amount' => 200.00,
                'order_currency' => 'INR',
            ],
            'payment' => [
                'cf_payment_id' => 'cf_pay_hook_1122',
                'payment_status' => 'SUCCESS',
                'payment_amount' => 200.00,
                'payment_currency' => 'INR',
            ],
        ],
        'type' => 'PAYMENT_SUCCESS_WEBHOOK',
    ]);

    $timestamp = (string) time();
    $signature = base64_encode(hash_hmac('sha256', $timestamp.$rawPayload, 'test_cf_secret_456', true));

    // 1st Webhook delivery
    $response = $this->call(
        'POST',
        route('api.webhooks.cashfree'),
        [],
        [],
        [],
        [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertSuccessful()
        ->assertJson(['success' => true]);

    $order->refresh();
    expect($order->status)->toBe('Confirmed');

    $product->refresh();
    expect($product->quantity)->toBe(8); // 10 - 2 = 8

    // 2nd Webhook delivery (Duplicate / Idempotent)
    $dupResponse = $this->call(
        'POST',
        route('api.webhooks.cashfree'),
        [],
        [],
        [],
        [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $dupResponse->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Webhook already processed (idempotent).',
        ]);

    // Inventory must remain 8 (NOT decremented twice)
    $product->refresh();
    expect($product->quantity)->toBe(8);
});

test('cashfree webhook rejects invalid signature with 400 Bad Request', function () {
    $rawPayload = json_encode(['data' => ['order' => ['order_id' => 'ORD-INVALID']]]);

    $response = $this->call(
        'POST',
        route('api.webhooks.cashfree'),
        [],
        [],
        [],
        [
            'HTTP_X_WEBHOOK_TIMESTAMP' => (string) time(),
            'HTTP_X_WEBHOOK_SIGNATURE' => 'invalid_signature_string',
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid webhook signature.',
        ]);
});

test('cashfree webhook rejects payment amount mismatch with 422', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-MISMATCH-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Mismatch',
        'mobile' => '9999999999',
        'email' => 'mismatch@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    $rawPayload = json_encode([
        'data' => [
            'order' => [
                'order_id' => $order->order_number,
                'order_amount' => 50.00, // Mismatch: 50 instead of 500
                'order_currency' => 'INR',
            ],
            'payment' => [
                'cf_payment_id' => 'cf_pay_mismatch',
                'payment_status' => 'SUCCESS',
                'payment_amount' => 50.00,
            ],
        ],
        'type' => 'PAYMENT_SUCCESS_WEBHOOK',
    ]);

    $timestamp = (string) time();
    $signature = base64_encode(hash_hmac('sha256', $timestamp.$rawPayload, 'test_cf_secret_456', true));

    $response = $this->call(
        'POST',
        route('api.webhooks.cashfree'),
        [],
        [],
        [],
        [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertStatus(422)
        ->assertJson(['success' => false, 'message' => 'Payment amount mismatch.']);

    $order->refresh();
    expect($order->status)->toBe('Pending');
});

test('verifyCashfreePayment rejects payment when server-side amount or currency does not match order', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-MISMATCH-999',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Alice',
        'mobile' => '9999999999',
        'email' => 'alice@example.com',
        'address' => '123 Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 300.00,
        'delivery_charge' => 0.00,
        'total_amount' => 300.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'cashfree',
        'cashfree_order_id' => 'ORD-MISMATCH-999',
        'amount' => 300.00,
        'currency' => 'INR',
        'status' => 'created',
        'payment_method' => 'online',
    ]);

    // Mock Cashfree returning amount 30.00 instead of 300.00
    Http::fake([
        'https://sandbox.cashfree.com/pg/orders/ORD-MISMATCH-999' => Http::response([
            'order_id' => 'ORD-MISMATCH-999',
            'order_status' => 'PAID',
            'order_amount' => 30.00,
            'order_currency' => 'INR',
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.cashfree.verify'), [
        'order_id' => 'ORD-MISMATCH-999',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Payment amount mismatch.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Pending');
});

test('online checkout retry cancels previous uncompleted pending online orders to prevent duplicates', function () {
    Http::fake([
        'https://sandbox.cashfree.com/pg/orders' => Http::response([
            'cf_order_id' => 'cf_order_retry_1',
            'order_id' => 'ORD-RETRY-01',
            'order_amount' => 100.00,
            'order_currency' => 'INR',
            'payment_session_id' => 'session_retry_1',
            'order_status' => 'ACTIVE',
        ], 200),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::create([
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

    // Initial checkout attempt (user closes modal or fails)
    $this->actingAs($user)->postJson(route('checkout.store'), [
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
    ])->assertSuccessful();

    $firstOrder = Order::where('user_id', $user->id)->first();
    expect($firstOrder->status)->toBe('Pending');

    // Second checkout attempt (retry)
    $this->actingAs($user)->postJson(route('checkout.store'), [
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
    ])->assertSuccessful();

    $firstOrder->refresh();
    // The previous uncompleted pending order must now be Cancelled to avoid duplicate active orders
    expect($firstOrder->status)->toBe('Cancelled')
        ->and($firstOrder->cancellation_reason)->toContain('retry');

    // Only one active pending order remains
    $pendingOrders = Order::where('user_id', $user->id)->where('status', 'Pending')->get();
    expect($pendingOrders->count())->toBe(1);
});
