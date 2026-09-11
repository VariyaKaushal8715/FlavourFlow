<?php

namespace Tests\Unit;

use App\AI\Services\CustomerPersonalizationService;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wishlist;
use App\Models\Cart;
use App\AI\Models\AiEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CustomerPersonalizationServiceTest extends TestCase
{
    /** @test */
    public function it_builds_a_profile_from_events_and_models()
    {
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

        // Wishlist and Cart entries (real models, not events)
        Wishlist::create(['user_id' => $user->id, 'product_id' => $productA->id]);
        Cart::create(['user_id' => $user->id, 'product_id' => $productB->id]);

        // Order history (real model)
        $order = Order::factory()->create(['user_id' => $user->id, 'total' => 120]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $productA->id]);

        // Ensure cache is clear for this test
        Cache::forget("customer_personalization_profile:{$user->id}");

        $service = new CustomerPersonalizationService();
        $profile = $service->getProfile($user->id);

        // Verify top‑level keys exist
        $this->assertArrayHasKey('preferred_categories', $profile);
        $this->assertArrayHasKey('preferred_products', $profile);
        $this->assertArrayHasKey('frequent_search_terms', $profile);
        $this->assertArrayHasKey('wishlist_product_ids', $profile);
        $this->assertArrayHasKey('cart_product_ids', $profile);
        $this->assertArrayHasKey('total_spent', $profile);

        // Check that the product we ordered appears in preferred_products
        $preferredIds = array_column($profile['preferred_products'], 'product_id');
        $this->assertContains((string) $productA->id, $preferredIds);

        // Search term should be present
        $this->assertContains('cumin', $profile['frequent_search_terms']);

        // Wishlist and cart IDs should match the models we created
        $this->assertContains((string) $productA->id, $profile['wishlist_product_ids']);
        $this->assertContains((string) $productB->id, $profile['cart_product_ids']);

        // Total spent should equal the order total
        $this->assertEquals(120.0, $profile['total_spent']);
    }

    /** @test */
    public function it_returns_an_empty_profile_when_no_events_exist()
    {
        $user = User::factory()->create();
        Cache::forget("customer_personalization_profile:{$user->id}");
        $service = new CustomerPersonalizationService();
        $profile = $service->getProfile($user->id);
        // All list‑type keys should be empty arrays
        $this->assertEmpty($profile['preferred_categories']);
        $this->assertEmpty($profile['preferred_products']);
        $this->assertEmpty($profile['frequent_search_terms']);
        $this->assertEmpty($profile['wishlist_product_ids']);
        $this->assertEmpty($profile['cart_product_ids']);
        $this->assertEquals(0.0, $profile['total_spent']);
    }
}
?>

