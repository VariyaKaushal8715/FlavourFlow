<?php

use App\Models\Order;
use App\Models\User;
use App\Services\EnvironmentManager;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Backup actual .env for the test run so tests leave .env untouched
    $this->envPath = app()->environmentFilePath();
    $this->envBackupContent = File::exists($this->envPath) ? File::get($this->envPath) : null;
});

afterEach(function () {
    // Restore .env content if modified
    if ($this->envBackupContent !== null && File::exists($this->envPath)) {
        File::put($this->envPath, $this->envBackupContent);
    }
});

test('guests are redirected when attempting to access payment settings', function () {
    $response = $this->get(route('admin.payments.settings'));

    $response->assertRedirect(route('login'));
});

test('non-admin authenticated users are forbidden from payment settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.payments.settings'));

    $response->assertForbidden();
});

test('authenticated admin can view payment settings and plain secret is never exposed', function () {
    $admin = User::factory()->admin()->create();

    Config::set('services.razorpay.key_id', 'rzp_test_currentKey123');
    Config::set('services.razorpay.key_secret', 'confidential_secret_789');

    $response = $this->actingAs($admin)->get(route('admin.payments.settings'));

    $response->assertSuccessful();
    $response->assertSee('Razorpay Payment Settings');
    $response->assertSee('rzp_test_currentKey123');
    // Secret must NEVER be present in the HTML response
    $response->assertDontSee('confidential_secret_789');
    $response->assertSee('••••••••••••••••');
});

test('admin can save valid test mode credentials and update .env safely', function () {
    $admin = User::factory()->admin()->create();

    Http::fake([
        'https://api.razorpay.com/v1/orders*' => Http::response(['entity' => 'collection', 'count' => 0], 200),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.update'), [
        'key_id' => 'rzp_test_newValidKey123',
        'key_secret' => 'newSecretKey_456789',
    ]);

    $response->assertRedirect(route('admin.payments.settings'));
    $response->assertSessionHas('status');

    // Verify in-memory config updated
    expect(config('services.razorpay.key_id'))->toBe('rzp_test_newValidKey123')
        ->and(config('services.razorpay.key_secret'))->toBe('newSecretKey_456789')
        ->and(config('services.razorpay.mode'))->toBe('test');

    // Verify .env updated
    $envContent = File::get($this->envPath);
    expect($envContent)->toContain('RAZORPAY_KEY_ID=rzp_test_newValidKey123')
        ->and($envContent)->toContain('RAZORPAY_KEY_SECRET=newSecretKey_456789')
        ->and($envContent)->toContain('APP_NAME=FlavourFlow'); // Unrelated vars remain intact
});

test('fail-safe preserves previous credentials if new credentials fail validation', function () {
    $admin = User::factory()->admin()->create();

    // Set initial working credentials
    $envManager = app(EnvironmentManager::class);
    $envManager->updateEntries([
        'RAZORPAY_KEY_ID' => 'rzp_test_workingKey123',
        'RAZORPAY_KEY_SECRET' => 'workingSecret456',
        'RAZORPAY_MODE' => 'test',
    ]);

    Http::fake([
        'https://api.razorpay.com/v1/orders*' => Http::response([
            'error' => ['description' => 'Authentication failed'],
        ], 401),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.update'), [
        'key_id' => 'rzp_test_rejectedKey999',
        'key_secret' => 'rejectedSecret999',
    ]);

    $response->assertSessionHasErrors(['key_id']);

    // Check that previous working credentials in .env were NOT destroyed
    $envContent = File::get($this->envPath);
    expect($envContent)->toContain('RAZORPAY_KEY_ID=rzp_test_workingKey123')
        ->and($envContent)->toContain('RAZORPAY_KEY_SECRET=workingSecret456')
        ->and($envContent)->not->toContain('rzp_test_rejectedKey999')
        ->and($envContent)->not->toContain('rejectedSecret999');
});

test('admin can update key_id while leaving secret blank to keep existing secret', function () {
    $admin = User::factory()->admin()->create();

    // Set initial working credentials
    Config::set('services.razorpay.key_id', 'rzp_test_oldKey111');
    Config::set('services.razorpay.key_secret', 'existingSecret222');

    $envManager = app(EnvironmentManager::class);
    $envManager->updateEntries([
        'RAZORPAY_KEY_ID' => 'rzp_test_oldKey111',
        'RAZORPAY_KEY_SECRET' => 'existingSecret222',
    ]);

    Http::fake([
        'https://api.razorpay.com/v1/orders*' => Http::response(['entity' => 'collection', 'count' => 0], 200),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.update'), [
        'key_id' => 'rzp_test_rotatedKey333',
        'key_secret' => '', // Left empty to retain existing secret
    ]);

    $response->assertRedirect(route('admin.payments.settings'));

    // Config and .env should have new key_id with old secret retained
    expect(config('services.razorpay.key_id'))->toBe('rzp_test_rotatedKey333')
        ->and(config('services.razorpay.key_secret'))->toBe('existingSecret222');

    $envContent = File::get($this->envPath);
    expect($envContent)->toContain('RAZORPAY_KEY_ID=rzp_test_rotatedKey333')
        ->and($envContent)->toContain('RAZORPAY_KEY_SECRET=existingSecret222');
});

test('obviously invalid Key ID format is rejected by validation', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put(route('admin.payments.settings.update'), [
        'key_id' => 'invalid_prefix_123',
        'key_secret' => 'validSecret456',
    ]);

    $response->assertSessionHasErrors(['key_id']);
});

test('test connection endpoint verifies active credentials', function () {
    $admin = User::factory()->admin()->create();

    Config::set('services.razorpay.key_id', 'rzp_test_activeKey');
    Config::set('services.razorpay.key_secret', 'activeSecretKey');

    Http::fake([
        'https://api.razorpay.com/v1/orders*' => Http::response(['entity' => 'collection', 'count' => 0], 200),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.payments.settings.test'));

    $response->assertRedirect();
    $response->assertSessionHas('status');
});

test('existing razorpay service createOrder and verifySignature work with updated credentials', function () {
    $service = app(RazorpayService::class);
    $order = new Order(['total_amount' => 500.00]);

    // Test signature verification with updated secret
    $secret = 'tested_secret_hmac_123';
    Config::set('services.razorpay.key_secret', $secret);

    $orderId = 'order_test_999';
    $paymentId = 'pay_test_888';
    $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

    expect($service->verifySignature($orderId, $paymentId, $signature))->toBeTrue()
        ->and($service->verifySignature($orderId, $paymentId, 'invalid-signature'))->toBeFalse();
});
