<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponRewardRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminCouponRewardRuleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_purchase_amount' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'payment_method_eligibility' => ['nullable', 'in:both,online,cod'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'min_next_order_amount' => ['nullable', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['payment_method_eligibility'] = $validated['payment_method_eligibility'] ?? 'online';
        $validated['min_next_order_amount'] = $validated['min_next_order_amount'] ?? 0.00;

        CouponRewardRule::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('status', 'Online payment reward rule created successfully.');
    }

    public function update(Request $request, CouponRewardRule $rewardRule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_purchase_amount' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'payment_method_eligibility' => ['nullable', 'in:both,online,cod'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'min_next_order_amount' => ['nullable', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['payment_method_eligibility'] = $validated['payment_method_eligibility'] ?? $rewardRule->payment_method_eligibility;
        $validated['min_next_order_amount'] = $validated['min_next_order_amount'] ?? 0.00;

        $rewardRule->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('status', 'Reward rule updated successfully.');
    }

    public function toggle(CouponRewardRule $rewardRule): RedirectResponse
    {
        $rewardRule->update(['is_active' => ! $rewardRule->is_active]);

        $statusText = $rewardRule->is_active ? 'enabled' : 'disabled';

        return redirect()->route('admin.coupons.index')
            ->with('status', "Reward rule '{$rewardRule->name}' has been {$statusText}.");
    }

    public function destroy(CouponRewardRule $rewardRule): RedirectResponse
    {
        $name = $rewardRule->name;
        $rewardRule->delete();

        return redirect()->route('admin.coupons.index')
            ->with('status', "Reward rule '{$name}' deleted successfully.");
    }
}
