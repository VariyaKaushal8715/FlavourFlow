<x-admin.layout title="Payments">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <span class="text-zinc-900">Payments Management</span>
        </div>

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Razorpay Test Payments</h1>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-300">RAZORPAY TEST</span>
                </div>
                <p class="mt-1 text-sm text-zinc-500">Track online payment transactions, Razorpay Order & Payment IDs, verification status, and failure logs.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.settings') }}" class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Payment Settings
                </a>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.payments.index') }}" class="mt-6 grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_12rem_auto]">
            <div>
                <label for="search" class="sr-only">Search</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search by Cashfree Order/Payment ID, Order #, or Customer..."
                    class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
            </div>
            <div>
                <label for="status" class="sr-only">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
                    <option value="">All Statuses</option>
                    <option value="captured" @selected(($filters['status'] ?? '') === 'captured')>Captured / Successful</option>
                    <option value="created" @selected(($filters['status'] ?? '') === 'created')>Pending / Created</option>
                    <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Failed</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full rounded-lg bg-zinc-950 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-zinc-800 transition md:w-auto">Filter</button>
                @if(!empty($filters['search']) || !empty($filters['status']))
                    <a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                    <thead class="bg-zinc-50 font-semibold text-zinc-600">
                        <tr>
                            <th class="px-6 py-4">Order #</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Gateway / Method</th>
                            <th class="px-6 py-4">Cashfree / Gateway ID</th>
                            <th class="px-6 py-4">Payment Status</th>
                            <th class="px-6 py-4">Refund Status</th>
                            <th class="px-6 py-4">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-900">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-zinc-50/80 transition">
                                <td class="px-6 py-4">
                                    @if($payment->order)
                                        <a href="{{ route('admin.orders.show', $payment->order->id) }}" class="font-bold text-red-700 hover:underline">
                                            #{{ $payment->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-zinc-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-zinc-950">{{ $payment->order->name ?? $payment->user->name ?? 'Customer' }}</p>
                                    <p class="text-xs text-zinc-500">{{ $payment->order->email ?? $payment->user->email ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4 font-bold text-zinc-950">
                                    ₹{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700">
                                        {{ $payment->cashfree_order_id ? 'Cashfree' : ($payment->razorpay_order_id ? 'Razorpay' : strtoupper($payment->payment_method)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-zinc-600">
                                    <div class="space-y-0.5">
                                        <p><span class="font-bold text-zinc-400">Ord:</span> {{ $payment->cashfree_order_id ?? $payment->razorpay_order_id ?? 'N/A' }}</p>
                                        <p><span class="font-bold text-zinc-400">Pay:</span> {{ $payment->cashfree_payment_id ?? $payment->razorpay_payment_id ?? 'Pending' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @switch($payment->status)
                                        @case('captured')
                                        @case('SUCCESS')
                                        @case('paid')
                                        @case('PAID')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                Paid / Captured
                                            </span>
                                            @break
                                        @case('failed')
                                        @case('FAILED')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20" title="{{ $payment->failure_reason ?? $payment->error_message }}">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                Failed
                                            </span>
                                            @if($payment->failure_reason || $payment->error_message)
                                                <p class="mt-1 text-[11px] text-rose-600 truncate max-w-[12rem]">{{ $payment->failure_reason ?? $payment->error_message }}</p>
                                            @endif
                                            @break
                                        @default
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($payment->refund_status)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-0.5 font-bold text-purple-800 border border-purple-200">
                                            {{ $payment->refund_status }} (₹{{ number_format($payment->refunded_amount, 2) }})
                                        </span>
                                    @else
                                        <span class="text-zinc-400">None</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-zinc-500">
                                    {{ $payment->paid_at ? $payment->paid_at->format('M d, Y - h:i A') : $payment->created_at->format('M d, Y - h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                                    No online payment transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="border-t border-zinc-200 px-6 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </main>
</x-admin.layout>
