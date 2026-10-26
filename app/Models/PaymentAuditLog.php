<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'gateway',
        'environment',
        'ip_address',
        'user_agent',
        'details',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a security audit log entry, strictly redacting any confidential secrets.
     *
     * @param  array<string, mixed>  $details
     */
    public static function logAction(
        string $action,
        ?User $user = null,
        ?string $gateway = null,
        ?string $environment = null,
        array $details = []
    ): self {
        $sanitizedDetails = static::sanitizeDetails($details);

        return static::create([
            'user_id' => $user?->id,
            'action' => $action,
            'gateway' => $gateway,
            'environment' => $environment,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'details' => $sanitizedDetails,
        ]);
    }

    /**
     * Ensure no credentials, API secrets, or passwords ever enter the audit log.
     *
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    protected static function sanitizeDetails(array $details): array
    {
        $sensitiveKeys = [
            'password',
            'current_password',
            'key_secret',
            'cashfree_secret_key',
            'secret_key',
            'secret',
            'token',
            'api_secret',
        ];

        $sanitized = [];

        foreach ($details as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, $sensitiveKeys, true)) {
                $sanitized[$key] = '[REDACTED_SECRET]';
            } elseif (is_array($value)) {
                $sanitized[$key] = static::sanitizeDetails($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
