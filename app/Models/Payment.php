<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_method',
        'provider',
        'transaction_id',
        'gateway_order_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'failure_reason',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
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
        return $this->status === 'successful';
    }

    public function methodDisplayName(): string
    {
        return match ($this->payment_method) {
            'card' => 'Credit / Debit Card',
            'netbanking' => 'Net Banking',
            'upi' => 'UPI Payment',
            'cod' => 'Cash on Delivery (COD)',
            default => strtoupper($this->payment_method),
        };
    }
}
