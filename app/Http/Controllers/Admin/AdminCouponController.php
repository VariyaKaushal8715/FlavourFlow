<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRewardRule;
use App\Models\CouponUsage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $type = $request->query('type');
        $paymentEligibility = $request->query('payment_method');

        $query = Coupon::query()
            ->with(['user', 'sourceOrder', 'rewardRule', 'usages.user', 'usages.order'])
            ->latest();

        if ($search) {
            $query->search($search);
        }

        if ($type && in_array($type, ['percent', 'fixed'])) {
            $query->where('discount_type', $type);
        }

        if ($paymentEligibility && in_array($paymentEligibility, ['both', 'online', 'cod'])) {
            $query->where('payment_method_eligibility', $paymentEligibility);
        }

        if ($status) {
            $now = now();
            if ($status === 'available') {
                $query->where('is_active', true)
                    ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
                    ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereRaw('times_used < usage_limit'));
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<', $now);
            } elseif ($status === 'used') {
                $query->whereNotNull('usage_limit')->whereRaw('times_used >= usage_limit');
            } elseif ($status === 'disabled') {
                $query->where('is_active', false);
            }
        }

        $coupons = $query->paginate(15)->withQueryString();

        $rewardRules = CouponRewardRule::query()
            ->withCount('coupons')
            ->orderBy('min_purchase_amount')
            ->get();

        $recentUsages = CouponUsage::query()
            ->with(['coupon', 'user', 'order'])
            ->latest('used_at')
            ->limit(10)
            ->get();

        $stats = (object) [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))->count(),
            'used' => CouponUsage::count(),
            'total_discount_given' => (float) CouponUsage::sum('discount_amount'),
        ];

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'rewardRules' => $rewardRules,
            'recentUsages' => $recentUsages,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'paymentMethod' => $paymentEligibility,
            'paymentEligibility' => $paymentEligibility,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'user_id' => ['nullable', 'exists:users,id'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'payment_method_eligibility' => ['required', 'in:both,online,cod'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::upper(str_replace(' ', '', $validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0.00;
        $validated['usage_limit_per_user'] = $validated['usage_limit_per_user'] ?? 1;

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('status', 'Coupon code created successfully.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,'.$coupon->id],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'user_id' => ['nullable', 'exists:users,id'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'payment_method_eligibility' => ['required', 'in:both,online,cod'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::upper(str_replace(' ', '', $validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0.00;
        $validated['usage_limit_per_user'] = $validated['usage_limit_per_user'] ?? 1;

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('status', 'Coupon updated successfully.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        $statusText = $coupon->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.coupons.index')
            ->with('status', "Coupon '{$coupon->code}' has been {$statusText}.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('status', "Coupon '{$code}' deleted successfully.");
    }
}
