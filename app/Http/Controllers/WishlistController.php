<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()
            ->wishlistProducts()
            ->active()
            ->latest('wishlists.created_at')
            ->get()
            ->map->toHighlightData()
            ->all();

        return view('wishlist.index', [
            'site' => config('personal_site'),
            'products' => $products,
            'wishlistProductIds' => array_column($products, 'id'),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        return response()->json([
            'product_ids' => $request->user()->wishlistProducts()->pluck('products.id')->all(),
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json(['wishlisted' => true]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json(['wishlisted' => false]);
    }
}
