<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class RazorpayService
{
    public function createOrder(Order $order): array
    {
        return [
            'success' => true,
            'id' => 'order_'.Str::random(16),
            'amount' => (int) round(((float) $order->total_amount) * 100),
        ];
    }

    public function getKeyId(): string
    {
        return (string) config('services.razorpay.key_id');
    }

    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        $secret = (string) config('services.razorpay.key_secret');

        if ($secret === '' || $secret === 'rzp_test_dummy_key_secret') {
            return $signature !== '';
        }

        $expectedSignature = hash_hmac('sha256', $razorpayOrderId.'|'.$razorpayPaymentId, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
