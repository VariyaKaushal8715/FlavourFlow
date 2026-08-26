<?php

namespace App\AI\Services;

use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiProviderInterface;
use App\AI\Core\AiReasoningResponse;
use Throwable;

class AiBrain implements AiBrainInterface
{
    /**
     * Supported language codes and their display names.
     *
     * @var array<string, string>
     */
    private const LANGUAGES = [
        'en' => 'English',
        'hi' => 'Hindi',
        'gu' => 'Gujarati',
        'hinglish' => 'Hinglish',
        'gujenglish' => 'GujEnglish',
    ];

    public function __construct(
        private AiProviderInterface $provider,
        private AiContextBuilderInterface $contextBuilder,
        private AiAnalyzerInterface $analyzer,
    ) {}

    public function reason(string $intent, array $context, array $options = []): AiReasoningResponse
    {
        try {
            $language = $options['language'] ?? 'en';
            $audience = $options['audience'] ?? 'customer';

            $analysis = $this->analyzer->analyze($context);

            return match ($intent) {
                'product_recommendation' => $this->reasonProductRecommendation($context, $analysis, $language),
                'cart_recovery' => $this->reasonCartRecovery($context, $analysis, $language),
                'purchase_intent' => $this->reasonPurchaseIntent($context, $analysis, $language),
                'order_help' => $this->reasonOrderHelp($context, $analysis, $language),
                'product_query' => $this->reasonProductQuery($context, $analysis, $language, $options['query'] ?? ''),
                'admin_dashboard' => $this->reasonAdminDashboard($context, $analysis),
                'admin_sales_insight' => $this->reasonAdminSalesInsight($context, $analysis),
                'admin_inventory_alert' => $this->reasonAdminInventoryAlert($context, $analysis),
                default => $this->reasonGeneral($context, $analysis, $language, $audience),
            };
        } catch (Throwable $e) {
            logger()->error('AI Brain Reasoning Exception: '.$e->getMessage());

            return new AiReasoningResponse(
                intent: $intent,
                reasoning: 'Unable to process reasoning at this time.',
                confidence: 0.0,
                language: $options['language'] ?? 'en',
                audience: $options['audience'] ?? 'customer',
                provider: $this->provider->getName(),
            );
        }
    }

    public function reasonForCustomer(array $context, string $query, string $language = 'en'): AiReasoningResponse
    {
        $analysis = $this->analyzer->analyze($context);
        $intent = $this->detectCustomerIntent($query, $context, $analysis);

        return $this->reason($intent, $context, [
            'language' => $language,
            'audience' => 'customer',
            'query' => $query,
        ]);
    }

    public function reasonForAdmin(array $context, string $query): AiReasoningResponse
    {
        $analysis = $this->analyzer->analyze($context);
        $intent = $this->detectAdminIntent($query, $analysis);

        return $this->reason($intent, $context, [
            'language' => 'en',
            'audience' => 'admin',
            'query' => $query,
        ]);
    }

    public function getSupportedLanguages(): array
    {
        return array_keys(self::LANGUAGES);
    }

    public function isReady(): bool
    {
        return $this->provider->isAvailable();
    }

    /**
     * Detect customer intent from the query and behavioural context.
     */
    private function detectCustomerIntent(string $query, array $context, array $analysis): string
    {
        $queryLower = strtolower(trim($query));

        if ($queryLower === '') {
            $abandonedCart = $analysis['cart_abandonment']['is_abandoned'] ?? false;

            return $abandonedCart ? 'cart_recovery' : 'product_recommendation';
        }

        $orderKeywords = ['order', 'delivery', 'track', 'shipping', 'status', 'cancel'];
        foreach ($orderKeywords as $kw) {
            if (str_contains($queryLower, $kw)) {
                return 'order_help';
            }
        }

        $cartKeywords = ['cart', 'checkout', 'buy', 'purchase', 'pay'];
        foreach ($cartKeywords as $kw) {
            if (str_contains($queryLower, $kw)) {
                return 'purchase_intent';
            }
        }

        $recommendKeywords = ['recommend', 'suggest', 'best', 'popular', 'trending', 'similar', 'like'];
        foreach ($recommendKeywords as $kw) {
            if (str_contains($queryLower, $kw)) {
                return 'product_recommendation';
            }
        }

        return 'product_query';
    }

    /**
     * Detect admin intent from the query.
     */
    private function detectAdminIntent(string $query, array $analysis): string
    {
        $queryLower = strtolower(trim($query));

        $salesKeywords = ['sales', 'revenue', 'earnings', 'profit', 'conversion'];
        foreach ($salesKeywords as $kw) {
            if (str_contains($queryLower, $kw)) {
                return 'admin_sales_insight';
            }
        }

        $inventoryKeywords = ['stock', 'inventory', 'out of stock', 'restock', 'supply'];
        foreach ($inventoryKeywords as $kw) {
            if (str_contains($queryLower, $kw)) {
                return 'admin_inventory_alert';
            }
        }

        return 'admin_dashboard';
    }

    private function reasonProductRecommendation(array $context, array $analysis, string $language): AiReasoningResponse
    {
        $recSignals = $analysis['recommendation_signals'] ?? [];
        $productInterest = $analysis['product_interest'] ?? [];
        $categoryPref = $analysis['category_preference'] ?? [];
        $topCategory = $categoryPref['top_category'] ?? null;

        $recommendedIds = $recSignals['recommended_product_ids'] ?? [];
        $trigger = $recSignals['trigger'] ?? 'general_browse';

        $reasoning = match ($trigger) {
            'cart_recovery' => $this->localize('Based on items left in your cart, we think you might still be interested in these products.', $language),
            'personalized_products' => $this->localize('Based on your browsing and interaction history, here are products we think you will love.', $language),
            'category_explore' => $this->localize("Since you enjoy {$topCategory}, here are more picks from that category.", $language),
            default => $this->localize('Here are our most popular products to get you started.', $language),
        };

        $confidence = match (true) {
            count($recommendedIds) >= 3 => 0.85,
            count($recommendedIds) >= 1 => 0.65,
            default => 0.35,
        };

        $actions = [];
        foreach (array_slice($recommendedIds, 0, 3) as $productId) {
            $name = $this->findProductName($productId, $productInterest);
            $actions[] = [
                'action' => 'view_product',
                'label' => $this->localize("View {$name}", $language),
                'data' => ['product_id' => $productId],
            ];
        }

        return new AiReasoningResponse(
            intent: 'product_recommendation',
            reasoning: $reasoning,
            confidence: $confidence,
            recommendedActions: $actions,
            data: [
                'recommended_product_ids' => $recommendedIds,
                'cross_sell_categories' => $recSignals['cross_sell_categories'] ?? [],
                'trigger' => $trigger,
            ],
            language: $language,
            audience: 'customer',
            provider: $this->provider->getName(),
        );
    }

    private function reasonCartRecovery(array $context, array $analysis, string $language): AiReasoningResponse
    {
        $abandonment = $analysis['cart_abandonment'] ?? [];
        $isAbandoned = $abandonment['is_abandoned'] ?? false;
        $unpurchased = $abandonment['unpurchased_item_ids'] ?? [];
        $riskLevel = $abandonment['risk_level'] ?? 'none';

        if (! $isAbandoned) {
            return new AiReasoningResponse(
                intent: 'cart_recovery',
                reasoning: $this->localize('Your cart is all clear! Browse our collection to find something you love.', $language),
                confidence: 0.9,
                language: $language,
                audience: 'customer',
                provider: $this->provider->getName(),
            );
        }

        $confidence = match ($riskLevel) {
            'high' => 0.9,
            'medium' => 0.7,
            default => 0.5,
        };

        $reasoning = $this->localize('You have items waiting in your cart! Complete your purchase before they sell out.', $language);

        $actions = [
            [
                'action' => 'go_to_cart',
                'label' => $this->localize('View Cart', $language),
                'data' => [],
            ],
            [
                'action' => 'go_to_checkout',
                'label' => $this->localize('Checkout Now', $language),
                'data' => [],
            ],
        ];

        return new AiReasoningResponse(
            intent: 'cart_recovery',
            reasoning: $reasoning,
            confidence: $confidence,
            recommendedActions: $actions,
            data: [
                'unpurchased_product_ids' => $unpurchased,
                'risk_level' => $riskLevel,
            ],
            language: $language,
            audience: 'customer',
            provider: $this->provider->getName(),
        );
    }

    private function reasonPurchaseIntent(array $context, array $analysis, string $language): AiReasoningResponse
    {
        $intent = $analysis['purchase_intent'] ?? [];
        $level = $intent['level'] ?? 'none';
        $score = $intent['score'] ?? 0;

        $reasoning = match ($level) {
            'converted' => $this->localize('Great news — you have already placed an order! Check your orders for tracking details.', $language),
            'high' => $this->localize('You are almost there! Complete your checkout to place your order.', $language),
            'medium' => $this->localize('You have products saved. Ready to add them to cart?', $language),
            'low' => $this->localize('Still exploring? Let us help you find the perfect spices for your kitchen.', $language),
            default => $this->localize('Welcome! Browse our collection of premium spices and pantry essentials.', $language),
        };

        $confidence = min(1.0, $score / 100);

        return new AiReasoningResponse(
            intent: 'purchase_intent',
            reasoning: $reasoning,
            confidence: $confidence,
            data: [
                'intent_level' => $level,
                'intent_score' => $score,
            ],
            language: $language,
            audience: 'customer',
            provider: $this->provider->getName(),
        );
    }

    private function reasonOrderHelp(array $context, array $analysis, string $language): AiReasoningResponse
    {
        $orders = $context['orders'] ?? [];
        $totalOrders = $orders['total_orders'] ?? 0;

        if ($totalOrders === 0) {
            return new AiReasoningResponse(
                intent: 'order_help',
                reasoning: $this->localize('You have not placed any orders yet. Browse our products and place your first order!', $language),
                confidence: 0.9,
                recommendedActions: [
                    [
                        'action' => 'browse_products',
                        'label' => $this->localize('Browse Products', $language),
                        'data' => [],
                    ],
                ],
                language: $language,
                audience: 'customer',
                provider: $this->provider->getName(),
            );
        }

        return new AiReasoningResponse(
            intent: 'order_help',
            reasoning: $this->localize("You have placed {$totalOrders} order(s). Visit your account to track delivery status.", $language),
            confidence: 0.85,
            recommendedActions: [
                [
                    'action' => 'view_orders',
                    'label' => $this->localize('View My Orders', $language),
                    'data' => [],
                ],
            ],
            data: [
                'total_orders' => $totalOrders,
                'total_spent' => $orders['total_spent'] ?? 0.0,
            ],
            language: $language,
            audience: 'customer',
            provider: $this->provider->getName(),
        );
    }

    private function reasonProductQuery(array $context, array $analysis, string $language, string $query): AiReasoningResponse
    {
        $productInterest = $analysis['product_interest'] ?? [];
        $frequentlyViewed = $analysis['frequently_viewed_products'] ?? [];

        $reasoning = $this->localize('Here is what we found based on your question. Let us know if you need more help!', $language);
        $confidence = 0.6;

        $actions = [];
        foreach (array_slice($frequentlyViewed, 0, 2) as $item) {
            $actions[] = [
                'action' => 'view_product',
                'label' => $this->localize('View '.$item['name'], $language),
                'data' => ['product_id' => $item['product_id'] ?? ''],
            ];
        }

        return new AiReasoningResponse(
            intent: 'product_query',
            reasoning: $reasoning,
            confidence: $confidence,
            recommendedActions: $actions,
            data: [
                'query' => $query,
                'matched_products' => count($productInterest),
            ],
            language: $language,
            audience: 'customer',
            provider: $this->provider->getName(),
        );
    }

    private function reasonAdminDashboard(array $context, array $analysis): AiReasoningResponse
    {
        $intent = $analysis['purchase_intent'] ?? [];
        $abandonment = $analysis['cart_abandonment'] ?? [];
        $categoryPref = $analysis['category_preference'] ?? [];

        $totalEvents = $context['total_events'] ?? 0;
        $orders = $context['orders'] ?? [];

        $insights = [];
        $insights[] = "Total tracked events: {$totalEvents}.";
        $insights[] = 'Orders placed: '.($orders['total_orders'] ?? 0).'.';
        $insights[] = 'Revenue recorded: ₹'.number_format((float) ($orders['total_spent'] ?? 0), 2).'.';

        if (($abandonment['is_abandoned'] ?? false)) {
            $insights[] = 'Cart abandonment detected (Risk: '.($abandonment['risk_level'] ?? 'none').').';
        }

        if ($topCat = ($categoryPref['top_category'] ?? null)) {
            $insights[] = "Top performing category: {$topCat}.";
        }

        $reasoning = implode(' ', $insights);
        $confidence = $totalEvents > 0 ? 0.8 : 0.3;

        return new AiReasoningResponse(
            intent: 'admin_dashboard',
            reasoning: $reasoning,
            confidence: $confidence,
            data: [
                'total_events' => $totalEvents,
                'orders' => $orders,
                'top_category' => $topCat,
                'cart_abandonment_risk' => $abandonment['risk_level'] ?? 'none',
            ],
            language: 'en',
            audience: 'admin',
            provider: $this->provider->getName(),
        );
    }

    private function reasonAdminSalesInsight(array $context, array $analysis): AiReasoningResponse
    {
        $orders = $context['orders'] ?? [];
        $totalOrders = $orders['total_orders'] ?? 0;
        $totalSpent = (float) ($orders['total_spent'] ?? 0.0);

        $avgOrderValue = $totalOrders > 0 ? round($totalSpent / $totalOrders, 2) : 0.0;

        $intentLevel = $analysis['purchase_intent']['level'] ?? 'none';

        $reasoning = $totalOrders > 0
            ? "Sales overview: {$totalOrders} orders totalling ₹".number_format($totalSpent, 2).". Average order value: ₹".number_format($avgOrderValue, 2).". Current purchase intent level: {$intentLevel}."
            : 'No sales data recorded yet. Drive traffic to the store to generate insights.';

        return new AiReasoningResponse(
            intent: 'admin_sales_insight',
            reasoning: $reasoning,
            confidence: $totalOrders > 0 ? 0.85 : 0.3,
            data: [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalSpent,
                'avg_order_value' => $avgOrderValue,
                'purchase_intent_level' => $intentLevel,
            ],
            language: 'en',
            audience: 'admin',
            provider: $this->provider->getName(),
        );
    }

    private function reasonAdminInventoryAlert(array $context, array $analysis): AiReasoningResponse
    {
        $frequentProducts = $analysis['product_interest'] ?? [];
        $highDemand = array_filter($frequentProducts, fn ($p) => ($p['level'] ?? '') === 'high');

        $reasoning = count($highDemand) > 0
            ? count($highDemand).' product(s) with high demand detected. Verify stock levels for these items to prevent lost sales.'
            : 'No high-demand inventory alerts at this time.';

        $actions = [];
        foreach (array_slice($highDemand, 0, 3) as $item) {
            $actions[] = [
                'action' => 'check_inventory',
                'label' => 'Check stock: '.($item['name'] ?? 'Product'),
                'data' => ['product_id' => $item['product_id'] ?? ''],
            ];
        }

        return new AiReasoningResponse(
            intent: 'admin_inventory_alert',
            reasoning: $reasoning,
            confidence: count($highDemand) > 0 ? 0.75 : 0.5,
            recommendedActions: $actions,
            data: [
                'high_demand_count' => count($highDemand),
                'high_demand_products' => array_column($highDemand, 'product_id'),
            ],
            language: 'en',
            audience: 'admin',
            provider: $this->provider->getName(),
        );
    }

    private function reasonGeneral(array $context, array $analysis, string $language, string $audience): AiReasoningResponse
    {
        $totalEvents = $context['total_events'] ?? 0;

        $reasoning = $audience === 'admin'
            ? "AI Brain is active. {$totalEvents} events analysed. Ask about sales, inventory, or customer behavior."
            : $this->localize('How can I help you today? Ask me about products, orders, or recommendations!', $language);

        return new AiReasoningResponse(
            intent: 'general',
            reasoning: $reasoning,
            confidence: 0.6,
            language: $language,
            audience: $audience,
            provider: $this->provider->getName(),
        );
    }

    /**
     * Localize a reasoning string to the requested language.
     *
     * In the deterministic phase this returns English with a language tag.
     * When a real LLM provider is connected, this will delegate to the provider
     * for native multilingual generation.
     */
    private function localize(string $text, string $language): string
    {
        if ($language === 'en') {
            return $text;
        }

        $langName = self::LANGUAGES[$language] ?? $language;

        return "[{$langName}] {$text}";
    }

    /**
     * Look up a product name from the interest array.
     *
     * @param  list<array{product_id: string, name: string, score: int, level: string}>  $productInterest
     */
    private function findProductName(string $productId, array $productInterest): string
    {
        foreach ($productInterest as $item) {
            if (($item['product_id'] ?? '') === $productId) {
                return $item['name'] ?? "Product #{$productId}";
            }
        }

        return "Product #{$productId}";
    }
}
