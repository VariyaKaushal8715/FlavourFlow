<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Handle incoming payment gateway webhooks.
     */
    public function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'event' => ['required', 'string'], // e.g. 'payment.captured', 'payment.success'
            'transaction_id' => ['required', 'string'],
        ]);

        $order = Order::where('order_number', $validated['order_number'])->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Only online payment orders receive reward coupons
        if ($order->payment_method !== 'online') {
            return response()->json([
                'success' => false,
                'message' => 'Reward coupons are not issued for COD orders.',
            ], 400);
        }

        // Idempotency: If order already confirmed & reward coupon already generated
        if ($order->earned_coupon_id !== null && $order->confirmed_at !== null) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook already processed (idempotent).',
                'earned_coupon_id' => $order->earned_coupon_id,
            ]);
        }

        if (! $order->confirmed_at) {
            $order->update([
                'status' => 'Confirmed',
                'confirmed_at' => now(),
            ]);
        }

        // Generate reward coupon
        $coupon = $this->couponService->generateRewardCouponForOrder($order);

        Log::info("Payment webhook confirmed for Order #{$order->order_number}. Reward coupon: ".($coupon ? $coupon->code : 'None'));

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed and reward coupon generated.',
            'earned_coupon' => $coupon ? [
                'code' => $coupon->code,
                'discount' => $coupon->formattedDiscount(),
                'expires_at' => $coupon->expires_at?->toIso8601String(),
            ] : null,
        ]);
    }
}
