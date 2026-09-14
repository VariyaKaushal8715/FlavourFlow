<?php

namespace App\Models;

use App\Services\EmailNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id',
        'reason',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (ReturnRequest $returnRequest) {
            app(EmailNotificationService::class)->sendReturnRequested($returnRequest);
        });

        static::updated(function (ReturnRequest $returnRequest) {
            if ($returnRequest->isDirty('status')) {
                app(EmailNotificationService::class)->sendReturnStatusUpdated($returnRequest);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
