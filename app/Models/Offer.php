<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'eyebrow',
    'title',
    'description',
    'discount_label',
    'coupon_code',
    'terms',
    'starts_at',
    'ends_at',
    'priority',
    'image_path',
    'is_featured',
    'is_active',
])]
class Offer extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'discount_label',
        'coupon_code',
        'terms',
        'starts_at',
        'ends_at',
        'priority',
        'image_path',
        'is_featured',
        'is_active',
    ];

    protected $attributes = [
        'eyebrow' => 'Limited-time offer',
        'priority' => 50,
        'is_featured' => false,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeVisibleNow(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('coupon_code', 'like', "%{$search}%")
                    ->orWhere('discount_label', 'like', "%{$search}%");
            });
        });
    }

    public function isCurrentlyVisible(): bool
    {
        $now = now();

        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte($now))
            && (! $this->ends_at || $this->ends_at->gte($now));
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        if ($this->starts_at?->isFuture()) {
            return 'Scheduled';
        }

        if ($this->ends_at?->isPast()) {
            return 'Expired';
        }

        return 'Live';
    }

    public function dateRangeLabel(): string
    {
        if (! $this->starts_at && ! $this->ends_at) {
            return 'Always available';
        }

        if ($this->starts_at && $this->ends_at) {
            return $this->starts_at->format('d M').' - '.$this->ends_at->format('d M Y');
        }

        if ($this->starts_at) {
            return 'Starts '.$this->starts_at->format('d M Y');
        }

        return 'Ends '.$this->ends_at?->format('d M Y');
    }

    public function suggestedProductCount(): int
    {
        $offerCopy = Str::lower(implode(' ', [
            $this->eyebrow,
            $this->title,
            $this->description,
            $this->discount_label,
        ]));

        if (preg_match('/\b(?:pick|choose)\s+(?:any\s+)?([1-6])\b/', $offerCopy, $matches) === 1) {
            return (int) $matches[1];
        }

        $numberWords = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4,
            'five' => 5,
            'six' => 6,
        ];

        foreach ($numberWords as $word => $count) {
            if (preg_match("/\b(?:pick|choose)\s+(?:any\s+)?{$word}\b/", $offerCopy) === 1) {
                return $count;
            }
        }

        return 0;
    }

    public function uploadedImageStoragePath(): ?string
    {
        if (! $this->image_path || ! Str::startsWith($this->image_path, 'storage/offers/')) {
            return null;
        }

        return Str::after($this->image_path, 'storage/');
    }

    public function getDiscountType(): string
    {
        if (preg_match('/(\d+)\s*%/', $this->discount_label)) {
            return 'percent';
        }
        if (preg_match('/(?:Rs\.?|₹|save)\s*(\d+)/i', $this->discount_label)) {
            return 'fixed';
        }

        return 'fixed';
    }

    public function getDiscountValue(): float
    {
        if (preg_match('/(\d+)\s*%/', $this->discount_label, $matches)) {
            return (float) $matches[1];
        }
        if (preg_match('/(?:Rs\.?|₹|save)\s*(\d+)/i', $this->discount_label, $matches)) {
            return (float) $matches[1];
        }
        if (Str::contains(Str::upper($this->coupon_code), 'DAILY3')) {
            return 30.00;
        }

        return 10.00;
    }

    public function getMinOrderAmount(): float
    {
        if ($this->terms && preg_match('/(?:above|min|minimum)\s+(?:Rs\.?|₹)?\s*(\d+)/i', $this->terms, $matches)) {
            return (float) $matches[1];
        }

        return 0.00;
    }

    public function isValidFor(float $subtotal, ?string &$error = null): bool
    {
        if (! $this->is_active) {
            $error = 'This coupon is inactive.';

            return false;
        }

        $now = now();
        if ($this->starts_at && $this->starts_at->isFuture()) {
            $error = 'This coupon has not started yet.';

            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            $error = 'This coupon has expired.';

            return false;
        }

        $minAmount = $this->getMinOrderAmount();
        if ($subtotal < $minAmount) {
            $error = 'Minimum order amount to use this coupon is Rs. '.number_format($minAmount, 2);

            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = 0.0;
        $val = $this->getDiscountValue();
        $type = $this->getDiscountType();

        if ($type === 'percent') {
            $discount = ($subtotal * $val) / 100.0;
        } else {
            $discount = $val;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return round($discount, 2);
    }
}
