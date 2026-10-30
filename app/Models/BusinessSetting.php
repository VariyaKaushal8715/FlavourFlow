<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'legal_name',
        'logo_path',
        'business_address',
        'billing_address',
        'phone',
        'email',
        'website_url',
        'gstin',
        'invoice_prefix',
        'next_invoice_number',
        'tax_rate',
        'tax_label',
        'tax_mode',
        'invoice_footer',
        'payment_instructions',
        'return_policy_text',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'next_invoice_number' => 'integer',
    ];

    /**
     * Retrieve the active business settings singleton.
     * Initializes with defaults from config/personal_site.php if not present.
     */
    public static function current(): self
    {
        $setting = static::first();

        if (! $setting) {
            $siteConfig = config('personal_site') ?? [];

            $setting = static::create([
                'store_name' => $siteConfig['brand']['name'] ?? config('app.name', 'FlavourFlow'),
                'legal_name' => 'FlavourFlow Pure Spices Private Limited',
                'logo_path' => $siteConfig['brand']['logo'] ?? 'images/flavourflow-mark.png',
                'business_address' => $siteConfig['contact']['address'] ?? 'Patan, Gujarat, India - 384265',
                'billing_address' => $siteConfig['contact']['address'] ?? 'Patan, Gujarat, India - 384265',
                'phone' => $siteConfig['contact']['phone'] ?? '+91 99999 99999',
                'email' => $siteConfig['contact']['email'] ?? 'support@flavourflow.com',
                'website_url' => config('app.url', 'https://flavourflow.test'),
                'gstin' => '24ABCDE1234F1Z5',
                'invoice_prefix' => 'INV',
                'next_invoice_number' => 1001,
                'tax_rate' => 0.00,
                'tax_label' => 'GST',
                'tax_mode' => 'inclusive',
                'invoice_footer' => 'Thank you for shopping with FlavourFlow! Pure spices packed fresh for authentic flavours.',
                'payment_instructions' => 'Payment made via authenticated digital gateway / verified UPI. Retain invoice for warranty and returns.',
                'return_policy_text' => 'Eligible items may be returned within 7 days of delivery in original unopened packaging. Contact support@flavourflow.com.',
            ]);
        }

        return $setting;
    }

    /**
     * Retrieve and lock the settings row for atomic invoice numbering.
     */
    public static function currentForUpdate(): self
    {
        $current = static::current();

        /** @var self $locked */
        $locked = static::whereKey($current->id)->lockForUpdate()->first();

        return $locked ?: $current;
    }

    /**
     * Resolve absolute URL or public asset path for the logo.
     */
    public function logoUrl(): string
    {
        if (blank($this->logo_path)) {
            return asset('images/flavourflow-mark.png');
        }

        if (Str::startsWith($this->logo_path, ['http://', 'https://'])) {
            return $this->logo_path;
        }

        if (Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }

        if (file_exists(public_path($this->logo_path))) {
            return asset($this->logo_path);
        }

        return asset('images/flavourflow-mark.png');
    }

    /**
     * Check if a custom uploaded logo file exists on disk.
     */
    public function hasCustomLogo(): bool
    {
        return ! blank($this->logo_path) && Storage::disk('public')->exists($this->logo_path);
    }
}
