<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayController extends Controller
{
    /**
     * Create a Razorpay Order for an existing application order.
     *
     * Called via AJAX after the checkout form is submitted and the
     * application order is created with payment_status = 'awaiting_payment'.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::where('id', $request->input('order_id'))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Only allow creating Razorpay order for online payment orders that are awaiting payment
        if ($order->payment_method !== 'online' || $order->payment_status !== 'awaiting_payment') {
            return response()->json(['error' => 'This order is not eligible for online payment.'], 422);
        }

        // Prevent creating a new Razorpay order if one already exists
        if ($order->razorpay_order_id) {
            return response()->json([
                'razorpay_order_id' => $order->razorpay_order_id,
                'amount' => (int) round($order->total * 100),
                'currency' => config('razorpay.currency', 'INR'),
                'key_id' => config('razorpay.key_id'),
                'order_id' => $order->id,
                'prefill' => [
                    'name' => $order->name,
                    'email' => $order->email,
                    'contact' => $order->mobile,
                ],
            ]);
        }

        $api = new Api(config('razorpay.key_id'), config('razorpay.key_secret'));

        $amountInPaise = (int) round($order->total * 100);

        try {
            $razorpayOrder = $api->order->create([
                'receipt' => $order->order_id,
                'amount' => $amountInPaise,
                'currency' => config('razorpay.currency', 'INR'),
                'notes' => [
                    'app_order_id' => $order->order_id,
                    'user_id' => $order->user_id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Unable to initiate payment. Please try again.'], 500);
        }

        // Store the Razorpay order ID
        $order->update(['razorpay_order_id' => $razorpayOrder->id]);

        return response()->json([
            'razorpay_order_id' => $razorpayOrder->id,
            'amount' => $amountInPaise,
            'currency' => config('razorpay.currency', 'INR'),
            'key_id' => config('razorpay.key_id'),
            'order_id' => $order->id,
            'prefill' => [
                'name' => $order->name,
                'email' => $order->email,
                'contact' => $order->mobile,
            ],
        ]);
    }

    /**
     * Verify Razorpay payment signature and mark order as paid.
     *
     * Called via AJAX after the Razorpay Checkout modal returns success.
     * The actual trust decision is based on server-side signature verification.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::where('id', $request->input('order_id'))
            ->where('user_id', $request->user()->id)
            ->where('razorpay_order_id', $request->input('razorpay_order_id'))
            ->firstOrFail();

        // Idempotency: if already paid, return success
        if ($order->payment_status === 'paid') {
            return response()->json(['success' => true, 'redirect' => route('checkout.success')]);
        }

        try {
            $api = new Api(config('razorpay.key_id'), config('razorpay.key_secret'));

            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->input('razorpay_order_id'),
                'razorpay_payment_id' => $request->input('razorpay_payment_id'),
                'razorpay_signature' => $request->input('razorpay_signature'),
            ]);
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay payment signature verification failed', [
                'order_id' => $order->order_id,
                'razorpay_payment_id' => $request->input('razorpay_payment_id'),
                'error' => $e->getMessage(),
            ]);

            $order->update(['payment_status' => 'failed']);

            return response()->json(['error' => 'Payment verification failed. Please try again or contact support.'], 422);
        }

        // Signature valid — mark as paid
        $order->update([
            'razorpay_payment_id' => $request->input('razorpay_payment_id'),
            'razorpay_signature' => $request->input('razorpay_signature'),
            'payment_status' => 'paid',
        ]);

        // Flash the order ID so the success page can pick it up
        session()->flash('placed_order_id', $order->id);

        return response()->json(['success' => true, 'redirect' => route('checkout.success')]);
    }

    /**
     * Handle payment failure reported by the frontend.
     *
     * This does NOT trust the frontend — it simply records the attempt.
     * The webhook will provide the authoritative status update.
     */
    public function handleFailure(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'error_description' => ['nullable', 'string', 'max:500'],
        ]);

        $order = Order::where('id', $request->input('order_id'))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Only update if still awaiting payment (don't overwrite a webhook-confirmed status)
        if ($order->payment_status === 'awaiting_payment') {
            $order->update(['payment_status' => 'failed']);
        }

        Log::info('Razorpay payment failed (frontend report)', [
            'order_id' => $order->order_id,
            'error' => $request->input('error_description'),
        ]);

        return response()->json(['success' => true, 'message' => 'Payment failure recorded.']);
    }

    /**
     * Razorpay Webhook handler for payment-status reconciliation.
     *
     * Verifies the webhook signature before processing.
     * Handles events idempotently to prevent duplicate processing.
     */
    public function webhook(Request $request): JsonResponse
    {
        $webhookSecret = config('razorpay.webhook_secret');

        if (empty($webhookSecret)) {
            Log::error('Razorpay webhook secret is not configured.');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $webhookBody = $request->getContent();
        $webhookSignature = $request->header('X-Razorpay-Signature', '');

        try {
            $api = new Api(config('razorpay.key_id'), config('razorpay.key_secret'));
            $api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $webhookSecret);
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = json_decode($webhookBody, true);
        $event = $payload['event'] ?? '';

        Log::info('Razorpay webhook received', ['event' => $event]);

        match ($event) {
            'payment.authorized', 'payment.captured' => $this->handlePaymentSuccess($payload),
            'payment.failed' => $this->handlePaymentWebhookFailure($payload),
            default => Log::info('Razorpay webhook event ignored', ['event' => $event]),
        };

        return response()->json(['status' => 'ok']);
    }

    /**
     * Process successful payment from webhook (authorized or captured).
     */
    private function handlePaymentSuccess(array $payload): void
    {
        $payment = $payload['payload']['payment']['entity'] ?? [];
        $razorpayOrderId = $payment['order_id'] ?? null;
        $razorpayPaymentId = $payment['id'] ?? null;

        if (! $razorpayOrderId || ! $razorpayPaymentId) {
            Log::warning('Razorpay webhook missing order_id or payment_id in payload.');

            return;
        }

        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $order) {
            Log::warning('Razorpay webhook: no matching order found', [
                'razorpay_order_id' => $razorpayOrderId,
            ]);

            return;
        }

        // Idempotency: skip if already paid
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'razorpay_payment_id' => $razorpayPaymentId,
            'payment_status' => 'paid',
        ]);

        Log::info('Razorpay webhook: order marked as paid', [
            'order_id' => $order->order_id,
            'razorpay_payment_id' => $razorpayPaymentId,
        ]);
    }

    /**
     * Process failed payment from webhook.
     */
    private function handlePaymentWebhookFailure(array $payload): void
    {
        $payment = $payload['payload']['payment']['entity'] ?? [];
        $razorpayOrderId = $payment['order_id'] ?? null;

        if (! $razorpayOrderId) {
            return;
        }

        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $order) {
            return;
        }

        // Only update if not already paid (webhook might arrive out of order)
        if ($order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed']);

            Log::info('Razorpay webhook: order payment marked as failed', [
                'order_id' => $order->order_id,
            ]);
        }
    }
}
