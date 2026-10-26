<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewaySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashfreeService
{
    /**
     * Get Cashfree App ID from database settings or configuration.
     */
    public function getAppId(): string
    {
        try {
            $val = PaymentGatewaySetting::current()->getEffectiveCashfreeAppId();
            if ($val !== '') {
                return $val;
            }
        } catch (\Throwable) {
            // DB table unmigrated or unavailable in isolated unit tests
        }

        return trim((string) config('services.cashfree.app_id', ''));
    }

    /**
     * Get Cashfree Secret Key from database settings or configuration.
     */
    public function getSecretKey(): string
    {
        try {
            $val = PaymentGatewaySetting::current()->getEffectiveCashfreeSecretKey();
            if ($val !== '') {
                return $val;
            }
        } catch (\Throwable) {
            // DB table unmigrated or unavailable in isolated unit tests
        }

        return trim((string) config('services.cashfree.secret_key', ''));
    }

    /**
     * Get configured Cashfree API Version.
     */
    public function getApiVersion(): string
    {
        try {
            $val = PaymentGatewaySetting::current()->cashfree_api_version;
            if (! empty($val)) {
                return trim((string) $val);
            }
        } catch (\Throwable) {
            // DB table unmigrated or unavailable in isolated unit tests
        }

        return trim((string) config('services.cashfree.api_version', '2023-08-01'));
    }

    /**
     * Get configured Cashfree Environment (TEST / SANDBOX / PRODUCTION).
     */
    public function getEnvironment(): string
    {
        try {
            return PaymentGatewaySetting::current()->getEffectiveCashfreeEnvironment();
        } catch (\Throwable) {
            // DB table unmigrated or unavailable in isolated unit tests
        }

        return strtoupper((string) config('services.cashfree.env', 'TEST'));
    }

    /**
     * Check whether Cashfree is in Sandbox / Test mode.
     * Enforces Sandbox mode as the default for safety.
     */
    public function isSandbox(): bool
    {
        $env = $this->getEnvironment();

        return in_array($env, ['TEST', 'SANDBOX'], true) || ($env !== 'PRODUCTION' && $env !== 'LIVE');
    }

    /**
     * Get the base Cashfree Payment Gateway API URL.
     */
    public function getBaseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.cashfree.com/pg'
            : 'https://api.cashfree.com/pg';
    }

    /**
     * Get SDK mode string expected by Cashfree Web JS SDK v3.
     */
    public function getSdkMode(): string
    {
        return $this->isSandbox() ? 'sandbox' : 'production';
    }

    /**
     * Check whether real Cashfree credentials are configured.
     */
    public function isConfigured(): bool
    {
        $appId = $this->getAppId();
        $secretKey = $this->getSecretKey();

        return ! empty($appId) && ! empty($secretKey);
    }

    /**
     * Get masked secret key for admin display.
     */
    public function getMaskedSecret(): string
    {
        try {
            return PaymentGatewaySetting::current()->getMaskedCashfreeSecret();
        } catch (\Throwable) {
            $secret = trim((string) config('services.cashfree.secret_key', ''));

            return empty($secret) ? '' : '••••••••••••••••';
        }
    }

    /**
     * Common request headers required by Cashfree PG API.
     *
     * @return array<string, string>
     */
    public function getHeaders(?string $appId = null, ?string $secretKey = null): array
    {
        return [
            'x-client-id' => $appId ?? $this->getAppId(),
            'x-client-secret' => $secretKey ?? $this->getSecretKey(),
            'x-api-version' => $this->getApiVersion(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create an order on Cashfree and obtain a payment session ID.
     *
     * @return array{
     *     success: bool,
     *     payment_session_id?: string,
     *     cf_order_id?: string,
     *     order_id?: string,
     *     order_amount?: float,
     *     order_currency?: string,
     *     environment?: string,
     *     message?: string
     * }
     */
    public function createOrder(Order $order): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Cashfree API credentials are not configured. Please set CASHFREE_APP_ID and CASHFREE_SECRET_KEY in your environment.',
            ];
        }

        $amount = (float) number_format((float) $order->total_amount, 2, '.', '');
        $orderId = $order->order_number;

        // Clean mobile number to 10-15 digits
        $phone = preg_replace('/[^0-9]/', '', (string) $order->mobile);
        if (strlen($phone) < 10) {
            $phone = '9999999999';
        } elseif (strlen($phone) > 15) {
            $phone = substr($phone, -10);
        }

        $customerDetails = [
            'customer_id' => 'user_'.($order->user_id ?? $order->id),
            'customer_name' => substr((string) ($order->name ?: 'Customer'), 0, 100),
            'customer_email' => $order->email ?: 'customer@example.com',
            'customer_phone' => $phone,
        ];

        $returnUrl = (string) (config('services.cashfree.return_url') ?: route('checkout.cashfree.verify').'?order_id={order_id}');
        $notifyUrl = (string) (config('services.cashfree.notify_url') ?: url('/api/webhooks/cashfree'));

        $payload = [
            'order_id' => $orderId,
            'order_amount' => $amount,
            'order_currency' => 'INR',
            'customer_details' => $customerDetails,
            'order_meta' => [
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl,
                'payment_methods' => 'cc,dc,upi,nb,app',
            ],
            'order_note' => "Order #{$order->order_number}",
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->post($this->getBaseUrl().'/orders', $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'payment_session_id' => $data['payment_session_id'] ?? null,
                    'cf_order_id' => (string) ($data['cf_order_id'] ?? ''),
                    'order_id' => (string) ($data['order_id'] ?? $orderId),
                    'order_amount' => (float) ($data['order_amount'] ?? $amount),
                    'order_currency' => (string) ($data['order_currency'] ?? 'INR'),
                    'environment' => $this->getSdkMode(),
                ];
            }

            $errorMessage = $response->json('message')
                ?? $response->json('error.message')
                ?? 'Failed to initialize Cashfree payment order.';

            Log::error('Cashfree Create Order Error', [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('Cashfree Order Creation Exception: '.$e->getMessage(), [
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to connect to Cashfree payment gateway.',
            ];
        }
    }

    /**
     * Authoritatively fetch order status and payment details from Cashfree API.
     *
     * @return array{
     *     success: bool,
     *     order_id?: string,
     *     cf_order_id?: string,
     *     order_status?: string,
     *     is_paid?: bool,
     *     order_amount?: float,
     *     order_currency?: string,
     *     cf_payment_id?: string,
     *     payment_method?: string,
     *     payment_time?: string,
     *     raw?: array,
     *     message?: string
     * }
     */
    public function getOrderStatus(string $cashfreeOrderId): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Cashfree API credentials are not configured.',
            ];
        }

        try {
            $url = $this->getBaseUrl().'/orders/'.rawurlencode($cashfreeOrderId);
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Cashfree Get Order Status Failed', [
                    'order_id' => $cashfreeOrderId,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Could not retrieve order details from Cashfree.',
                ];
            }

            $orderData = $response->json();
            $orderStatus = strtoupper((string) ($orderData['order_status'] ?? 'UNKNOWN'));
            $isPaid = ($orderStatus === 'PAID');

            $cfPaymentId = null;
            $paymentMethod = null;
            $paymentTime = null;

            // Fetch payments array for this order to extract cf_payment_id
            try {
                $paymentsRes = Http::withHeaders($this->getHeaders())
                    ->timeout(10)
                    ->get($this->getBaseUrl().'/orders/'.rawurlencode($cashfreeOrderId).'/payments');

                if ($paymentsRes->successful() && is_array($paymentsRes->json())) {
                    foreach ($paymentsRes->json() as $paymentItem) {
                        if (strtoupper((string) ($paymentItem['payment_status'] ?? '')) === 'SUCCESS') {
                            $cfPaymentId = (string) ($paymentItem['cf_payment_id'] ?? '');
                            $paymentMethod = (string) ($paymentItem['payment_group'] ?? 'online');
                            $paymentTime = (string) ($paymentItem['payment_time'] ?? '');
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::notice('Cashfree Payments sub-query note: '.$e->getMessage());
            }

            return [
                'success' => true,
                'order_id' => (string) ($orderData['order_id'] ?? $cashfreeOrderId),
                'cf_order_id' => (string) ($orderData['cf_order_id'] ?? ''),
                'order_status' => $orderStatus,
                'is_paid' => $isPaid,
                'order_amount' => (float) ($orderData['order_amount'] ?? 0.0),
                'order_currency' => (string) ($orderData['order_currency'] ?? 'INR'),
                'cf_payment_id' => $cfPaymentId,
                'payment_method' => $paymentMethod,
                'payment_time' => $paymentTime,
                'raw' => $orderData,
            ];
        } catch (\Throwable $e) {
            Log::error('Cashfree Order Status Exception: '.$e->getMessage(), [
                'order_id' => $cashfreeOrderId,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to verify order with Cashfree: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify Cashfree webhook signature using exact raw payload and timestamp.
     * Procedure: base64(hmac_sha256(timestamp + rawBody, secretKey))
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $timestamp, ?string $signature): bool
    {
        $secretKey = $this->getSecretKey();

        if (empty($secretKey) || empty($signature) || empty($timestamp)) {
            return false;
        }

        $data = $timestamp.$rawPayload;
        $expectedSignature = base64_encode(hash_hmac('sha256', $data, $secretKey, true));

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Initiate genuine refund via Cashfree API for an existing payment.
     *
     * @return array{
     *     success: bool,
     *     refund_id?: string,
     *     refund_status?: string,
     *     refund_amount?: float,
     *     reason?: string,
     *     message?: string
     * }
     */
    public function initiateRefund(Payment $payment, float $amount, string $reason): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Cashfree API credentials are not configured.',
            ];
        }

        $order = $payment->order;
        $orderId = $payment->cashfree_order_id ?: ($order?->order_number ?: '');

        if (empty($orderId)) {
            return [
                'success' => false,
                'message' => 'Cashfree order ID missing on payment record.',
            ];
        }

        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Refund amount must be greater than zero.',
            ];
        }

        // Verify refund does not exceed refundable balance
        $paidAmount = (float) $payment->amount;
        $alreadyRefunded = (float) ($payment->refunded_amount ?? 0.0);
        $maxRefundable = max(0.0, $paidAmount - $alreadyRefunded);

        if ($amount > ($maxRefundable + 0.01)) {
            return [
                'success' => false,
                'message' => "Refund amount of ₹{$amount} exceeds available refundable balance of ₹{$maxRefundable}.",
            ];
        }

        $refundId = 'ref_'.Str::random(16);
        $payload = [
            'refund_amount' => round($amount, 2),
            'refund_id' => $refundId,
            'refund_note' => Str::limit($reason ?: 'Refund request processed', 100),
            'refund_speed' => 'STANDARD',
        ];

        try {
            $url = $this->getBaseUrl().'/orders/'.rawurlencode($orderId).'/refunds';
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'refund_id' => (string) ($data['cf_refund_id'] ?? $data['refund_id'] ?? $refundId),
                    'refund_status' => strtoupper((string) ($data['refund_status'] ?? 'SUCCESS')),
                    'refund_amount' => (float) ($data['refund_amount'] ?? $amount),
                    'reason' => $reason,
                    'raw' => $data,
                ];
            }

            $errorMessage = $response->json('message') ?? 'Cashfree refund request was declined.';
            Log::error('Cashfree Refund API Error', [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('Cashfree Refund Exception: '.$e->getMessage(), [
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to connect to Cashfree refund service.',
            ];
        }
    }

    /**
     * Validate Cashfree credentials without modifying data or charging.
     *
     * @return array{valid: bool, mode: string, message: string}
     */
    public function validateCredentials(string $appId, string $secretKey, string $env = 'TEST'): array
    {
        $appId = trim($appId);
        $secretKey = trim($secretKey);
        $env = strtoupper(trim($env));

        if ($appId === '' || $secretKey === '') {
            return [
                'valid' => false,
                'mode' => 'sandbox',
                'message' => 'Cashfree App ID and Secret Key cannot be empty.',
            ];
        }

        $isSandbox = in_array($env, ['TEST', 'SANDBOX'], true) || ($env !== 'PRODUCTION' && $env !== 'LIVE');
        $baseUrl = $isSandbox ? 'https://sandbox.cashfree.com/pg' : 'https://api.cashfree.com/pg';
        $mode = $isSandbox ? 'sandbox' : 'production';

        try {
            $headers = [
                'x-client-id' => $appId,
                'x-client-secret' => $secretKey,
                'x-api-version' => $this->getApiVersion(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            // Make a read-only request to verify credentials
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($baseUrl.'/orders', ['limit' => 1]);

            if ($response->successful()) {
                return [
                    'valid' => true,
                    'mode' => $mode,
                    'message' => 'Cashfree API authentication successful.',
                ];
            }

            if ($response->status() === 401) {
                return [
                    'valid' => false,
                    'mode' => $mode,
                    'message' => 'Cashfree authentication failed: Invalid App ID or Secret Key.',
                ];
            }

            $errorDesc = $response->json('message') ?? 'Authentication failed with status '.$response->status();

            return [
                'valid' => false,
                'mode' => $mode,
                'message' => 'Cashfree API returned an error: '.$errorDesc,
            ];
        } catch (\Throwable $e) {
            return [
                'valid' => false,
                'mode' => $mode,
                'message' => 'Unable to connect to Cashfree payment gateway: '.$e->getMessage(),
            ];
        }
    }
}
