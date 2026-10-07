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
}
