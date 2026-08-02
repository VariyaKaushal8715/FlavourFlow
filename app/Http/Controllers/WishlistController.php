<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(Product $product, WishlistState $wishlist): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $wishlist->add($product);

        return response()->json(['wishlisted' => true]);
    }

    public function destroy(Product $product, WishlistState $wishlist): JsonResponse
    {
        $wishlist->remove($product);

        return response()->json(['wishlisted' => false]);
    }
}
