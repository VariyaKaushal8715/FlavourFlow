<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Models\DeliverySetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\AddressValidationService;
use App\Services\CouponService;
use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

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
        $availableCoupons = $this->couponService->getAvailableCouponsForUser($user);

        return view('checkout.index', [
            'site' => config('personal_site'),
            'items' => $cart->items(),
            'subtotal' => $subtotal,
            'deliveryCharge' => $deliveryCharge,
            'total' => $total,
            'profile' => $profile,
            'user' => $user,
            'availableCoupons' => $availableCoupons,
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
            'delivery_option' => ['nullable', 'string', 'in:standard,express'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        // Validate global delivery location settings
        $deliverySetting = DeliverySetting::current();
        if (! $deliverySetting->isDeliverable($validated['country'], $validated['state'], $validated['city'])) {
            throw ValidationException::withMessages([
                'country' => 'Sorry, we don’t deliver to this location.',
            ]);
        }

        // Validate per-product deliverable locations
        foreach ($cart->items() as $item) {
            /** @var Product $itemProduct */
            $itemProduct = $item['product'];
            if (! $itemProduct->isDeliverableTo($validated['country'], $validated['state'], $validated['city'])) {
                $locationStr = implode(', ', array_filter([$validated['city'], $validated['state'], $validated['country']]));
                throw ValidationException::withMessages([
                    'country' => "Sorry, '{$itemProduct->name}' cannot be delivered to {$locationStr}.",
                ]);
            }
        }

        // Additional address validation using the address validation service
        $addressResult = AddressValidationService::validate($validated);
        if ($addressResult !== AddressValidationService::VALID) {
            throw ValidationException::withMessages([
                'address' => AddressValidationService::message($addressResult),
            ]);
        }

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
                $couponModel = null;

                if ($couponCode) {
                    $couponResult = $this->couponService->validateCoupon(
                        $couponCode,
                        $request->user(),
                        $subtotal,
                        $validated['payment_method']
                    );

                    if (! $couponResult['valid']) {
                        throw ValidationException::withMessages([
                            'coupon_code' => $couponResult['error'] ?: 'The coupon code is invalid or expired.',
                        ]);
                    }

                    $discountAmount = $couponResult['discount'];
                    $couponModel = $couponResult['coupon'];
                }

                $totalAmount = max(0.00, $subtotal - $discountAmount + $deliveryCharge);

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => $request->user()->id,
                    'status' => $validated['payment_method'] === 'online' ? 'Confirmed' : 'Pending',
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
                    'confirmed_at' => $validated['payment_method'] === 'online' ? now() : null,
                ]);

                // Record coupon usage if applied
                if ($couponModel) {
                    $this->couponService->recordUsage($couponModel, $request->user(), $order, $discountAmount);
                }

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

                // Generate automatic reward coupon on confirmed online payment
                if ($order->payment_method === 'online') {
                    $this->couponService->generateRewardCouponForOrder($order);
                }

                // Clear the Cart
                $cart->clear();

                return $order;
            });

            // Dispatch Order Confirmation Notifications (WhatsApp + Email for Customer and Admin)
            try {
                OrderPlaced::dispatch($order);
            } catch (\Throwable $notificationException) {
                Log::error('OrderPlaced event error: '.$notificationException->getMessage());
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

        $order = Order::with(['items', 'earnedCoupon'])->findOrFail($orderId);

        return view('checkout.success', [
            'site' => config('personal_site'),
            'order' => $order,
            'earnedCoupon' => $order->earnedCoupon,
        ]);
    }

    public function applyCoupon(Request $request, CartState $cart): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
            'delivery_option' => ['nullable', 'string', 'in:standard,express'],
            'payment_method' => ['nullable', 'string', 'in:cod,online'],
        ]);

        $subtotal = $cart->subtotal();
        $paymentMethod = $validated['payment_method'] ?? 'cod';
        $couponResult = $this->couponService->validateCoupon(
            $validated['coupon_code'],
            $request->user(),
            $subtotal,
            $paymentMethod
        );

        if (! $couponResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $couponResult['error'] ?: 'Invalid coupon code.',
            ], 422);
        }

        $discount = $couponResult['discount'];
        $deliveryOption = $validated['delivery_option'] ?? 'standard';
        $deliveryCharge = $deliveryOption === 'express' ? 99.00 : ($subtotal < 300.00 ? 30.00 : 0.00);
        $total = max(0.0, $subtotal - $discount + $deliveryCharge);

        $coupon = $couponResult['coupon'];
        $code = $couponResult['type'] === 'coupon' ? $coupon->code : $coupon->coupon_code;
        $type = $couponResult['type'] === 'coupon' ? $coupon->discount_type : $coupon->getDiscountType();
        $value = $couponResult['type'] === 'coupon' ? (float) $coupon->discount_value : (float) $coupon->getDiscountValue();
        $paymentEligibility = $couponResult['type'] === 'coupon' ? $coupon->payment_method_eligibility : 'both';

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'formatted' => $couponResult['type'] === 'coupon' ? $coupon->formattedDiscount() : $coupon->discount_label,
                'payment_method_eligibility' => $paymentEligibility,
            ],
            'discount' => $discount,
            'subtotal' => $subtotal,
            'deliveryCharge' => $deliveryCharge,
            'total' => $total,
        ]);
    }
}
