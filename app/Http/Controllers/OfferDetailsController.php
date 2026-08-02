<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Support\WishlistState;
use Illuminate\Contracts\View\View;

class OfferDetailsController extends Controller
{
    public function __invoke(Offer $offer, WishlistState $wishlist): View
    {
        abort_unless($offer->isCurrentlyVisible(), 404);

        $suggestedProductCount = max($offer->suggestedProductCount(), 3);
        $suggestedProducts = Product::query()
            ->active()
            ->inStock()
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->orderByDesc('priority')
            ->limit($suggestedProductCount)
            ->get()
            ->map->toHighlightData()
            ->all();

        return view('offers.show', [
            'site' => config('personal_site'),
            'offer' => $offer,
            'suggestedProducts' => $suggestedProducts,
            'wishlistProductIds' => $wishlist->productIds(),
        ]);
    }
}
