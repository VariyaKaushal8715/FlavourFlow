<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewaySetting;
use Illuminate\Support\Facades\Log;

class UpiPaymentVerificationService
{
    public const STATUS_VERIFIED_SUCCESS = 'VERIFIED_SUCCESS';

    public const STATUS_PENDING_VERIFICATION = 'PENDING_VERIFICATION';

    public const STATUS_VERIFICATION_FAILED = 'VERIFICATION_FAILED';

    public const STATUS_REFERENCE_MISMATCH = 'REFERENCE_MISMATCH';

    public const STATUS_VERIFICATION_UNAVAILABLE = 'VERIFICATION_UNAVAILABLE';

    public function __construct(
        protected RazorpayService $razorpayService,
        protected CashfreeService $cashfreeService,
        protected OrderFulfillmentService $fulfillmentService
    ) {}

    /**
     * Verify a customer-submitted UPI payment transaction reference.
     *
     * @return array{
     *     status: string,
     *     message: string,
     *     verified: bool,
     *     data?: array<string, mixed>
     * }
     */
    public function verifyPayment(Order $order, Payment $payment, string $transactionRef): array
    {
        $ref = trim($transactionRef);

        // 1. Sanitize & validate reference format
        if (empty($ref) || strlen($ref) < 6 || strlen($ref) > 100 || ! preg_match('/^[a-zA-Z0-9_\-]+$/', $ref)) {
            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Invalid transaction reference format. Please provide a valid 12-digit UPI UTR or Payment ID.',
                'verified' => false,
            ];
        }

        // 2. Check Order state
        if ($order->status === 'Confirmed' && $order->payment_status === 'paid') {
            return [
                'status' => self::STATUS_VERIFIED_SUCCESS,
                'message' => 'This order has already been verified and confirmed.',
                'verified' => true,
            ];
        }

        if (in_array($order->status, ['Cancelled', 'cancelled', 'Refunded', 'refunded'], true)) {
            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'This order is cancelled or refunded and cannot accept payment verification.',
                'verified' => false,
            ];
        }

        // 3. Brute Force Protection: Limit verification attempts per payment
        if ($payment->verification_attempts >= 10) {
            Log::warning('Payment verification throttled: maximum attempts exceeded', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'attempts' => $payment->verification_attempts,
            ]);

            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Maximum verification attempts exceeded for this payment. Please contact store support with your payment receipt.',
                'verified' => false,
            ];
        }

        // 4. Prevent duplicate reference consumption across different orders
        $alreadyUsedCaptured = Payment::where(function ($query) use ($ref) {
            $query->where('transaction_id', $ref)
                ->orWhere('customer_utr', $ref)
                ->orWhere('razorpay_payment_id', $ref)
                ->orWhere('cashfree_payment_id', $ref);
        })
            ->where('id', '!=', $payment->id)
            ->whereIn('status', ['captured', 'successful'])
            ->exists();

        if ($alreadyUsedCaptured) {
            Log::warning('Payment verification rejected: transaction reference already consumed by another order', [
                'reference' => $ref,
                'order_id' => $order->id,
            ]);

            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'This payment reference has already been used for another order. Each transaction can only be used once.',
                'verified' => false,
            ];
        }

        // Also prevent claiming a customer_utr that is already pending review on another order
        $alreadyPendingOther = Payment::where('customer_utr', $ref)
            ->where('id', '!=', $payment->id)
            ->where('order_id', '!=', $order->id)
            ->exists();

        if ($alreadyPendingOther) {
            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'This transaction reference is already pending review on another order.',
                'verified' => false,
            ];
        }

        // Record attempt
        $payment->increment('verification_attempts');
        $payment->customer_utr = $ref;
        if (empty($payment->transaction_id) || str_starts_with($payment->transaction_id, 'UPI-')) {
            $payment->transaction_id = $ref;
        }
        $details = $payment->payment_details ?? [];
        $details['last_verification_attempt_at'] = now()->toIso8601String();
        $details['customer_acknowledged_at'] = now()->toIso8601String();
        $details['customer_transaction_ref'] = $ref;

        $gatewaySetting = PaymentGatewaySetting::current();

        // 5. Verification Strategy:
        // A. Demo Mode Isolation & Protection:
        // Predictable demo tokens (e.g. DEMO-SUCCESS) can NEVER authorize live orders.
        // They are strictly rejected if:
        // 1. In production environment (app()->environment('production'))
        // 2. OR the gateway is not in Demo mode and the order was not created as a demo order
        $isDemoRef = str_starts_with($ref, 'DEMO-');
        $isProduction = app()->environment('production');
        $isDemoContext = ($gatewaySetting->isDemo() || (bool) $payment->is_demo || (bool) $order->is_demo);

        if ($isDemoRef && ($isProduction || ! $isDemoContext)) {
            Log::warning('Security rejection: demo payment token submitted outside demo mode or in production', [
                'order_id' => $order->id,
                'reference' => $ref,
                'is_production' => $isProduction,
                'is_demo_context' => $isDemoContext,
            ]);

            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Simulated demo tokens cannot be used to confirm real orders.',
                'verified' => false,
            ];
        }

        if ($isDemoContext && ! $isProduction) {
            return $this->verifyDemoPayment($order, $payment, $ref, $details);
        }

        // B. Razorpay Route (Payment ID format: pay_...)
        if (str_starts_with($ref, 'pay_') || $gatewaySetting->isRazorpay()) {
            if ($this->razorpayService->isConfigured()) {
                return $this->verifyRazorpayPayment($order, $payment, $ref, $details);
            }
        }

        // C. Cashfree Route
        if (str_starts_with($ref, 'cf_') || $gatewaySetting->isCashfree()) {
            if ($this->cashfreeService->isConfigured()) {
                return $this->verifyCashfreePayment($order, $payment, $ref, $details);
            }
        }

        // D. Direct Merchant VPA Route (No automated gateway API credentials configured)
        // Safely record details for store manual verification without confirming order or dispatching OTP
        $payment->verification_status = 'unavailable';
        $payment->payment_details = $details;
        $payment->save();

        Log::info('Automated verification API unavailable for direct VPA: reference stored for manual verification', [
            'order_id' => $order->id,
            'reference' => $ref,
        ]);

        return [
            'status' => self::STATUS_VERIFICATION_UNAVAILABLE,
            'message' => 'Your payment reference has been recorded. Direct VPA automated API verification is not currently active for this merchant account. Your order remains Pending and our store team will manually confirm receipt before dispatch.',
            'verified' => false,
        ];
    }

    /**
     * Verify payment through Razorpay API.
     *
     * @param  array<string, mixed>  $details
     * @return array{status: string, message: string, verified: bool}
     */
    protected function verifyRazorpayPayment(Order $order, Payment $payment, string $ref, array $details): array
    {
        $response = $this->razorpayService->fetchPayment($ref);

        if (! ($response['success'] ?? false)) {
            $payment->verification_status = 'failed';
            $details['verification_error'] = $response['message'] ?? 'Payment ID not found on Razorpay.';
            $payment->payment_details = $details;
            $payment->save();

            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Could not verify payment with Razorpay: '.($response['message'] ?? 'Transaction not found.'),
                'verified' => false,
            ];
        }

        $expectedPaise = (int) round(((float) $order->total_amount) * 100);
        $actualPaise = (int) ($response['amount'] ?? 0);
        $currency = strtoupper((string) ($response['currency'] ?? 'INR'));
        $rzpStatus = strtolower((string) ($response['status'] ?? ''));

        // Amount & Currency Validation
        if ($actualPaise !== $expectedPaise || $currency !== 'INR') {
            $payment->verification_status = 'mismatch';
            $details['mismatch_details'] = [
                'expected_paise' => $expectedPaise,
                'actual_paise' => $actualPaise,
                'currency' => $currency,
            ];
            $payment->payment_details = $details;
            $payment->save();

            Log::warning('Razorpay UPI reference mismatch', [
                'order_id' => $order->id,
                'expected' => $expectedPaise,
                'actual' => $actualPaise,
            ]);

            return [
                'status' => self::STATUS_REFERENCE_MISMATCH,
                'message' => 'Transaction amount or currency mismatch. Expected ₹'.number_format($order->total_amount, 2).', but payment record shows ₹'.number_format($actualPaise / 100, 2).'.',
                'verified' => false,
            ];
        }

        // Captured status confirmation
        if ($rzpStatus === 'captured') {
            $payment->verification_status = 'verified';
            $payment->verified_at = now();
            $payment->razorpay_payment_id = $ref;
            $details['verified_provider'] = 'razorpay';
            $payment->payment_details = $details;
            $payment->save();

            // Atomically fulfill order and dispatch OTP
            $this->fulfillmentService->fulfill($order, $payment, $ref, $response);

            return [
                'status' => self::STATUS_VERIFIED_SUCCESS,
                'message' => 'Payment successfully verified! Your order is confirmed and an OTP confirmation code has been emailed to you.',
                'verified' => true,
            ];
        }

        if (in_array($rzpStatus, ['created', 'authorized'], true)) {
            $payment->verification_status = 'pending';
            $payment->payment_details = $details;
            $payment->save();

            return [
                'status' => self::STATUS_PENDING_VERIFICATION,
                'message' => 'Payment is currently processing by the bank. Please allow 1-2 minutes and check again.',
                'verified' => false,
            ];
        }

        $payment->verification_status = 'failed';
        $payment->payment_details = $details;
        $payment->save();

        return [
            'status' => self::STATUS_VERIFICATION_FAILED,
            'message' => 'Payment verification failed: The transaction status reported by Razorpay is "'.$rzpStatus.'".',
            'verified' => false,
        ];
    }

    /**
     * Verify payment through Cashfree API.
     *
     * @param  array<string, mixed>  $details
     * @return array{status: string, message: string, verified: bool}
     */
    protected function verifyCashfreePayment(Order $order, Payment $payment, string $ref, array $details): array
    {
        $cfOrderId = $payment->cashfree_order_id ?: $order->order_number;
        $response = $this->cashfreeService->getOrderStatus($cfOrderId);

        if (! ($response['success'] ?? false)) {
            $payment->verification_status = 'failed';
            $payment->payment_details = $details;
            $payment->save();

            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Cashfree order lookup failed: '.($response['message'] ?? 'Order not found.'),
                'verified' => false,
            ];
        }

        if ($response['is_paid'] ?? false) {
            $payment->verification_status = 'verified';
            $payment->verified_at = now();
            $payment->cashfree_payment_id = $response['cf_payment_id'] ?? $ref;
            $details['verified_provider'] = 'cashfree';
            $payment->payment_details = $details;
            $payment->save();

            $this->fulfillmentService->fulfill($order, $payment, $response['cf_payment_id'] ?? $ref, $response);

            return [
                'status' => self::STATUS_VERIFIED_SUCCESS,
                'message' => 'Payment successfully verified! Your order is confirmed and your confirmation OTP has been emailed.',
                'verified' => true,
            ];
        }

        $payment->verification_status = 'pending';
        $payment->payment_details = $details;
        $payment->save();

        return [
            'status' => self::STATUS_PENDING_VERIFICATION,
            'message' => 'Cashfree payment is pending settlement. Please retry in a few moments.',
            'verified' => false,
        ];
    }

    /**
     * Verify payment in Demo Mode.
     *
     * @param  array<string, mixed>  $details
     * @return array{status: string, message: string, verified: bool}
     */
    protected function verifyDemoPayment(Order $order, Payment $payment, string $ref, array $details): array
    {
        if (app()->environment('production')) {
            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Simulated demo transactions are strictly forbidden in production environments.',
                'verified' => false,
            ];
        }

        $payment->is_demo = true;
        $order->update(['is_demo' => true]);
        if (str_contains($ref, 'FAIL')) {
            $payment->verification_status = 'failed';
            $payment->payment_details = $details;
            $payment->save();

            return [
                'status' => self::STATUS_VERIFICATION_FAILED,
                'message' => 'Simulated verification failed as requested by demo transaction reference.',
                'verified' => false,
            ];
        }

        if (str_contains($ref, 'MISMATCH')) {
            $payment->verification_status = 'mismatch';
            $payment->payment_details = $details;
            $payment->save();

            return [
                'status' => self::STATUS_REFERENCE_MISMATCH,
                'message' => 'Simulated reference mismatch error: Amount does not match expected total.',
                'verified' => false,
            ];
        }

        if (str_contains($ref, 'PENDING')) {
            $payment->verification_status = 'pending';
            $payment->payment_details = $details;
            $payment->save();

            return [
                'status' => self::STATUS_PENDING_VERIFICATION,
                'message' => 'Simulated verification pending: Transfer is awaiting banking settlement.',
                'verified' => false,
            ];
        }

        // Demo Success outcome
        $payment->verification_status = 'verified';
        $payment->verified_at = now();
        $details['verified_provider'] = 'demo';
        $payment->payment_details = $details;
        $payment->save();

        $this->fulfillmentService->fulfill($order, $payment, $ref, ['simulated' => true]);

        return [
            'status' => self::STATUS_VERIFIED_SUCCESS,
            'message' => 'Simulated UPI payment verified successfully! Order confirmed and confirmation OTP dispatched.',
            'verified' => true,
        ];
    }
}
