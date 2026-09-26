<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRewardRule;
use App\Models\CouponUsage;
use App\Models\Offer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Generate a unique reward coupon for an online order above threshold.
     */
    public function generateRewardCouponForOrder(Order $order): ?Coupon
    {
        // Reward coupon only applies to confirmed online payment orders
        if ($order->payment_method !== 'online') {
            return null;
        }

        // Prevent duplicate reward generation for this order (Idempotency)
        if ($order->earned_coupon_id !== null) {
            return Coupon::find($order->earned_coupon_id);
        }

        $existing = Coupon::where('source_order_id', $order->id)->first();
        if ($existing) {
            if (! $order->earned_coupon_id) {
                $order->update(['earned_coupon_id' => $existing->id]);
            }

            return $existing;
        }

        // Find the best matching active reward rule
        $rule = CouponRewardRule::query()
            ->active()
            ->where('min_purchase_amount', '<=', (float) $order->subtotal)
            ->orderByDesc('min_purchase_amount')
            ->first();

        if (! $rule) {
            return null;
        }

        return DB::transaction(function () use ($order, $rule) {
            // Generate a unique coupon code
            $prefix = $rule->discount_type === 'percent'
                ? 'SPICE'.((int) $rule->discount_value)
                : 'FLAVOUR'.((int) $rule->discount_value);

            do {
                $code = $prefix.'-'.strtoupper(Str::random(6));
            } while (Coupon::where('code', $code)->exists());

            $coupon = Coupon::create([
                'code' => $code,
                'title' => "{$rule->formattedDiscount()} Reward Coupon",
                'description' => "Reward coupon earned from Online Order #{$order->order_number}",
                'user_id' => $order->user_id,
                'discount_type' => $rule->discount_type,
                'discount_value' => $rule->discount_value,
                'payment_method_eligibility' => $rule->payment_method_eligibility ?? 'online',
                'min_order_amount' => $rule->min_next_order_amount,
                'max_discount' => $rule->max_discount,
                'starts_at' => now(),
                'expires_at' => now()->addDays($rule->validity_days),
                'usage_limit' => 1,
                'usage_limit_per_user' => 1,
                'times_used' => 0,
                'status' => 'available',
                'is_active' => true,
                'reward_rule_id' => $rule->id,
                'source_order_id' => $order->id,
            ]);

            $order->update(['earned_coupon_id' => $coupon->id]);

            return $coupon;
        });
    }

    /**
     * Server-side validate a coupon code for a given user, subtotal, and payment method.
     *
     * @return array{valid: bool, coupon: Coupon|Offer|null, type: string|null, discount: float, error: string|null}
     */
    public function validateCoupon(string $code, ?User $user, float $subtotal, ?string $paymentMethod = null): array
    {
        $codeClean = Str::upper(str_replace(' ', '', $code));

        if (empty($codeClean)) {
            return [
                'valid' => false,
                'coupon' => null,
                'type' => null,
                'discount' => 0.0,
                'error' => 'Please enter a coupon code.',
            ];
        }

        // 1. Check dedicated Coupon table
        $coupon = Coupon::query()
            ->whereRaw('UPPER(REPLACE(code, " ", "")) = ?', [$codeClean])
            ->first();

        if ($coupon) {
            $error = null;
            $isValid = $coupon->isValidFor($subtotal, $user, $error, $paymentMethod);

            return [
                'valid' => $isValid,
                'coupon' => $coupon,
                'type' => 'coupon',
                'discount' => $isValid ? $coupon->calculateDiscount($subtotal) : 0.0,
                'error' => $error,
            ];
        }

        // 2. Check legacy Offer coupons
        $offer = Offer::whereNotNull('coupon_code')
            ->get()
            ->first(function ($item) use ($codeClean) {
                return Str::upper(str_replace(' ', '', $item->coupon_code)) === $codeClean;
            });

        if ($offer) {
            $error = null;
            $isValid = $offer->isValidFor($subtotal, $error);

            return [
                'valid' => $isValid,
                'coupon' => $offer,
                'type' => 'offer',
                'discount' => $isValid ? $offer->calculateDiscount($subtotal) : 0.0,
                'error' => $error,
            ];
        }

        return [
            'valid' => false,
            'coupon' => null,
            'type' => null,
            'discount' => 0.0,
            'error' => 'Invalid coupon code.',
        ];
    }

    /**
     * Record coupon redemption.
     */
    public function recordUsage(Coupon|Offer $couponOrOffer, User $user, Order $order, float $discountAmount): void
    {
        if ($couponOrOffer instanceof Coupon) {
            CouponUsage::create([
                'coupon_id' => $couponOrOffer->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);

            $couponOrOffer->increment('times_used');

            if ($couponOrOffer->usage_limit !== null && $couponOrOffer->times_used >= $couponOrOffer->usage_limit) {
                $couponOrOffer->update(['status' => 'used']);
            } elseif ($couponOrOffer->user_id !== null && $couponOrOffer->times_used >= $couponOrOffer->usage_limit_per_user) {
                $couponOrOffer->update(['status' => 'used']);
            }

            $order->update(['coupon_id' => $couponOrOffer->id]);
        }
    }

    /**
     * Get user's available and applicable coupons for checkout.
     *
     * @return Collection<int, Coupon>
     */
    public function getAvailableCouponsForUser(?User $user, ?string $paymentMethod = null): Collection
    {
        if (! $user) {
            return new Collection;
        }

        $query = Coupon::availableForUser($user)
            ->orderBy('expires_at');

        if ($paymentMethod && in_array($paymentMethod, ['online', 'cod'], true)) {
            $query->whereIn('payment_method_eligibility', ['both', $paymentMethod]);
        }

        return $query->get();
    }
}
