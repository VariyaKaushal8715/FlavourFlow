<?php

namespace App\Http\Controllers;

use App\AI\Contracts\AiEventTrackerInterface;
use App\Models\Offer;
use App\Models\Product;
use App\Support\ProductHighlightBuilder;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __construct(private ProductHighlightBuilder $productHighlightBuilder) {}

    public function __invoke(Request $request, WishlistState $wishlist, AiEventTrackerInterface $tracker): View
    {
        $site = config('personal_site');
        $sort = $request->string('sort')->toString();
        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();

        if ($search !== '') {
            $tracker->track('product_searched', null, null, [
                'query' => $search,
                'sort' => $sort,
            ]);
        }

        if ($category !== '' && $category !== 'all') {
            $tracker->track('category_viewed', 'category', null, [
                'category' => $category,
            ]);
        }

        $storedProducts = $this->sortedProducts($sort, $search, $category);
        $products = $storedProducts->isNotEmpty()
            ? $storedProducts->map->toHighlightData()->all()
            : $site['products'];

        $site['hero']['product_showcase'] = $this->productHighlightBuilder->forHero($products);
        $offers = Offer::query()
            ->visibleNow()
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->limit(6)
            ->get();

        return view('welcome', [
            'site' => $site,
            'products' => $products,
            'offers' => $offers,
            'wishlistProductIds' => $wishlist->productIds(),
            'sort' => $sort,
        ]);
    }

    private function sortedProducts(string $sort, string $search = '', string $category = ''): Collection
    {
        $query = Product::query()->active();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderByDesc('priority'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('priority'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('priority'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('is_featured')->orderByDesc('priority')->latest(),
        };

        return $query->get();
    }
}
