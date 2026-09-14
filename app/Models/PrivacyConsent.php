<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyConsent extends Model
{
    public const VERSION = '2026-09-13';

    public const STATUS_ALLOWED = 'allowed';

    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'user_id',
        'consent_status',
        'activity_tracking',
        'consent_version',
        'consented_at',
    ];

    protected $casts = [
        'activity_tracking' => 'boolean',
        'consented_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allowsActivityTracking(): bool
    {
        return $this->consent_status === self::STATUS_ALLOWED && $this->activity_tracking;
    }
}
