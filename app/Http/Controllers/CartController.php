<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->cart($request);
        $products = Product::query()->active()->whereIn('slug', array_keys($cart))->get()->keyBy('slug');
        $items = collect($cart)
            ->map(fn (array $item, string $slug): ?array => $products->has($slug) ? [
                'product' => $products->get($slug),
                'quantity' => $item['quantity'],
                'selected_options' => $item['selected_options'],
            ] : null)
            ->filter()
            ->values();

        return view('cart.index', ['site' => config('personal_site'), 'items' => $items]);
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json(['count' => $this->count($request)]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active && $product->quantity > 0, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'selected_options' => ['nullable', 'array'],
        ]);
        $cart = $this->cart($request);
        $cart[$product->slug] = [
            'quantity' => ($cart[$product->slug]['quantity'] ?? 0) + ($validated['quantity'] ?? 1),
            'selected_options' => $validated['selected_options'] ?? ($cart[$product->slug]['selected_options'] ?? null),
        ];
        $request->session()->put('cart', $cart);

        return response()->json([
            'message' => $product->name.' was added to your cart.',
            'count' => $this->count($request),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $cart = $this->cart($request);
        abort_unless(isset($cart[$product->slug]), 404);
        $cart[$product->slug]['quantity'] = $validated['quantity'];
        $request->session()->put('cart', $cart);

        return response()->json([
            'quantity' => $cart[$product->slug]['quantity'],
            'line_total' => number_format((float) $product->price * $cart[$product->slug]['quantity'], 2, '.', ''),
            'subtotal' => number_format($this->subtotal($request), 2, '.', ''),
            'count' => $this->count($request),
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $cart = $this->cart($request);
        unset($cart[$product->slug]);
        $request->session()->put('cart', $cart);

        return response()->json([
            'count' => $this->count($request),
            'subtotal' => number_format($this->subtotal($request), 2, '.', ''),
        ]);
    }

    private function subtotal(Request $request): float
    {
        $cart = $this->cart($request);

        return (float) Product::query()->whereIn('slug', array_keys($cart))->get()
            ->sum(fn (Product $product): float => (float) $product->price * $cart[$product->slug]['quantity']);
    }

    /** @return array<string, array{quantity: int, selected_options: array<string, mixed>|null}> */
    private function cart(Request $request): array
    {
        return $request->session()->get('cart', []);
    }

    private function count(Request $request): int
    {
        return array_sum(array_column($this->cart($request), 'quantity'));
    }
}
