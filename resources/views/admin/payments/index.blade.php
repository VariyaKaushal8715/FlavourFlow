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
