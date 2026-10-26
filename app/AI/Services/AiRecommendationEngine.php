<?php

namespace App\AI\Services;

use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Core\AiDecisionResult;
use App\AI\Core\AiRecommendationResult;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

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
        int $limit = 6,
        array $contextOptions = []
    ): AiRecommendationResult {
        $effectiveUserId = $userId ?? (auth()->check() ? auth()->id() : null);
        $effectiveSessionId = $sessionId ?? (session()->isStarted() ? session()->getId() : null);

        // Privacy consent enforcement: If user has explicitly declined tracking, return safe non-personalized recommendations
        if ($effectiveUserId !== null) {
            $user = User::find($effectiveUserId);
            if ($user && $user->privacyConsent && ! $user->allowsActivityTracking()) {
                return $this->buildCuratedFallbackRecommendations(
                    $limit,
                    'Recommendations curated from our top catalog items. Activity personalization is paused per your privacy preference.'
                );
            }
        }

        $context = $this->contextBuilder->buildContext($effectiveUserId, $effectiveSessionId);
        $analysis = $this->analyzer->analyze($context);

        return match ($type) {
            'complementary' => $this->buildComplementaryRecommendations($context, $analysis, $limit, $contextOptions),
            'cart_wishlist' => $this->buildCartWishlistRecommendations($context, $analysis, $limit),
            'recently_viewed' => $this->buildRecentlyViewedRecommendations($context, $analysis, $limit),
            'category_based' => $this->buildCategoryBasedRecommendations($context, $analysis, $limit, $contextOptions),
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

    public function buildCuratedFallbackRecommendations(int $limit, string $reason): AiRecommendationResult
    {
        $fallbacks = Product::query()
            ->active()
            ->inStock()
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->orderByDesc('rating')
            ->take($limit)
            ->get();

        $recommended = [];
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

        return new AiRecommendationResult(
            recommendationType: 'curated_fallback',
            products: $recommended,
            reason: $reason,
            confidence: 0.50,
            suggestedAction: ['action' => 'browse_catalog', 'label' => 'Explore Spices'],
            metadata: ['is_fallback' => true]
        );
    }

    private function buildPersonalizedRecommendations(array $context, array $analysis, int $limit): AiRecommendationResult
    {
        $interacted = $analysis['product_interest'] ?? [];
        $profile = $context['personalization_profile'] ?? [];
        $preferredProducts = $profile['preferred_products'] ?? [];
        $topCat = $analysis['category_preference']['top_category'] ?? ($profile['top_category'] ?? null);

        // Collect all candidate IDs from recent interactions and profile
        $candidateIds = [];
        foreach ($interacted as $item) {
            if (! empty($item['product_id'])) {
                $candidateIds[] = (string) $item['product_id'];
            }
        }
        foreach ($preferredProducts as $p) {
            if (! empty($p['product_id'])) {
                $candidateIds[] = (string) $p['product_id'];
            }
        }

        // Only active, in-stock products from DB (never fake or out-of-stock items)
        $productsMap = $this->loadProductsMap($candidateIds);

        $recommended = [];
        $usedIds = [];

        // 1. Rank based on recent high-intent interactions (order, cart, wishlist, view)
        foreach ($interacted as $item) {
            $id = (string) ($item['product_id'] ?? '');
            if ($id === '' || isset($usedIds[$id])) {
                continue;
            }

            $model = $productsMap[$id] ?? null;
            if (! $model || ! $model->is_active || $model->quantity <= 0) {
                continue;
            }

            $reason = "Recommended based on your interest in {$model->name} (score: ".($item['score'] ?? 1).').';
            $confidence = match ($item['level'] ?? 'low') {
                'high' => 0.90,
                'medium' => 0.75,
                default => 0.60,
            };

            $recommended[] = [
                'product_id' => (string) $model->id,
                'name' => $model->name,
                'slug' => $model->slug,
                'price' => (float) $model->price,
                'category' => $model->category,
                'reason' => $reason,
                'confidence' => $confidence,
                'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Add to Cart'],
            ];
            $usedIds[(string) $model->id] = true;

            if (count($recommended) >= $limit) {
                break;
            }
        }

        // 2. Next, supplement with customer profile's preferred products
        if (count($recommended) < $limit && ! empty($preferredProducts)) {
            foreach ($preferredProducts as $p) {
                $pid = (string) ($p['product_id'] ?? '');
                if ($pid === '' || isset($usedIds[$pid])) {
                    continue;
                }

                $model = $productsMap[$pid] ?? null;
                if (! $model || ! $model->is_active || $model->quantity <= 0) {
                    continue;
                }

                $recommended[] = [
                    'product_id' => (string) $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'price' => (float) $model->price,
                    'category' => $model->category,
                    'reason' => 'Matches your frequently purchased and preferred spice preferences.',
                    'confidence' => 0.80,
                    'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Add to Cart'],
                ];
                $usedIds[(string) $model->id] = true;

                if (count($recommended) >= $limit) {
                    break;
                }
            }
        }

        // 3. Category affinity recommendations
        if (count($recommended) < $limit && $topCat) {
            $catProducts = Product::query()
                ->active()
                ->inStock()
                ->where('category', $topCat)
                ->whereNotIn('id', array_keys($usedIds))
                ->orderByDesc('priority')
                ->take($limit - count($recommended))
                ->get();

            foreach ($catProducts as $p) {
                $recommended[] = [
                    'product_id' => (string) $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => (float) $p->price,
                    'category' => $p->category,
                    'reason' => "Curated for you from your favorite spice collection: {$topCat}.",
                    'confidence' => 0.70,
                    'suggested_action' => ['action' => 'view_product', 'label' => 'Explore Spice'],
                ];
                $usedIds[(string) $p->id] = true;

                if (count($recommended) >= $limit) {
                    break;
                }
            }
        }

        // 4. Fill remainder with top featured catalog products
        if (count($recommended) < $limit) {
            $fallbacks = Product::query()
                ->active()
                ->inStock()
                ->whereNotIn('id', array_keys($usedIds))
                ->orderByDesc('is_featured')
                ->orderByDesc('priority')
                ->orderByDesc('rating')
                ->take($limit - count($recommended))
                ->get();

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
                $usedIds[(string) $product->id] = true;
            }
        }

        $hasPersonalSignals = count($interacted) > 0 || count($preferredProducts) > 0;
        $overallReason = $hasPersonalSignals
            ? 'Personalized recommendations tailored from your recent views, cart, wishlist, and spice preferences.'
            : 'Featured recommendations curated from our top FlavourFlow spice catalog.';

        return new AiRecommendationResult(
            recommendationType: 'personalized',
            products: $recommended,
            reason: $overallReason,
            confidence: $hasPersonalSignals ? 0.85 : 0.50,
            suggestedAction: ['action' => 'browse_personalized', 'label' => 'Explore Tailored Selection'],
            metadata: ['interacted_count' => count($interacted)]
        );
    }

    private function buildComplementaryRecommendations(array $context, array $analysis, int $limit, array $contextOptions = []): AiRecommendationResult
    {
        $currentProduct = $contextOptions['current_product'] ?? null;
        if (! $currentProduct && ! empty($contextOptions['product_id'])) {
            $currentProduct = Product::find($contextOptions['product_id']);
        }

        $cartIds = $context['cart_activity']['current_product_ids'] ?? [];
        $excludeIds = array_map('strval', $cartIds);
        if ($currentProduct) {
            $excludeIds[] = (string) $currentProduct->id;
        }

        $recommended = [];
        $usedIds = [];
        foreach ($excludeIds as $ex) {
            $usedIds[$ex] = true;
        }

        // 1. If current product has frequent co-purchases from OrderItems
        if ($currentProduct) {
            $coPurchasedIds = OrderItem::query()
                ->whereIn('order_id', function ($q) use ($currentProduct): void {
                    $q->select('order_id')
                        ->from('order_items')
                        ->where('product_id', $currentProduct->id);
                })
                ->where('product_id', '!=', $currentProduct->id)
                ->groupBy('product_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit($limit)
                ->pluck('product_id')
                ->all();

            if (! empty($coPurchasedIds)) {
                $coProducts = Product::query()
                    ->active()
                    ->inStock()
                    ->whereIn('id', $coPurchasedIds)
                    ->get();

                foreach ($coProducts as $p) {
                    $recommended[] = [
                        'product_id' => (string) $p->id,
                        'name' => $p->name,
                        'slug' => $p->slug,
                        'price' => (float) $p->price,
                        'category' => $p->category,
                        'reason' => "Frequently bought together with {$currentProduct->name}.",
                        'confidence' => 0.88,
                        'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Pair with Order'],
                    ];
                    $usedIds[(string) $p->id] = true;
                    if (count($recommended) >= $limit) {
                        break;
                    }
                }
            }

            // 2. Complementary categories (e.g. Blended Spices complement Whole Spices, etc.)
            if (count($recommended) < $limit) {
                $pairCategory = match ($currentProduct->category) {
                    'Whole Spices' => 'Blended Spices',
                    'Blended Spices' => 'Whole Spices',
                    default => $currentProduct->category,
                };

                $pairProducts = Product::query()
                    ->active()
                    ->inStock()
                    ->where('category', $pairCategory)
                    ->whereNotIn('id', array_keys($usedIds))
                    ->orderByDesc('priority')
                    ->take($limit - count($recommended))
                    ->get();

                foreach ($pairProducts as $p) {
                    $recommended[] = [
                        'product_id' => (string) $p->id,
                        'name' => $p->name,
                        'slug' => $p->slug,
                        'price' => (float) $p->price,
                        'category' => $p->category,
                        'reason' => "Perfect culinary pairing for {$currentProduct->name}.",
                        'confidence' => 0.80,
                        'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Add to Cart'],
                    ];
                    $usedIds[(string) $p->id] = true;
                    if (count($recommended) >= $limit) {
                        break;
                    }
                }
            }
        }

        // 3. Fallback active in-stock products
        if (count($recommended) < $limit) {
            $fallbacks = Product::query()
                ->active()
                ->inStock()
                ->whereNotIn('id', array_keys($usedIds))
                ->orderByDesc('priority')
                ->take($limit - count($recommended))
                ->get();

            foreach ($fallbacks as $p) {
                $recommended[] = [
                    'product_id' => (string) $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => (float) $p->price,
                    'category' => $p->category,
                    'reason' => 'Pairs nicely with items in your spice collection.',
                    'confidence' => 0.75,
                    'suggested_action' => ['action' => 'add_to_cart', 'label' => 'Add to Cart'],
                ];
                $usedIds[(string) $p->id] = true;
            }
        }

        return new AiRecommendationResult(
            recommendationType: 'complementary',
            products: $recommended,
            reason: 'Complementary spice recommendations designed to enhance your cooking recipes.',
            confidence: count($recommended) > 0 ? 0.82 : 0.40,
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
            if (! $p || ! $p->is_active || $p->quantity <= 0) {
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

            if (count($recommended) >= $limit) {
                break;
            }
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
        $used = [];
        foreach ($recent as $item) {
            $id = (string) ($item['product_id'] ?? '');
            if ($id === '' || isset($used[$id])) {
                continue;
            }
            $p = $productsMap[$id] ?? null;
            if (! $p || ! $p->is_active || $p->quantity <= 0) {
                continue;
            }

            $recommended[] = [
                'product_id' => (string) $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'category' => $p->category,
                'reason' => "You recently viewed {$p->name}.",
                'confidence' => 0.85,
                'suggested_action' => ['action' => 'view_product', 'label' => 'Revisit Product'],
            ];
            $used[$id] = true;

            if (count($recommended) >= $limit) {
                break;
            }
        }

        return new AiRecommendationResult(
            recommendationType: 'recently_viewed',
            products: $recommended,
            reason: 'Products you recently viewed during your session.',
            confidence: count($recommended) > 0 ? 0.85 : 0.30,
            suggestedAction: ['action' => 'view_history', 'label' => 'View Browsing History']
        );
    }

    private function buildCategoryBasedRecommendations(array $context, array $analysis, int $limit, array $contextOptions = []): AiRecommendationResult
    {
        $topCat = $contextOptions['category'] ?? ($analysis['category_preference']['top_category'] ?? 'Whole Spices');

        $products = Product::query()
            ->active()
            ->inStock()
            ->where('category', $topCat)
            ->orderByDesc('priority')
            ->take($limit)
            ->get();

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
            if (! $p || ! $p->is_active || $p->quantity <= 0) {
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

            if (count($recommended) >= $limit) {
                break;
            }
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

        return Product::query()
            ->active()
            ->inStock()
            ->whereIn('id', $cleanIds)
            ->get()
            ->keyBy(fn ($p) => (string) $p->id)
            ->all();
    }
}
