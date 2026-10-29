<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'active_gateway',
        'environment',
        'razorpay_key_id',
        'razorpay_key_secret',
        'razorpay_webhook_secret',
        'cashfree_app_id',
        'cashfree_secret_key',
        'cashfree_api_version',
        'upi_enabled',
        'upi_id',
        'upi_name',
        'upi_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'razorpay_key_secret' => 'encrypted',
            'razorpay_webhook_secret' => 'encrypted',
            'cashfree_secret_key' => 'encrypted',
            'upi_enabled' => 'boolean',
        ];
    }

    /**
     * Get the singleton payment gateway setting or create default.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'active_gateway' => 'cashfree',
            'environment' => 'sandbox',
            'cashfree_api_version' => '2023-08-01',
        ]);
    }

    public function isDemo(): bool
    {
        return $this->active_gateway === 'demo';
    }

    public function isCashfree(): bool
    {
        return $this->active_gateway === 'cashfree';
    }

    public function isRazorpay(): bool
    {
        return $this->active_gateway === 'razorpay';
    }

    public function isLive(): bool
    {
        return $this->environment === 'live';
    }

    public function isSandbox(): bool
    {
        return $this->environment === 'sandbox' || ! $this->isLive();
    }

    /**
     * Get effective Razorpay Key ID (database setting with config fallback).
     */
    public function getEffectiveRazorpayKeyId(): string
    {
        if (! empty($this->razorpay_key_id)) {
            return trim((string) $this->razorpay_key_id);
        }

        $configKey = (string) config('services.razorpay.key_id', '');

        return $configKey === 'rzp_test_dummy_key_id' ? '' : trim($configKey);
    }

    /**
     * Get effective Razorpay Key Secret (database setting with config fallback).
     */
    public function getEffectiveRazorpayKeySecret(): string
    {
        if (! empty($this->razorpay_key_secret)) {
            return trim((string) $this->razorpay_key_secret);
        }

        $configSecret = (string) config('services.razorpay.key_secret', '');

        return $configSecret === 'rzp_test_dummy_key_secret' ? '' : trim($configSecret);
    }

    /**
     * Get effective Razorpay Webhook Secret (database setting with config fallback).
     */
    public function getEffectiveRazorpayWebhookSecret(): string
    {
        if (! empty($this->razorpay_webhook_secret)) {
            return trim((string) $this->razorpay_webhook_secret);
        }

        $configWebhookSecret = (string) config('services.razorpay.webhook_secret', '');

        return $configWebhookSecret === 'rzp_test_dummy_webhook_secret' ? '' : trim($configWebhookSecret);
    }

    /**
     * Get effective Cashfree App ID (database setting with config fallback).
     */
    public function getEffectiveCashfreeAppId(): string
    {
        if (! empty($this->cashfree_app_id)) {
            return trim((string) $this->cashfree_app_id);
        }

        return trim((string) config('services.cashfree.app_id', ''));
    }

    /**
     * Get effective Cashfree Secret Key (database setting with config fallback).
     */
    public function getEffectiveCashfreeSecretKey(): string
    {
        if (! empty($this->cashfree_secret_key)) {
            return trim((string) $this->cashfree_secret_key);
        }

        return trim((string) config('services.cashfree.secret_key', ''));
    }

    /**
     * Get effective Cashfree Environment (TEST or PRODUCTION).
     */
    public function getEffectiveCashfreeEnvironment(): string
    {
        if ($this->isLive()) {
            return 'PRODUCTION';
        }

        return 'TEST';
    }

    /**
     * Get masked Razorpay Secret for admin UI display.
     */
    public function getMaskedRazorpaySecret(): string
    {
        $secret = $this->getEffectiveRazorpayKeySecret();

        return empty($secret) ? '' : '••••••••••••••••';
    }

    /**
     * Get masked Razorpay Webhook Secret for admin UI display.
     */
    public function getMaskedRazorpayWebhookSecret(): string
    {
        $secret = $this->getEffectiveRazorpayWebhookSecret();

        return empty($secret) ? '' : '••••••••••••••••';
    }

    /**
     * Get masked Cashfree Secret for admin UI display.
     */
    public function getMaskedCashfreeSecret(): string
    {
        $secret = $this->getEffectiveCashfreeSecretKey();

        return empty($secret) ? '' : '••••••••••••••••';
    }

    public function hasRealRazorpayCredentials(): bool
    {
        $keyId = $this->getEffectiveRazorpayKeyId();
        $secret = $this->getEffectiveRazorpayKeySecret();

        return ! empty($keyId) && ! empty($secret) && $keyId !== 'rzp_test_dummy_key_id';
    }

    public function hasRealCashfreeCredentials(): bool
    {
        $appId = $this->getEffectiveCashfreeAppId();
        $secret = $this->getEffectiveCashfreeSecretKey();

        return ! empty($appId) && ! empty($secret);
    }

    /**
     * Validate if live mode can be enabled for the target gateway.
     */
    public function canActivateLive(?string $gateway = null): bool
    {
        $targetGateway = $gateway ?? $this->active_gateway;

        if ($targetGateway === 'demo') {
            return false;
        }

        if ($targetGateway === 'razorpay') {
            $keyId = $this->getEffectiveRazorpayKeyId();

            return $this->hasRealRazorpayCredentials() && str_starts_with($keyId, 'rzp_live_');
        }

        if ($targetGateway === 'cashfree') {
            return $this->hasRealCashfreeCredentials();
        }

        return false;
    }

    /**
     * Determine whether the UPI QR code payment method is enabled.
     */
    public function isUpiEnabled(): bool
    {
        return $this->upi_enabled ?? true;
    }

    /**
     * Determine whether UPI is configured with a valid merchant UPI ID.
     */
    public function isUpiConfigured(): bool
    {
        return ! empty($this->getEffectiveUpiId());
    }

    /**
     * Get effective Merchant UPI ID (database setting with configuration fallback).
     */
    public function getEffectiveUpiId(): string
    {
        if (! empty($this->upi_id)) {
            return trim((string) $this->upi_id);
        }

        return trim((string) config('services.upi.id', env('MERCHANT_UPI_ID', '')));
    }

    /**
     * Get effective Payee / Business Name (database setting with fallback).
     */
    public function getEffectiveUpiName(): string
    {
        if (! empty($this->upi_name)) {
            return trim((string) $this->upi_name);
        }

        return trim((string) config('services.upi.name', env('MERCHANT_UPI_NAME', config('app.name', 'FlavourFlow Spices'))));
    }

    /**
     * Get effective default Transaction Note (database setting with fallback).
     */
    public function getEffectiveUpiNote(): string
    {
        if (! empty($this->upi_note)) {
            return trim((string) $this->upi_note);
        }

        return trim((string) config('services.upi.note', env('MERCHANT_UPI_NOTE', 'FlavourFlow Order Payment')));
    }
}
