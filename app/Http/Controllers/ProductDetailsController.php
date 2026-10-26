<?php

namespace App\Http\Controllers;

use App\AI\Contracts\AiRecommendationEngineInterface;
use App\Models\Product;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    public function __invoke(Request $request, Product $product, WishlistState $wishlist, AiRecommendationEngineInterface $recEngine): View
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

        // Generate context-aware recommendations (complementary spices & frequent pairings)
        $recResult = $recEngine->getCustomerRecommendations(
            userId: $user?->id,
            sessionId: $request->hasSession() ? $request->session()->getId() : null,
            type: 'complementary',
            limit: 3,
            contextOptions: ['current_product' => $product]
        );

        $recProductIds = array_column($recResult->products, 'product_id');
        $relatedProductsCollection = Product::query()
            ->active()
            ->inStock()
            ->whereIn('id', $recProductIds)
            ->get();

        $idPositions = array_flip($recProductIds);
        $relatedProducts = $relatedProductsCollection
            ->sortBy(fn ($p) => $idPositions[(string) $p->id] ?? 999)
            ->map->toHighlightData()
            ->values()
            ->all();

        // Safe fallback if catalog has limited items
        if (empty($relatedProducts)) {
            $relatedProducts = Product::query()
                ->active()
                ->inStock()
                ->whereKeyNot($product->getKey())
                ->where('category', $product->category)
                ->orderByDesc('priority')
                ->limit(3)
                ->get()
                ->map->toHighlightData()
                ->all();
        }

        return view('products.show', [
            'site' => config('personal_site'),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'wishlistProductIds' => $wishlist->productIds(),
            'userLocation' => $userLocation,
        ]);
    }
}
