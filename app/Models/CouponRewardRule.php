<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CouponRewardRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_purchase_amount',
        'discount_type',
        'discount_value',
        'payment_method_eligibility',
        'max_discount',
        'min_next_order_amount',
        'validity_days',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_purchase_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_next_order_amount' => 'decimal:2',
            'validity_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'reward_rule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method_eligibility) {
            'online' => 'Online Payment Only',
            'cod' => 'Cash on Delivery Only',
            default => 'Both Online & COD',
        };
    }

    public function formattedDiscount(): string
    {
        if ($this->discount_type === 'percent') {
            return (int) $this->discount_value == $this->discount_value
                ? (int) $this->discount_value.'% OFF'
                : number_format($this->discount_value, 1).'% OFF';
        }

        return '₹'.number_format($this->discount_value, 2).' OFF';
    }
}
