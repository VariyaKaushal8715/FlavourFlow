<?php

namespace App\Http\Controllers;

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

    public function __invoke(Request $request, WishlistState $wishlist): View
    {
        $site = config('personal_site');
        $storedProducts = $this->sortedProducts($request->string('sort')->toString());
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
            'sort' => $request->string('sort')->toString(),
        ]);
    }

    private function sortedProducts(string $sort): Collection
    {
        $query = Product::query()->active();

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
