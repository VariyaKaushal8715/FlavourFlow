<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishlistItemRequest;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistItemController extends Controller
{
    public function store(StoreWishlistItemRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        WishlistItem::query()->updateOrCreate(
            [
                'user_id' => $request->user()->getAuthIdentifier(),
                'product_id' => $product->getKey(),
            ],
            [
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'sku' => $product->sku,
                'category' => $product->categoryName(),
                'unit' => $product->unit,
                'unit_price' => $product->price,
                'image_path' => $product->image_path,
            ],
        );

        return back()->with('status', $product->name.' was added to your wishlist.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        WishlistItem::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('product_id', $product->getKey())
            ->delete();

        return back()->with('status', $product->name.' was removed from your wishlist.');
    }
}
