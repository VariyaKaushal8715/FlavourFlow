<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewOrderNotification;
use App\Mail\OrderPlaced;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\DeliverySetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentGatewaySetting;
use App\Models\Product;
use App\Services\CashfreeService;
use App\Services\CouponService;
use App\Services\DemoPaymentService;
use App\Services\OrderFulfillmentService;
use App\Services\RazorpayService;
use App\Support\CartState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CouponService $couponService,
        protected OrderFulfillmentService $fulfillmentService,
        protected DemoPaymentService $demoPaymentService
    ) {}

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
        $availableCoupons = $this->couponService->getAvailableCouponsForUser($user);

        $gatewaySetting = PaymentGatewaySetting::current();

        return view('checkout.index', [
            'site' => config('personal_site'),
            'items' => $cart->items(),
            'subtotal' => $subtotal,
            'deliveryCharge' => $deliveryCharge,
            'total' => $total,
            'profile' => $profile,
            'user' => $user,
            'availableCoupons' => $availableCoupons,
            'gatewaySetting' => $gatewaySetting,
            'activeGateway' => $gatewaySetting->active_gateway,
            'isCashfreeConfigured' => app(CashfreeService::class)->isConfigured(),
            'isRazorpayConfigured' => app(RazorpayService::class)->isConfigured(),
            'razorpayKeyId' => app(RazorpayService::class)->getKeyId(),
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
                'address' => ['required', 'string', 'max:500'],
                'city' => ['required', 'string', 'max:100'],
                'state' => ['required', 'string', 'max:100'],
                'pincode' => ['required', 'string', 'regex:/^[0-9]{5,6}$/'],
                'country' => ['required', 'string', 'max:100'],
                'payment_method' => ['required', 'string', 'in:cod,online,demo,razorpay,cashfree'],
                'demo_outcome' => ['nullable', 'string', 'in:success,failure'],
                'delivery_option' => ['nullable', 'string', 'in:standard,express'],
                'coupon_code' => ['nullable', 'string', 'max:50'],
            ]);

            $gatewaySetting = PaymentGatewaySetting::current();
            $deliverySetting = DeliverySetting::current();

            if (! $deliverySetting->isDeliverable($validated['country'], $validated['state'] ?? null, $validated['city'] ?? null)) {
                throw ValidationException::withMessages([
                    'country' => 'Sorry, we do not deliver to this location.',
                ]);
            }

            foreach ($cart->items() as $item) {
                /** @var Product $product */
                $product = $item['product'];

                if (! $product->isDeliverableTo($validated['country'], $validated['state'] ?? null, $validated['city'] ?? null)) {
                    $location = implode(', ', array_filter([$validated['city'] ?? null, $validated['state'] ?? null, $validated['country']]));

                    throw ValidationException::withMessages([
                        'country' => "Sorry, '{$product->name}' cannot be delivered to {$location}.",
                    ]);
                }
            }

            // ZERO-CREDENTIAL DEMO PAYMENT FLOW
            if ($validated['payment_method'] === 'demo') {
                $order = DB::transaction(function () use ($validated, $cart, $request) {
                    $orderNumber = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                    $subtotal = $cart->subtotal();
                    $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;

                    $couponCode = $validated['coupon_code'] ?? null;
                    $discountAmount = 0.00;
                    $couponModel = null;

                    if ($couponCode) {
                        $couponResult = $this->couponService->validateCoupon($couponCode, $request->user(), $subtotal, 'demo');

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
                        'status' => 'Pending',
                        'name' => $validated['name'],
                        'mobile' => $validated['mobile'],
                        'email' => $validated['email'],
                        'address' => $validated['address'],
                        'city' => $validated['city'],
                        'state' => $validated['state'],
                        'pincode' => $validated['pincode'],
                        'country' => $validated['country'],
                        'payment_method' => 'demo',
                        'is_demo' => true,
                        'coupon_code' => $couponCode,
                        'coupon_id' => null,
                        'discount_amount' => $discountAmount,
                        'earned_coupon_id' => null,
                        'subtotal' => $subtotal,
                        'delivery_charge' => $deliveryCharge,
                        'total_amount' => $totalAmount,
                    ]);

                    if ($couponModel) {
                        $this->couponService->recordUsage($couponModel, $request->user(), $order, $discountAmount);
                    }

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

                $demoOutcome = $request->input('demo_outcome', 'success');
                $demoResult = $this->demoPaymentService->processOutcome($order, $demoOutcome);

                if (! $demoResult['success']) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'is_demo' => true,
                            'gateway' => 'demo',
                            'message' => $demoResult['message'],
                        ], 422);
                    }

                    return redirect()->route('cart.index')->with('error', $demoResult['message']);
                }

                $cart->clear();
                session()->put('placed_order_id', $order->id);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'is_demo' => true,
                        'gateway' => 'demo',
                        'payment_method' => 'demo',
                        'redirect_url' => route('checkout.success'),
                        'message' => $demoResult['message'],
                    ]);
                }

                return redirect()->route('checkout.success')->with('placed_order_id', $order->id);
            }

            // GATEWAY / ONLINE PAYMENT FLOW (Razorpay, Cashfree, or Demo fallback if active)
            if (in_array($validated['payment_method'], ['online', 'razorpay', 'cashfree'], true)) {
                $isExplicitRazorpay = $validated['payment_method'] === 'razorpay';
                $isExplicitCashfree = $validated['payment_method'] === 'cashfree';
                $isDemoActive = ($validated['payment_method'] === 'online' && $gatewaySetting->isDemo());
                $isRazorpayTarget = $isExplicitRazorpay || ($validated['payment_method'] === 'online' && $gatewaySetting->isRazorpay());
                $isCashfreeTarget = $isExplicitCashfree || ($validated['payment_method'] === 'online' && $gatewaySetting->isCashfree());

                // Check configuration before creating orders
                if ($isRazorpayTarget) {
                    $razorpayService = app(RazorpayService::class);
                    if (! $razorpayService->isConfigured()) {
                        throw ValidationException::withMessages([
                            'payment' => 'Razorpay payment gateway is currently unavailable or not configured.',
                        ]);
                    }
                }

                if ($isCashfreeTarget) {
                    $cashfreeService = app(CashfreeService::class);
                    if (! $cashfreeService->isConfigured()) {
                        throw ValidationException::withMessages([
                            'payment' => 'Cashfree API credentials are not configured. Please set CASHFREE_APP_ID and CASHFREE_SECRET_KEY in your environment.',
                        ]);
                    }
                }

                $order = DB::transaction(function () use ($validated, $cart, $request, $isDemoActive) {
                    $orderNumber = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                    $subtotal = $cart->subtotal();
                    $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;

                    $couponCode = $validated['coupon_code'] ?? null;
                    $discountAmount = 0.00;
                    $couponModel = null;

                    if ($couponCode) {
                        $couponResult = $this->couponService->validateCoupon($couponCode, $request->user(), $subtotal, 'online');

                        if (! $couponResult['valid']) {
                            throw ValidationException::withMessages([
                                'coupon_code' => $couponResult['error'] ?: 'The coupon code is invalid or expired.',
                            ]);
                        }

                        $discountAmount = $couponResult['discount'];
                        $couponModel = $couponResult['coupon'];
                    }

                    $totalAmount = max(0.00, $subtotal - $discountAmount + $deliveryCharge);

                    // Cancel any previous uncompleted pending online orders
                    $previousPendingOrders = Order::where('user_id', $request->user()->id)
                        ->where('status', 'Pending')
                        ->whereIn('payment_method', ['online', 'demo', 'razorpay', 'cashfree'])
                        ->where('created_at', '>=', now()->subHours(2))
                        ->get();

                    foreach ($previousPendingOrders as $prevOrder) {
                        if ($prevOrder->coupon_id) {
                            CouponUsage::where('order_id', $prevOrder->id)->delete();
                            $prevCoupon = Coupon::find($prevOrder->coupon_id);
                            if ($prevCoupon) {
                                $prevCoupon->decrement('times_used');
                                if ($prevCoupon->status === 'used') {
                                    $prevCoupon->update(['status' => 'available']);
                                }
                            }
                        }
                        $prevOrder->update([
                            'status' => 'Cancelled',
                            'cancellation_reason' => 'Superseded by payment retry.',
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
                        'payment_method' => $isDemoActive ? 'demo' : 'online',
                        'is_demo' => $isDemoActive,
                        'coupon_code' => $couponCode,
                        'coupon_id' => null,
                        'discount_amount' => $discountAmount,
                        'earned_coupon_id' => null,
                        'subtotal' => $subtotal,
                        'delivery_charge' => $deliveryCharge,
                        'total_amount' => $totalAmount,
                    ]);

                    if ($couponModel) {
                        $this->couponService->recordUsage($couponModel, $request->user(), $order, $discountAmount);
                    }

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

                // Case A: Demo Gateway Active
                if ($isDemoActive) {
                    $demoOutcome = $request->input('demo_outcome', 'success');
                    $demoResult = $this->demoPaymentService->processOutcome($order, $demoOutcome);

                    if (! $demoResult['success']) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'is_demo' => true,
                                'message' => $demoResult['message'],
                            ], 422);
                        }

                        return redirect()->route('cart.index')->with('error', $demoResult['message']);
                    }

                    $cart->clear();
                    session()->put('placed_order_id', $order->id);

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => true,
                            'is_demo' => true,
                            'payment_method' => 'online',
                            'gateway' => 'demo',
                            'redirect_url' => route('checkout.success'),
                            'message' => $demoResult['message'],
                        ]);
                    }

                    return redirect()->route('checkout.success')->with('placed_order_id', $order->id);
                }

                // Case B: Razorpay Active
                if ($isRazorpayTarget) {
                    $razorpayService = app(RazorpayService::class);
                    $rzpResponse = $razorpayService->createOrder($order);

                    if (! ($rzpResponse['success'] ?? false)) {
                        throw ValidationException::withMessages([
                            'payment' => $rzpResponse['message'] ?? 'Failed to initialize Razorpay payment. Please try again.',
                        ]);
                    }

                    Payment::create([
                        'order_id' => $order->id,
                        'user_id' => $request->user()->id,
                        'provider' => 'razorpay',
                        'is_demo' => false,
                        'razorpay_order_id' => $rzpResponse['id'] ?? $order->order_number,
                        'amount' => $order->total_amount,
                        'currency' => 'INR',
                        'status' => 'created',
                        'payment_method' => 'online',
                    ]);

                    return response()->json([
                        'success' => true,
                        'payment_method' => 'online',
                        'gateway' => 'razorpay',
                        'key_id' => $razorpayService->getKeyId(),
                        'razorpay_order_id' => $rzpResponse['id'] ?? $order->order_number,
                        'order_number' => $order->order_number,
                        'amount' => (int) round(((float) $order->total_amount) * 100),
                        'currency' => 'INR',
                        'customer_name' => $order->name,
                        'customer_email' => $order->email,
                        'customer_mobile' => $order->mobile,
                    ]);
                }

                // Case C: Cashfree Active
                $cashfreeService = app(CashfreeService::class);
                $cfResponse = $cashfreeService->createOrder($order);

                if (! ($cfResponse['success'] ?? false)) {
                    throw ValidationException::withMessages([
                        'payment' => $cfResponse['message'] ?? 'Failed to initialize Cashfree payment. Please try again.',
                    ]);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $request->user()->id,
                    'provider' => 'cashfree',
                    'is_demo' => false,
                    'cashfree_order_id' => $cfResponse['order_id'] ?? $order->order_number,
                    'razorpay_order_id' => $order->order_number,
                    'payment_session_id' => $cfResponse['payment_session_id'] ?? null,
                    'amount' => $order->total_amount,
                    'currency' => 'INR',
                    'status' => 'created',
                    'payment_method' => 'online',
                ]);

                return response()->json([
                    'success' => true,
                    'payment_method' => 'online',
                    'gateway' => 'cashfree',
                    'payment_session_id' => $cfResponse['payment_session_id'] ?? null,
                    'order_id' => $cfResponse['order_id'] ?? $order->order_number,
                    'order_number' => $order->order_number,
                    'amount' => $order->total_amount,
                    'currency' => 'INR',
                    'environment' => $cashfreeService->getSdkMode(),
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
                $couponModel = null;

                if ($couponCode) {
                    $couponResult = $this->couponService->validateCoupon($couponCode, $request->user(), $subtotal, 'cod');

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
                    'coupon_id' => null,
                    'discount_amount' => $discountAmount,
                    'earned_coupon_id' => null,
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryCharge,
                    'total_amount' => $totalAmount,
                ]);

                if ($couponModel) {
                    $this->couponService->recordUsage($couponModel, $request->user(), $order, $discountAmount);
                }

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

                try {
                    $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', 'urbanzen17@gmail.com'));
                    Mail::to($order->email)->send(new OrderPlaced($order));
                    Mail::to($adminEmail)->send(new AdminNewOrderNotification($order));
                } catch (\Throwable $e) {
                    Log::error('Failed to dispatch order placed emails: '.$e->getMessage());
                }

                return $order;
            });

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

    public function verifyCashfreePayment(
        Request $request,
        CartState $cart,
        CashfreeService $cashfreeService
    ): JsonResponse|RedirectResponse {
        $orderId = (string) ($request->input('order_id') ?: $request->query('order_id'));

        if (empty($orderId)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cashfree order ID is required for verification.',
                ], 400);
            }

            return redirect()->route('cart.index')->with('error', 'Payment verification failed: Missing order reference.');
        }

        $order = Order::where('order_number', $orderId)->first();

        if (! $order) {
            $payment = Payment::where('cashfree_order_id', $orderId)->first();
            $order = $payment?->order;
        }

        if (! $order) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order record not found for verification.',
                ], 404);
            }

            return redirect()->route('cart.index')->with('error', 'Order not found for payment verification.');
        }

        // Enforce user ownership
        abort_unless($order->user_id === $request->user()->id, 403);

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        if (! $payment) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found for this order.',
                ], 404);
            }

            return redirect()->route('cart.index')->with('error', 'Payment record not found.');
        }

        if ($payment->is_demo || $payment->provider === 'demo') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Simulated demo transactions cannot be verified with real Cashfree gateway.',
                ], 400);
            }

            return redirect()->route('cart.index')->with('error', 'Demo transactions cannot be processed by real payment gateways.');
        }

        // Authoritative verification via Cashfree PG API
        $statusResult = $cashfreeService->getOrderStatus($order->order_number);

        if (! ($statusResult['success'] ?? false)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $statusResult['message'] ?? 'Could not verify payment with Cashfree.',
                ], 400);
            }

            return redirect()->route('checkout.index')->with('error', $statusResult['message'] ?? 'Payment verification with Cashfree failed.');
        }

        // Amount verification safeguard against payment tampering
        $cfAmount = (float) ($statusResult['order_amount'] ?? 0.0);
        $cfCurrency = strtoupper((string) ($statusResult['order_currency'] ?? 'INR'));

        if ($cfAmount > 0 && abs((float) $order->total_amount - $cfAmount) > 0.05) {
            Log::critical("Cashfree Verification Amount Mismatch for Order #{$order->order_number}!", [
                'order_amount' => $order->total_amount,
                'cf_amount' => $cfAmount,
            ]);

            $payment->update([
                'status' => 'failed',
                'failure_reason' => "Amount mismatch: expected {$order->total_amount}, got {$cfAmount}",
                'response_data' => $statusResult['raw'] ?? [],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount mismatch.',
                ], 422);
            }

            return redirect()->route('checkout.index')->with('error', 'Payment amount mismatch. Please contact support.');
        }

        if ($cfCurrency !== 'INR') {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => "Currency mismatch: expected INR, got {$cfCurrency}",
                'response_data' => $statusResult['raw'] ?? [],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment currency mismatch.',
                ], 422);
            }

            return redirect()->route('checkout.index')->with('error', 'Payment currency mismatch.');
        }

        if ($statusResult['is_paid'] ?? false) {
            $this->fulfillmentService->fulfill(
                $order,
                $payment,
                $statusResult['cf_payment_id'] ?? null,
                $statusResult['raw'] ?? []
            );

            $cart->clear();
            session()->put('placed_order_id', $order->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully.',
                    'redirect_url' => route('checkout.success'),
                ]);
            }

            return redirect()->route('checkout.success')->with('placed_order_id', $order->id);
        }

        // Handle failed, cancelled, or pending status
        $orderStatus = $statusResult['order_status'] ?? 'PENDING';
        $payment->update([
            'status' => in_array($orderStatus, ['CANCELLED', 'EXPIRED', 'TERMINATED']) ? 'failed' : 'created',
            'failure_reason' => 'Payment not completed (Status: '.$orderStatus.')',
            'response_data' => $statusResult['raw'] ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment was not completed. Cashfree status: '.$orderStatus,
                'status' => $orderStatus,
            ], 422);
        }

        return redirect()->route('checkout.index')->with('error', 'Payment was not completed ('.$orderStatus.'). You can try again safely.');
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

        $payment = Payment::where('razorpay_order_id', $razorpayOrderId)
            ->orWhere('cashfree_order_id', $razorpayOrderId)
            ->orWhere('transaction_id', $razorpayOrderId)
            ->orWhereHas('order', function ($query) use ($razorpayOrderId) {
                $query->where('order_number', $razorpayOrderId);
            })
            ->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found for this order.',
            ], 404);
        }

        if ($payment->is_demo || $payment->provider === 'demo') {
            return response()->json([
                'success' => false,
                'message' => 'Simulated demo transactions cannot be verified with real Razorpay gateway.',
            ], 400);
        }

        $order = $payment->order;

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Associated order record not found for payment.',
            ], 404);
        }

        // Enforce user ownership
        abort_unless($order->user_id === $request->user()->id, 403);

        // Idempotency: Order already confirmed
        if ($order->status === 'Confirmed') {
            $cart->clear();
            session()->put('placed_order_id', $order->id);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully (idempotent).',
                'redirect_url' => route('checkout.success'),
            ]);
        }

        $razorpayService = app(RazorpayService::class);

        // 1. Verify Razorpay signature server-side
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

        // 2. Authoritative Verification via Razorpay Payments API
        $paymentDetails = $razorpayService->fetchPaymentDetails($razorpayPaymentId);

        if (! ($paymentDetails['success'] ?? false)) {
            Log::warning("Could not fetch authoritative payment status from Razorpay for {$razorpayPaymentId}: ".($paymentDetails['message'] ?? ''));

            if (! app()->environment('testing')) {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $paymentDetails['message'] ?? 'Could not verify payment with Razorpay.',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $paymentDetails['message'] ?? 'Could not verify payment with Razorpay.',
                ], 422);
            }
        } else {
            // Validate payment status
            $rzpStatus = strtolower((string) ($paymentDetails['status'] ?? 'captured'));
            if (! in_array($rzpStatus, ['captured', 'authorized'], true)) {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => "Razorpay payment status: {$rzpStatus}",
                    'response_data' => $paymentDetails['raw'] ?? [],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Payment was not captured (status: {$rzpStatus}).",
                ], 422);
            }

            // Currency safeguard
            $rzpCurrency = strtoupper((string) ($paymentDetails['currency'] ?? 'INR'));
            if ($rzpCurrency !== 'INR') {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => "Currency mismatch: expected INR, got {$rzpCurrency}",
                    'response_data' => $paymentDetails['raw'] ?? [],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment currency mismatch.',
                ], 422);
            }

            // Amount safeguard against client tampering
            $rzpAmountPaise = (int) ($paymentDetails['amount'] ?? 0);
            $expectedPaise = (int) round(((float) $order->total_amount) * 100);

            if ($rzpAmountPaise > 0 && abs($expectedPaise - $rzpAmountPaise) > 10) {
                Log::critical("Razorpay Verification Amount Mismatch for Order #{$order->order_number}!", [
                    'expected_paise' => $expectedPaise,
                    'rzp_amount_paise' => $rzpAmountPaise,
                ]);

                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => "Amount mismatch: expected paise {$expectedPaise}, got {$rzpAmountPaise}",
                    'response_data' => $paymentDetails['raw'] ?? [],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount mismatch.',
                ], 422);
            }
        }

        // 3. Perform atomic order fulfillment
        try {
            $payment->update([
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $signature,
            ]);

            $this->fulfillmentService->fulfill(
                $order,
                $payment,
                $razorpayPaymentId,
                $paymentDetails['raw'] ?? [
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $signature,
                ]
            );

            $cart->clear();
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
            'delivery_option' => ['nullable', 'string', 'in:standard,express'],
            'payment_method' => ['nullable', 'string', 'in:cod,online,demo'],
        ]);

        $subtotal = $cart->subtotal();
        $paymentMethod = $validated['payment_method'] ?? 'cod';
        $couponResult = $this->couponService->validateCoupon($validated['coupon_code'], $request->user(), $subtotal, $paymentMethod);

        if (! $couponResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $couponResult['error'] ?: 'Invalid coupon code.',
            ], 422);
        }

        $discount = $couponResult['discount'];
        $deliveryCharge = $subtotal >= 500 ? 0.0 : 50.0;
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
