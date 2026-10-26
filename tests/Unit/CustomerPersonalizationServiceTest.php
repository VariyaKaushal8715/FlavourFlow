<?php

use App\AI\Models\AiEvent;
use App\AI\Services\CustomerPersonalizationService;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

test('it builds a profile from events and models', function () {
    // Create a user and some products
    $user = User::factory()->create();
    $productA = Product::factory()->create(['category' => 'Spice A']);
    $productB = Product::factory()->create(['category' => 'Spice B']);

    // Create events spanning different types
    $now = Carbon::now();
    AiEvent::factory()->create([
        'user_id' => $user->id,
        'event_type' => 'order_placed',
        'entity_id' => $productA->id,
        'metadata' => ['category' => $productA->category, 'name' => $productA->name, 'total' => 120],
        'created_at' => $now->copy()->subDays(5),
    ]);
    AiEvent::factory()->create([
        'user_id' => $user->id,
        'event_type' => 'cart_added',
        'entity_id' => $productB->id,
        'metadata' => ['category' => $productB->category, 'name' => $productB->name],
        'created_at' => $now->copy()->subDays(2),
    ]);
    AiEvent::factory()->create([
        'user_id' => $user->id,
        'event_type' => 'product_searched',
        'metadata' => ['query' => 'cumin'],
        'created_at' => $now->copy()->subDays(1),
    ]);

    // Wishlist and Cart entries
    Wishlist::create(['user_id' => $user->id, 'product_id' => $productA->id]);
    CartItem::create([
        'user_id' => $user->id,
        'product_id' => $productB->id,
        'product_name' => $productB->name,
        'product_slug' => $productB->slug,
        'sku' => $productB->sku,
        'category' => $productB->category,
        'unit' => $productB->unit,
        'unit_price' => $productB->price,
        'quantity' => 1,
        'line_total' => $productB->price,
    ]);

    // Order history (real model)
    $order = Order::factory()->create(['user_id' => $user->id, 'total_amount' => 120.00]);
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $productA->id]);

    // Ensure cache is clear for this test
    Cache::forget("customer_personalization_profile:{$user->id}");

    $service = new CustomerPersonalizationService;
    $profile = $service->getProfile($user->id);

    // Verify top-level keys exist
    expect($profile)->toHaveKeys([
        'preferred_categories',
        'preferred_products',
        'frequent_search_terms',
        'wishlist_product_ids',
        'cart_product_ids',
        'total_spent',
    ]);

    $preferredIds = array_map('strval', array_column($profile['preferred_products'], 'product_id'));
    expect($preferredIds)->toContain((string) $productA->id);
    expect($profile['frequent_search_terms'])->toContain('cumin');
    $wishlistIds = array_map('strval', $profile['wishlist_product_ids']);
    expect($wishlistIds)->toContain((string) $productA->id);
    $cartIds = array_map('strval', $profile['cart_product_ids']);
    expect($cartIds)->toContain((string) $productB->id);
    expect((float) $profile['total_spent'])->toBe(120.0);
});

test('it returns an empty profile when no events exist', function () {
    $user = User::factory()->create();
    Cache::forget("customer_personalization_profile:{$user->id}");

    $service = new CustomerPersonalizationService;
    $profile = $service->getProfile($user->id);

    expect($profile['preferred_categories'])->toBeEmpty();
    expect($profile['preferred_products'])->toBeEmpty();
    expect($profile['frequent_search_terms'])->toBeEmpty();
    expect($profile['wishlist_product_ids'])->toBeEmpty();
    expect($profile['cart_product_ids'])->toBeEmpty();
    expect((float) $profile['total_spent'])->toBe(0.0);
});
