<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductSortOption;
use App\Support\ProductHighlightBuilder;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __construct(private ProductHighlightBuilder $productHighlightBuilder) {}

    public function __invoke(Request $request, WishlistState $wishlist): View
    {
        $site = config('personal_site');

        // Retrieve active sort options from database ordered by display_order
        $sortOptions = ProductSortOption::query()->active()->ordered()->get();
        if ($sortOptions->isEmpty()) {
            ProductSortOption::seedDefaults();
            $sortOptions = ProductSortOption::query()->active()->ordered()->get();
        }

        $defaultOption = $sortOptions->firstWhere('is_default', true) ?? $sortOptions->first();
        $defaultSortKey = $defaultOption ? $defaultOption->key : 'featured';

        $requestedSort = $request->string('sort')->toString();
        $selectedSortOption = $sortOptions->firstWhere('key', $requestedSort);

        // If requested sort is not active or empty, fall back to default active option
        if (! $selectedSortOption) {
            $selectedSortOption = $defaultOption;
            $sort = $defaultSortKey;
        } else {
            $sort = $selectedSortOption->key;
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;

        $hasActiveProducts = Product::query()->active()->exists();
        $storedProducts = $this->sortedProducts($selectedSortOption, $sort, $minPrice, $maxPrice);

        if ($hasActiveProducts) {
            $products = $storedProducts->map->toHighlightData()->all();
        } else {
            $products = $site['products'];
        }

        $heroProducts = ! empty($products)
            ? $products
            : ($hasActiveProducts ? Product::query()->active()->get()->map->toHighlightData()->all() : $site['products']);
        $site['hero']['product_showcase'] = $this->productHighlightBuilder->forHero($heroProducts);

        $offers = Offer::query()
            ->visibleNow()
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->limit(6)
            ->get();

        $minDbPrice = (float) (Product::query()->active()->min('price') ?? 50);
        $maxDbPrice = (float) (Product::query()->active()->max('price') ?? 1000);
        $lowestPrice = max(0, (float) floor($minDbPrice / 10) * 10);
        $highestPrice = max($lowestPrice + 100, (float) ceil($maxDbPrice / 10) * 10);

        return view('welcome', [
            'site' => $site,
            'products' => $products,
            'offers' => $offers,
            'wishlistProductIds' => $wishlist->productIds(),
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'lowestPrice' => $lowestPrice,
            'highestPrice' => $highestPrice,
        ]);
    }

    private function sortedProducts(?ProductSortOption $sortOption, string $sort, ?float $minPrice = null, ?float $maxPrice = null): Collection
    {
        $query = Product::query()->active();

        if ($minPrice !== null && $minPrice > 0) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null && $maxPrice > 0) {
            $query->where('price', '<=', $maxPrice);
        }

        if ($sortOption) {
            $sortOption->applyToQuery($query);
        } else {
            match ($sort) {
                'price_asc', 'price_range' => $query->orderBy('price')->orderByDesc('priority'),
                'price_desc' => $query->orderByDesc('price')->orderByDesc('priority'),
                'rating' => $query->orderByDesc('rating')->orderByDesc('priority'),
                'name' => $query->orderBy('name'),
                'newest' => $query->latest('created_at')->orderByDesc('priority'),
                'best_selling' => $query->withSum('orderItems', 'quantity')->orderByDesc('order_items_sum_quantity')->orderByDesc('priority'),
                'discount' => $query->orderByRaw('(CASE WHEN compare_at_price > price THEN (compare_at_price - price) ELSE 0 END) DESC')->orderByDesc('priority'),
                default => $query->orderByDesc('is_featured')->orderByDesc('priority')->latest(),
            };
        }

        return $query->get();
    }
}
