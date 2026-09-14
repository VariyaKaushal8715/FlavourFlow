<?php

use App\Events\OrderPlaced;
use App\Mail\AdminNewOrderAlertMail;
use App\Mail\CustomerOrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderDeliveryNotification;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderNotificationService;
use App\Services\WhatsApp\Providers\MetaWhatsAppProvider;
use App\Services\WhatsApp\Providers\TwilioWhatsAppProvider;
use App\Services\WhatsApp\WhatsAppSendResult;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Mail::fake();
    config([
        'order_notifications.whatsapp.provider' => 'log',
        'order_notifications.whatsapp.enabled' => true,
        'order_notifications.email.enabled' => true,
        'order_notifications.admin.email' => 'admin@flavourflow.test',
        'order_notifications.admin.whatsapp' => '+919999999999',
    ]);
});

test('successful order dispatches WhatsApp and Email notifications to customer and admin', function () {
    $user = User::factory()->create([
        'email' => 'customer@example.com',
    ]);

    $product = Product::factory()->create([
        'name' => 'Royal Garam Masala',
        'price' => 150.00,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-001',
        'name' => 'John Doe',
        'mobile' => '+919876543210',
        'email' => 'customer@example.com',
        'address' => '123 Spice Street',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380001',
        'country' => 'India',
        'subtotal' => 300.00,
        'delivery_charge' => 50.00,
        'total_amount' => 350.00,
        'status' => 'Confirmed',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit' => '250g',
        'quantity' => 2,
        'unit_price' => 150.00,
        'total_price' => 300.00,
    ]);

    // Dispatch event
    OrderPlaced::dispatch($order);

    // Verify Customer Email sent
    Mail::assertSent(CustomerOrderConfirmationMail::class, function ($mail) use ($order) {
        return $mail->hasTo('customer@example.com') && $mail->order->id === $order->id;
    });

    // Verify Admin Email sent
    Mail::assertSent(AdminNewOrderAlertMail::class, function ($mail) use ($order) {
        return $mail->hasTo('admin@flavourflow.test') && $mail->order->id === $order->id;
    });

    // Verify Database Notification Records
    expect(OrderDeliveryNotification::where('order_id', $order->id)->count())->toBe(4);

    $customerWa = OrderDeliveryNotification::where('order_id', $order->id)
        ->where('channel', OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP)
        ->first();
    expect($customerWa)->not->toBeNull();
    expect($customerWa->status)->toBe(OrderDeliveryNotification::STATUS_SENT);
    expect($customerWa->recipient)->toBe('+919876543210');

    $adminWa = OrderDeliveryNotification::where('order_id', $order->id)
        ->where('channel', OrderDeliveryNotification::CHANNEL_ADMIN_WHATSAPP)
        ->first();
    expect($adminWa)->not->toBeNull();
    expect($adminWa->status)->toBe(OrderDeliveryNotification::STATUS_SENT);
});

test('notifications are idempotent and prevent duplicates on subsequent triggers', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'mobile' => '+919876543210',
        'email' => 'duplicate_check@example.com',
    ]);

    $service = app(OrderNotificationService::class);

    // First run
    $firstRun = $service->sendOrderConfirmation($order);
    expect($firstRun['customer_email'])->toBeTrue();
    expect($firstRun['customer_whatsapp'])->toBeTrue();

    // Verify 1 mail sent
    Mail::assertSent(CustomerOrderConfirmationMail::class, 1);

    // Second run (e.g. page refresh / retry)
    $secondRun = $service->sendOrderConfirmation($order);
    expect($secondRun['customer_email'])->toBeTrue(); // Returns true because already sent
    expect($secondRun['customer_whatsapp'])->toBeTrue();

    // Verify NO duplicate mail was sent
    Mail::assertSent(CustomerOrderConfirmationMail::class, 1);
});

test('notification failure does not interrupt processing or throw unhandled exceptions', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'mobile' => '+919876543210',
        'email' => 'error_check@example.com',
    ]);

    // Mock WhatsAppService to simulate provider failure
    $failingWhatsApp = Mockery::mock(WhatsAppService::class);
    $failingWhatsApp->shouldReceive('sendMessage')
        ->andReturn(WhatsAppSendResult::failure('Simulated WhatsApp API gateway error'));

    $service = new OrderNotificationService($failingWhatsApp);
    $results = $service->sendOrderConfirmation($order);

    expect($results['customer_whatsapp'])->toBeFalse();
    expect($results['customer_email'])->toBeTrue(); // Email still succeeded

    // Check failed record saved in DB
    $failedRecord = OrderDeliveryNotification::where('order_id', $order->id)
        ->where('channel', OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP)
        ->first();
    expect($failedRecord->status)->toBe(OrderDeliveryNotification::STATUS_FAILED);
    expect($failedRecord->error_message)->toContain('Simulated WhatsApp API gateway error');
});

test('secure signed tracking URL allows guest or customer access while invalid signature is rejected', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-SECURE-999',
        'status' => 'Confirmed',
    ]);

    $service = app(OrderNotificationService::class);
    $signedUrl = $service->generateSecureTrackingUrl($order);

    // 1. Access via valid signed URL
    $response = $this->get($signedUrl);
    $response->assertSuccessful();
    $response->assertSee($order->order_number);

    // 2. Access via tampered URL without signature
    $tamperedUrl = route('orders.track.signed', ['order' => $order->order_number]);
    $badResponse = $this->get($tamperedUrl);
    $badResponse->assertStatus(403);

    // 3. Authenticated owner can access their normal tracking route
    $this->actingAs($user)
        ->get(route('account.orders.track', $order->order_number))
        ->assertSuccessful();

    // 4. Another authenticated user cannot access unauthorized order
    $this->actingAs($otherUser)
        ->get(route('account.orders.track', $order->order_number))
        ->assertStatus(403);
});

test('twilio and meta whatsapp providers format and dispatch payloads correctly', function () {
    // Test Twilio Provider
    Http::fake([
        'https://api.twilio.com/*' => Http::response(['sid' => 'SM123456789'], 200),
        'https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.HBgL']]], 200),
    ]);

    $twilio = new TwilioWhatsAppProvider([
        'sid' => 'ACtest',
        'token' => 'tokentest',
        'from' => 'whatsapp:+14155238886',
    ]);

    $twilioResult = $twilio->send('+919876543210', 'Test Twilio Message');
    expect($twilioResult->success)->toBeTrue();
    expect($twilioResult->messageId)->toBe('SM123456789');

    // Test Meta Provider
    $meta = new MetaWhatsAppProvider([
        'apiUrl' => 'https://graph.facebook.com/v19.0',
        'phoneNumberId' => '100012345678',
        'accessToken' => 'EAAGtest',
    ]);

    $metaResult = $meta->send('+919876543210', 'Test Meta Message');
    expect($metaResult->success)->toBeTrue();
    expect($metaResult->messageId)->toBe('wamid.HBgL');
});
