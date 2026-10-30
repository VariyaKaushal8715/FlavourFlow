<?php

use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guest cannot access admin business settings', function () {
    $this->get(route('admin.business.settings'))
        ->assertRedirect(route('login'));
});

test('non-admin user is forbidden from admin business settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.business.settings'))
        ->assertForbidden();
});

test('admin can view and update business settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.business.settings'))
        ->assertOk()
        ->assertSee('Business Details & Invoicing');

    $payload = [
        'store_name' => 'FlavourFlow Spices Private Limited',
        'legal_name' => 'FlavourFlow India Retail Pvt Ltd',
        'business_address' => 'Plot 42, Spice Valley, Ring Road, Surat, Gujarat 395002',
        'billing_address' => 'Corporate Hub, Floor 4, Surat 395002',
        'phone' => '+91 98765 43210',
        'email' => 'billing@flavourflow.test',
        'website_url' => 'https://flavourflow.test',
        'gstin' => '24ABCDE1234F1Z5',
        'invoice_prefix' => 'FF-INV',
        'next_invoice_number' => 105,
        'tax_mode' => 'inclusive',
        'tax_rate' => 5.0,
        'tax_label' => 'GST (Inclusive 5%)',
        'invoice_footer' => 'Thank you for choosing FlavourFlow!',
        'payment_instructions' => 'UPI Payments: Scan QR & enter UTR reference.',
        'return_policy_text' => '7-day return policy for sealed spice packets.',
    ];

    $response = $this->actingAs($admin)
        ->put(route('admin.business.settings.update'), $payload);

    $response->assertRedirect(route('admin.business.settings'))
        ->assertSessionHas('status');

    $settings = BusinessSetting::current();
    expect($settings->store_name)->toBe('FlavourFlow Spices Private Limited');
    expect($settings->legal_name)->toBe('FlavourFlow India Retail Pvt Ltd');
    expect($settings->gstin)->toBe('24ABCDE1234F1Z5');
    expect($settings->invoice_prefix)->toBe('FF-INV');
    expect($settings->next_invoice_number)->toBe(105);
});

test('logo upload is strictly validated and stored securely', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);

    // 1. Invalid file extension should fail validation
    $fakeTxt = UploadedFile::fake()->create('malicious.php', 100);
    $this->actingAs($admin)
        ->put(route('admin.business.settings.update'), [
            'store_name' => 'FlavourFlow',
            'business_address' => 'Surat, Gujarat',
            'phone' => '+91 98765 43210',
            'email' => 'test@flavourflow.test',
            'invoice_prefix' => 'INV',
            'next_invoice_number' => 1,
            'tax_mode' => 'inclusive',
            'logo' => $fakeTxt,
        ])
        ->assertSessionHasErrors(['logo']);

    // 2. Valid image is accepted and stored
    $fakeImage = UploadedFile::fake()->image('brand-logo.png', 200, 200);
    $this->actingAs($admin)
        ->put(route('admin.business.settings.update'), [
            'store_name' => 'FlavourFlow Spices',
            'business_address' => 'Surat, Gujarat',
            'phone' => '+91 98765 43210',
            'email' => 'test@flavourflow.test',
            'invoice_prefix' => 'INV',
            'next_invoice_number' => 1,
            'tax_mode' => 'inclusive',
            'logo' => $fakeImage,
        ])
        ->assertSessionHasNoErrors();

    $settings = BusinessSetting::current();
    expect($settings->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($settings->logo_path);

    // 3. Admin can remove custom logo
    $this->actingAs($admin)
        ->delete(route('admin.business.settings.logo.destroy'))
        ->assertRedirect(route('admin.business.settings'));

    $settings->refresh();
    expect($settings->logo_path)->toBeNull();
});

test('invoice snapshot preserves historical business details on past orders', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Organic Turmeric',
        'price' => 150.00,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-SNAP-001',
        'payment_status' => 'paid',
        'status' => 'confirmed',
        'subtotal' => 150.00,
        'delivery_charge' => 0.00,
        'discount_amount' => 0.00,
        'total_amount' => 150.00,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'unit_price' => 150.00,
        'total_price' => 150.00,
    ]);

    $invoiceService = app(InvoiceService::class);
    $snapshot = $invoiceService->getOrCreateSnapshot($order);

    expect($order->fresh()->invoice_number)->not->toBeNull();
    $originalStoreName = $snapshot['business']['name'];

    // Admin now updates store name in BusinessSetting
    $settings = BusinessSetting::current();
    $settings->update(['store_name' => 'COMPLETELY NEW STORE NAME']);

    // Order snapshot must remain unchanged (immutable historical record)
    $secondSnapshot = $invoiceService->getOrCreateSnapshot($order->fresh());
    expect($secondSnapshot['business']['name'])->toBe($originalStoreName);
});

test('customer can download their own invoice but cannot download another users invoice', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $orderA = Order::factory()->create([
        'user_id' => $userA->id,
        'order_number' => 'ORD-USER-A-01',
        'payment_status' => 'paid',
        'status' => 'confirmed',
        'total_amount' => 200.00,
    ]);

    // User A can download their own invoice
    $response = $this->actingAs($userA)
        ->get(route('account.orders.invoice', ['order' => $orderA->order_number]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf');

    // User B is forbidden from downloading User A's invoice
    $this->actingAs($userB)
        ->get(route('account.orders.invoice', ['order' => $orderA->order_number]))
        ->assertForbidden();
});

test('admin can download invoice for any customer order', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create(['is_admin' => false]);

    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'order_number' => 'ORD-ADMIN-DOWNLOAD-01',
        'payment_status' => 'paid',
        'status' => 'confirmed',
        'total_amount' => 300.00,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.orders.invoice.download', ['order' => $order]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf');
});

test('unverified direct UPI order is labeled proforma invoice and payment pending', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-UPI-UNVERIFIED-01',
        'payment_method' => 'upi',
        'payment_status' => 'pending',
        'status' => 'pending',
        'total_amount' => 248.00,
    ]);

    $invoiceService = app(InvoiceService::class);
    $snapshot = $invoiceService->getOrCreateSnapshot($order);

    expect($snapshot['invoice']['document_type'])->toBe('PROFORMA INVOICE / ORDER SUMMARY');
    expect($snapshot['invoice']['payment_status_label'])->toContain('PAYMENT PENDING');

    $pdfBytes = $invoiceService->generatePdf($order);
    expect($pdfBytes)->toStartWith('%PDF-1.4');
    expect($pdfBytes)->toContain('PROFORMA INVOICE');
    expect($pdfBytes)->toContain('PAYMENT PENDING');
});
