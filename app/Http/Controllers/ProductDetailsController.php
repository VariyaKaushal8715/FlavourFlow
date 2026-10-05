<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    public function __invoke(Request $request, Product $product, WishlistState $wishlist): View
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();
        $profile = $user ? $user->profile()->first() : null;

        $userLocation = null;
        if ($profile && (! empty($profile->country) || ! empty($profile->state) || ! empty($profile->city))) {
            $userLocation = [
                'country' => $profile->country ?: 'India',
                'state' => $profile->state ?: '',
                'city' => $profile->city ?: '',
            ];
        }

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
            'userLocation' => $userLocation,
        ]);
    }
}
