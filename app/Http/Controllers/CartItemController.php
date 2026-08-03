<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function store(StoreCartItemRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $quantity = max(1, $request->integer('quantity', 1));

        $cartItem = CartItem::query()->firstOrNew([
            'user_id' => $request->user()->getAuthIdentifier(),
            'product_id' => $product->getKey(),
        ]);

        $cartItem->fill([
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'sku' => $product->sku,
            'category' => $product->categoryName(),
            'unit' => $product->unit,
            'quantity' => ($cartItem->exists ? $cartItem->quantity : 0) + $quantity,
            'unit_price' => $product->price,
            'image_path' => $product->image_path,
        ]);
        $cartItem->recalculateTotal();
        $cartItem->save();

        if ($request->boolean('buy_now')) {
            return redirect()->route('home')->with('status', $product->name.' was added to your cart. Proceed to checkout from your cart.');
        }

        return back()->with('status', $product->name.' was added to your cart.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        CartItem::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('product_id', $product->getKey())
            ->delete();

        return back()->with('status', $product->name.' was removed from your cart.');
    }
}
