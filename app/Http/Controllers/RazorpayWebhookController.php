<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderFulfillmentService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayService $razorpayService,
        protected OrderFulfillmentService $fulfillmentService
    ) {}

    /**
     * Handle incoming Razorpay webhook events.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('x-razorpay-signature');

        // 1. Signature Verification
        if (! $this->razorpayService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('Razorpay Webhook rejected: Invalid signature', [
                'has_signature' => ! empty($signature),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 400);
        }

        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON payload.',
            ], 400);
        }

        // 2. Extract Event Data
        $event = (string) ($payload['event'] ?? '');
        $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
        $orderEntity = $payload['payload']['order']['entity'] ?? [];
        $refundEntity = $payload['payload']['refund']['entity'] ?? [];

        $razorpayOrderId = (string) ($paymentEntity['order_id'] ?? $orderEntity['id'] ?? '');
        $receipt = (string) ($orderEntity['receipt'] ?? $paymentEntity['notes']['order_number'] ?? '');
        $notesOrderId = (string) ($paymentEntity['notes']['order_id'] ?? $orderEntity['notes']['order_id'] ?? '');

        // 3. Locate Order
        $order = null;
        if (! empty($receipt)) {
            $order = Order::where('order_number', $receipt)->first();
        }

        if (! $order && ! empty($razorpayOrderId)) {
            $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();
            $order = $payment?->order;
        }

        if (! $order && ! empty($notesOrderId)) {
            $order = Order::find($notesOrderId);
        }

        if (! $order) {
            Log::warning('Razorpay Webhook: Order not found in database', [
                'razorpay_order_id' => $razorpayOrderId,
                'receipt' => $receipt,
                'notes_order_id' => $notesOrderId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // 4. Demo Isolation Safeguard
        if ($order->is_demo) {
            Log::warning("Razorpay Webhook rejected demo order #{$order->order_number}");

            return response()->json([
                'success' => false,
                'message' => 'Simulated demo orders cannot be processed by real Razorpay webhook.',
            ], 400);
        }

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'razorpay',
                'is_demo' => false,
                'razorpay_order_id' => $razorpayOrderId ?: $order->order_number,
                'amount' => $order->total_amount,
                'currency' => 'INR',
                'status' => 'created',
                'payment_method' => 'online',
            ]);
        }

        // 5. Handle Refund Events
        if (str_starts_with($event, 'refund.') || ! empty($refundEntity)) {
            $refundId = (string) ($refundEntity['id'] ?? '');
            $refundStatus = strtoupper((string) ($refundEntity['status'] ?? 'PROCESSED'));
            $refundAmount = isset($refundEntity['amount']) ? ((float) $refundEntity['amount']) / 100 : 0.0;

            $payment->update([
                'razorpay_refund_id' => $refundId ?: $payment->razorpay_refund_id,
                'refund_status' => $refundStatus,
                'refunded_amount' => $refundAmount > 0 ? $refundAmount : $payment->refunded_amount,
                'response_data' => array_merge((array) $payment->response_data, ['webhook_refund' => $payload]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund event processed.',
            ]);
        }

        // 6. Idempotency Check
        if ($order->status === 'Confirmed') {
            Log::info("Razorpay Webhook for Order #{$order->order_number} already confirmed (idempotent).");

            return response()->json([
                'success' => true,
                'message' => 'Webhook already processed (idempotent).',
            ]);
        }

        // 7. Amount & Currency Validation Safeguard
        $paidPaise = (int) ($paymentEntity['amount'] ?? $orderEntity['amount'] ?? 0);
        $expectedPaise = (int) round(((float) $order->total_amount) * 100);
        $currency = strtoupper((string) ($paymentEntity['currency'] ?? $orderEntity['currency'] ?? 'INR'));

        if ($currency !== 'INR') {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => "Currency mismatch: expected INR, got {$currency}",
                'response_data' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment currency mismatch.',
            ], 422);
        }

        if ($paidPaise > 0 && abs($expectedPaise - $paidPaise) > 10) {
            Log::critical("Razorpay Webhook Amount Mismatch for Order #{$order->order_number}!", [
                'expected_paise' => $expectedPaise,
                'paid_paise' => $paidPaise,
            ]);

            $payment->update([
                'status' => 'failed',
                'failure_reason' => "Amount mismatch: expected paise {$expectedPaise}, got {$paidPaise}",
                'response_data' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment amount mismatch.',
            ], 422);
        }

        // 8. Success Event Processing
        if (in_array($event, ['payment.captured', 'order.paid'], true)
            || (isset($paymentEntity['status']) && strtolower((string) $paymentEntity['status']) === 'captured')) {

            $razorpayPaymentId = (string) ($paymentEntity['id'] ?? '');

            $payment->update([
                'razorpay_payment_id' => $razorpayPaymentId ?: $payment->razorpay_payment_id,
                'razorpay_order_id' => $razorpayOrderId ?: $payment->razorpay_order_id,
            ]);

            $this->fulfillmentService->fulfill(
                $order,
                $payment,
                $razorpayPaymentId ?: null,
                $payload
            );

            Log::info("Razorpay Webhook successfully confirmed Order #{$order->order_number}.");

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and order fulfilled.',
            ]);
        }

        // 9. Payment Failure Event Processing
        if ($event === 'payment.failed') {
            $failureReason = (string) (
                $paymentEntity['error_description']
                ?? $paymentEntity['error_reason']
                ?? 'Payment failed on Razorpay.'
            );

            $payment->update([
                'status' => 'failed',
                'failure_reason' => $failureReason,
                'response_data' => $payload,
            ]);

            Log::info("Razorpay Webhook recorded payment failure for Order #{$order->order_number}: {$failureReason}");

            return response()->json([
                'success' => true,
                'message' => 'Payment failure recorded.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event acknowledged.',
        ]);
    }
}
