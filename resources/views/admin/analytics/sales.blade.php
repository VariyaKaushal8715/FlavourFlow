<x-admin.layout title="Sales Analytics">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <span class="text-zinc-900">Sales & Top Selling Analytics</span>
        </div>

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Sales & Top-Selling Analytics</h1>
                <p class="mt-1 text-sm text-zinc-500">Deep-dive into product performance, sales volume, and gross revenue generated.</p>
            </div>
            <div>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Product Catalog →
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Period Revenue</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">₹{{ number_format($totalStoreRevenue, 0) }}</p>
                <p class="mt-1 text-xs text-zinc-400">From completed orders in selected timeframe</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Period Orders</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ number_format($totalStoreOrders) }}</p>
                <p class="mt-1 text-xs text-zinc-400">Total customer checkout volume</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Top Categories</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ $categories->count() }}</p>
                <p class="mt-1 text-xs text-zinc-400">Active spice blend classifications</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.analytics.sales') }}" class="mt-6 grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_12rem_12rem_auto]">
            <div>
                <label for="search" class="sr-only">Search</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search product by name or SKU..."
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
            </div>

            <div>
                <label for="category" class="sr-only">Category</label>
                <select
                    id="category"
                    name="category"
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected($filters['category'] === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="timeframe" class="sr-only">Timeframe</label>
                <select
                    id="timeframe"
                    name="timeframe"
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
                    <option value="today" @selected($filters['timeframe'] === 'today')>Today</option>
                    <option value="7" @selected($filters['timeframe'] === '7')>Last 7 Days</option>
                    <option value="30" @selected($filters['timeframe'] === '30')>Last 30 Days</option>
                    <option value="90" @selected($filters['timeframe'] === '90')>Last 90 Days</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-950 px-4 text-xs font-semibold text-white transition hover:bg-zinc-800">
                    Apply Filter
                </button>
                @if($filters['search'] || $filters['category'] !== 'all' || $filters['timeframe'] !== '30')
                    <a href="{{ route('admin.analytics.sales') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Products Leaderboard Table --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Product Sales Leaderboard</h2>
                    <p class="text-xs text-zinc-500">Sorted by verified gross revenue and units sold</p>
                </div>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                    {{ $products->total() }} Products Listed
                </span>
            </div>

            @if($products->isEmpty())
                <div class="p-12 text-center text-sm text-zinc-500">
                    No products match your criteria.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3.5 pl-6 pr-3"># Rank</th>
                                <th class="px-3 py-3.5">Product</th>
                                <th class="px-3 py-3.5">Category</th>
                                <th class="px-3 py-3.5 text-right">Price</th>
                                <th class="px-3 py-3.5 text-right">Units Sold</th>
                                <th class="px-3 py-3.5 text-right">Revenue</th>
                                <th class="px-3 py-3.5 text-center">Orders</th>
                                <th class="px-3 py-3.5 text-center">Stock</th>
                                <th class="py-3.5 pl-3 pr-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($products as $i => $product)
                                <tr class="transition hover:bg-zinc-50/70">
                                    <td class="py-4 pl-6 pr-3 text-sm font-bold text-zinc-400">
                                        {{ ($products->currentPage() - 1) * $products->perPage() + $i + 1 }}
                                    </td>
                                    <td class="px-3 py-4">
                                        <a href="{{ route('admin.analytics.products.show', $product) }}" class="group flex items-center gap-3">
                                            @if($product->image_path)
                                                <img class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-zinc-200" src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 ring-1 ring-zinc-200">
                                                    <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-zinc-900 group-hover:text-red-700 transition">{{ $product->name }}</p>
                                                <p class="text-xs text-zinc-400">SKU: {{ $product->sku ?? '—' }}</p>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-3 py-4 text-xs font-medium text-zinc-600 capitalize">
                                        <a href="{{ route('admin.categories.show', ['category' => $product->category]) }}" class="hover:text-red-700 underline">
                                            {{ $product->category }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-4 text-right text-sm font-medium text-zinc-700">
                                        ₹{{ number_format($product->price, 0) }}
                                    </td>
                                    <td class="px-3 py-4 text-right text-sm font-bold text-zinc-950">
                                        {{ number_format($product->units_sold) }}
                                    </td>
                                    <td class="px-3 py-4 text-right text-sm font-bold text-emerald-700">
                                        ₹{{ number_format($product->revenue_generated, 0) }}
                                    </td>
                                    <td class="px-3 py-4 text-center text-xs font-semibold text-zinc-600">
                                        {{ $product->orders_count }}
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        @if($product->quantity === 0)
                                            <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Out</span>
                                        @elseif($product->quantity <= $product->low_stock_threshold)
                                            <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">{{ $product->quantity }} left</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ $product->quantity }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 pl-3 pr-6 text-right">
                                        <a href="{{ route('admin.analytics.products.show', $product) }}" class="inline-flex items-center gap-1 rounded-md bg-zinc-900 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-zinc-800">
                                            Detail Analytics →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="border-t border-zinc-200 px-6 py-4">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
        </div>
    </main>
</x-admin.layout>

