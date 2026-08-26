<?php

namespace App\AI\Services;

use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Core\AiDecisionResult;
use App\AI\Core\AiRecommendationResult;
use App\Models\Product;

class AiRecommendationEngine implements AiRecommendationEngineInterface
{
    public function __construct(
        private AiContextBuilderInterface $contextBuilder,
        private AiAnalyzerInterface $analyzer,
        private AiBrainInterface $brain,
    ) {}

    public function getCustomerRecommendations(
        ?int $userId = null,
        ?string $sessionId = null,
        string $type = 'personalized',
        int $limit = 6
    ): AiRecommendationResult {
        $context = $this->contextBuilder->buildContext($userId, $sessionId);
        $analysis = $this->analyzer->analyze($context);

        return match ($type) {
            'complementary' => $this->buildComplementaryRecommendations($context, $analysis, $limit),
            'cart_wishlist' => $this->buildCartWishlistRecommendations($context, $analysis, $limit),
            'recently_viewed' => $this->buildRecentlyViewedRecommendations($context, $analysis, $limit),
            'category_based' => $this->buildCategoryBasedRecommendations($context, $analysis, $limit),
            'abandoned_cart_recovery' => $this->buildAbandonedCartRecoveryRecommendations($context, $analysis, $limit),
            default => $this->buildPersonalizedRecommendations($context, $analysis, $limit),
        };
    }

    public function getAdminDecisionSignals(int $days = 30): AiDecisionResult
    {
        $globalAnalysis = $this->analyzer->analyzeGlobal($days);
        $globalContext = $this->contextBuilder->buildGlobalContext($days);

        $winningProducts = $this->evaluateWinningProducts($globalAnalysis, $globalContext);
        $promotionNeeds = $this->evaluatePromotionNeeds($globalAnalysis, $globalContext);
        $categoryOpps = $this->evaluateCategoryOpportunities($globalAnalysis, $globalContext);
        $abandonment = $this->evaluateAbandonmentSignals($globalAnalysis, $globalContext);
        $offerOpps = $this->evaluateOfferOpportunities($globalAnalysis, $globalContext);
        $adCampaigns = $this->evaluateAdCampaignSuggestions($winningProducts, $categoryOpps);
        $actions = $this->generateSuggestedAdminActions($winningProducts, $promotionNeeds, $abandonment);

        $confidence = ($globalContext['total_events'] ?? 0) > 0 ? 0.85 : 0.4;

        return new AiDecisionResult(
            decisionType: 'admin_business_decisions',
            winningProducts: $winningProducts,
            promotionNeeds: $promotionNeeds,
            categoryOpportunities: $categoryOpps,
            abandonmentSignals: $abandonment,
            offerOpportunities: $offerOpps,
            adCampaignSuggestions: $adCampaigns,
            suggestedActions: $actions,
            confidence: $confidence
        );
    }

    private function buildPersonalizedRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $interacted = $analysis['product_interest'] ?? [];
        $topCat = $analysis['category_preference']['top_category'] ?? null;

        $productIds = array_column($interacted, 'product_id');
        $productsMap = $this->loadProductsMap($productIds);

        $recommended = [];
        $usedIds = [];

        foreach ($interacted as $item) {
            $id = (string) ($item['product_id'] ?? '');
            if ($id === '' || isset($usedIds[$id])) {
                continue;
            }

            $model = $productsMap[$id] ?? null;
            $name = $model ? $model->name : ($item['name'] ?? "Product #{$id}");
            $category = $model ? $model->category : ($topCat ?? 'General');
            $price = $model ? (float) $model->price : 299.0;
            $slug = $model ? $model->slug : 'product-'.$id;

            $reason = "Recommended based on your high interest in {$name} (score: ".($item['score'] ?? 1).').';
            $confidence = match ($item['level'] ?? 'low') {
                'high' => 0.90,
                'medium' => 0.75,
                default => 0.55,
            };

            $recommended[] = [
                'product_id' => $id,
                'name' => $name,
                'slug' => $slug,
                'price' => $price,
                'category' => $category,
                'reason' => $reason,
                'confidence' => $confidence,
                'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Add to Cart'],
            ];
            $usedIds[$id] = true;

            if (count($recommended) >= $limit) {
                break;
            }
        }

        // Fill remaining with active catalog products if needed
        if (count($recommended) < $limit) {
            $fallbacks = Product::query()->active()->whereNotIn('id', array_keys($usedIds))->take($limit - count($recommended))->get();
            foreach ($fallbacks as $product) {
                $recommended[] = [
                    'product_id' => (string) $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'category' => $product->category,
                    'reason' => 'Popular spice choice loved by FlavourFlow customers.',
                    'confidence' => 0.50,
                    'suggested_action' => ['action' => 'view_product', 'label' => 'Explore Product'],
                ];
            }
        }

        $overallReason = count($interacted) > 0
            ? 'Personalized recommendations tailored from your recent views, cart, and wishlist activity.'
            : 'Featured recommendations curated from our top FlavourFlow spice catalog.';

        return new AiRecommendationResult(
            recommendationType: 'personalized',
            products: $recommended,
            reason: $overallReason,
            confidence: count($interacted) > 0 ? 0.85 : 0.50,
            suggestedAction: ['action' => 'browse_personalized', 'label' => 'Explore Tailored Selection'],
            metadata: ['interacted_count' => count($interacted)]
        );
    }

    private function buildComplementaryRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $topCat = $analysis['category_preference']['top_category'] ?? null;
        $cartIds = $context['cart_activity']['current_product_ids'] ?? [];

        $query = Product::query()->active();
        if ($topCat) {
            $query->where('category', $topCat);
        }
        if (! empty($cartIds)) {
            $query->whereNotIn('id', $cartIds);
        }

        $items = $query->take($limit)->get();
        $recommended = [];

        foreach ($items as $p) {
            $recommended[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'category' => $p->category,
                'reason' => "Pairs perfectly with items in your {$p->category} collection.",
                'confidence' => 0.80,
                'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Pair with Order'],
            ];
        }

        return new AiRecommendationResult(
            recommendationType: 'complementary',
            products: $recommended,
            reason: 'Complementary spice recommendations designed to enhance your cooking recipes.',
            confidence: 0.80,
            suggestedAction: ['action' => 'view_pairs', 'label' => 'Complete Your Recipe Setup']
        );
    }

    private function buildCartWishlistRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $wishlistIds = $context['wishlist_activity']['current_product_ids'] ?? [];
        $cartIds = $context['cart_activity']['current_product_ids'] ?? [];

        $combinedIds = array_unique(array_merge($wishlistIds, $cartIds));
        $productsMap = $this->loadProductsMap($combinedIds);

        $recommended = [];
        foreach ($combinedIds as $id) {
            $p = $productsMap[$id] ?? null;
            if (! $p) {
                continue;
            }

            $isCart = in_array($id, $cartIds, true);
            $reason = $isCart ? 'Currently ready in your cart for checkout.' : 'Saved in your wishlist for quick purchase.';

            $recommended[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'category' => $p->category,
                'reason' => $reason,
                'confidence' => 0.90,
                'suggested_action' => ['action' => $isCart ? 'checkout' : 'move_to_cart', 'label' => $isCart ? 'Checkout Item' : 'Move to Cart'],
            ];
        }

        return new AiRecommendationResult(
            recommendationType: 'cart_wishlist',
            products: $recommended,
            reason: 'Items saved in your cart and wishlist awaiting purchase.',
            confidence: count($recommended) > 0 ? 0.90 : 0.40,
            suggestedAction: ['action' => 'view_cart', 'label' => 'Review Cart & Wishlist']
        );
    }

    private function buildRecentlyViewedRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $recent = $context['recently_viewed_products'] ?? [];
        $productIds = array_column($recent, 'product_id');
        $productsMap = $this->loadProductsMap($productIds);

        $recommended = [];
        foreach ($recent as $item) {
            $id = (string) ($item['product_id'] ?? '');
            $p = $productsMap[$id] ?? null;
            if (! $p) {
                continue;
            }

            $recommended[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'category' => $p->category,
                'reason' => "You viewed this product on {$item['viewed_at']}.",
                'confidence' => 0.85,
                'suggested_action' => ['action' => 'view_product', 'label' => 'Revisit Product'],
            ];
        }

        return new AiRecommendationResult(
            recommendationType: 'recently_viewed',
            products: $recommended,
            reason: 'Products you recently viewed during your session.',
            confidence: count($recommended) > 0 ? 0.85 : 0.30,
            suggestedAction: ['action' => 'view_history', 'label' => 'View Browsing History']
        );
    }

    private function buildCategoryBasedRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $topCat = $analysis['category_preference']['top_category'] ?? 'Whole Spices';

        $products = Product::query()->active()->where('category', $topCat)->take($limit)->get();
        $recommended = [];

        foreach ($products as $p) {
            $recommended[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'category' => $p->category,
                'reason' => "Selected from your top preferred category: {$topCat}.",
                'confidence' => 0.75,
                'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Add to Cart'],
            ];
        }

        return new AiRecommendationResult(
            recommendationType: 'category_based',
            products: $recommended,
            reason: "Top spice selections from your favorite category ({$topCat}).",
            confidence: 0.75,
            suggestedAction: ['action' => 'explore_category', 'label' => "Explore {$topCat}"]
        );
    }

    private function buildAbandonedCartRecoveryRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $unpurchasedIds = $analysis['cart_abandonment']['unpurchased_item_ids'] ?? [];
        $productsMap = $this->loadProductsMap($unpurchasedIds);

        $recommended = [];
        foreach ($unpurchasedIds as $id) {
            $p = $productsMap[$id] ?? null;
            if (! $p) {
                continue;
            }

            $recommended[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'category' => $p->category,
                'reason' => 'Item left uncheckout in your cart. Complete purchase now to secure stock.',
                'confidence' => 0.95,
                'suggested_action' => ['action' => 'checkout_item', 'label' => 'Complete Purchase'],
            ];
        }

        return new AiRecommendationResult(
            recommendationType: 'abandoned_cart_recovery',
            products: $recommended,
            reason: 'High priority cart recovery suggestions.',
            confidence: count($recommended) > 0 ? 0.95 : 0.40,
            suggestedAction: ['action' => 'checkout_now', 'label' => 'Proceed to Checkout']
        );
    }

    private function evaluateWinningProducts(array $globalAnalysis, array $globalContext): array
    {
        $winning = $globalAnalysis['winning_products'] ?? [];
        $result = [];

        foreach ($winning as $w) {
            $pId = $w['product_id'] ?? '';
            $model = $pId ? Product::find($pId) : null;
            $name = $model ? $model->name : "Product #{$pId}";

            $result[] = [
                'product_id' => $pId,
                'name' => $name,
                'score' => (int) ($w['views'] ?? 1),
                'status' => $w['status'] ?? 'Trending',
                'reason' => "High customer engagement with {$w['views']} direct views in the last timeframe.",
            ];
        }

        if (empty($result)) {
            $topProduct = Product::query()->active()->orderByDesc('priority')->first();
            if ($topProduct) {
                $result[] = [
                    'product_id' => (string) $topProduct->id,
                    'name' => $topProduct->name,
                    'score' => 10,
                    'status' => 'Hot Seller',
                    'reason' => 'Top featured spice in catalog.',
                ];
            }
        }

        return $result;
    }

    private function evaluatePromotionNeeds(array $globalAnalysis, array $globalContext): array
    {
        $products = Product::query()->active()->where('quantity', '>', 0)->orderByDesc('quantity')->take(3)->get();
        $result = [];

        foreach ($products as $p) {
            $result[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'stock' => $p->quantity,
                'reason' => "High stock inventory ({$p->quantity} units). Promote to accelerate inventory turnover.",
                'suggested_action' => 'Create Banner Offer / Flash Sale',
            ];
        }

        return $result;
    }

    private function evaluateCategoryOpportunities(array $globalAnalysis, array $globalContext): array
    {
        $topCategories = $globalAnalysis['top_categories'] ?? [];
        $result = [];

        foreach ($topCategories as $cat) {
            $result[] = [
                'category' => $cat['category'] ?? 'Spices',
                'reason' => "Strong customer view concentration ({$cat['count']} view interactions).",
                'demand_level' => 'High Demand',
            ];
        }

        if (empty($result)) {
            $result[] = [
                'category' => 'Whole Spices',
                'reason' => 'Core revenue generator category.',
                'demand_level' => 'High Demand',
            ];
        }

        return $result;
    }

    private function evaluateAbandonmentSignals(array $globalAnalysis, array $globalContext): array
    {
        $abandonedCount = $globalAnalysis['abandoned_checkouts'] ?? 0;

        return [
            'abandoned_count' => $abandonedCount,
            'risk_level' => $abandonedCount > 0 ? 'medium' : 'low',
            'reason' => $abandonedCount > 0 ? "Detected {$abandonedCount} uncompleted checkouts in the system." : 'No major checkout abandonment recorded.',
            'action' => 'Deploy automated cart recovery reminder notifications.',
        ];
    }

    private function evaluateOfferOpportunities(array $globalAnalysis, array $globalContext): array
    {
        $topSearches = $globalAnalysis['top_search_queries'] ?? [];
        $searchQuery = isset($topSearches[0]['query']) ? $topSearches[0]['query'] : 'spices';

        return [
            [
                'offer_name' => 'Popular Bundle Discount',
                'target_category' => 'Whole Spices',
                'discount_type' => '10% OFF Combo',
                'reason' => "High customer interest in '{$searchQuery}'. Offer a bundle discount to convert views to sales.",
            ],
        ];
    }

    private function evaluateAdCampaignSuggestions(array $winningProducts, array $categoryOpps): array
    {
        $result = [];

        foreach (array_slice($winningProducts, 0, 2) as $w) {
            $result[] = [
                'product_id' => $w['product_id'],
                'name' => $w['name'],
                'campaign_angle' => 'Authentic Premium Quality Spotlight',
                'target_audience' => 'Culinary Enthusiasts & Home Chefs',
            ];
        }

        return $result;
    }

    private function generateSuggestedAdminActions(array $winning, array $promo, array $abandonment): array
    {
        return [
            [
                'title' => 'Launch Flash Sale for High-Stock Items',
                'description' => 'Promote items with over 50 units in stock to maintain inventory velocity.',
                'priority' => 'High',
                'action_type' => 'create_offer',
            ],
            [
                'title' => 'Optimize Cart Recovery Push',
                'description' => 'Target users with uncompleted checkouts via email or banner reminders.',
                'priority' => 'Medium',
                'action_type' => 'send_reminders',
            ],
        ];
    }

    /**
     * @param  list<string|int>  $ids
     * @return array<string, Product>
     */
    private function loadProductsMap(array $ids): array
    {
        $cleanIds = array_filter(array_unique($ids));
        if (empty($cleanIds)) {
            return [];
        }

        return Product::whereIn('id', $cleanIds)->get()->keyBy(fn ($p) => (string) $p->id)->all();
    }
}
