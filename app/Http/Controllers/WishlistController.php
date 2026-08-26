<?php

namespace App\Http\Controllers;

use App\AI\Contracts\AiEventTrackerInterface;
use App\Models\Product;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    public function index(WishlistState $wishlist): View
    {
        $products = $wishlist->products()->map->toHighlightData()->all();

        return view('wishlist.index', [
            'site' => config('personal_site'),
            'products' => $products,
            'wishlistProductIds' => array_column($products, 'id'),
        ]);
    }

    public function products(WishlistState $wishlist): JsonResponse
    {
        return response()->json([
            'product_ids' => $wishlist->productIds(),
        ]);
    }

    public function store(Product $product, WishlistState $wishlist, AiEventTrackerInterface $tracker): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $wishlist->add($product);

        $tracker->track('wishlist_added', 'product', $product->id, [
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
        ]);

        return response()->json(['wishlisted' => true]);
    }

    public function destroy(Product $product, WishlistState $wishlist, AiEventTrackerInterface $tracker): JsonResponse
    {
        $wishlist->remove($product);

        $tracker->track('wishlist_removed', 'product', $product->id, [
            'name' => $product->name,
            'slug' => $product->slug,
        ]);

        return response()->json(['wishlisted' => false]);
    }
}
