<?php

namespace App\Http\Controllers;

use App\Models\DeliverySetting;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
        $deliveryCharge = ($subtotal < 300.00) ? 30.00 : 0.00;
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
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $order = DB::transaction(function () use ($validated, $cart, $request) {
                // Generate a unique Order Number
                $orderNumber = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                // Calculate checkout totals based on selected delivery option
                $subtotal = $cart->subtotal();
                $deliveryOption = $validated['delivery_option'] ?? 'standard';
                $deliveryCharge = $deliveryOption === 'express' ? 99.00 : ($subtotal < 300.00 ? 30.00 : 0.00);
                $deliveryDays = $deliveryOption === 'express' ? '1-2 days' : '4-5 days';

                $couponCode = $validated['coupon_code'] ?? null;
                $discountAmount = 0.00;

                if ($couponCode) {
                    $codeClean = Str::upper(str_replace(' ', '', $couponCode));
                    $coupon = Offer::whereNotNull('coupon_code')
                        ->get()
                        ->first(function ($offer) use ($codeClean) {
                            return Str::upper(str_replace(' ', '', $offer->coupon_code)) === $codeClean;
                        });

                    if ($coupon && $coupon->isValidFor($subtotal)) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                    } else {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'The coupon code is invalid, expired, or does not meet the requirements.',
                        ]);
                    }
                }

                $totalAmount = max(0.00, $subtotal - $discountAmount + $deliveryCharge);

                // Additional address validation using the new service
                $addressResult = \App\Services\AddressValidationService::validate($validated);
                if ($addressResult !== \App\Services\AddressValidationService::VALID) {
                    throw ValidationException::withMessages([
                        'address' => \App\Services\AddressValidationService::message($addressResult),
                    ]);
                }

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => $request->user()->id,
                    'status' => 'Pending',
                    'name' => $validated['name'],
                    'mobile' => $validated['mobile'],
                    'email' => $validated['email'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'pincode' => $validated['pincode'],
                    'country' => $validated['country'],
                    'payment_method' => $validated['payment_method'],
                    'delivery_option' => $deliveryOption,
                    'delivery_days' => $deliveryDays,
                    'coupon_code' => $couponCode,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryCharge,
                    'total_amount' => $totalAmount,
                ]);

                // Validate stock, decrement inventory, and save Order Items
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
                        'total_price' => $item['line_total'],
                    ]);
                }

                // Clear the Cart
                $cart->clear();

                return $order;
            });

            // Dispatch Order Confirmation Notifications (WhatsApp + Email for Customer and Admin)
            try {
                \App\Events\OrderPlaced::dispatch($order);
            } catch (\Throwable $notificationException) {
                \Illuminate\Support\Facades\Log::error('OrderPlaced event error: ' . $notificationException->getMessage());
            }

            return redirect()->route('checkout.success')->with('placed_order_id', $order->id);
        } catch (ValidationException $e) {
            if (isset($e->errors()['cart'])) {
                return redirect()->route('cart.index')->withErrors($e->errors());
            }

            return back()->withInput()->withErrors($e->errors());
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

    public function applyCoupon(Request $request, CartState $cart): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
            'delivery_option' => ['nullable', 'string', 'in:standard,express'],
        ]);

        $code = $validated['coupon_code'];
        $codeClean = Str::upper(str_replace(' ', '', $code));
        $coupon = Offer::whereNotNull('coupon_code')
            ->get()
            ->first(function ($offer) use ($codeClean) {
                return Str::upper(str_replace(' ', '', $offer->coupon_code)) === $codeClean;
            });

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code.',
            ], 422);
        }

        $subtotal = $cart->subtotal();
        $error = null;
        if (! $coupon->isValidFor($subtotal, $error)) {
            return response()->json([
                'success' => false,
                'message' => $error ?: 'This coupon is not valid.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        $deliveryOption = $validated['delivery_option'] ?? 'standard';
        $deliveryCharge = $deliveryOption === 'express' ? 99.00 : ($subtotal < 300.00 ? 30.00 : 0.00);
        $total = max(0.0, $subtotal - $discount + $deliveryCharge);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'code' => $coupon->coupon_code,
                'type' => $coupon->getDiscountType(),
                'value' => (float) $coupon->getDiscountValue(),
            ],
            'discount' => $discount,
            'subtotal' => $subtotal,
            'deliveryCharge' => $deliveryCharge,
            'total' => $total,
        ]);
    }
}
