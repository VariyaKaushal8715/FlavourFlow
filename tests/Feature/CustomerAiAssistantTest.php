<?php

use App\AI\Models\AiEvent;
use App\Models\Order;
use App\Models\PrivacyConsent;
use App\Models\Product;
use App\Models\User;

test('guest user can chat with the assistant and it returns success', function () {
    Product::factory()->create([
        'name' => 'Kashmiri Red Chilli',
        'category' => 'Whole Spices',
        'price' => 299.00,
        'quantity' => 25,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('ai.chat'), [
        'message' => 'Recommend Kashmiri Red Chilli',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'response' => [
                'id',
                'sender',
                'message',
                'language',
                'intent',
                'products',
                'actions',
                'follow_up_suggestions',
                'timestamp',
            ],
            'provider_available',
        ]);

    expect($response['success'])->toBeTrue();
    expect($response['response']['intent'])->not->toBeEmpty();
});

test('authenticated user can chat and history is stored and cleared', function () {
    $user = User::factory()->create();

    // 1. Initial Chat
    $response = $this->actingAs($user)->postJson(route('ai.chat'), [
        'message' => 'Recommend some whole spices',
    ]);

    $response->assertOk();
    expect($response['success'])->toBeTrue();

    // 2. Retrieve history
    $historyResponse = $this->actingAs($user)->getJson(route('ai.chat.history'));
    $historyResponse->assertOk()
        ->assertJsonStructure([
            'success',
            'history',
            'provider_available',
        ]);

    expect(count($historyResponse['history']))->toBeGreaterThan(0);

    // 3. Clear history
    $clearResponse = $this->actingAs($user)->deleteJson(route('ai.chat.clear'));
    $clearResponse->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'provider_available',
        ]);

    // 4. Verify history is empty now
    $emptyHistoryResponse = $this->actingAs($user)->getJson(route('ai.chat.history'));
    $emptyHistoryResponse->assertOk();
    expect($emptyHistoryResponse['history'])->toBeEmpty();
});

test('assistant understands and responds naturally across Gujarati, GujEnglish, Hindi, Hinglish, and English', function () {
    Product::factory()->create([
        'name' => 'Organic Turmeric',
        'category' => 'Ground Spices',
        'price' => 149.00,
        'quantity' => 40,
        'is_active' => true,
    ]);

    // 1. Gujarati
    $guRes = $this->postJson(route('ai.chat'), [
        'message' => 'હળદર નો ભાવ કેટલો છે?',
    ]);
    $guRes->assertOk();
    expect($guRes['response']['language'])->toBe('gu');
    expect($guRes['response']['message'])->toContain('₹149.00');

    // 2. GujEnglish
    $gujEngRes = $this->postJson(route('ai.chat'), [
        'message' => 'halder stock ma che?',
    ]);
    $gujEngRes->assertOk();
    expect($gujEngRes['response']['language'])->toBe('gujenglish');
    expect($gujEngRes['response']['message'])->toContain('stock ma available che');

    // 3. Hindi
    $hiRes = $this->postJson(route('ai.chat'), [
        'message' => 'हल्दी का क्या दाम है?',
    ]);
    $hiRes->assertOk();
    expect($hiRes['response']['language'])->toBe('hi');
    expect($hiRes['response']['message'])->toContain('₹149.00');

    // 4. Hinglish
    $hinglishRes = $this->postJson(route('ai.chat'), [
        'message' => 'bhai haldi powder ka price kya hai?',
    ]);
    $hinglishRes->assertOk();
    expect($hinglishRes['response']['language'])->toBe('hinglish');
    expect($hinglishRes['response']['message'])->toContain('₹149.00');

    // 5. English
    $enRes = $this->postJson(route('ai.chat'), [
        'message' => 'What is the price of Organic Turmeric?',
    ]);
    $enRes->assertOk();
    expect($enRes['response']['language'])->toBe('en');
    expect($enRes['response']['message'])->toContain('149.00');
});

test('assistant grounds responses in real database prices and stock and never invents products', function () {
    $realProduct = Product::factory()->create([
        'name' => 'Kashmiri Saffron',
        'category' => 'Whole Spices',
        'price' => 450.00,
        'quantity' => 12,
        'unit' => '1 g',
        'is_active' => true,
    ]);

    // Query real product
    $res = $this->postJson(route('ai.chat'), [
        'message' => 'How much is Kashmiri Saffron and is it in stock?',
    ]);

    $res->assertOk();
    expect($res['response']['message'])->toContain('₹450.00');
    expect($res['response']['products'])->toHaveCount(1);
    expect($res['response']['products'][0]['formatted_price'])->toBe('₹450.00');
    expect($res['response']['products'][0]['in_stock'])->toBeTrue();
    expect($res['response']['products'][0]['stock_quantity'])->toBe(12);

    // Query non-existent product - should not invent or claim it exists
    $fakeQuery = $this->postJson(route('ai.chat'), [
        'message' => 'Do you sell extra virgin olive oil?',
    ]);

    $fakeQuery->assertOk();
    // Message should honestly report item is not in catalog
    expect($fakeQuery['response']['message'])->toContain("don't have 'extra virgin olive oil'");
});

test('assistant supports product comparison with real attributes and safe navigation links', function () {
    $chilli = Product::factory()->create([
        'name' => 'Kashmiri Red Chilli',
        'category' => 'Whole Spices',
        'price' => 299.00,
        'quantity' => 30,
        'unit' => '100 g',
        'is_active' => true,
    ]);

    $turmeric = Product::factory()->create([
        'name' => 'Organic Turmeric',
        'category' => 'Ground Spices',
        'price' => 149.00,
        'quantity' => 50,
        'unit' => '100 g',
        'is_active' => true,
    ]);

    $response = $this->postJson(route('ai.chat'), [
        'message' => 'Compare Kashmiri Red Chilli and Organic Turmeric',
    ]);

    $response->assertOk();
    expect($response['response']['intent'])->toBe('product_comparison');
    expect($response['response']['products'])->toHaveCount(2);

    // Should include safe view action links for both products
    $actions = $response['response']['actions'];
    expect(count($actions))->toBeGreaterThanOrEqual(2);
    $urls = array_column($actions, 'url');
    expect($urls)->toContain(route('products.show', ['product' => $chilli->slug]));
    expect($urls)->toContain(route('products.show', ['product' => $turmeric->slug]));
});

test('guest order tracking is protected and provides login action without exposing private data', function () {
    // Create an order for a user
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'FF-SECRET-999',
        'status' => 'delivered',
    ]);

    // Guest asking where order is
    $response = $this->postJson(route('ai.chat'), [
        'message' => 'Where is my order? Track order status please',
    ]);

    $response->assertOk();
    // Secret order number MUST NOT be in response
    expect($response['response']['message'])->not->toContain('FF-SECRET-999');
    expect($response['response']['message'])->toContain('protected');

    // Safe action must be to login
    $actions = $response['response']['actions'];
    expect($actions[0]['action'])->toBe('login');
    expect($actions[0]['url'])->toBe(route('login'));
});

test('authenticated user order tracking shows their own order status safely', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'FF-AUTH-777',
        'status' => 'out_for_delivery',
    ]);

    $response = $this->actingAs($user)->postJson(route('ai.chat'), [
        'message' => 'Where is my order? What is delivery status?',
    ]);

    $response->assertOk();
    expect($response['response']['message'])->toContain('FF-AUTH-777');
    expect($response['response']['message'])->toContain('Out for delivery');

    $actions = $response['response']['actions'];
    $urls = array_column($actions, 'url');
    expect($urls)->toContain(route('account.orders.show', ['order' => 'FF-AUTH-777']));
});

test('activity tracking respects privacy consent', function () {
    $consentingUser = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $consentingUser->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
        'consented_at' => now(),
    ]);

    $initialEventCount = AiEvent::count();

    // 1. Consenting user query
    $this->actingAs($consentingUser)->postJson(route('ai.chat'), [
        'message' => 'Recommend some good spices',
    ])->assertOk();

    expect(AiEvent::count())->toBeGreaterThan($initialEventCount);

    // 2. User with declined tracking
    $declinedUser = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $declinedUser->id,
        'consent_status' => PrivacyConsent::STATUS_DECLINED,
        'activity_tracking' => false,
        'consent_version' => PrivacyConsent::VERSION,
        'consented_at' => now(),
    ]);

    $beforeDeclinedCount = AiEvent::count();

    $this->actingAs($declinedUser)->postJson(route('ai.chat'), [
        'message' => 'Recommend some good spices',
    ])->assertOk();

    // No new event should be stored for declined user
    expect(AiEvent::count())->toBe($beforeDeclinedCount);
});
