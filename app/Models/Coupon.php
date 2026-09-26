<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'user_id',
        'discount_type',
        'discount_value',
        'payment_method_eligibility',
        'min_order_amount',
        'max_discount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_limit_per_user',
        'times_used',
        'status',
        'is_active',
        'reward_rule_id',
        'source_order_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'times_used' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rewardRule(): BelongsTo
    {
        return $this->belongsTo(CouponRewardRule::class, 'reward_rule_id');
    }

    public function sourceOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Disabled';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return 'Used';
        }

        if ($this->user_id && $this->times_used >= $this->usage_limit_per_user) {
            return 'Used';
        }

        return 'Available';
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method_eligibility) {
            'online' => 'Online Payment Only',
            'cod' => 'Cash on Delivery Only',
            default => 'Both Online & COD',
        };
    }

    public function isEligibleForPaymentMethod(?string $paymentMethod): bool
    {
        if (! $paymentMethod || ! in_array($this->payment_method_eligibility, ['online', 'cod'], true)) {
            return true;
        }

        return $this->payment_method_eligibility === $paymentMethod;
    }

    public function formattedDiscount(): string
    {
        if ($this->discount_type === 'percent') {
            $pct = (int) $this->discount_value == $this->discount_value
                ? (int) $this->discount_value
                : number_format($this->discount_value, 1);

            return $this->max_discount
                ? "{$pct}% OFF (Up to ₹{$this->max_discount})"
                : "{$pct}% OFF";
        }

        return '₹'.number_format($this->discount_value, 2).' OFF';
    }

    public function isValidFor(float $subtotal, ?User $user = null, ?string &$error = null, ?string $paymentMethod = null): bool
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

        if ($this->isExpired()) {
            $error = 'Sorry, this coupon has expired.';

            return false;
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            $error = 'This coupon has reached its maximum usage limit.';

            return false;
        }

        // Ownership check
        if ($this->user_id !== null) {
            if (! $user || $user->id !== $this->user_id) {
                $error = 'This coupon is not valid for your account.';

                return false;
            }
        }

        // Per user usage limit check
        if ($user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                $error = 'You have already used this coupon.';

                return false;
            }
        }

        // Payment method eligibility check
        if ($paymentMethod !== null) {
            if ($this->payment_method_eligibility === 'online' && $paymentMethod === 'cod') {
                $error = 'This coupon is valid only for online payment. Please select online payment to use this coupon.';

                return false;
            }

            if ($this->payment_method_eligibility === 'cod' && $paymentMethod === 'online') {
                $error = 'This coupon is valid only for Cash on Delivery. Please select Cash on Delivery to use this coupon.';

                return false;
            }
        }

        if ($subtotal < (float) $this->min_order_amount) {
            $error = 'Minimum order amount to use this coupon is Rs. '.number_format($this->min_order_amount, 2);

            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = 0.0;
        $val = (float) $this->discount_value;

        if ($this->discount_type === 'percent') {
            $discount = ($subtotal * $val) / 100.0;
            if ($this->max_discount !== null && $discount > (float) $this->max_discount) {
                $discount = (float) $this->max_discount;
            }
        } else {
            $discount = $val;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return round($discount, 2);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        });
    }

    public function scopeAvailableForUser(Builder $query, User $user): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $q) use ($user): void {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->where(function (Builder $q): void {
                $q->whereNull('usage_limit')->orWhereRaw('times_used < usage_limit');
            })
            ->whereDoesntHave('usages', function (Builder $q) use ($user): void {
                $q->where('user_id', $user->id);
            });
    }
}
