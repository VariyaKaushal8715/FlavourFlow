<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

class WishlistState
{
    private const SESSION_KEY = 'wishlist.product_ids';

    /**
     * @return array<int, int>
     */
    public function productIds(): array
    {
        if (auth()->check()) {
            return auth()
                ->user()
                ->wishlistProducts()
                ->active()
                ->latest('wishlists.created_at')
                ->pluck('products.id')
                ->map(fn ($productId): int => (int) $productId)
                ->all();
        }

        return array_values($this->guestProductIds());
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        $productIds = $this->productIds();

        if ($productIds === []) {
            return collect();
        }

        $products = Product::query()
            ->active()
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');

        return collect($productIds)
            ->map(fn (int $productId): ?Product => $products->get($productId))
            ->filter()
            ->values();
    }

    public function add(Product $product): void
    {
        if (auth()->check()) {
            Wishlist::query()->firstOrCreate([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);

            return;
        }

        $productIds = $this->guestProductIds();
        $productIds[(string) $product->id] = $product->id;
        $this->storeGuestProductIds($productIds);
    }

    public function remove(Product $product): void
    {
        if (auth()->check()) {
            Wishlist::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->delete();

            return;
        }

        $productIds = $this->guestProductIds();
        unset($productIds[(string) $product->id]);
        $this->storeGuestProductIds($productIds);
    }

    /**
     * @return array<string, int>
     */
    private function guestProductIds(): array
    {
        $productIds = session()->get(self::SESSION_KEY, []);

        if (! is_array($productIds)) {
            return [];
        }

        $normalizedIds = [];
        foreach ($productIds as $key => $productId) {
            $normalizedIds[(string) (int) $key] = (int) $productId;
        }

        if ($normalizedIds === []) {
            return [];
        }

        $validIds = Product::query()
            ->active()
            ->whereKey(array_values($normalizedIds))
            ->pluck('id')
            ->map(fn ($productId): int => (int) $productId)
            ->all();

        $validLookup = array_fill_keys(array_map('strval', $validIds), true);
        $filteredIds = array_filter(
            $normalizedIds,
            fn (int $productId, string $key): bool => isset($validLookup[$key]) && $productId === (int) $key,
            ARRAY_FILTER_USE_BOTH,
        );

        if ($filteredIds !== $normalizedIds) {
            $this->storeGuestProductIds($filteredIds);
        }

        return $filteredIds;
    }

    /**
     * @param  array<string, int>  $productIds
     */
    private function storeGuestProductIds(array $productIds): void
    {
        session()->put(self::SESSION_KEY, $productIds);
    }
}
