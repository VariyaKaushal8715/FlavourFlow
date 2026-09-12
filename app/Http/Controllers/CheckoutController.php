<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\RazorpayService;
use App\Services\WhatsAppService;
use App\Support\CartState;
use App\Support\GujaratLocation;
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

    public function store(Request $request, CartState $cart): RedirectResponse|JsonResponse
    {
        try {
            if ($cart->count() === 0) {
                return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string', 'regex:/^[0-9+\s-]{10,15}$/'],
                'email' => ['required', 'email', 'max:255'],
                'address' => [
                    'required', 'string', 'max:500',
                    function ($attribute, $value, $fail) {
                        if (! GujaratLocation::isValidAddress($value)) {
                            $fail('Only Gujarat addresses are supported.');
                        }
                    },
                ],
                'city' => [
                    'required', 'string', 'max:100',
                    function ($attribute, $value, $fail) {
                        if (! GujaratLocation::isGujaratCity($value)) {
                            $fail('Only Gujarat cities are supported.');
                        }
                    },
                ],
                'state' => [
                    'required', 'string', 'max:100',
                    function ($attribute, $value, $fail) {
                        if (! GujaratLocation::isGujaratState($value)) {
                            $fail('Only Gujarat state is supported.');
                        }
                    },
                ],
                'pincode' => ['required', 'string', 'regex:/^[0-9]{5,6}$/'],
                'country' => ['required', 'string', 'max:100'],
                'payment_method' => ['required', 'string', 'in:cod,online'],
                'coupon_code' => ['nullable', 'string', 'max:50'],
            ]);

            $razorpayService = app(RazorpayService::class);

            if ($validated['payment_method'] === 'online') {
                // For online payment: Create Order in Pending status without reducing stock or clearing cart yet
                $order = DB::transaction(function () use ($validated, $cart, $request) {
                    $orderNumber = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                    $subtotal = $cart->subtotal();
                    $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;

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
                        'payment_method' => 'online',
                        'coupon_code' => $couponCode,
                        'discount_amount' => $discountAmount,
                        'subtotal' => $subtotal,
                        'delivery_charge' => $deliveryCharge,
                        'total_amount' => $totalAmount,
                    ]);

                    // Save Order Items without decrementing stock yet
                    foreach ($cart->items() as $item) {
                        $product = Product::findOrFail($item['product']->id);

                        if ($product->quantity < $item['quantity']) {
                            throw ValidationException::withMessages([
                                'cart' => "The product '{$product->name}' only has {$product->quantity} units left in stock.",
                            ]);
                        }

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

                    return $order;
                });

                // Create Razorpay Order via API
                $rzpResponse = $razorpayService->createOrder($order);

                if (! ($rzpResponse['success'] ?? false)) {
                    throw ValidationException::withMessages([
                        'payment' => $rzpResponse['message'] ?? 'Failed to initialize Razorpay payment. Please try again.',
                    ]);
                }

                // Create initial Payment record in DB
                Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $request->user()->id,
                    'razorpay_order_id' => $rzpResponse['id'],
                    'amount' => $order->total_amount,
                    'currency' => 'INR',
                    'status' => 'created',
                    'payment_method' => 'online',
                ]);

                return response()->json([
                    'success' => true,
                    'payment_method' => 'online',
                    'key_id' => $razorpayService->getKeyId(),
                    'razorpay_order_id' => $rzpResponse['id'],
                    'amount' => $rzpResponse['amount'],
                    'currency' => 'INR',
                    'order_number' => $order->order_number,
                    'customer_name' => $order->name,
                    'customer_email' => $order->email,
                    'customer_mobile' => $order->mobile,
                ]);
            }

            // COD Payment Flow
            $order = DB::transaction(function () use ($validated, $cart, $request) {
                $orderNumber = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                $subtotal = $cart->subtotal();
                $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;

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
                    'payment_method' => 'cod',
                    'coupon_code' => $couponCode,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryCharge,
                    'total_amount' => $totalAmount,
                ]);

                foreach ($cart->items() as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product']->id);

                    if ($product->quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'cart' => "The product '{$product->name}' only has {$product->quantity} units left in stock.",
                        ]);
                    }

                    $product->decrement('quantity', $item['quantity']);

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

                $cart->clear();

                return $order;
            });

            // Trigger Notifications for COD Order
            $this->sendOrderNotifications($order);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'payment_method' => 'cod',
                    'redirect_url' => route('checkout.success'),
                ]);
            }

            session()->put('placed_order_id', $order->id);

            return redirect()->route('checkout.success')->with('placed_order_id', $order->id);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }

            return redirect()->route('cart.index')->withErrors($e->errors());
        }
    }

    private function sendOrderNotifications(Order $order): void
    {
        // Trigger Queued WhatsApp notifications
        $waService = app(WhatsAppService::class);

        // 1. Customer Notification: Order Placed
        $customerPhone = $order->mobile ?? $order->user?->profile?->mobile_number;
        if ($customerPhone) {
            $customerMsg = "Hi {$order->name}, your order #{$order->order_number} for Rs. {$order->total_amount} has been successfully placed at FlavourFlow! We are processing it now.";
            $waService->dispatchNotification(
                recipientPhone: $customerPhone,
                event: 'order_placed',
                parameters: [$order->name, $order->order_number, "Rs. {$order->total_amount}"],
                fallbackMessage: $customerMsg,
                recipientType: 'customer'
            );
        }

        // 2. Admin Notification: New Order Received
        $adminPhones = WhatsAppService::getAdminPhoneNumbers();
        $adminMsg = "🛍️ *New Order Received!*\nOrder #: {$order->order_number}\nCustomer: {$order->name}\nAmount: Rs. {$order->total_amount}\nPayment: ".strtoupper($order->payment_method);
        foreach ($adminPhones as $adminPhone) {
            $waService->dispatchNotification(
                recipientPhone: $adminPhone,
                event: 'new_order_received',
                parameters: [$order->order_number, $order->name, "Rs. {$order->total_amount}", strtoupper($order->payment_method)],
                fallbackMessage: $adminMsg,
                recipientType: 'admin'
            );
        }

        // 3. Stock Level Alerts for items in order
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product) {
                if ($product->quantity === 0) {
                    $stockMsg = "⚠️ *Out of Stock Alert!*\nProduct: {$product->name} (SKU: {$product->sku}) is now OUT OF STOCK.";
                    foreach ($adminPhones as $adminPhone) {
                        $waService->dispatchNotification(
                            recipientPhone: $adminPhone,
                            event: 'out_of_stock',
                            parameters: [$product->name, $product->sku ?? 'N/A'],
                            fallbackMessage: $stockMsg,
                            recipientType: 'admin'
                        );
                    }
                } elseif ($product->isLowStock()) {
                    $stockMsg = "⚠️ *Low Stock Alert!*\nProduct: {$product->name} (SKU: {$product->sku}) has only {$product->quantity} units left.";
                    foreach ($adminPhones as $adminPhone) {
                        $waService->dispatchNotification(
                            recipientPhone: $adminPhone,
                            event: 'low_stock',
                            parameters: [$product->name, $product->sku ?? 'N/A', (string) $product->quantity],
                            fallbackMessage: $stockMsg,
                            recipientType: 'admin'
                        );
                    }
                }
            }
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

    public function verifyRazorpayPayment(Request $request, CartState $cart): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $razorpayOrderId = $validated['razorpay_order_id'];
        $razorpayPaymentId = $validated['razorpay_payment_id'];
        $signature = $validated['razorpay_signature'];

        $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found for this order.',
            ], 404);
        }

        $order = $payment->order;
        $razorpayService = app(RazorpayService::class);

        // Verify Razorpay signature server-side
        $isValidSignature = $razorpayService->verifySignature($razorpayOrderId, $razorpayPaymentId, $signature);

        if (! $isValidSignature) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => 'Invalid Razorpay payment signature.',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Razorpay payment signature verification failed.',
            ], 400);
        }

        // Perform atomic completion: Payment -> Captured, Order -> Confirmed, Stock -> Decremented, Cart -> Cleared
        try {
            DB::transaction(function () use ($order, $payment, $razorpayPaymentId, $signature, $cart) {
                // Update Payment Record
                $payment->update([
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $signature,
                    'status' => 'captured',
                    'paid_at' => now(),
                ]);

                // Update Order Status if not already confirmed
                if ($order->status !== 'Confirmed') {
                    $order->update([
                        'status' => 'Confirmed',
                        'confirmed_at' => now(),
                    ]);

                    // Decrement Inventory Stock
                    foreach ($order->items as $item) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $product->decrement('quantity', min($product->quantity, $item->quantity));
                        }
                    }
                }

                // Clear Cart
                $cart->clear();
            });

            // Dispatch Notifications
            $this->sendOrderNotifications($order);

            session()->put('placed_order_id', $order->id);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully.',
                'redirect_url' => route('checkout.success'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay Verification Completion Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment completion: '.$e->getMessage(),
            ], 500);
        }
    }

    public function applyCoupon(Request $request, CartState $cart): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
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
        $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;
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
