<?php

namespace App\AI\Services;

use App\AI\Models\AiEvent;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Service to build a deterministic personalization profile for a customer.
 *
 * The profile is cached (15 minutes) and aggregates activity from the past 12 months.
 * It includes weighted scores based on recency (exponential decay, half‑life ≈ 30 days).
 */
class CustomerPersonalizationService
{
    /** Cache TTL in minutes */
    protected const CACHE_TTL = 15;

    /** History window for aggregation */
    protected const HISTORY_DAYS = 365;

    /** Retrieve the cached profile for a customer. */
    public function getProfile(int $customerId): array
    {
        return Cache::remember(
            $this->cacheKey($customerId),
            self::CACHE_TTL * 60,
            fn () => $this->buildProfile($customerId)
        );
    }

    /** Build the profile from raw data. */
    protected function buildProfile(int $customerId): array
    {
        $since = Carbon::now()->subDays(self::HISTORY_DAYS);
        $events = AiEvent::query()
            ->where('user_id', $customerId)
            ->where('created_at', '>=', $since)
            ->latest()
            ->get();

        // ---------- Category preferences ----------
        $categoryScores = [];
        foreach ($events as $event) {
            $cat = (string) ($event->metadata['category'] ?? '');
            if ($cat === '') {
                continue;
            }
            $weight = $this->recencyWeight($event->created_at);
            $base = match ($event->event_type) {
                'order_placed' => 5,
                'cart_added' => 4,
                'wishlist_added' => 3,
                'category_viewed' => 2,
                default => 1,
            };
            $categoryScores[$cat] = ($categoryScores[$cat] ?? 0) + $weight * $base;
        }
        arsort($categoryScores);
        $preferredCategories = [];
        foreach (array_slice($categoryScores, 0, 5, true) as $cat => $score) {
            $preferredCategories[] = ['category' => $cat, 'score' => (int) round($score)];
        }
        $topCategory = $preferredCategories[0]['category'] ?? null;

        // ---------- Product preferences ----------
        $productScores = [];
        $productNames = [];
        foreach ($events as $event) {
            $productId = (string) ($event->entity_id ?? ($event->metadata['product_id'] ?? ''));
            if ($productId === '') {
                continue;
            }
            $weight = $this->recencyWeight($event->created_at);
            $base = match ($event->event_type) {
                'order_placed' => 6,
                'cart_added' => 4,
                'wishlist_added' => 3,
                'product_viewed' => 1,
                default => 1,
            };
            $productScores[$productId] = ($productScores[$productId] ?? 0) + $weight * $base;
            if (!isset($productNames[$productId]) && isset($event->metadata['name'])) {
                $productNames[$productId] = (string) $event->metadata['name'];
            }
        }
        arsort($productScores);
        $preferredProducts = [];
        foreach (array_slice($productScores, 0, 10, true) as $pid => $score) {
            $preferredProducts[] = [
                'product_id' => $pid,
                'name' => $productNames[$pid] ?? "Product #{$pid}",
                'score' => (int) round($score),
            ];
        }

        // ---------- Search terms ----------
        $searchCounts = [];
        foreach ($events->where('event_type', 'product_searched') as $e) {
            $q = strtolower(trim((string) ($e->metadata['query'] ?? '')));
            if ($q === '') {
                continue;
            }
            $weight = $this->recencyWeight($e->created_at);
            $searchCounts[$q] = ($searchCounts[$q] ?? 0) + $weight;
        }
        arsort($searchCounts);
        $frequentSearchTerms = array_slice(array_keys($searchCounts), 0, 5);

        // ---------- Wishlist & Cart ----------
        $wishlistIds = Wishlist::where('user_id', $customerId)->pluck('product_id')->unique()->values()->all();
        $cartIds = Cart::where('user_id', $customerId)->pluck('product_id')->unique()->values()->all();

        // ---------- Purchase history ----------
        $orders = Order::where('user_id', $customerId)->where('created_at', '>=', $since)->get();
        $totalSpent = $orders->sum('total');
        $orderCount = $orders->count();
        $purchasedCategories = [];
        $repeatPurchaseProductIds = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $pid = (string) $item->product_id;
                $repeatPurchaseProductIds[] = $pid;
                $cat = $item->product->category ?? '';
                if ($cat !== '') {
                    $purchasedCategories[$cat] = ($purchasedCategories[$cat] ?? 0) + 1;
                }
            }
        }
        $purchasedCategories = array_keys($purchasedCategories);
        $repeatPurchaseProductIds = array_values(array_unique($repeatPurchaseProductIds));

        return [
            'preferred_categories' => $preferredCategories,
            'top_category' => $topCategory,
            'preferred_products' => $preferredProducts,
            'frequent_search_terms' => $frequentSearchTerms,
            'wishlist_product_ids' => $wishlistIds,
            'cart_product_ids' => $cartIds,
            'total_spent' => round($totalSpent, 2),
            'order_count' => $orderCount,
            'purchased_categories' => $purchasedCategories,
            'repeat_purchase_product_ids' => $repeatPurchaseProductIds,
        ];
    }

    /** Calculate exponential decay weight based on recency. Half‑life ≈ 30 days. */
    protected function recencyWeight(Carbon $timestamp): float
    {
        $days = Carbon::now()->diffInDays($timestamp);
        return pow(0.5, $days / 30);
    }

    protected function cacheKey(int $customerId): string
    {
        return "customer_personalization_profile:{$customerId}";
    }
}

?>

