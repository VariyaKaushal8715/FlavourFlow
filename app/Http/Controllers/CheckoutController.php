<?php

namespace App\Http\Controllers;

use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        // Calculate checkout totals
        $subtotal = $cart->subtotal();
        $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;
        $total = $subtotal + $deliveryCharge;

        // Clear Cart
        $cart->clear();

        return redirect()->route('checkout.success')->with([
            'order_details' => [
                'name' => $validated['name'],
                'mobile' => $validated['mobile'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'pincode' => $validated['pincode'],
                'country' => $validated['country'],
                'payment_method' => $validated['payment_method'],
                'total' => $total,
            ],
        ]);
    }

    public function success(): View|RedirectResponse
    {
        $orderDetails = session('order_details');

        if (! $orderDetails) {
            return redirect()->route('home');
        }

        return view('checkout.success', [
            'site' => config('personal_site'),
            'order' => $orderDetails,
        ]);
    }
}
