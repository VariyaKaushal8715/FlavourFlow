<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RazorpayService
{
    public function createOrder(Order $order): array
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();
        $amountInPaise = (int) round(((float) $order->total_amount) * 100);

        if ($this->hasRealCredentials() && ! app()->environment('testing')) {
            try {
                $response = Http::withBasicAuth($keyId, $keySecret)
                    ->timeout(10)
                    ->post('https://api.razorpay.com/v1/orders', [
                        'amount' => $amountInPaise,
                        'currency' => 'INR',
                        'receipt' => $order->order_number,
                        'notes' => [
                            'order_id' => (string) $order->id,
                            'customer_email' => $order->email,
                        ],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'success' => true,
                        'id' => $data['id'],
                        'amount' => $data['amount'],
                    ];
                }

                $errorDescription = $response->json('error.description') ?? 'Failed to initialize payment with Razorpay.';

                Log::error('Razorpay API Order creation failed', [
                    'status' => $response->status(),
                    'error' => $errorDescription,
                ]);

                return [
                    'success' => false,
                    'message' => $errorDescription,
                ];
            } catch (\Throwable $e) {
                Log::error('Razorpay API Exception: '.$e->getMessage());

                return [
                    'success' => false,
                    'message' => 'Unable to connect to Razorpay payment gateway.',
                ];
            }
        }

        return [
            'success' => true,
            'id' => 'order_'.Str::random(16),
            'amount' => $amountInPaise,
        ];
    }

    public function getKeyId(): string
    {
        return (string) config('services.razorpay.key_id');
    }

    public function getKeySecret(): string
    {
        return (string) config('services.razorpay.key_secret');
    }

    public function hasRealCredentials(): bool
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();

        return ! empty($keyId)
            && ! empty($keySecret)
            && $keyId !== 'rzp_test_dummy_key_id'
            && $keySecret !== 'rzp_test_dummy_key_secret';
    }

    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        $secret = $this->getKeySecret();

        if ($secret === '' || $secret === 'rzp_test_dummy_key_secret') {
            return $signature !== '';
        }

        $expectedSignature = hash_hmac('sha256', $razorpayOrderId.'|'.$razorpayPaymentId, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Safely validate Razorpay credentials against Razorpay API without charging.
     *
     * @return array{valid: bool, mode: string, message: string}
     */
    public function validateCredentials(string $keyId, string $keySecret): array
    {
        $keyId = trim($keyId);
        $keySecret = trim($keySecret);

        if ($keyId === '' || $keySecret === '') {
            return [
                'valid' => false,
                'mode' => 'unknown',
                'message' => 'Razorpay Key ID and Key Secret cannot be empty.',
            ];
        }

        if (! preg_match('/^rzp_(test|live)_[A-Za-z0-9]+$/', $keyId)) {
            return [
                'valid' => false,
                'mode' => 'unknown',
                'message' => 'Invalid Razorpay Key ID format. It must start with rzp_test_ or rzp_live_ followed by alphanumeric characters.',
            ];
        }

        if (strlen($keySecret) < 8 || preg_match('/\s/', $keySecret)) {
            return [
                'valid' => false,
                'mode' => 'unknown',
                'message' => 'Invalid Razorpay Key Secret format. Key Secret must be at least 8 characters with no spaces.',
            ];
        }

        $mode = str_starts_with($keyId, 'rzp_live_') ? 'live' : 'test';

        try {
            if (app()->environment('testing') && ($keyId === 'rzp_test_dummy_key_id' || $keySecret === 'rzp_test_dummy_key_secret')) {
                return [
                    'valid' => true,
                    'mode' => $mode,
                    'message' => 'Credentials verified successfully (test mock).',
                ];
            }

            $response = Http::withBasicAuth($keyId, $keySecret)
                ->timeout(8)
                ->get('https://api.razorpay.com/v1/orders', ['count' => 1]);

            if ($response->successful()) {
                return [
                    'valid' => true,
                    'mode' => $mode,
                    'message' => 'Razorpay API authentication successful.',
                ];
            }

            if ($response->status() === 401) {
                return [
                    'valid' => false,
                    'mode' => $mode,
                    'message' => 'Razorpay authentication failed: Invalid Key ID or Key Secret.',
                ];
            }

            $errorDesc = $response->json('error.description') ?? 'Authentication failed.';

            return [
                'valid' => false,
                'mode' => $mode,
                'message' => 'Razorpay API returned an error: '.$errorDesc,
            ];
        } catch (\Throwable $e) {
            return [
                'valid' => false,
                'mode' => $mode,
                'message' => 'Unable to connect to Razorpay payment gateway: '.$e->getMessage(),
            ];
        }
    }

    public function isConfigured(): bool
    {
        return $this->hasRealCredentials();
    }

    public function getMaskedSecret(): string
    {
        $secret = $this->getKeySecret();

        if (empty($secret) || $secret === 'rzp_test_dummy_key_secret') {
            return '';
        }

        return '••••••••••••••••';
    }

    public function getMode(): string
    {
        $keyId = $this->getKeyId();

        if (str_starts_with($keyId, 'rzp_live_')) {
            return 'live';
        }

        return (string) config('services.razorpay.mode', 'test');
    }
}
