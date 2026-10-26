<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CashfreeService;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CashfreeWebhookController extends Controller
{
    public function __construct(
        protected CashfreeService $cashfreeService,
        protected OrderFulfillmentService $fulfillmentService
    ) {}

    /**
     * Handle incoming Cashfree webhook events.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $timestamp = $request->header('x-webhook-timestamp');
        $signature = $request->header('x-webhook-signature');

        // 1. Signature Verification
        if (! $this->cashfreeService->verifyWebhookSignature($rawPayload, $timestamp, $signature)) {
            Log::warning('Cashfree Webhook rejected: Invalid signature', [
                'has_timestamp' => ! empty($timestamp),
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
        $eventType = strtoupper((string) ($payload['type'] ?? ''));
        $data = $payload['data'] ?? $payload;

        $orderId = (string) (
            $data['order']['order_id']
            ?? $data['order_id']
            ?? $payload['order_id']
            ?? ''
        );

        if (empty($orderId)) {
            Log::warning('Cashfree Webhook missing order ID', ['payload' => $payload]);

            return response()->json([
                'success' => false,
                'message' => 'Missing order reference in webhook payload.',
            ], 422);
        }

        // 3. Find Order & Associated Payment
        $order = Order::where('order_number', $orderId)->first();

        if (! $order) {
            $payment = Payment::where('cashfree_order_id', $orderId)->first();
            $order = $payment?->order;
        }

        if (! $order) {
            Log::warning("Cashfree Webhook: Order #{$orderId} not found in database.");

            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'cashfree',
                'cashfree_order_id' => $orderId,
                'amount' => $order->total_amount,
                'currency' => 'INR',
                'status' => 'created',
                'payment_method' => 'online',
            ]);
        }

        // 4. Handle Refund Events
        if (str_contains($eventType, 'REFUND') || isset($data['refund'])) {
            $refundData = $data['refund'] ?? $data;
            $refundStatus = strtoupper((string) ($refundData['refund_status'] ?? 'SUCCESS'));
            $refundId = (string) ($refundData['cf_refund_id'] ?? $refundData['refund_id'] ?? '');
            $refundAmount = (float) ($refundData['refund_amount'] ?? 0.0);

            $payment->update([
                'cashfree_refund_id' => $refundId ?: $payment->cashfree_refund_id,
                'refund_status' => $refundStatus,
                'refunded_amount' => $refundAmount > 0 ? $refundAmount : $payment->refunded_amount,
                'response_data' => array_merge((array) $payment->response_data, ['webhook_refund' => $payload]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund event processed.',
            ]);
        }

        // 5. Idempotency Check
        if ($order->status === 'Confirmed') {
            Log::info("Cashfree Webhook for Order #{$order->order_number} already confirmed (idempotent).");

            return response()->json([
                'success' => true,
                'message' => 'Webhook already processed (idempotent).',
            ]);
        }

        // 6. Payment Status Extraction & Amount Validation
        $paymentStatus = strtoupper((string) (
            $data['payment']['payment_status']
            ?? $data['payment_status']
            ?? ($eventType === 'PAYMENT_SUCCESS_WEBHOOK' ? 'SUCCESS' : '')
        ));

        $paidAmount = (float) (
            $data['payment']['payment_amount']
            ?? $data['order']['order_amount']
            ?? $data['order_amount']
            ?? 0.0
        );

        $currency = strtoupper((string) (
            $data['payment']['payment_currency']
            ?? $data['order']['order_currency']
            ?? 'INR'
        ));

        // Amount verification safeguard against payload tampering
        if ($paidAmount > 0 && abs((float) $order->total_amount - $paidAmount) > 0.05) {
            Log::critical("Cashfree Webhook Amount Mismatch for Order #{$order->order_number}!", [
                'order_amount' => $order->total_amount,
                'webhook_amount' => $paidAmount,
            ]);

            $payment->update([
                'status' => 'failed',
                'failure_reason' => "Amount mismatch: expected {$order->total_amount}, got {$paidAmount}",
                'response_data' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment amount mismatch.',
            ], 422);
        }

        // 7. Success Processing
        if ($paymentStatus === 'SUCCESS') {
            $cfPaymentId = (string) ($data['payment']['cf_payment_id'] ?? $data['cf_payment_id'] ?? '');

            $this->fulfillmentService->fulfill(
                $order,
                $payment,
                $cfPaymentId ?: null,
                $payload
            );

            Log::info("Cashfree Webhook successfully confirmed Order #{$order->order_number}.");

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and order fulfilled.',
            ]);
        }

        // 8. Failure Processing
        $failureReason = (string) (
            $data['payment']['payment_message']
            ?? $data['error_details']['error_description']
            ?? "Payment {$paymentStatus}"
        );

        $payment->update([
            'status' => 'failed',
            'failure_reason' => $failureReason,
            'response_data' => $payload,
        ]);

        Log::info("Cashfree Webhook recorded payment failure for Order #{$order->order_number}: {$failureReason}");

        return response()->json([
            'success' => true,
            'message' => 'Payment status recorded.',
        ]);
    }
}
