<?php

namespace App\Models;

use App\Services\EmailNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'reason',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (RefundRequest $refundRequest) {
            app(EmailNotificationService::class)->sendRefundRequested($refundRequest);
        });

        static::updated(function (RefundRequest $refundRequest) {
            if ($refundRequest->isDirty('status')) {
                app(EmailNotificationService::class)->sendRefundStatusUpdated($refundRequest);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
