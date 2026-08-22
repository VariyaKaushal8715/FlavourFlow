<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function store(Request $request, Product $product, CartState $cart): JsonResponse|RedirectResponse
    {
        abort_unless($product->is_active && $product->quantity > 0, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'selected_options' => ['nullable', 'array'],
        ]);

        $cart->add($product, $validated['quantity'] ?? 1, $validated['selected_options'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $product->name.' was added to your cart.',
                'count' => $cart->count(),
                'subtotal' => number_format($cart->subtotal(), 2, '.', ''),
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function update(Request $request, Product $product, CartState $cart): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        abort_unless($cart->update($product, $validated['quantity']), 404);

        if ($request->expectsJson()) {
            $updatedItem = $cart->items()->first(fn ($item) => $item['product']->id === $product->id);
            $unitPrice = $updatedItem ? $updatedItem['unit_price'] : (float) $product->price;
            $lineTotal = $updatedItem ? $updatedItem['line_total'] : ($unitPrice * $validated['quantity']);

            return response()->json([
                'quantity' => $validated['quantity'],
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
                'subtotal' => number_format($cart->subtotal(), 2, '.', ''),
                'count' => $cart->count(),
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function destroy(Request $request, Product $product, CartState $cart): JsonResponse|RedirectResponse
    {
        $cart->remove($product);

        if ($request->expectsJson()) {
            return response()->json([
                'count' => $cart->count(),
                'subtotal' => number_format($cart->subtotal(), 2, '.', ''),
                'message' => $product->name.' was removed from your cart.',
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function clear(Request $request, CartState $cart): JsonResponse|RedirectResponse
    {
        $cart->clear();

        if ($request->expectsJson()) {
            return response()->json([
                'count' => 0,
                'subtotal' => '0.00',
                'message' => 'Cart has been cleared.',
            ]);
        }

        return redirect()->route('cart.index');
    }
}
