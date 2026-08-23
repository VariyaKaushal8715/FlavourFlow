<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(Request $request, CartState $cart): View|RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user = $request->user();
        $profile = $user ? $user->profile()->first() : null;

        $subtotal = $cart->subtotal();
        $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;
        $total = $subtotal + $deliveryCharge;

        return view('checkout.index', [
            'site' => config('personal_site'),
            'items' => $cart->items(),
            'subtotal' => $subtotal,
            'deliveryCharge' => $deliveryCharge,
            'total' => $total,
            'profile' => $profile,
            'user' => $user,
        ]);
    }

    public function store(Request $request, CartState $cart): RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^[0-9+\s-]{10,15}$/'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{5,6}$/'],
            'country' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:cod,online'],
        ]);

        try {
            $order = DB::transaction(function () use ($validated, $cart, $request) {
                // Generate a unique Order ID
                $orderId = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6));

                // 1. Create the Order
                $subtotal = $cart->subtotal();
                $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;
                $total = $subtotal + $deliveryCharge;

                $order = Order::create([
                    'order_id' => $orderId,
                    'user_id' => $request->user()->id,
                    'status' => 'Confirmed',
                    'name' => $validated['name'],
                    'mobile' => $validated['mobile'],
                    'email' => $validated['email'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'pincode' => $validated['pincode'],
                    'country' => $validated['country'],
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryCharge,
                    'total' => $total,
                ]);

                // 2. Validate stock, decrement inventory, and save Order Items
                foreach ($cart->items() as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product']->id);

                    if ($product->quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'cart' => "The product '{$product->name}' only has {$product->quantity} units left in stock.",
                        ]);
                    }

                    // Reduce product inventory
                    $product->decrement('quantity', $item['quantity']);

                    // Save Order Item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_slug' => $product->slug,
                        'sku' => $product->sku,
                        'unit' => $item['unit'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['line_total'],
                    ]);
                }

                // 3. Clear the Cart
                $cart->clear();

                return $order;
            });

            return redirect()->route('checkout.success')->with('placed_order_id', $order->id);
        } catch (ValidationException $e) {
            return redirect()->route('cart.index')->withErrors($e->errors());
        }
    }

    public function success(): View|RedirectResponse
    {
        $orderId = session('placed_order_id');

        if (! $orderId) {
            return redirect()->route('home');
        }

        $order = Order::with('items')->findOrFail($orderId);

        return view('checkout.success', [
            'site' => config('personal_site'),
            'order' => $order,
        ]);
    }
}
