<?php

namespace App\AI\Services;

use App\AI\Contracts\AiAnalyzerInterface;
use Throwable;

class AiAnalyzer implements AiAnalyzerInterface
{
    public function analyze(array $context): array
    {
        try {
            $totalEvents = (int) ($context['total_events'] ?? 0);
            if ($totalEvents === 0) {
                return $this->emptyAnalysis();
            }

            $purchaseIntent = $this->detectPurchaseIntent($context);
            $productInterest = $this->detectProductInterest($context);
            $categoryPreference = $this->detectCategoryPreference($context);
            $frequentlyViewed = $this->detectFrequentlyViewed($context);
            $cartAbandonment = $this->detectCartAbandonment($context);
            $recommendations = $this->generateRecommendationSignals($context, $productInterest, $categoryPreference);

            return [
                'purchase_intent' => $purchaseIntent,
                'product_interest' => $productInterest,
                'category_preference' => $categoryPreference,
                'frequently_viewed_products' => $frequentlyViewed,
                'cart_abandonment' => $cartAbandonment,
                'recommendation_signals' => $recommendations,
                'analyzed_at' => now()->toIso8601String(),
            ];
        } catch (Throwable $e) {
            logger()->error('AI Analyzer Exception: '.$e->getMessage());

            return $this->emptyAnalysis();
        }
    }

    public function analyzeGlobal(int $days = 30): array
    {
        try {
            /** @var AiContextBuilder $contextBuilder */
            $contextBuilder = app(AiContextBuilder::class);
            $globalContext = $contextBuilder->buildGlobalContext($days);

            $winningProducts = $this->detectWinningProducts($globalContext);

            return [
                'days_analyzed' => $days,
                'winning_products' => $winningProducts,
                'top_search_queries' => $globalContext['top_searches'] ?? [],
                'top_categories' => $globalContext['top_categories'] ?? [],
                'abandoned_checkouts' => $globalContext['abandoned_checkout_count'] ?? 0,
                'total_orders' => $globalContext['total_orders_placed'] ?? 0,
                'analyzed_at' => now()->toIso8601String(),
            ];
        } catch (Throwable $e) {
            logger()->error('AI Global Analyzer Exception: '.$e->getMessage());

            return [
                'days_analyzed' => $days,
                'winning_products' => [],
                'top_search_queries' => [],
                'top_categories' => [],
                'abandoned_checkouts' => 0,
                'total_orders' => 0,
                'analyzed_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Safe empty analysis structure.
     *
     * @return array<string, mixed>
     */
    private function emptyAnalysis(): array
    {
        return [
            'purchase_intent' => [
                'level' => 'none',
                'score' => 0,
                'rationale' => 'No activity recorded for this user/session.',
            ],
            'product_interest' => [],
            'category_preference' => [
                'top_category' => null,
                'categories' => [],
            ],
            'frequently_viewed_products' => [],
            'cart_abandonment' => [
                'is_abandoned' => false,
                'risk_level' => 'none',
                'unpurchased_item_ids' => [],
            ],
            'recommendation_signals' => [
                'recommended_product_ids' => [],
                'cross_sell_categories' => [],
                'trigger' => 'general_browse',
            ],
            'analyzed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{level: string, score: int, rationale: string}
     */
    private function detectPurchaseIntent(array $context): array
    {
        $ordersCount = (int) ($context['orders']['total_orders'] ?? 0);
        $checkoutsCount = (int) ($context['checkout_activity']['total_checkouts_started'] ?? 0);
        $cartAddedCount = (int) ($context['cart_activity']['total_added'] ?? 0);
        $wishlistAddedCount = (int) ($context['wishlist_activity']['total_added'] ?? 0);
        $viewsCount = count($context['recently_viewed_products'] ?? []);

        if ($ordersCount > 0) {
            return [
                'level' => 'converted',
                'score' => 100,
                'rationale' => 'User completed order purchase.',
            ];
        }

        if ($checkoutsCount > 0) {
            return [
                'level' => 'high',
                'score' => 85,
                'rationale' => 'User initiated checkout flow.',
            ];
        }

        if ($cartAddedCount > 0) {
            return [
                'level' => 'high',
                'score' => 70,
                'rationale' => 'User added products to shopping cart.',
            ];
        }

        if ($wishlistAddedCount > 0) {
            return [
                'level' => 'medium',
                'score' => 45,
                'rationale' => 'User saved products to wishlist.',
            ];
        }

        if ($viewsCount > 0) {
            return [
                'level' => 'low',
                'score' => 20,
                'rationale' => 'User browsing products without cart activity.',
            ];
        }

        return [
            'level' => 'none',
            'score' => 0,
            'rationale' => 'No interaction detected.',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{product_id: string, name: string, score: int, level: string}>
     */
    private function detectProductInterest(array $context): array
    {
        $interacted = $context['frequently_interacted_products'] ?? [];
        $result = [];

        foreach ($interacted as $item) {
            $score = (int) ($item['interaction_score'] ?? 0);
            $level = match (true) {
                $score >= 8 => 'high',
                $score >= 4 => 'medium',
                default => 'low',
            };

            $result[] = [
                'product_id' => (string) ($item['product_id'] ?? ''),
                'name' => (string) ($item['name'] ?? 'Product'),
                'score' => $score,
                'level' => $level,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{top_category: ?string, categories: list<array{category: string, score: int}>}
     */
    private function detectCategoryPreference(array $context): array
    {
        $categories = $context['preferred_categories'] ?? [];
        $topCat = isset($categories[0]['category']) ? (string) $categories[0]['category'] : null;

        return [
            'top_category' => $topCat,
            'categories' => $categories,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{product_id: string, name: string, viewed_at: string}>
     */
    private function detectFrequentlyViewed(array $context): array
    {
        return array_slice($context['recently_viewed_products'] ?? [], 0, 5);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{is_abandoned: bool, risk_level: string, unpurchased_item_ids: list<string>}
     */
    private function detectCartAbandonment(array $context): array
    {
        $signals = $context['abandoned_cart_signals'] ?? [];
        $isAbandoned = (bool) ($signals['has_abandoned_cart'] ?? false);
        $unpurchased = $signals['unpurchased_cart_product_ids'] ?? [];

        $checkouts = (int) ($context['checkout_activity']['total_checkouts_started'] ?? 0);

        $riskLevel = match (true) {
            $isAbandoned && $checkouts > 0 => 'high',
            $isAbandoned => 'medium',
            default => 'none',
        };

        return [
            'is_abandoned' => $isAbandoned,
            'risk_level' => $riskLevel,
            'unpurchased_item_ids' => $unpurchased,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  list<array{product_id: string, name: string, score: int, level: string}>  $productInterest
     * @param  array{top_category: ?string, categories: list<array{category: string, score: int}>}  $categoryPref
     * @return array{recommended_product_ids: list<string>, cross_sell_categories: list<string>, trigger: string}
     */
    private function generateRecommendationSignals(array $context, array $productInterest, array $categoryPref): array
    {
        $recommendedIds = array_column(array_slice($productInterest, 0, 3), 'product_id');

        $categories = array_column($categoryPref['categories'] ?? [], 'category');
        $crossSellCats = array_slice($categories, 0, 3);

        $trigger = match (true) {
            ($context['abandoned_cart_signals']['has_abandoned_cart'] ?? false) => 'cart_recovery',
            ! empty($recommendedIds) => 'personalized_products',
            ! empty($crossSellCats) => 'category_explore',
            default => 'popular_spices',
        };

        return [
            'recommended_product_ids' => array_values(array_filter($recommendedIds)),
            'cross_sell_categories' => array_values(array_filter($crossSellCats)),
            'trigger' => $trigger,
        ];
    }

    /**
     * @param  array<string, mixed>  $globalContext
     * @return list<array{product_id: string, views: int, status: string}>
     */
    private function detectWinningProducts(array $globalContext): array
    {
        $topProducts = $globalContext['top_viewed_products'] ?? [];
        $res = [];

        foreach ($topProducts as $item) {
            $views = (int) ($item['views'] ?? 0);
            $res[] = [
                'product_id' => (string) ($item['product_id'] ?? ''),
                'views' => $views,
                'status' => $views >= 5 ? 'Hot Seller' : 'Trending',
            ];
        }

        return $res;
    }
}
