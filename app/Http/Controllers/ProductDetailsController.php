<?php

namespace App\Http\Controllers;

use App\AI\Contracts\AiEventTrackerInterface;
use App\Models\Product;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;

class ProductDetailsController extends Controller
{
    public function __invoke(Product $product, WishlistState $wishlist, AiEventTrackerInterface $tracker): View
    {
        abort_unless($product->is_active, 404);

        $tracker->track('product_viewed', 'product', $product->id, [
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category,
            'price' => (float) $product->price,
        ]);

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->getKey())
            ->where('category', $product->category)
            ->orderByDesc('priority')
            ->limit(3)
            ->get()
            ->map->toHighlightData()
            ->all();

        return view('products.show', [
            'site' => config('personal_site'),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'wishlistProductIds' => $wishlist->productIds(),
        ]);
    }
}
