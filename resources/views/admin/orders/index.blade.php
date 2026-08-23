<x-admin.layout title="Orders">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <span class="text-zinc-900">Orders Management</span>
        </div>

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Customer Orders</h1>
                <p class="mt-1 text-sm text-zinc-500">Track real customer purchases, payment verification, and fulfillment statuses.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.analytics.sales') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Sales Analytics →
                </a>
            </div>
        </div>

        {{-- Stats Strip --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Orders</p>
                <p class="mt-1 text-xl font-bold text-zinc-950">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Completed</p>
                <p class="mt-1 text-xl font-bold text-emerald-700">{{ number_format($stats['completed']) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Pending</p>
                <p class="mt-1 text-xl font-bold text-amber-700">{{ number_format($stats['pending']) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Processing</p>
                <p class="mt-1 text-xl font-bold text-blue-700">{{ number_format($stats['processing']) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Cancelled</p>
                <p class="mt-1 text-xl font-bold text-zinc-600">{{ number_format($stats['cancelled']) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Gross Revenue</p>
                <p class="mt-1 text-xl font-bold text-zinc-950">₹{{ number_format($stats['revenue'], 0) }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-6 grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_12rem_12rem_auto]">
            <div>
                <label for="search" class="sr-only">Search</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search by Order #, customer name or email..."
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select
                    id="status"
                    name="status"
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
                    <option value="all" @selected($filters['status'] === 'all')>All Statuses</option>
                    <option value="completed" @selected($filters['status'] === 'completed')>Completed</option>
                    <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                    <option value="processing" @selected($filters['status'] === 'processing')>Processing</option>
                    <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <div>
                <label for="sort" class="sr-only">Sort</label>
                <select
                    id="sort"
                    name="sort"
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
                    <option value="latest" @selected($filters['sort'] === 'latest')>Newest First</option>
                    <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest First</option>
                    <option value="amount_high" @selected($filters['sort'] === 'amount_high')>Amount: High to Low</option>
                    <option value="amount_low" @selected($filters['sort'] === 'amount_low')>Amount: Low to High</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-950 px-4 text-xs font-semibold text-white transition hover:bg-zinc-800">
                    Apply Filter
                </button>
                @if($filters['search'] || $filters['status'] !== 'all' || $filters['sort'] !== 'latest')
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Orders Table --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Orders Registry</h2>
                    <p class="text-xs text-zinc-500">Live order database records</p>
                </div>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                    {{ $orders->total() }} Total
                </span>
            </div>

            @if($orders->isEmpty())
                <div class="p-12 text-center text-sm text-zinc-500">
                    No orders match your filter criteria.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3.5 pl-6 pr-3">Order Number</th>
                                <th class="px-3 py-3.5">Customer</th>
                                <th class="px-3 py-3.5 text-center">Placed Date</th>
                                <th class="px-3 py-3.5 text-center">Items</th>
                                <th class="px-3 py-3.5 text-center">Payment Status</th>
                                <th class="px-3 py-3.5 text-center">Order Status</th>
                                <th class="px-3 py-3.5 text-right">Order Amount</th>
                                <th class="py-3.5 pl-3 pr-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($orders as $order)
                                <tr class="transition hover:bg-zinc-50/70">
                                    <td class="py-4 pl-6 pr-3 text-sm font-bold text-zinc-900">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-zinc-900 hover:text-red-700 transition">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <p class="font-medium text-zinc-900">{{ $order->user?->name ?? 'Deleted User' }}</p>
                                        <p class="text-xs text-zinc-400">{{ $order->user?->email ?? '—' }}</p>
                                    </td>
                                    <td class="px-3 py-4 text-center text-xs text-zinc-500">
                                        <p class="font-medium text-zinc-700">{{ $order->created_at->format('M d, Y') }}</p>
                                        <p class="text-zinc-400">{{ $order->created_at->format('h:i A') }} ({{ $order->created_at->diffForHumans() }})</p>
                                    </td>
                                    <td class="px-3 py-4 text-center text-sm font-semibold text-zinc-700">
                                        {{ $order->items->count() }}
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        @if($order->payment_status === 'paid')
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Paid</span>
                                        @elseif($order->payment_status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">{{ ucfirst($order->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        @if($order->status === 'completed')
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Completed</span>
                                        @elseif($order->status === 'processing')
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">Processing</span>
                                        @elseif($order->status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-right text-sm font-bold text-zinc-950">
                                        ₹{{ number_format($order->total_amount, 0) }}
                                    </td>
                                    <td class="py-4 pl-3 pr-6 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 rounded-md bg-zinc-900 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-zinc-800">
                                            View Details →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="border-t border-zinc-200 px-6 py-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>
    </main>
</x-admin.layout>

