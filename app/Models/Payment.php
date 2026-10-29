<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = match ($payment->provider) {
                    'upi' => 'UPI-'.strtoupper(Str::random(12)),
                    'demo' => 'DEMO-TXN-'.strtoupper(Str::random(12)),
                    default => 'TXN-'.strtoupper(Str::random(12)),
                };
            }
        });
    }

    protected $fillable = [
        'order_id',
        'user_id',
        'cashfree_order_id',
        'cashfree_payment_id',
        'payment_session_id',
        'provider',
        'transaction_id',
        'gateway_order_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount',
        'currency',
        'status',
        'payment_method',
        'is_demo',
        'paid_at',
        'error_message',
        'failure_reason',
        'cashfree_refund_id',
        'razorpay_refund_id',
        'refund_status',
        'refunded_amount',
        'response_data',
        'payment_details',
        'verification_status',
        'verification_attempts',
        'verified_at',
        'customer_utr',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'is_demo' => 'boolean',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_attempts' => 'integer',
        'response_data' => 'array',
        'payment_details' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['successful', 'captured'], true);
    }

    public function isDemo(): bool
    {
        return (bool) $this->is_demo || $this->provider === 'demo';
    }

    public function methodDisplayName(): string
    {
        return match ($this->payment_method) {
            'card' => 'Credit / Debit Card',
            'netbanking' => 'Net Banking',
            'upi' => 'UPI Payment',
            'cod' => 'Cash on Delivery (COD)',
            'online' => 'Online Payment',
            'demo' => 'Demo Payment (Simulated - No Real Payment)',
            default => strtoupper((string) $this->payment_method),
        };
    }
}
