<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserCouponController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->query('status', 'all');

        $couponsQuery = Coupon::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($q) {
                        $q->whereNull('user_id')->where('is_active', true);
                    });
            })
            ->with(['sourceOrder', 'usages' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->latest();

        $coupons = $couponsQuery->get()->map(function (Coupon $coupon) {
            $userUsage = $coupon->usages->count();
            $isUsed = $coupon->user_id !== null && $coupon->times_used >= $coupon->usage_limit_per_user
                || $userUsage >= $coupon->usage_limit_per_user
                || ($coupon->usage_limit !== null && $coupon->times_used >= $coupon->usage_limit);

            $isExpired = $coupon->isExpired();

            if (! $coupon->is_active) {
                $computedStatus = 'disabled';
            } elseif ($isUsed) {
                $computedStatus = 'used';
            } elseif ($isExpired) {
                $computedStatus = 'expired';
            } else {
                $computedStatus = 'available';
            }

            $coupon->computed_status = $computedStatus;
            $coupon->is_used_by_me = $isUsed;

            return $coupon;
        });

        if ($statusFilter !== 'all') {
            $coupons = $coupons->filter(fn ($c) => $c->computed_status === $statusFilter);
        }

        $stats = [
            'all' => $couponsQuery->count(),
            'available' => Coupon::availableForUser($user)->count(),
            'used' => $user->couponUsages()->count(),
            'expired' => Coupon::where('user_id', $user->id)->where('expires_at', '<', now())->count(),
        ];

        return view('account.coupons', [
            'site' => config('personal_site'),
            'coupons' => $coupons,
            'currentFilter' => $statusFilter,
            'stats' => $stats,
        ]);
    }
}
