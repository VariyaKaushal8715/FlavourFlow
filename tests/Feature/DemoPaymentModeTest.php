<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAuditLog;
use App\Models\PaymentGatewaySetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Ensure zero credentials configured by default
    Config::set('services.razorpay.key_id', null);
    Config::set('services.razorpay.key_secret', null);
    Config::set('services.cashfree.app_id', null);
    Config::set('services.cashfree.secret_key', null);
});

function addTestCartItem(User $user, Product $product, int $quantity = 2): CartItem
{
    return CartItem::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => $quantity,
        'unit_price' => $product->price,
        'line_total' => $product->price * $quantity,
        'image_path' => $product->image_path,
    ]);
}

test('customer can complete a demo payment with zero credentials and simulated success', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 250.00,
        'quantity' => 10,
    ]);

    addTestCartItem($user, $product, 2);

    $checkoutData = [
        'name' => 'Demo Tester',
        'email' => 'tester@example.com',
        'mobile' => '9876543210',
        'address' => '123 Demo Street',
        'city' => 'Demo City',
        'state' => 'Gujarat',
        'pincode' => '380001',
        'country' => 'India',
        'payment_method' => 'demo',
        'demo_outcome' => 'success',
    ];

    $response = $this->actingAs($user)->postJson(route('checkout.store'), $checkoutData);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'is_demo' => true,
        'gateway' => 'demo',
    ]);

    // Order assertions
    $order = Order::where('email', 'tester@example.com')->first();
    expect($order)->not->toBeNull()
        ->and($order->is_demo)->toBeTrue()
        ->and($order->isDemo())->toBeTrue()
        ->and($order->payment_method)->toBe('demo');

    // Payment assertions
    $payment = Payment::where('order_id', $order->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->is_demo)->toBeTrue()
        ->and($payment->isDemo())->toBeTrue()
        ->and($payment->status)->toBe('captured')
        ->and($payment->payment_method)->toBe('demo');

    // Cart must be cleared on success
    expect(CartItem::where('user_id', $user->id)->count())->toBe(0);

    // Inventory quantity must be decremented
    expect($product->fresh()->quantity)->toBe(8);

    // Success view must prominently display DEMO — NO REAL PAYMENT
    $successView = $this->actingAs($user)->get(route('checkout.success', $order->order_number));
    $successView->assertSuccessful();
    $successView->assertSee('DEMO — NO REAL PAYMENT');
});

test('customer simulated failure preserves cart and does not decrement stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 300.00,
        'quantity' => 15,
    ]);

    addTestCartItem($user, $product, 3);

    $checkoutData = [
        'name' => 'Demo Tester Failure',
        'email' => 'failure@example.com',
        'mobile' => '9876543210',
        'address' => '456 Test Lane',
        'city' => 'Sim City',
        'state' => 'Gujarat',
        'pincode' => '380002',
        'country' => 'India',
        'payment_method' => 'demo',
        'demo_outcome' => 'failure',
    ];

    $response = $this->actingAs($user)->postJson(route('checkout.store'), $checkoutData);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'is_demo' => true,
        'gateway' => 'demo',
    ]);

    // Cart must remain completely intact
    expect(CartItem::where('user_id', $user->id)->count())->toBe(1);

    // Stock must NOT be decremented
    expect($product->fresh()->quantity)->toBe(15);
});

test('demo transactions are strictly isolated and rejected by real gateway verification', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'is_demo' => true,
        'status' => 'Pending Payment',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'provider' => 'demo',
        'payment_method' => 'demo',
        'amount' => 500.00,
        'currency' => 'INR',
        'is_demo' => true,
        'status' => 'created',
        'transaction_id' => 'DEMO_TXN_ISOLATION_TEST',
    ]);

    // Cashfree verification endpoint must reject demo transactions
    $cfResponse = $this->actingAs($user)->postJson(route('checkout.cashfree.verify'), [
        'order_id' => $order->order_number,
    ]);
    $cfResponse->assertStatus(400);
    $cfResponse->assertJson([
        'success' => false,
    ]);
    expect($cfResponse->json('message'))->toContain('Simulated demo transactions cannot be verified with real Cashfree gateway.');

    // Razorpay verification endpoint must reject demo transactions
    $rzpResponse = $this->actingAs($user)->postJson(route('checkout.razorpay.verify'), [
        'razorpay_order_id' => $order->order_number,
        'razorpay_payment_id' => 'pay_demo_isolation_test',
        'razorpay_signature' => 'mock_signature',
    ]);
    $rzpResponse->assertStatus(400);
    $rzpResponse->assertJson([
        'success' => false,
    ]);
    expect($rzpResponse->json('message'))->toContain('Simulated demo transactions cannot be verified with real Razorpay gateway.');
});

test('admin can switch to demo mode with current password confirmation', function () {
    $admin = User::factory()->admin()->create([
        'password' => Hash::make('secret_admin_pass'),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.gateway.update'), [
        'active_gateway' => 'demo',
        'environment' => 'sandbox',
        'current_password' => 'secret_admin_pass',
    ]);

    $response->assertRedirect(route('admin.payments.settings'));
    $response->assertSessionHas('status');

    $setting = PaymentGatewaySetting::current();
    expect($setting->active_gateway)->toBe('demo')
        ->and($setting->environment)->toBe('sandbox');

    // Audit log recorded
    $log = PaymentAuditLog::latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('gateway_mode_updated')
        ->and($log->gateway)->toBe('demo');
});

test('admin cannot update payment settings with incorrect password', function () {
    $admin = User::factory()->admin()->create([
        'password' => Hash::make('correct_password'),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.gateway.update'), [
        'active_gateway' => 'demo',
        'environment' => 'sandbox',
        'current_password' => 'wrong_password_attempt',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

test('admin cannot enable live mode without verified merchant credentials', function () {
    $admin = User::factory()->admin()->create([
        'password' => Hash::make('admin_password'),
    ]);

    // Attempting to activate Cashfree Live without credentials
    $response = $this->actingAs($admin)->put(route('admin.payments.settings.gateway.update'), [
        'active_gateway' => 'cashfree',
        'environment' => 'live',
        'current_password' => 'admin_password',
    ]);

    $response->assertSessionHasErrors(['environment']);
});

test('admin payment settings page masks sensitive secrets and logs securely', function () {
    $admin = User::factory()->admin()->create();

    $setting = PaymentGatewaySetting::current();
    $setting->update([
        'razorpay_key_id' => 'rzp_test_public_key',
        'razorpay_key_secret' => 'super_secret_private_key_xyz',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.payments.settings'));

    $response->assertSuccessful();
    $response->assertSee('rzp_test_public_key');
    // Plain secret must NEVER be visible in HTML or rendered output
    $response->assertDontSee('super_secret_private_key_xyz');
    $response->assertSee('••••••••••••••••');
});
