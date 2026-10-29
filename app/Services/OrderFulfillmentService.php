<?php

namespace App\Services;

use App\Mail\AdminNewOrderNotification;
use App\Mail\OrderPlaced;
use App\Mail\PaymentSuccessful;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderFulfillmentService
{
    public function __construct(
        protected CouponService $couponService,
        protected OrderOtpService $otpService
    ) {}

    /**
     * Atomically and idempotently fulfill a paid order.
     *
     * @param  array<string, mixed>  $paymentDetails
     */
    public function fulfill(Order $order, Payment $payment, ?string $gatewayPaymentId = null, array $paymentDetails = []): bool
    {
        return DB::transaction(function () use ($order, $payment, $gatewayPaymentId, $paymentDetails) {
            // Lock order row for update to prevent concurrent race condition fulfillment
            /** @var Order $lockedOrder */
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $lockedOrder) {
                return false;
            }

            // Update payment record
            $paymentUpdates = [
                'status' => 'captured',
                'paid_at' => now(),
            ];

            if ($gatewayPaymentId) {
                if ($payment->provider === 'demo' || $payment->is_demo) {
                    $paymentUpdates['transaction_id'] = $gatewayPaymentId;
                } elseif ($payment->provider === 'razorpay' || str_starts_with($gatewayPaymentId, 'pay_')) {
                    $paymentUpdates['razorpay_payment_id'] = $gatewayPaymentId;
                } else {
                    $paymentUpdates['cashfree_payment_id'] = $gatewayPaymentId;
                }
            }

            if (! empty($paymentDetails)) {
                $paymentUpdates['response_data'] = $paymentDetails;
            }

            $payment->update($paymentUpdates);

            // Idempotency check: if order is already Confirmed, do not re-decrement stock or re-send emails
            if ($lockedOrder->status === 'Confirmed') {
                return true;
            }

            // Confirm order
            $lockedOrder->update([
                'status' => 'Confirmed',
                'payment_status' => 'paid',
                'payment_verified_at' => now(),
                'confirmed_at' => now(),
            ]);

            // Decrement Inventory Stock using row locks
            foreach ($lockedOrder->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->decrement('quantity', min($product->quantity, $item->quantity));
                }
            }

            // Clear database cart for the user
            if ($lockedOrder->user_id) {
                CartItem::where('user_id', $lockedOrder->user_id)->delete();
            }

            // Generate reward coupon
            $this->couponService->generateRewardCouponForOrder($lockedOrder->refresh());

            // Dispatch notification emails
            try {
                $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', 'urbanzen17@gmail.com'));
                Mail::to($lockedOrder->email)->send(new OrderPlaced($lockedOrder));
                Mail::to($lockedOrder->email)->send(new PaymentSuccessful($lockedOrder));
                Mail::to($adminEmail)->send(new AdminNewOrderNotification($lockedOrder));

                // Dispatch order confirmation OTP (Strictly gated after verified payment)
                $this->otpService->generateAndSendOrderOtp($lockedOrder);
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch order fulfillment emails: '.$e->getMessage(), [
                    'order_id' => $lockedOrder->id,
                ]);
            }

            return true;
        });
    }
}
