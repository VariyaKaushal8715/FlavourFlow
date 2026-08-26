<?php

namespace App\AI\Services;

use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Models\AiEvent;
use Illuminate\Support\Collection;
use Throwable;

class AiContextBuilder implements AiContextBuilderInterface
{
    public function buildContext(?int $userId = null, ?string $sessionId = null, int $limit = 100): array
    {
        try {
            $effectiveUserId = $userId ?? (auth()->check() ? auth()->id() : null);
            $effectiveSessionId = $sessionId ?? (session()->isStarted() ? session()->getId() : null);

            $events = $this->fetchEvents($effectiveUserId, $effectiveSessionId, $limit);

            if ($events->isEmpty()) {
                return $this->emptyContext($effectiveUserId, $effectiveSessionId);
            }

            return [
                'user_id' => $effectiveUserId,
                'session_id' => $effectiveSessionId,
                'total_events' => $events->count(),
                'first_activity_at' => $events->last()->created_at->toIso8601String(),
                'last_activity_at' => $events->first()->created_at->toIso8601String(),

                'recently_viewed_products' => $this->extractRecentlyViewed($events),
                'searches' => $this->extractSearches($events),
                'wishlist_activity' => $this->extractWishlistActivity($events),
                'cart_activity' => $this->extractCartActivity($events),
                'checkout_activity' => $this->extractCheckoutActivity($events),
                'orders' => $this->extractOrders($events),

                'preferred_categories' => $this->extractPreferredCategories($events),
                'frequently_interacted_products' => $this->extractFrequentlyInteractedProducts($events),
                'abandoned_cart_signals' => $this->extractAbandonedCartSignals($events),
            ];
        } catch (Throwable $e) {
            logger()->error('AI Context Builder Exception: '.$e->getMessage());

            return $this->emptyContext($userId, $sessionId);
        }
    }

    public function buildGlobalContext(int $days = 30): array
    {
        try {
            $query = AiEvent::query()->where('created_at', '>=', now()->subDays($days));
            $events = $query->latest()->take(2000)->get();

            if ($events->isEmpty()) {
                return [
                    'days_analyzed' => $days,
                    'total_events' => 0,
                    'top_viewed_products' => [],
                    'top_searches' => [],
                    'top_categories' => [],
                    'abandoned_checkout_count' => 0,
                    'total_orders_placed' => 0,
                ];
            }

            return [
                'days_analyzed' => $days,
                'total_events' => $events->count(),
                'top_viewed_products' => $this->extractGlobalTopProducts($events),
                'top_searches' => $this->extractGlobalTopSearches($events),
                'top_categories' => $this->extractGlobalTopCategories($events),
                'abandoned_checkout_count' => $this->calculateGlobalAbandonedCheckouts($events),
                'total_orders_placed' => $events->where('event_type', 'order_placed')->count(),
            ];
        } catch (Throwable $e) {
            logger()->error('AI Global Context Builder Exception: '.$e->getMessage());

            return [
                'days_analyzed' => $days,
                'total_events' => 0,
                'top_viewed_products' => [],
                'top_searches' => [],
                'top_categories' => [],
                'abandoned_checkout_count' => 0,
                'total_orders_placed' => 0,
            ];
        }
    }

    /**
     * Fetch user/session events.
     *
     * @return Collection<int, AiEvent>
     */
    private function fetchEvents(?int $userId, ?string $sessionId, int $limit): Collection
    {
        if ($userId === null && ($sessionId === null || $sessionId === '')) {
            return collect();
        }

        return AiEvent::query()
            ->where(function ($q) use ($userId, $sessionId): void {
                if ($userId !== null) {
                    $q->where('user_id', $userId);
                }
                if ($sessionId !== null && $sessionId !== '') {
                    if ($userId !== null) {
                        $q->orWhere('session_id', $sessionId);
                    } else {
                        $q->where('session_id', $sessionId);
                    }
                }
            })
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Default safe empty context structure.
     *
     * @return array<string, mixed>
     */
    private function emptyContext(?int $userId, ?string $sessionId): array
    {
        return [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'total_events' => 0,
            'first_activity_at' => null,
            'last_activity_at' => null,
            'recently_viewed_products' => [],
            'searches' => [],
            'wishlist_activity' => [
                'current_product_ids' => [],
                'total_added' => 0,
                'total_removed' => 0,
            ],
            'cart_activity' => [
                'current_product_ids' => [],
                'total_added' => 0,
                'total_removed' => 0,
            ],
            'checkout_activity' => [
                'total_checkouts_started' => 0,
                'last_checkout_at' => null,
            ],
            'orders' => [
                'total_orders' => 0,
                'total_spent' => 0.0,
            ],
            'preferred_categories' => [],
            'frequently_interacted_products' => [],
            'abandoned_cart_signals' => [
                'has_abandoned_cart' => false,
                'unpurchased_cart_product_ids' => [],
            ],
        ];
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{product_id: string, name: string, viewed_at: string}>
     */
    private function extractRecentlyViewed(Collection $events): array
    {
        $views = $events->where('event_type', 'product_viewed');
        $seen = [];
        $result = [];

        foreach ($views as $event) {
            $productId = (string) ($event->entity_id ?? ($event->metadata['product_id'] ?? ''));
            if ($productId !== '' && ! isset($seen[$productId])) {
                $seen[$productId] = true;
                $result[] = [
                    'product_id' => $productId,
                    'name' => $event->metadata['name'] ?? "Product #{$productId}",
                    'viewed_at' => $event->created_at->toIso8601String(),
                ];
            }
        }

        return $result;
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{query: string, count: int, last_searched_at: string}>
     */
    private function extractSearches(Collection $events): array
    {
        $searches = $events->where('event_type', 'product_searched');
        $grouped = [];

        foreach ($searches as $event) {
            $query = strtolower(trim((string) ($event->metadata['query'] ?? '')));
            if ($query !== '') {
                if (! isset($grouped[$query])) {
                    $grouped[$query] = [
                        'query' => $query,
                        'count' => 0,
                        'last_searched_at' => $event->created_at->toIso8601String(),
                    ];
                }
                $grouped[$query]['count']++;
            }
        }

        return array_values($grouped);
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return array{current_product_ids: list<string>, total_added: int, total_removed: int}
     */
    private function extractWishlistActivity(Collection $events): array
    {
        $adds = $events->where('event_type', 'wishlist_added');
        $removes = $events->where('event_type', 'wishlist_removed');

        $activeMap = [];
        foreach ($events->sortBy('created_at') as $event) {
            $productId = (string) ($event->entity_id ?? '');
            if ($productId === '') {
                continue;
            }
            if ($event->event_type === 'wishlist_added') {
                $activeMap[$productId] = true;
            } elseif ($event->event_type === 'wishlist_removed') {
                unset($activeMap[$productId]);
            }
        }

        return [
            'current_product_ids' => array_keys($activeMap),
            'total_added' => $adds->count(),
            'total_removed' => $removes->count(),
        ];
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return array{current_product_ids: list<string>, total_added: int, total_removed: int}
     */
    private function extractCartActivity(Collection $events): array
    {
        $adds = $events->where('event_type', 'cart_added');
        $removes = $events->where('event_type', 'cart_removed');

        $activeMap = [];
        foreach ($events->sortBy('created_at') as $event) {
            $productId = (string) ($event->entity_id ?? '');
            if ($productId === '') {
                continue;
            }
            if ($event->event_type === 'cart_added') {
                $activeMap[$productId] = true;
            } elseif ($event->event_type === 'cart_removed') {
                unset($activeMap[$productId]);
            }
        }

        return [
            'current_product_ids' => array_keys($activeMap),
            'total_added' => $adds->count(),
            'total_removed' => $removes->count(),
        ];
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return array{total_checkouts_started: int, last_checkout_at: ?string}
     */
    private function extractCheckoutActivity(Collection $events): array
    {
        $checkouts = $events->where('event_type', 'checkout_started');

        return [
            'total_checkouts_started' => $checkouts->count(),
            'last_checkout_at' => $checkouts->first()?->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return array{total_orders: int, total_spent: float}
     */
    private function extractOrders(Collection $events): array
    {
        $orders = $events->where('event_type', 'order_placed');
        $totalSpent = 0.0;

        foreach ($orders as $event) {
            $totalSpent += (float) ($event->metadata['total'] ?? 0.0);
        }

        return [
            'total_orders' => $orders->count(),
            'total_spent' => round($totalSpent, 2),
        ];
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{category: string, score: int}>
     */
    private function extractPreferredCategories(Collection $events): array
    {
        $scores = [];

        foreach ($events as $event) {
            $cat = (string) ($event->metadata['category'] ?? '');
            if ($cat !== '') {
                $weight = match ($event->event_type) {
                    'product_viewed' => 1,
                    'category_viewed' => 2,
                    'wishlist_added' => 3,
                    'cart_added' => 4,
                    'order_placed' => 5,
                    default => 1,
                };
                $scores[$cat] = ($scores[$cat] ?? 0) + $weight;
            }
        }

        arsort($scores);

        $result = [];
        foreach ($scores as $category => $score) {
            $result[] = ['category' => $category, 'score' => $score];
        }

        return $result;
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{product_id: string, name: string, interaction_score: int}>
     */
    private function extractFrequentlyInteractedProducts(Collection $events): array
    {
        $scores = [];
        $names = [];

        foreach ($events as $event) {
            $productId = (string) ($event->entity_id ?? '');
            if ($productId === '') {
                continue;
            }

            $weight = match ($event->event_type) {
                'product_viewed' => 1,
                'wishlist_added' => 3,
                'cart_added' => 4,
                'order_placed' => 6,
                default => 1,
            };

            $scores[$productId] = ($scores[$productId] ?? 0) + $weight;
            if (isset($event->metadata['name'])) {
                $names[$productId] = (string) $event->metadata['name'];
            }
        }

        arsort($scores);

        $result = [];
        foreach ($scores as $productId => $score) {
            $result[] = [
                'product_id' => $productId,
                'name' => $names[$productId] ?? "Product #{$productId}",
                'interaction_score' => $score,
            ];
        }

        return array_slice($result, 0, 10);
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return array{has_abandoned_cart: bool, unpurchased_cart_product_ids: list<string>}
     */
    private function extractAbandonedCartSignals(Collection $events): array
    {
        $hasCartAddOrCheckout = $events->contains(fn ($e) => in_array($e->event_type, ['cart_added', 'checkout_started'], true));
        $latestOrder = $events->where('event_type', 'order_placed')->first();
        $latestOrderTime = $latestOrder?->created_at;

        if (! $hasCartAddOrCheckout) {
            return [
                'has_abandoned_cart' => false,
                'unpurchased_cart_product_ids' => [],
            ];
        }

        $cartAddsAfterOrder = $events->filter(function ($e) use ($latestOrderTime): bool {
            if (! in_array($e->event_type, ['cart_added', 'checkout_started'], true)) {
                return false;
            }

            return $latestOrderTime === null || $e->created_at->gt($latestOrderTime);
        });

        $unpurchasedIds = $cartAddsAfterOrder
            ->pluck('entity_id')
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($id) => (string) $id)
            ->all();

        return [
            'has_abandoned_cart' => $cartAddsAfterOrder->isNotEmpty(),
            'unpurchased_cart_product_ids' => $unpurchasedIds,
        ];
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{product_id: string, views: int}>
     */
    private function extractGlobalTopProducts(Collection $events): array
    {
        $counts = $events->where('event_type', 'product_viewed')
            ->groupBy('entity_id')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(5);

        $res = [];
        foreach ($counts as $productId => $views) {
            if ($productId !== null && $productId !== '') {
                $res[] = ['product_id' => (string) $productId, 'views' => $views];
            }
        }

        return $res;
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{query: string, count: int}>
     */
    private function extractGlobalTopSearches(Collection $events): array
    {
        $searches = $events->where('event_type', 'product_searched');
        $counts = [];

        foreach ($searches as $e) {
            $q = strtolower(trim((string) ($e->metadata['query'] ?? '')));
            if ($q !== '') {
                $counts[$q] = ($counts[$q] ?? 0) + 1;
            }
        }

        arsort($counts);

        $res = [];
        foreach (array_slice($counts, 0, 5, true) as $q => $cnt) {
            $res[] = ['query' => $q, 'count' => $cnt];
        }

        return $res;
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     * @return list<array{category: string, count: int}>
     */
    private function extractGlobalTopCategories(Collection $events): array
    {
        $counts = [];

        foreach ($events as $e) {
            $cat = (string) ($e->metadata['category'] ?? '');
            if ($cat !== '') {
                $counts[$cat] = ($counts[$cat] ?? 0) + 1;
            }
        }

        arsort($counts);

        $res = [];
        foreach (array_slice($counts, 0, 5, true) as $cat => $cnt) {
            $res[] = ['category' => $cat, 'count' => $cnt];
        }

        return $res;
    }

    /**
     * @param  Collection<int, AiEvent>  $events
     */
    private function calculateGlobalAbandonedCheckouts(Collection $events): int
    {
        $checkouts = $events->where('event_type', 'checkout_started');
        $orders = $events->where('event_type', 'order_placed');

        return max(0, $checkouts->count() - $orders->count());
    }
}
