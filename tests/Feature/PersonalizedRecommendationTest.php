<?php

use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Core\AiRecommendationResult;
use App\AI\Models\AiEvent;
use App\Models\Order;
use App\Models\PrivacyConsent;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

test('personalized ranking ranks customer interacted products highest with real database attributes', function (): void {
    $user = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
        'consented_at' => now(),
    ]);

    $spiceA = Product::factory()->create([
        'name' => 'Royal Shahi Jeera',
        'category' => 'Whole Spices',
        'price' => 180.00,
        'quantity' => 25,
        'is_active' => true,
    ]);

    $spiceB = Product::factory()->create([
        'name' => 'Kashmiri Red Chilli',
        'category' => 'Ground Spices',
        'price' => 240.00,
        'quantity' => 40,
        'is_active' => true,
    ]);

    $spiceC = Product::factory()->create([
        'name' => 'Green Cardamom Pods',
        'category' => 'Whole Spices',
        'price' => 450.00,
        'quantity' => 15,
        'is_active' => true,
        'priority' => 10,
    ]);

    // Interacted with spiceA (order) and spiceB (cart)
    AiEvent::create([
        'user_id' => $user->id,
        'session_id' => 'session-user-1',
        'event_type' => 'order_placed',
        'entity_type' => 'product',
        'entity_id' => (string) $spiceA->id,
        'metadata' => ['product_id' => $spiceA->id, 'name' => $spiceA->name, 'category' => $spiceA->category],
        'created_at' => Carbon::now()->subDays(2),
    ]);

    AiEvent::create([
        'user_id' => $user->id,
        'session_id' => 'session-user-1',
        'event_type' => 'cart_added',
        'entity_type' => 'product',
        'entity_id' => (string) $spiceB->id,
        'metadata' => ['product_id' => $spiceB->id, 'name' => $spiceB->name, 'category' => $spiceB->category],
        'created_at' => Carbon::now()->subHours(5),
    ]);

    Cache::forget("customer_personalization_profile:{$user->id}");

    $engine = app(AiRecommendationEngineInterface::class);
    $result = $engine->getCustomerRecommendations(
        userId: $user->id,
        sessionId: 'session-user-1',
        type: 'personalized',
        limit: 4
    );

    expect($result->recommendationType)->toBe('personalized')
        ->and($result->confidence)->toBeGreaterThanOrEqual(0.75)
        ->and(count($result->products))->toBeGreaterThanOrEqual(2);

    $topRecommendedIds = array_column($result->products, 'product_id');
    expect($topRecommendedIds)->toContain((string) $spiceA->id)
        ->and($topRecommendedIds)->toContain((string) $spiceB->id);

    // Verify prices and names match actual database records, not hallucinated values
    $recommendedA = collect($result->products)->firstWhere('product_id', (string) $spiceA->id);
    expect($recommendedA)->not->toBeNull()
        ->and((float) $recommendedA['price'])->toBe(180.00)
        ->and($recommendedA['name'])->toBe('Royal Shahi Jeera')
        ->and($recommendedA['category'])->toBe('Whole Spices');
});

test('consent denial forces safe non-personalized curated fallback and suppresses user history', function (): void {
    $user = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_DECLINED,
        'activity_tracking' => false,
        'consent_version' => PrivacyConsent::VERSION,
        'consented_at' => now(),
    ]);

    $privateProduct = Product::factory()->create([
        'name' => 'Secret Private Herb',
        'price' => 999.00,
        'quantity' => 10,
        'is_active' => true,
        'is_featured' => false,
        'priority' => 0,
    ]);

    $popularProducts = Product::factory()->count(3)->create([
        'name' => 'Popular Kitchen Turmeric',
        'price' => 120.00,
        'quantity' => 100,
        'is_active' => true,
        'is_featured' => true,
        'priority' => 100,
    ]);

    // Create event for user that should be ignored due to consent status
    AiEvent::create([
        'user_id' => $user->id,
        'session_id' => 'session-declined',
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => (string) $privateProduct->id,
        'metadata' => ['product_id' => $privateProduct->id, 'name' => $privateProduct->name],
    ]);

    Cache::forget("customer_personalization_profile:{$user->id}");

    $engine = app(AiRecommendationEngineInterface::class);
    $result = $engine->getCustomerRecommendations(
        userId: $user->id,
        sessionId: 'session-declined',
        type: 'personalized',
        limit: 3
    );

    expect($result->recommendationType)->toBe('curated_fallback')
        ->and($result->reason)->toContain('Activity personalization is paused');

    $productIds = array_column($result->products, 'product_id');
    expect($productIds)->not->toContain((string) $privateProduct->id)
        ->and($productIds)->toHaveCount(3);
});

test('strict customer data isolation ensures User A activity never leaks to User B', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    PrivacyConsent::create([
        'user_id' => $userA->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
    ]);
    PrivacyConsent::create([
        'user_id' => $userB->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
    ]);

    $productA = Product::factory()->create([
        'name' => 'User A Only Saffron',
        'price' => 800.00,
        'quantity' => 5,
        'is_active' => true,
        'is_featured' => false,
        'priority' => 1,
    ]);

    $productB = Product::factory()->create([
        'name' => 'User B Only Cinnamon',
        'price' => 300.00,
        'quantity' => 15,
        'is_active' => true,
        'is_featured' => false,
        'priority' => 1,
    ]);

    // Catalog fillers so fallback doesn't pull the other user's product
    Product::factory()->count(2)->create([
        'is_active' => true,
        'is_featured' => true,
        'priority' => 50,
    ]);

    // User A views productA
    AiEvent::create([
        'user_id' => $userA->id,
        'session_id' => 'session-user-a',
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => (string) $productA->id,
        'metadata' => ['product_id' => $productA->id, 'name' => $productA->name],
    ]);

    // User B views productB
    AiEvent::create([
        'user_id' => $userB->id,
        'session_id' => 'session-user-b',
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => (string) $productB->id,
        'metadata' => ['product_id' => $productB->id, 'name' => $productB->name],
    ]);

    $engine = app(AiRecommendationEngineInterface::class);

    $recsA = $engine->getCustomerRecommendations(userId: $userA->id, sessionId: 'session-user-a', type: 'personalized', limit: 2);
    $recsB = $engine->getCustomerRecommendations(userId: $userB->id, sessionId: 'session-user-b', type: 'personalized', limit: 2);

    $idsA = array_column($recsA->products, 'product_id');
    $idsB = array_column($recsB->products, 'product_id');

    expect($idsA)->toContain((string) $productA->id)
        ->and($idsA)->not->toContain((string) $productB->id)
        ->and($idsB)->toContain((string) $productB->id)
        ->and($idsB)->not->toContain((string) $productA->id);
});

test('guest users receive safe catalog recommendations without leaking authenticated customer events', function (): void {
    $authUser = User::factory()->create();
    $authProduct = Product::factory()->create([
        'name' => 'Authenticated Customer Secret Blend',
        'quantity' => 20,
        'is_active' => true,
        'is_featured' => false,
        'priority' => 0,
    ]);

    AiEvent::create([
        'user_id' => $authUser->id,
        'session_id' => 'auth-user-session',
        'event_type' => 'cart_added',
        'entity_type' => 'product',
        'entity_id' => (string) $authProduct->id,
        'metadata' => ['product_id' => $authProduct->id],
    ]);

    $publicCatalogProducts = Product::factory()->count(3)->create([
        'name' => 'Public Featured Mustard Seeds',
        'quantity' => 50,
        'is_active' => true,
        'is_featured' => true,
        'priority' => 90,
    ]);

    $engine = app(AiRecommendationEngineInterface::class);
    $guestRecs = $engine->getCustomerRecommendations(userId: null, sessionId: 'guest-session-456', type: 'personalized', limit: 3);

    expect($guestRecs->confidence)->toBeGreaterThan(0.0)
        ->and(count($guestRecs->products))->toBe(3);

    $guestProductIds = array_column($guestRecs->products, 'product_id');
    expect($guestProductIds)->not->toContain((string) $authProduct->id);
});

test('unavailable out-of-stock and inactive products are strictly excluded from recommendations', function (): void {
    $user = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
    ]);

    $activeInStock = Product::factory()->create([
        'name' => 'Available Bay Leaves',
        'quantity' => 10,
        'is_active' => true,
    ]);

    $outOfStock = Product::factory()->create([
        'name' => 'Sold Out Cloves',
        'quantity' => 0,
        'is_active' => true,
    ]);

    $inactiveProduct = Product::factory()->create([
        'name' => 'Discontinued Star Anise',
        'quantity' => 50,
        'is_active' => false,
    ]);

    // Customer interacted with all three in past sessions
    foreach ([$activeInStock, $outOfStock, $inactiveProduct] as $prod) {
        AiEvent::create([
            'user_id' => $user->id,
            'session_id' => 'session-stock-test',
            'event_type' => 'product_viewed',
            'entity_type' => 'product',
            'entity_id' => (string) $prod->id,
            'metadata' => ['product_id' => $prod->id, 'name' => $prod->name],
        ]);
    }

    // Event pointing to a deleted / non-existent product ID
    AiEvent::create([
        'user_id' => $user->id,
        'session_id' => 'session-stock-test',
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => '999999',
        'metadata' => ['product_id' => 999999, 'name' => 'Ghost Product'],
    ]);

    Cache::forget("customer_personalization_profile:{$user->id}");

    $engine = app(AiRecommendationEngineInterface::class);
    $result = $engine->getCustomerRecommendations(userId: $user->id, sessionId: 'session-stock-test', type: 'personalized', limit: 5);

    $recommendedIds = array_column($result->products, 'product_id');

    expect($recommendedIds)->toContain((string) $activeInStock->id)
        ->and($recommendedIds)->not->toContain((string) $outOfStock->id)
        ->and($recommendedIds)->not->toContain((string) $inactiveProduct->id)
        ->and($recommendedIds)->not->toContain('999999');
});

test('recommendations work deterministically without external LLM provider credentials', function (): void {
    $engine = app(AiRecommendationEngineInterface::class);

    $product = Product::factory()->create([
        'name' => 'Pure Asafoetida Hing',
        'price' => 95.00,
        'quantity' => 20,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $result = $engine->getCustomerRecommendations(userId: null, sessionId: 'offline-session', type: 'personalized', limit: 2);

    expect($result)->toBeInstanceOf(AiRecommendationResult::class)
        ->and($result->products)->not->toBeEmpty()
        ->and($result->confidence)->toBeGreaterThan(0.0)
        ->and($result->reason)->toBeString()
        ->and($result->suggestedAction)->toBeArray();
});

test('complementary recommendations exclude current product and suggest valid pairings', function (): void {
    $mainSpice = Product::factory()->create([
        'name' => 'Special Garam Masala',
        'category' => 'Blended Spices',
        'quantity' => 15,
        'is_active' => true,
    ]);

    $pairedSpice = Product::factory()->create([
        'name' => 'Whole Coriander Seeds',
        'category' => 'Whole Spices',
        'quantity' => 20,
        'is_active' => true,
    ]);

    $engine = app(AiRecommendationEngineInterface::class);
    $result = $engine->getCustomerRecommendations(
        userId: null,
        sessionId: 'pair-session',
        type: 'complementary',
        limit: 3,
        contextOptions: ['current_product' => $mainSpice]
    );

    expect($result->recommendationType)->toBe('complementary');

    $productIds = array_column($result->products, 'product_id');
    expect($productIds)->not->toContain((string) $mainSpice->id)
        ->and($productIds)->toContain((string) $pairedSpice->id);
});

test('storefront AI Assistant integrates CustomerPersonalizationService and AiRecommendationEngine', function (): void {
    $user = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
    ]);

    $spice = Product::factory()->create([
        'name' => 'Organic Fenugreek Seeds',
        'price' => 110.00,
        'quantity' => 30,
        'is_active' => true,
    ]);

    // Record user interest
    AiEvent::create([
        'user_id' => $user->id,
        'session_id' => 'chat-session-test',
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => (string) $spice->id,
        'metadata' => ['product_id' => $spice->id, 'name' => $spice->name],
    ]);

    $response = $this->actingAs($user)->postJson(route('ai.chat'), [
        'message' => 'Can you recommend some spices for me?',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true);

    $data = $response->json('response');
    expect($data['intent'])->toBe('recommendation')
        ->and(count($data['products']))->toBeGreaterThanOrEqual(1);

    $returnedProductIds = array_column($data['products'], 'id');
    expect($returnedProductIds)->toContain($spice->id);
});

test('storefront recommendations API endpoint returns valid JSON payload', function (): void {
    $product = Product::factory()->create([
        'name' => 'Royal Saffron Threads',
        'price' => 650.00,
        'quantity' => 12,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $response = $this->getJson(route('ai.recommendations', ['type' => 'personalized', 'limit' => 3]));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'recommendation_type',
            'reason',
            'confidence',
            'products' => [
                '*' => ['product_id', 'name', 'slug', 'price', 'category', 'reason', 'confidence', 'suggested_action'],
            ],
            'suggested_action',
            'provider_available',
        ]);
});

test('homepage displays personalized section for consented authenticated customers', function (): void {
    $user = User::factory()->create();
    PrivacyConsent::create([
        'user_id' => $user->id,
        'consent_status' => PrivacyConsent::STATUS_ALLOWED,
        'activity_tracking' => true,
        'consent_version' => PrivacyConsent::VERSION,
    ]);

    $favProduct = Product::factory()->create([
        'name' => 'Kashmiri Saffron Gold',
        'price' => 750.00,
        'quantity' => 20,
        'is_active' => true,
    ]);

    AiEvent::create([
        'user_id' => $user->id,
        'session_id' => 'home-rec-session',
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => (string) $favProduct->id,
        'metadata' => ['product_id' => $favProduct->id, 'name' => $favProduct->name],
    ]);

    $user->refresh();

    // Consented user visits home
    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Recommended For You')
        ->assertSee('Kashmiri Saffron Gold');
});

test('homepage suppresses personalized section for guest users', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Recommended For You');
});
