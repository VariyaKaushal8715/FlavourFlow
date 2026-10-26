<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('services.razorpay.key_id', 'rzp_test_validKey123');
    Config::set('services.razorpay.key_secret', 'validSecretKey456');
    Config::set('services.razorpay.webhook_secret', 'test_webhook_secret_789');
    Config::set('services.razorpay.mode', 'test');
});

test('razorpay checkout initiates Razorpay order and creates pending order and payment record', function () {
    Http::fake([
        'https://api.razorpay.com/v1/orders' => Http::response([
            'id' => 'order_rzp_998877',
            'entity' => 'order',
            'amount' => 20000,
            'amount_paid' => 0,
            'amount_due' => 20000,
            'currency' => 'INR',
            'receipt' => 'ORD-20261026-TEST99',
            'status' => 'created',
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
        'payment_method' => 'razorpay',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'payment_method' => 'online',
            'gateway' => 'razorpay',
            'razorpay_order_id' => 'order_rzp_998877',
            'amount' => 25000,
            'currency' => 'INR',
            'key_id' => 'rzp_test_validKey123',
        ]);

    // Order must be Pending
    $order = Order::where('user_id', $user->id)->latest()->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('Pending')
        ->and($order->payment_method)->toBe('online');

    // Payment record created
    $payment = Payment::where('order_id', $order->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->provider)->toBe('razorpay')
        ->and($payment->razorpay_order_id)->toBe('order_rzp_998877')
        ->and($payment->status)->toBe('created')
        ->and($payment->is_demo)->toBeFalse();

    // Inventory must NOT be decremented before verification
    $product->refresh();
    expect($product->quantity)->toBe(10);

    // Cart must NOT be cleared before verification
    expect(CartItem::where('user_id', $user->id)->count())->toBe(1);
});

test('online checkout handles missing Razorpay credentials safely with clear validation error', function () {
    Config::set('services.razorpay.key_id', '');
    Config::set('services.razorpay.key_secret', '');

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
        'payment_method' => 'razorpay',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment']);
});

test('verifyRazorpayPayment confirms order, decrements inventory, and clears cart on successful payment', function () {
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
        'order_number' => 'ORD-20261026-RZP01',
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
        'payment_method' => 'razorpay',
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
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_match_123',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
        'payment_method' => 'online',
    ]);

    $secret = 'validSecretKey456';
    $razorpayOrderId = 'order_rzp_match_123';
    $razorpayPaymentId = 'pay_rzp_valid_456';
    $validSignature = hash_hmac('sha256', $razorpayOrderId.'|'.$razorpayPaymentId, $secret);

    // Mock authoritative payment status fetch from Razorpay API
    Http::fake([
        'https://api.razorpay.com/v1/payments/'.$razorpayPaymentId => Http::response([
            'id' => $razorpayPaymentId,
            'entity' => 'payment',
            'amount' => 20000,
            'currency' => 'INR',
            'status' => 'captured',
            'order_id' => $razorpayOrderId,
            'method' => 'upi',
            'captured' => true,
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => $razorpayOrderId,
        'razorpay_payment_id' => $razorpayPaymentId,
        'razorpay_signature' => $validSignature,
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
        ->and($payment->razorpay_payment_id)->toBe($razorpayPaymentId)
        ->and($payment->paid_at)->not->toBeNull();

    // Inventory decremented (10 - 2 = 8)
    $product->refresh();
    expect($product->quantity)->toBe(8);

    // Cart cleared
    expect(CartItem::where('user_id', $user->id)->count())->toBe(0);
});

test('verifyRazorpayPayment rejects invalid signature with 400 Bad Request', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-20261026-BADSIG',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Bad Sig',
        'mobile' => '9999999999',
        'email' => 'badsig@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_badsig',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => 'order_rzp_badsig',
        'razorpay_payment_id' => 'pay_rzp_fake_999',
        'razorpay_signature' => 'invalid_tampered_signature',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Razorpay payment signature verification failed.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Pending');
});

test('verifyRazorpayPayment rejects amount mismatch with 422', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-20261026-RZPMISMATCH',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Mismatch',
        'mobile' => '9999999999',
        'email' => 'mismatch@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_mismatch',
        'amount' => 500.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $secret = 'validSecretKey456';
    $orderId = 'order_rzp_mismatch';
    $paymentId = 'pay_rzp_mismatch_1';
    $validSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

    // Mock Razorpay returning amount of 5000 paise (Rs. 50) instead of 50000 paise (Rs. 500)
    Http::fake([
        'https://api.razorpay.com/v1/payments/'.$paymentId => Http::response([
            'id' => $paymentId,
            'entity' => 'payment',
            'amount' => 5000, // 50 INR
            'currency' => 'INR',
            'status' => 'captured',
            'order_id' => $orderId,
            'captured' => true,
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $validSignature,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Payment amount mismatch.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Pending');
});

test('verifyRazorpayPayment rejects currency mismatch with 422', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-20261026-RZPCURR',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Currency',
        'mobile' => '9999999999',
        'email' => 'curr@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_curr',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $secret = 'validSecretKey456';
    $orderId = 'order_rzp_curr';
    $paymentId = 'pay_rzp_curr_1';
    $validSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

    // Mock Razorpay returning currency USD instead of INR
    Http::fake([
        'https://api.razorpay.com/v1/payments/'.$paymentId => Http::response([
            'id' => $paymentId,
            'entity' => 'payment',
            'amount' => 20000,
            'currency' => 'USD',
            'status' => 'captured',
            'order_id' => $orderId,
            'captured' => true,
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $validSignature,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Payment currency mismatch.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Pending');
});

test('verifyRazorpayPayment is idempotent when order is already Confirmed', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-20261026-RZPIDEMP',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'confirmed_at' => now(),
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_idemp',
        'razorpay_payment_id' => 'pay_rzp_idemp_1',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'captured',
    ]);

    $secret = 'validSecretKey456';
    $orderId = 'order_rzp_idemp';
    $paymentId = 'pay_rzp_idemp_1';
    $validSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

    $response = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $validSignature,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'redirect_url' => route('checkout.success'),
        ]);
});

test('verifyRazorpayPayment rejects demo order verification strictly', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'DEMO-20261026-ISOLATION',
        'user_id' => $user->id,
        'status' => 'Pending',
        'is_demo' => true,
        'name' => 'Demo User',
        'mobile' => '9999999999',
        'email' => 'demo@example.com',
        'address' => '123 Demo St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'demo',
        'subtotal' => 150.00,
        'delivery_charge' => 0.00,
        'total_amount' => 150.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'demo',
        'is_demo' => true,
        'razorpay_order_id' => 'order_rzp_fake_demo',
        'amount' => 150.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => 'order_rzp_fake_demo',
        'razorpay_payment_id' => 'pay_rzp_fake',
        'razorpay_signature' => 'any_signature',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Simulated demo transactions cannot be verified with real Razorpay gateway.',
        ]);
});

test('verifyRazorpayPayment forbids unauthorized user from verifying someone elses order', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-RZP-FORBIDDEN-01',
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
        'payment_method' => 'razorpay',
        'subtotal' => 100.00,
        'delivery_charge' => 0.00,
        'total_amount' => 100.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $owner->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_forbidden',
        'amount' => 100.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $this->actingAs($attacker)
        ->postJson(route('checkout.razorpay.verify'), [
            'razorpay_order_id' => 'order_rzp_forbidden',
            'razorpay_payment_id' => 'pay_123',
            'razorpay_signature' => 'sig_123',
        ])
        ->assertForbidden();
});

test('razorpay webhook processes payment.captured event idempotently', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    $order = Order::create([
        'order_number' => 'ORD-RZP-HOOK-01',
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
        'payment_method' => 'razorpay',
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
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_hook_01',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
        'payment_method' => 'online',
    ]);

    $rawPayload = json_encode([
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_hook_captured_1',
                    'order_id' => 'order_rzp_hook_01',
                    'amount' => 20000,
                    'currency' => 'INR',
                    'status' => 'captured',
                    'notes' => [
                        'order_number' => $order->order_number,
                    ],
                ],
            ],
            'order' => [
                'entity' => [
                    'id' => 'order_rzp_hook_01',
                    'receipt' => $order->order_number,
                    'amount' => 20000,
                    'currency' => 'INR',
                ],
            ],
        ],
    ]);

    $webhookSecret = 'test_webhook_secret_789';
    $signature = hash_hmac('sha256', $rawPayload, $webhookSecret);

    // 1st Webhook delivery
    $response = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Payment confirmed and order fulfilled.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Confirmed');

    $product->refresh();
    expect($product->quantity)->toBe(8); // 10 - 2 = 8

    $payment->refresh();
    expect($payment->status)->toBe('captured')
        ->and($payment->razorpay_payment_id)->toBe('pay_hook_captured_1');

    // 2nd Webhook delivery (Duplicate / Idempotent)
    $dupResponse = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
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

test('razorpay webhook rejects invalid signature with 400 Bad Request', function () {
    $rawPayload = json_encode(['event' => 'payment.captured']);

    $response = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => 'invalid_webhook_signature',
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

test('razorpay webhook rejects payment amount mismatch with 422', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-RZP-MISMATCH-HOOK',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Mismatch Hook',
        'mobile' => '9999999999',
        'email' => 'mismatch_hook@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_hook_mismatch',
        'amount' => 500.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    // Payload has 5000 paise (Rs. 50) instead of 50000 paise (Rs. 500)
    $rawPayload = json_encode([
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_hook_mismatch_1',
                    'order_id' => 'order_rzp_hook_mismatch',
                    'amount' => 5000,
                    'currency' => 'INR',
                    'status' => 'captured',
                    'notes' => [
                        'order_number' => $order->order_number,
                    ],
                ],
            ],
            'order' => [
                'entity' => [
                    'id' => 'order_rzp_hook_mismatch',
                    'receipt' => $order->order_number,
                    'amount' => 5000,
                    'currency' => 'INR',
                ],
            ],
        ],
    ]);

    $webhookSecret = 'test_webhook_secret_789';
    $signature = hash_hmac('sha256', $rawPayload, $webhookSecret);

    $response = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Payment amount mismatch.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Pending');
});

test('razorpay webhook handles payment.failed event and updates payment record', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-RZP-FAILED-HOOK',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Failed Hook',
        'mobile' => '9999999999',
        'email' => 'failed_hook@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_hook_failed',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $rawPayload = json_encode([
        'event' => 'payment.failed',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_hook_failed_1',
                    'order_id' => 'order_rzp_hook_failed',
                    'amount' => 20000,
                    'currency' => 'INR',
                    'status' => 'failed',
                    'error_description' => 'Card was declined by bank',
                    'notes' => [
                        'order_number' => $order->order_number,
                    ],
                ],
            ],
            'order' => [
                'entity' => [
                    'id' => 'order_rzp_hook_failed',
                    'receipt' => $order->order_number,
                    'amount' => 20000,
                    'currency' => 'INR',
                ],
            ],
        ],
    ]);

    $webhookSecret = 'test_webhook_secret_789';
    $signature = hash_hmac('sha256', $rawPayload, $webhookSecret);

    $response = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Payment failure recorded.',
        ]);

    $order->refresh();
    expect($order->status)->toBe('Pending'); // Order stays pending

    $payment->refresh();
    expect($payment->status)->toBe('failed')
        ->and($payment->failure_reason)->toBe('Card was declined by bank');
});

test('razorpay webhook handles refund.processed event and records refund status', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-RZP-REFUND-HOOK',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'confirmed_at' => now(),
        'name' => 'Refund Hook',
        'mobile' => '9999999999',
        'email' => 'refund_hook@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'razorpay',
        'subtotal' => 200.00,
        'delivery_charge' => 0.00,
        'total_amount' => 200.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'razorpay',
        'is_demo' => false,
        'razorpay_order_id' => 'order_rzp_hook_refund',
        'razorpay_payment_id' => 'pay_hook_ref_1',
        'amount' => 200.00,
        'currency' => 'INR',
        'status' => 'captured',
    ]);

    $rawPayload = json_encode([
        'event' => 'refund.processed',
        'payload' => [
            'refund' => [
                'entity' => [
                    'id' => 'rfnd_rzp_processed_99',
                    'payment_id' => 'pay_hook_ref_1',
                    'amount' => 20000, // 200 INR
                    'currency' => 'INR',
                    'status' => 'processed',
                    'notes' => [
                        'order_number' => $order->order_number,
                    ],
                ],
            ],
            'order' => [
                'entity' => [
                    'id' => 'order_rzp_hook_refund',
                    'receipt' => $order->order_number,
                ],
            ],
        ],
    ]);

    $webhookSecret = 'test_webhook_secret_789';
    $signature = hash_hmac('sha256', $rawPayload, $webhookSecret);

    $response = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Refund event processed.',
        ]);

    $payment->refresh();
    expect($payment->razorpay_refund_id)->toBe('rfnd_rzp_processed_99')
        ->and($payment->refund_status)->toBe('PROCESSED')
        ->and((float) $payment->refunded_amount)->toBe(200.0);
});

test('razorpay webhook strictly isolates and rejects demo orders', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'DEMO-HOOK-REJECT-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'is_demo' => true,
        'name' => 'Demo User',
        'mobile' => '9999999999',
        'email' => 'demo_hook@example.com',
        'address' => 'Demo Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'demo',
        'subtotal' => 100.00,
        'delivery_charge' => 0.00,
        'total_amount' => 100.00,
    ]);

    $rawPayload = json_encode([
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_demo_fake',
                    'order_id' => 'order_demo_fake',
                    'amount' => 10000,
                    'currency' => 'INR',
                    'status' => 'captured',
                    'notes' => [
                        'order_number' => $order->order_number,
                    ],
                ],
            ],
            'order' => [
                'entity' => [
                    'id' => 'order_demo_fake',
                    'receipt' => $order->order_number,
                ],
            ],
        ],
    ]);

    $webhookSecret = 'test_webhook_secret_789';
    $signature = hash_hmac('sha256', $rawPayload, $webhookSecret);

    $response = $this->call(
        'POST',
        route('api.webhooks.razorpay'),
        [],
        [],
        [],
        [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawPayload
    );

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Simulated demo orders cannot be processed by real Razorpay webhook.',
        ]);
});
