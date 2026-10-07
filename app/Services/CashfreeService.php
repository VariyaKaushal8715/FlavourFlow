<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Str;

class CashfreeService
{
    public function initiateRefund(Payment $payment, float $amount, string $reason): array
    {
        return [
            'success' => true,
            'refund_id' => 'refund_'.Str::random(16),
            'refund_status' => 'SUCCESS',
            'refund_amount' => $amount,
            'reason' => $reason,
        ];
    }
}
