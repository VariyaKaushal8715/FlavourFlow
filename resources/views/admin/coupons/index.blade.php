<x-admin.layout title="Coupon Control">
    <div class="mx-auto w-full max-w-[90rem] px-6 py-8 lg:px-8">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-red-600">Admin Control</p>
                <h1 class="mt-1 text-2xl font-bold text-zinc-950 sm:text-3xl">Coupon & Discount Management</h1>
                <p class="mt-1 text-sm text-zinc-600">Create, configure, filter, and monitor promotional vouchers and automated online rewards.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('admin.coupons.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Create Coupon</span>
                </a>
            </div>
        </div>

        {{-- Status / Alerts --}}
        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Total Coupons</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ $stats->total }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Active / Live</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats->active }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Total Usages</p>
                <p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats->used }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Discount Granted</p>
                <p class="mt-2 text-2xl font-bold text-indigo-700">₹{{ number_format($stats->total_discount_given, 2) }}</p>
            </div>
        </div>

        {{-- Online Payment Reward Rules Engine (Automated Tiered Offers) --}}
        <div class="mt-8 rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/50 via-white to-amber-50/20 p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                        🎁 Automatic Online Payment Reward Rules
                    </span>
                    <h2 class="mt-2 text-lg font-bold text-zinc-950">Tiered Purchase Reward Rules</h2>
                    <p class="mt-1 text-xs text-zinc-600">When customers pay online above the threshold, generate unique reward coupons automatically (e.g. ₹1,000+ &rarr; 10%, ₹2,000+ &rarr; 15%).</p>
                </div>
                <button
                    type="button"
                    onclick="document.getElementById('reward-rule-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-900 shadow-sm transition hover:bg-amber-100"
                >
                    <svg class="h-3.5 w-3.5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Add Reward Rule</span>
                </button>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($rewardRules as $rule)
                    <div class="flex flex-col justify-between rounded-xl border border-amber-200 bg-white p-4 shadow-sm">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-zinc-950 text-sm">{{ $rule->name }}</h3>
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-[10px] font-bold',
                                    'bg-emerald-100 text-emerald-800' => $rule->is_active,
                                    'bg-zinc-100 text-zinc-600' => ! $rule->is_active,
                                ])>
                                    {{ $rule->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </div>
                            <div class="mt-3 space-y-1 text-xs text-zinc-600">
                                <p><span class="font-semibold text-zinc-900">Min Spend:</span> ₹{{ number_format($rule->min_purchase_amount, 2) }}</p>
                                <p><span class="font-semibold text-zinc-900">Reward:</span> <span class="font-bold text-brand-primary">{{ $rule->formattedDiscount() }}</span> @if ($rule->max_discount) (Up to ₹{{ number_format($rule->max_discount, 2) }}) @endif</p>
                                <p><span class="font-semibold text-zinc-900">Payment Eligibility:</span> <span class="font-semibold text-amber-900">{{ $rule->paymentMethodLabel() }}</span></p>
                                <p><span class="font-semibold text-zinc-900">Next Order Min:</span> ₹{{ number_format($rule->min_next_order_amount, 2) }}</p>
                                <p><span class="font-semibold text-zinc-900">Validity:</span> {{ $rule->validity_days }} days</p>
                                <p><span class="font-semibold text-zinc-900">Coupons Issued:</span> {{ $rule->coupons_count }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-2 border-t border-zinc-100 pt-3 text-xs">
                            <form method="POST" action="{{ route('admin.coupons.rewardRules.toggle', $rule) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-semibold text-zinc-700 hover:text-zinc-950">
                                    {{ $rule->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <span class="text-zinc-300">|</span>
                            <form method="POST" action="{{ route('admin.coupons.rewardRules.destroy', $rule) }}" onsubmit="return confirm('Delete reward rule {{ $rule->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-amber-300 bg-white/60 p-6 text-center text-xs text-zinc-500">
                        No online reward rules configured yet. Click "Add Reward Rule" to create tiers like ₹1,000+ &rarr; 10% OFF.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Add Reward Rule Modal --}}
        <div id="reward-rule-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-zinc-950/60 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                    <h3 class="font-bold text-zinc-950 text-base">New Online Payment Reward Rule</h3>
                    <button type="button" onclick="document.getElementById('reward-rule-modal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-700">&times;</button>
                </div>

                <form class="mt-4 space-y-4" method="POST" action="{{ route('admin.coupons.rewardRules.store') }}">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700">Rule Name</label>
                        <input type="text" name="name" placeholder="e.g. 10% Online Reward (₹1,000+)" required class="admin-input mt-1">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700">Min Cart Subtotal (₹)</label>
                            <input type="number" step="0.01" min="0" name="min_purchase_amount" placeholder="1000.00" required class="admin-input mt-1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700">Discount Type</label>
                            <select name="discount_type" class="admin-input mt-1" required>
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700">Discount Value</label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" placeholder="10.00" required class="admin-input mt-1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" min="0" name="max_discount" placeholder="Optional, e.g. 300" class="admin-input mt-1">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700">Min Spend for Next Order (₹)</label>
                            <input type="number" step="0.01" min="0" name="min_next_order_amount" placeholder="500.00" class="admin-input mt-1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700">Validity (Days)</label>
                            <input type="number" min="1" name="validity_days" value="30" required class="admin-input mt-1">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700">Payment Eligibility</label>
                        <select name="payment_method_eligibility" class="admin-input mt-1" required>
                            <option value="online" selected>Online Payment Only</option>
                            <option value="cod">Cash on Delivery Only</option>
                            <option value="both">Both Online Payment & Cash On Delivery</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-100 pt-4">
                        <button type="button" onclick="document.getElementById('reward-rule-modal').classList.add('hidden')" class="rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Cancel</button>
                        <button type="submit" class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">Save Rule</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Filter & Search Section --}}
        <div class="mt-8 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid gap-4 sm:grid-cols-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700" for="search">Search Coupons</label>
                    <input
                        type="search"
                        name="search"
                        id="search"
                        value="{{ $search }}"
                        placeholder="Search code, title, or customer name/email..."
                        class="admin-input mt-1"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="status">Filter by Status</label>
                    <select name="status" id="status" class="admin-input mt-1">
                        <option value="">All Statuses</option>
                        <option value="available" @selected($status === 'available')>Available / Live</option>
                        <option value="used" @selected($status === 'used')>Used / Max Limit</option>
                        <option value="expired" @selected($status === 'expired')>Expired</option>
                        <option value="disabled" @selected($status === 'disabled')>Disabled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="admin-input mt-1">
                        <option value="">All Payment Types</option>
                        <option value="both" @selected($paymentMethod === 'both')>Both Online & COD</option>
                        <option value="online" @selected($paymentMethod === 'online')>Online Only</option>
                        <option value="cod" @selected($paymentMethod === 'cod')>COD Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700" for="type">Discount Type</label>
                    <select name="type" id="type" class="admin-input mt-1">
                        <option value="">All Types</option>
                        <option value="percent" @selected($type === 'percent')>Percentage (%)</option>
                        <option value="fixed" @selected($type === 'fixed')>Fixed Amount (₹)</option>
                    </select>
                </div>

                <div class="sm:col-span-5 flex items-center justify-between pt-2">
                    <button type="submit" class="rounded-xl bg-zinc-950 px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-red-700">
                        Apply Filters
                    </button>
                    @if ($search || $status || $type || $paymentMethod)
                        <a href="{{ route('admin.coupons.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-red-700">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Coupons Table --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h2 class="text-base font-bold text-zinc-950">Coupons List</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600">
                    <thead class="bg-zinc-50 text-xs font-bold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th class="px-6 py-3.5">Code / Title</th>
                            <th class="px-6 py-3.5">Discount</th>
                            <th class="px-6 py-3.5">Payment Method</th>
                            <th class="px-6 py-3.5">Min Order & Cap</th>
                            <th class="px-6 py-3.5">Assigned User</th>
                            <th class="px-6 py-3.5">Usage / Limit</th>
                            <th class="px-6 py-3.5">Validity</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($coupons as $coupon)
                            <tr class="hover:bg-zinc-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-bold text-zinc-950">{{ $coupon->code }}</span>
                                    </div>
                                    @if ($coupon->title)
                                        <p class="text-xs text-zinc-500">{{ $coupon->title }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-brand-primary">{{ $coupon->formattedDiscount() }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold',
                                        'bg-sky-100 text-sky-800' => $coupon->payment_method_eligibility === 'online',
                                        'bg-amber-100 text-amber-800' => $coupon->payment_method_eligibility === 'cod',
                                        'bg-zinc-100 text-zinc-700' => $coupon->payment_method_eligibility === 'both',
                                    ])>
                                        {{ $coupon->paymentMethodLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <p>Min: ₹{{ number_format($coupon->min_order_amount, 2) }}</p>
                                    @if ($coupon->max_discount)
                                        <p class="text-zinc-400">Cap: ₹{{ number_format($coupon->max_discount, 2) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if ($coupon->user)
                                        <p class="font-semibold text-zinc-950">{{ $coupon->user->name }}</p>
                                        <p class="text-zinc-400">{{ $coupon->user->email }}</p>
                                    @else
                                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600">All Customers</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="font-bold text-zinc-900">{{ $coupon->times_used }}</span>
                                    <span class="text-zinc-400">/ {{ $coupon->usage_limit ?: '∞' }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if ($coupon->expires_at)
                                        <p @class(['font-semibold', 'text-red-600' => $coupon->isExpired()])>
                                            {{ $coupon->expires_at->format('M d, Y') }}
                                        </p>
                                        <p class="text-[10px] text-zinc-400">{{ $coupon->isExpired() ? 'Expired' : $coupon->expires_at->diffForHumans() }}</p>
                                    @else
                                        <span class="text-zinc-400">No Expiry</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-bold',
                                        'bg-emerald-100 text-emerald-800' => $coupon->statusLabel() === 'Available',
                                        'bg-zinc-200 text-zinc-700' => $coupon->statusLabel() === 'Used',
                                        'bg-red-100 text-red-800' => $coupon->statusLabel() === 'Expired',
                                        'bg-zinc-100 text-zinc-500' => $coupon->statusLabel() === 'Disabled',
                                    ])>
                                        {{ $coupon->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-zinc-200 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-100">
                                                {{ $coupon->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="rounded-lg border border-zinc-200 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:border-zinc-950 hover:text-zinc-950">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete coupon {{ $coupon->code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-50">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-zinc-500">
                                    No coupons found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-6 py-4">
                {{ $coupons->links() }}
            </div>
        </div>

        {{-- Recent Usage Audit Log --}}
        <div class="mt-8 rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h2 class="text-base font-bold text-zinc-950">Recent Coupon Redemptions (Audit Log)</h2>
                <p class="text-xs text-zinc-500">Track which customers redeemed coupons across recent orders.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600">
                    <thead class="bg-zinc-50 text-xs font-bold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th class="px-6 py-3.5">Date & Time</th>
                            <th class="px-6 py-3.5">Coupon Code</th>
                            <th class="px-6 py-3.5">Customer</th>
                            <th class="px-6 py-3.5">Order Number</th>
                            <th class="px-6 py-3.5">Discount Saved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($recentUsages as $usage)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-6 py-3.5 text-xs text-zinc-500">
                                    {{ $usage->used_at ? $usage->used_at->format('M d, Y h:i A') : $usage->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-3.5 font-mono text-xs font-bold text-zinc-950">
                                    {{ $usage->coupon ? $usage->coupon->code : '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-xs">
                                    <p class="font-semibold text-zinc-900">{{ $usage->user ? $usage->user->name : '-' }}</p>
                                    <p class="text-zinc-400">{{ $usage->user ? $usage->user->email : '' }}</p>
                                </td>
                                <td class="px-6 py-3.5 text-xs">
                                    @if ($usage->order)
                                        <a href="{{ route('admin.orders.show', $usage->order) }}" class="font-bold text-brand-primary hover:underline">
                                            {{ $usage->order->order_number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 font-bold text-emerald-700 text-xs">
                                    ₹{{ number_format($usage->discount_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-xs text-zinc-500">
                                    No coupon redemptions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin.layout>
