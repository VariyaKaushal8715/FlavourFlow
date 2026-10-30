<?php

use App\Mail\OrderConfirmationOtp;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentGatewaySetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->setting = PaymentGatewaySetting::current();
    $this->setting->update([
        'upi_id' => 'flavourflow@okhdfcbank',
        'upi_name' => 'FlavourFlow Spices',
        'upi_note' => 'Spices Order Payment',
        'upi_enabled' => true,
    ]);
});

test('checkout page displays UPI option when configured and enabled', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 150, 'quantity' => 10]);

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

    $response = $this->actingAs($user)->get(route('checkout.index'));

    $response->assertSuccessful();
    $response->assertSee('UPI QR Code Payment');
    $response->assertSee('value="upi"', false);
    $response->assertSee('MANUAL CONFIRMATION REQUIRED');
    $response->assertSee('flavourflow@okhdfcbank');
});

test('checkout page hides UPI option when UPI is disabled in admin settings', function () {
    $this->setting->update(['upi_enabled' => false]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 150, 'quantity' => 10]);

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

    $response = $this->actingAs($user)->get(route('checkout.index'));

    $response->assertSuccessful();
    $response->assertDontSee('value="upi"', false);
});

test('user can place an order using UPI and server strictly calculates order total', function () {
    $user = User::factory()->create();
    // Product price 300, quantity 1 -> subtotal 300, delivery charge 50 (since < 500) -> Total must be 350.00
    $product = Product::factory()->create(['price' => 300, 'quantity' => 5]);

    CartItem::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 300,
        'line_total' => 300,
        'image_path' => $product->image_path,
    ]);

    // Attacker tries to submit a tampered amount (e.g. total_amount = 1.00)
    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Aarav Patel',
        'mobile' => '+91 9876543210',
        'email' => 'aarav@example.com',
        'address' => '404 Heritage Square',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'total_amount' => 1.00, // Tampered input: MUST BE IGNORED
    ]);

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toBe('upi')
        ->and($order->status)->toBe('Pending') // Strictly Pending
        ->and((float) $order->total_amount)->toBe(350.00) // Server calculated 300 + 50
        ->and((float) $order->subtotal)->toBe(300.00)
        ->and((float) $order->delivery_charge)->toBe(50.00);

    // Stock is NOT decremented for unpaid, pending UPI orders (decremented only upon confirmed fulfillment)
    expect($product->fresh()->quantity)->toBe(5);

    // Payment record created in pending state
    $payment = Payment::where('order_id', $order->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->provider)->toBe('upi')
        ->and($payment->payment_method)->toBe('upi')
        ->and($payment->status)->toBe('pending')
        ->and((float) $payment->amount)->toBe(350.00);

    // Cart cleared
    expect(CartItem::where('user_id', $user->id)->count())->toBe(0);

    // Redirected to UPI QR payment page
    $response->assertRedirect(route('checkout.upi.show', $order->order_number));
});

test('placing UPI order via JSON returns JSON with redirect URL and order details', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 600, 'quantity' => 10]);

    CartItem::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 600,
        'line_total' => 600,
        'image_path' => $product->image_path,
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.store'), [
        'name' => 'Diya Shah',
        'mobile' => '+91 9888877777',
        'email' => 'diya@example.com',
        'address' => '701 Green Heights',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'upi',
    ]);

    $order = Order::where('user_id', $user->id)->latest()->first();

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'payment_method' => 'upi',
        'redirect_url' => route('checkout.upi.show', $order->order_number),
        'order_number' => $order->order_number,
    ]);
});

test('UPI payment page displays scannable QR code and merchant details', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-20261029-UPI111',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Aarav Patel',
        'mobile' => '+91 9876543210',
        'email' => 'aarav@example.com',
        'address' => '404 Heritage Square',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 400.00,
        'delivery_charge' => 50.00,
        'total_amount' => 450.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 450.00,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->get(route('checkout.upi.show', $order->order_number));

    $response->assertSuccessful();
    $response->assertSee('Scan &amp; Pay via UPI', false);
    $response->assertSee('₹450.00');
    $response->assertSee('flavourflow@okhdfcbank');
    $response->assertSee('FlavourFlow Spices');
    $response->assertSee('ORD-20261029-UPI111');
    $response->assertSee('upi://pay?pa=flavourflow@okhdfcbank');
    $response->assertSee('UPI PAYMENT — MANUAL CONFIRMATION REQUIRED');
    $response->assertSee('<svg', false); // SVG QR code present
});

test('displaying the QR page alone never marks the order as paid', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-20261029-QRONLY',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Customer',
        'mobile' => '9999999999',
        'email' => 'cust@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200.00,
        'delivery_charge' => 50.00,
        'total_amount' => 250.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 250.00,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    // View QR page multiple times
    $this->actingAs($user)->get(route('checkout.upi.show', $order->order_number));
    $this->actingAs($user)->get(route('checkout.upi.show', $order->order_number));

    // Order status and payment status MUST strictly remain Pending
    expect($order->fresh()->status)->toBe('Pending')
        ->and($payment->fresh()->status)->toBe('pending');
});

test('customer clicking I Have Paid records acknowledgment but keeps order and payment Pending', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-20261029-ACK001',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Customer',
        'mobile' => '9999999999',
        'email' => 'cust@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500.00,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    // Customer clicks "I Have Paid" and submits their optional UPI UTR reference
    $response = $this->actingAs($user)->post(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => '429812345678',
    ]);

    $response->assertRedirect(route('checkout.success'));

    // CRITICAL: Order status MUST still be Pending (Rule 8)
    expect($order->fresh()->status)->toBe('Pending');

    // CRITICAL: Payment status MUST still be pending (Rule 8)
    $refreshedPayment = $payment->fresh();
    expect($refreshedPayment->status)->toBe('pending')
        ->and($refreshedPayment->transaction_id)->toBe('429812345678')
        ->and($refreshedPayment->payment_details['customer_transaction_ref'])->toBe('429812345678')
        ->and($refreshedPayment->payment_details['customer_acknowledged_at'])->not->toBeNull();
});

test('user cannot view or confirm UPI payment for another user order', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-20261029-SECRET',
        'user_id' => $userA->id,
        'status' => 'Pending',
        'name' => 'User A',
        'mobile' => '9999999999',
        'email' => 'a@example.com',
        'address' => 'Address A',
        'city' => 'City A',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    // User B tries to view User A's order QR
    $this->actingAs($userB)
        ->get(route('checkout.upi.show', $order->order_number))
        ->assertForbidden();

    // User B tries to confirm User A's order
    $this->actingAs($userB)
        ->post(route('checkout.upi.confirm', $order->order_number), ['transaction_ref' => 'fake'])
        ->assertForbidden();
});

test('admin can confirm UPI order and update its status to Confirmed', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-20261029-ADMINCONF',
        'user_id' => $customer->id,
        'status' => 'Pending',
        'name' => 'Customer',
        'mobile' => '9999999999',
        'email' => 'customer@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    // Admin views order
    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertSuccessful()
        ->assertSee('UPI QR Code')
        ->assertSee('MANUAL CONFIRMATION');

    // Admin confirms order using existing updateStatus flow
    $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
        'status' => 'Confirmed',
    ]);

    $response->assertSessionHas('success');
    expect($order->fresh()->status)->toBe('Confirmed')
        ->and($order->fresh()->confirmed_at)->not->toBeNull();
});

test('pending UPI order does not trigger mandatory review modal before payment is confirmed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Garam Masala', 'price' => 200]);

    $order = Order::create([
        'order_number' => 'ORD-20261029-NO-REVIEW',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Customer',
        'mobile' => '9999999999',
        'email' => 'customer@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200.00,
        'delivery_charge' => 50.00,
        'total_amount' => 250.00,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'unit' => '100g',
        'quantity' => 1,
        'unit_price' => 200.00,
        'total_price' => 200.00,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'is_demo' => false,
        'transaction_id' => 'UPI-ORD-NO-REVIEW-01',
        'amount' => 250.00,
        'currency' => 'INR',
        'status' => 'pending',
        'payment_method' => 'upi',
    ]);

    // 1. Visiting UPI QR checkout page must not include the mandatory review modal
    $this->actingAs($user)
        ->get(route('checkout.upi.show', $order->order_number))
        ->assertSuccessful()
        ->assertDontSee('id="mandatory-review-modal"', false);

    // 2. Pending reviews endpoint must not return this pending UPI order
    $this->actingAs($user)
        ->getJson(route('reviews.pending'))
        ->assertSuccessful()
        ->assertJson([
            'has_pending' => false,
        ]);
});

test('transaction reference is strictly mandatory when submitting I Have Paid', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-MANDATORY-UTR',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Test User',
        'mobile' => '9999999999',
        'email' => 'user@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    // Submitting with empty transaction_ref
    $response = $this->actingAs($user)
        ->postJson(route('checkout.upi.confirm', $order->order_number), [
            'transaction_ref' => '',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['transaction_ref']);

    expect($order->fresh()->status)->toBe('Pending');
});

test('submitting invalid transaction reference characters or length is rejected', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-INVALID-UTR',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Test User',
        'mobile' => '9999999999',
        'email' => 'user@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    // Too short (< 6 chars)
    $this->actingAs($user)
        ->postJson(route('checkout.upi.confirm', $order->order_number), [
            'transaction_ref' => '12345',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['transaction_ref']);

    // Invalid characters (e.g. spaces or symbols)
    $this->actingAs($user)
        ->postJson(route('checkout.upi.confirm', $order->order_number), [
            'transaction_ref' => 'INV@LID UTR',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['transaction_ref']);
});

test('order placement and QR display alone sends NO OTP email or order confirmation email', function () {
    Mail::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 300, 'quantity' => 10]);

    CartItem::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 300,
        'line_total' => 300,
        'image_path' => $product->image_path,
    ]);

    // 1. Checkout store
    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'name' => 'Aarav Patel',
        'mobile' => '+91 9876543210',
        'email' => 'aarav@example.com',
        'address' => 'Heritage Square',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
    ]);

    $order = Order::where('user_id', $user->id)->latest()->first();
    $response->assertRedirect(route('checkout.upi.show', $order->order_number));

    // 2. View QR page
    $this->actingAs($user)->get(route('checkout.upi.show', $order->order_number))->assertSuccessful();

    // STRICT CHECK: Zero OTP and confirmation emails sent
    Mail::assertNothingSent();
    expect($order->fresh()->status)->toBe('Pending')
        ->and($order->fresh()->payment_status)->toBe('pending')
        ->and($order->fresh()->order_confirmation_otp)->toBeNull();
});

test('failed verification with DEMO-FAIL keeps order Pending and sends zero OTP emails', function () {
    $this->setting->update(['active_gateway' => 'demo']);
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-DEMO-FAIL-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Test User',
        'mobile' => '9999999999',
        'email' => 'user@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => 'DEMO-FAIL-999999',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
        ]);

    Mail::assertNothingSent();
    expect($order->fresh()->status)->toBe('Pending')
        ->and($order->fresh()->payment_status)->toBe('pending')
        ->and($order->fresh()->order_confirmation_otp)->toBeNull();
});

test('pending settlement with DEMO-PENDING keeps order Pending and sends zero OTP emails', function () {
    $this->setting->update(['active_gateway' => 'demo']);
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-DEMO-PENDING-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Test User',
        'mobile' => '9999999999',
        'email' => 'user@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => 'DEMO-PENDING-111111',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'PENDING_VERIFICATION',
        ]);

    Mail::assertNothingSent();
    expect($order->fresh()->status)->toBe('Pending')
        ->and($order->fresh()->payment_status)->toBe('pending')
        ->and($order->fresh()->order_confirmation_otp)->toBeNull();
});

test('amount mismatch with DEMO-MISMATCH keeps order Pending and sends zero OTP emails', function () {
    $this->setting->update(['active_gateway' => 'demo']);
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-DEMO-MISMATCH-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Test User',
        'mobile' => '9999999999',
        'email' => 'user@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => 'DEMO-MISMATCH-222222',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'REFERENCE_MISMATCH',
        ]);

    Mail::assertNothingSent();
    expect($order->fresh()->status)->toBe('Pending')
        ->and($order->fresh()->payment_status)->toBe('pending')
        ->and($order->fresh()->order_confirmation_otp)->toBeNull();
});

test('successful payment verification with DEMO-SUCCESS atomically fulfills order, deducts stock, and sends OrderConfirmationOtp email', function () {
    $this->setting->update(['active_gateway' => 'demo']);
    Mail::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 300, 'quantity' => 10]);

    $order = Order::create([
        'order_number' => 'ORD-DEMO-SUCCESS-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Aarav Patel',
        'mobile' => '9876543210',
        'email' => 'aarav@example.com',
        'address' => 'Heritage Square',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 300,
        'delivery_charge' => 50,
        'total_amount' => 350,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'unit' => '100g',
        'quantity' => 2,
        'unit_price' => 150,
        'total_price' => 300,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 350,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $initialStock = $product->fresh()->quantity;

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => 'DEMO-SUCCESS-888888',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'status' => 'VERIFIED_SUCCESS',
            'redirect_url' => route('checkout.success'),
        ]);

    // Order must be atomically confirmed
    $refreshedOrder = $order->fresh();
    expect($refreshedOrder->status)->toBe('Confirmed')
        ->and($refreshedOrder->payment_status)->toBe('paid')
        ->and($refreshedOrder->payment_verified_at)->not->toBeNull()
        ->and($refreshedOrder->confirmed_at)->not->toBeNull()
        ->and($refreshedOrder->order_confirmation_otp)->not->toBeNull()
        ->and(strlen($refreshedOrder->order_confirmation_otp))->toBe(6);

    // Payment must be captured
    $refreshedPayment = $payment->fresh();
    expect($refreshedPayment->status)->toBe('captured')
        ->and($refreshedPayment->verification_status)->toBe('verified')
        ->and($refreshedPayment->verified_at)->not->toBeNull();

    // Stock must be decremented upon payment fulfillment
    expect($product->fresh()->quantity)->toBe($initialStock - 2);

    // Confirmation OTP must be dispatched
    Mail::assertSent(OrderConfirmationOtp::class, function ($mail) use ($refreshedOrder) {
        return $mail->hasTo('aarav@example.com')
            && $mail->otp === $refreshedOrder->order_confirmation_otp;
    });

    // Success page displays the OTP confirmation badge
    $this->actingAs($user)
        ->withSession(['placed_order_id' => $order->id])
        ->get(route('checkout.success'))
        ->assertSuccessful()
        ->assertSee('PAID &amp; VERIFIED', false)
        ->assertSee($refreshedOrder->order_confirmation_otp);
});

test('duplicate transaction reference cannot be reused across different orders', function () {
    $this->setting->update(['active_gateway' => 'demo']);
    Mail::fake();

    $user = User::factory()->create();

    // Order 1: Verified
    $order1 = Order::create([
        'order_number' => 'ORD-DUP-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'User 1',
        'mobile' => '9999999999',
        'email' => 'user1@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total_amount' => 250,
    ]);

    Payment::create([
        'order_id' => $order1->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 250,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order1->order_number), [
        'transaction_ref' => 'DEMO-SUCCESS-UNIQUE99',
    ])->assertSuccessful();

    expect($order1->fresh()->status)->toBe('Confirmed');

    // Order 2: Tries to reuse the exact same reference
    $order2 = Order::create([
        'order_number' => 'ORD-DUP-02',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'User 2',
        'mobile' => '9999999999',
        'email' => 'user2@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total_amount' => 250,
    ]);

    Payment::create([
        'order_id' => $order2->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 250,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response2 = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order2->order_number), [
        'transaction_ref' => 'DEMO-SUCCESS-UNIQUE99',
    ]);

    $response2->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
        ]);

    expect($order2->fresh()->status)->toBe('Pending')
        ->and($order2->fresh()->payment_status)->toBe('pending');
});

test('predictable demo tokens cannot confirm live orders when demo mode is not active', function () {
    $this->setting->update(['active_gateway' => 'cashfree']); // Live gateway configured

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-ATTACK-LIVE-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'is_demo' => false,
        'name' => 'Attacker',
        'mobile' => '9999999999',
        'email' => 'attacker@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'is_demo' => false,
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => 'DEMO-SUCCESS-ATTACK',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
            'message' => 'Simulated demo tokens cannot be used to confirm real orders.',
        ]);

    expect($order->fresh()->status)->toBe('Pending')
        ->and($order->fresh()->payment_status)->toBe('pending');
});

test('demo payment verification is strictly forbidden in production environment', function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->setting->update(['active_gateway' => 'demo']);

    // Simulate production environment
    app()->detectEnvironment(fn () => 'production');

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-PROD-ATTACK-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Attacker',
        'mobile' => '9999999999',
        'email' => 'attacker@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-csrf-token'])
        ->withHeader('X-CSRF-TOKEN', 'test-csrf-token')
        ->postJson(route('checkout.upi.confirm', $order->order_number), [
            'transaction_ref' => 'DEMO-SUCCESS-999999',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
        ]);

    expect($order->fresh()->status)->toBe('Pending')
        ->and($order->fresh()->payment_status)->toBe('pending');

    // Reset environment
    app()->detectEnvironment(fn () => 'testing');
});

test('repeated failed verification attempts on same payment are throttled after maximum threshold', function () {
    $this->setting->update(['active_gateway' => 'demo']);

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-BRUTE-FORCE-01',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'Attacker',
        'mobile' => '9999999999',
        'email' => 'attacker@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 500,
        'delivery_charge' => 0,
        'total_amount' => 500,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'verification_attempts' => 10, // Max attempts reached
        'amount' => 500,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $order->order_number), [
        'transaction_ref' => 'DEMO-SUCCESS-BRUTE',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
            'message' => 'Maximum verification attempts exceeded for this payment. Please contact store support with your payment receipt.',
        ]);

    expect($order->fresh()->status)->toBe('Pending');
});

test('submitting a transaction reference already pending review on another order is rejected', function () {
    $user = User::factory()->create();

    // Order A has pending UTR
    $orderA = Order::create([
        'order_number' => 'ORD-PENDING-A',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'User A',
        'mobile' => '9999999999',
        'email' => 'a@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total_amount' => 250,
    ]);

    Payment::create([
        'order_id' => $orderA->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'customer_utr' => '429876543210',
        'amount' => 250,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    // Order B tries to submit the same UTR
    $orderB = Order::create([
        'order_number' => 'ORD-PENDING-B',
        'user_id' => $user->id,
        'status' => 'Pending',
        'payment_status' => 'pending',
        'name' => 'User B',
        'mobile' => '9999999998',
        'email' => 'b@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total_amount' => 250,
    ]);

    Payment::create([
        'order_id' => $orderB->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 250,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    $response = $this->actingAs($user)->postJson(route('checkout.upi.confirm', $orderB->order_number), [
        'transaction_ref' => '429876543210',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
            'message' => 'This transaction reference is already pending review on another order.',
        ]);

    expect($orderB->fresh()->status)->toBe('Pending');
});

test('cannot view or pay for a cancelled UPI order', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-CANCELLED-01',
        'user_id' => $user->id,
        'status' => 'Cancelled',
        'payment_status' => 'pending',
        'name' => 'Customer',
        'mobile' => '9999999999',
        'email' => 'cust@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total_amount' => 250,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'upi',
        'status' => 'pending',
        'amount' => 250,
        'currency' => 'INR',
        'payment_method' => 'upi',
    ]);

    // View redirected
    $this->actingAs($user)
        ->get(route('checkout.upi.show', $order->order_number))
        ->assertRedirect(route('account.orders.show', $order->order_number));

    // Confirm rejected
    $this->actingAs($user)
        ->postJson(route('checkout.upi.confirm', $order->order_number), [
            'transaction_ref' => '429812345678',
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'status' => 'VERIFICATION_FAILED',
        ]);
});

test('user cannot view order success receipt of another user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-IDOR-RECEIPT',
        'user_id' => $userA->id,
        'status' => 'Confirmed',
        'payment_status' => 'paid',
        'name' => 'User A',
        'mobile' => '9999999999',
        'email' => 'a@example.com',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'upi',
        'subtotal' => 200,
        'delivery_charge' => 50,
        'total_amount' => 250,
    ]);

    // User B tries to view User A's order on success screen
    $this->actingAs($userB)
        ->withSession(['placed_order_id' => $order->id])
        ->get(route('checkout.success'))
        ->assertForbidden();
});
