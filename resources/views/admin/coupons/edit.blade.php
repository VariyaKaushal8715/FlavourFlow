<x-admin.layout title="Edit Coupon">
    <div class="mx-auto w-full max-w-4xl px-6 py-8 lg:px-8">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-5">
            <div>
                <a href="{{ route('admin.coupons.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-red-700">&larr; Back to Coupons</a>
                <h1 class="mt-1 text-2xl font-bold text-zinc-950">Edit Coupon: {{ $coupon->code }}</h1>
                <p class="mt-1 text-xs text-zinc-600">Update configuration, limits, discount value, or assigned customer.</p>
            </div>
            <div class="flex items-center gap-2">
                <span @class([
                    'rounded-full px-3 py-1 text-xs font-bold',
                    'bg-emerald-100 text-emerald-800' => $coupon->statusLabel() === 'Available',
                    'bg-zinc-200 text-zinc-700' => $coupon->statusLabel() === 'Used',
                    'bg-red-100 text-red-800' => $coupon->statusLabel() === 'Expired',
                    'bg-zinc-100 text-zinc-500' => $coupon->statusLabel() === 'Disabled',
                ])>
                    {{ $coupon->statusLabel() }}
                </span>
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-semibold text-red-800">
                <p class="font-bold">Please resolve the following errors:</p>
                <ul class="mt-1 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="mt-6 space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="code">Coupon Code *</label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        value="{{ old('code', $coupon->code) }}"
                        required
                        class="admin-input mt-1 uppercase"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="title">Title / Headline</label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title', $coupon->title) }}"
                        class="admin-input mt-1"
                    >
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700" for="description">Description / Terms</label>
                <textarea
                    name="description"
                    id="description"
                    rows="2"
                    class="admin-input mt-1"
                >{{ old('description', $coupon->description) }}</textarea>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="discount_type">Discount Type *</label>
                    <select name="discount_type" id="discount_type" class="admin-input mt-1" required>
                        <option value="percent" @selected(old('discount_type', $coupon->discount_type) === 'percent')>Percentage (%)</option>
                        <option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Fixed Amount (₹)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="discount_value">Discount Value *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="discount_value"
                        id="discount_value"
                        value="{{ old('discount_value', $coupon->discount_value) }}"
                        required
                        class="admin-input mt-1"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="max_discount">Max Discount Cap (₹)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="max_discount"
                        id="max_discount"
                        value="{{ old('max_discount', $coupon->max_discount) }}"
                        class="admin-input mt-1"
                    >
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="min_order_amount">Minimum Purchase Amount (₹)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="min_order_amount"
                        id="min_order_amount"
                        value="{{ old('min_order_amount', $coupon->min_order_amount) }}"
                        class="admin-input mt-1"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="payment_method_eligibility">Payment Method Eligibility *</label>
                    <select name="payment_method_eligibility" id="payment_method_eligibility" class="admin-input mt-1" required>
                        <option value="both" @selected(old('payment_method_eligibility', $coupon->payment_method_eligibility) === 'both')>Both Online Payment & Cash On Delivery</option>
                        <option value="online" @selected(old('payment_method_eligibility', $coupon->payment_method_eligibility) === 'online')>Online Payment Only</option>
                        <option value="cod" @selected(old('payment_method_eligibility', $coupon->payment_method_eligibility) === 'cod')>Cash on Delivery Only</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700" for="user_id">Assign to Specific Customer (Optional)</label>
                <select name="user_id" id="user_id" class="admin-input mt-1">
                    <option value="">All Customers (Public Coupon)</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $coupon->user_id) == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="starts_at">Valid From (Start Date)</label>
                    <input
                        type="datetime-local"
                        name="starts_at"
                        id="starts_at"
                        value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                        class="admin-input mt-1"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="expires_at">Expires At (Expiry Date)</label>
                    <input
                        type="datetime-local"
                        name="expires_at"
                        id="expires_at"
                        value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                        class="admin-input mt-1"
                    >
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="usage_limit">Total Usage Limit (Across All Users)</label>
                    <input
                        type="number"
                        min="1"
                        name="usage_limit"
                        id="usage_limit"
                        value="{{ old('usage_limit', $coupon->usage_limit) }}"
                        class="admin-input mt-1"
                    >
                    <p class="mt-1 text-[11px] text-zinc-400">Current times used: {{ $coupon->times_used }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="usage_limit_per_user">Usage Limit Per Customer</label>
                    <input
                        type="number"
                        min="1"
                        name="usage_limit_per_user"
                        id="usage_limit_per_user"
                        value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}"
                        class="admin-input mt-1"
                    >
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    value="1"
                    @checked(old('is_active', $coupon->is_active))
                    class="h-4 w-4 rounded border-zinc-300 text-brand-primary focus:ring-brand-primary"
                >
                <label for="is_active" class="text-xs font-bold text-zinc-800">
                    Active (Enabled for checkout application)
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-5">
                <a href="{{ route('admin.coupons.index') }}" class="rounded-xl border border-zinc-300 px-5 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-zinc-950 px-6 py-2.5 text-xs font-semibold text-white transition hover:bg-red-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-admin.layout>
