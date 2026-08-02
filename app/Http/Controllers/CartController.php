<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartState $cart): View
    {
        return view('cart.index', [
            'site' => config('personal_site'),
            'items' => $cart->items(),
        ]);
    }

    public function summary(CartState $cart): JsonResponse
    {
        return response()->json(['count' => $cart->count()]);
    }

    public function store(Request $request, Product $product, CartState $cart): JsonResponse
    {
        abort_unless($product->is_active && $product->quantity > 0, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'selected_options' => ['nullable', 'array'],
        ]);

        $cart->add($product, $validated['quantity'] ?? 1, $validated['selected_options'] ?? null);

        return response()->json([
            'message' => $product->name.' was added to your cart.',
            'count' => $cart->count(),
        ]);
    }

    public function update(Request $request, Product $product, CartState $cart): JsonResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        abort_unless($cart->update($product, $validated['quantity']), 404);

        return response()->json([
            'quantity' => $validated['quantity'],
            'line_total' => number_format((float) $product->price * $validated['quantity'], 2, '.', ''),
            'subtotal' => number_format($cart->subtotal(), 2, '.', ''),
            'count' => $cart->count(),
        ]);
    }

    public function destroy(Product $product, CartState $cart): JsonResponse
    {
        $cart->remove($product);

        return response()->json([
            'count' => $cart->count(),
            'subtotal' => number_format($cart->subtotal(), 2, '.', ''),
        ]);
    }
}
