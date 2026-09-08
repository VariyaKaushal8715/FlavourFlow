<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'channel',
        'type',
        'recipient',
        'status',
        'error_message',
        'payload',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const CHANNEL_CUSTOMER_WHATSAPP = 'customer_whatsapp';
    public const CHANNEL_CUSTOMER_EMAIL = 'customer_email';
    public const CHANNEL_ADMIN_WHATSAPP = 'admin_whatsapp';
    public const CHANNEL_ADMIN_EMAIL = 'admin_email';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
