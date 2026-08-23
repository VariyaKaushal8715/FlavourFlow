<x-admin.layout title="{{ $product->name }} Analytics">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8" x-data="{
        trend: @js($productTrend),
        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById('productSalesChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.trend.map(d => d.date),
                        datasets: [
                            {
                                type: 'line',
                                label: 'Revenue (₹)',
                                data: this.trend.map(d => d.revenue),
                                borderColor: '#b42318',
                                backgroundColor: 'rgba(180, 35, 24, 0.1)',
                                borderWidth: 2,
                                yAxisID: 'y1',
                                tension: 0.3,
                                fill: false,
                                pointRadius: 3,
                            },
                            {
                                type: 'bar',
                                label: 'Units Sold',
                                data: this.trend.map(d => d.units),
                                backgroundColor: '#18181b',
                                borderRadius: 4,
                                yAxisID: 'y',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { boxWidth: 12, font: { size: 11 } }
                            },
                            tooltip: {
                                backgroundColor: '#18181b',
                                padding: 10,
                                borderRadius: 8,
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                title: { display: true, text: 'Units Sold', font: { size: 10 } },
                                grid: { color: '#f4f4f5' },
                                ticks: { precision: 0 }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                title: { display: true, text: 'Revenue (₹)', font: { size: 10 } },
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    callback: (v) => '₹' + new Intl.NumberFormat('en-IN', { notation: 'compact' }).format(v)
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            });
        }
    }">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <a href="{{ route('admin.analytics.sales') }}" class="text-zinc-600 hover:text-red-700 transition">
                Sales Analytics
            </a>
            <span>/</span>
            <span class="text-zinc-900 truncate max-w-xs">{{ $product->name }}</span>
        </div>

        {{-- Product Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-6">
            <div class="flex items-center gap-4">
                @if($product->image_path)
                    <img class="h-16 w-16 rounded-xl object-cover ring-1 ring-zinc-200 shadow-sm" src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-zinc-100 ring-1 ring-zinc-200">
                        <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 capitalize">{{ $product->category }}</span>
                        @if($product->is_active)
                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-500">
                                Inactive
                            </span>
                        @endif
                        @if($product->is_featured)
                            <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">★ Featured Spotlight</span>
                        @endif
                    </div>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">{{ $product->name }}</h1>
                    <p class="text-xs text-zinc-400">SKU: <strong class="font-mono text-zinc-600">{{ $product->sku ?? 'N/A' }}</strong> · Unit: {{ $product->unit }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('products.show', $product) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Storefront ↗
                </a>
                <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-950 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                    Edit Product
                </a>
            </div>
        </div>

        {{-- Metrics Row --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Lifetime Revenue</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">₹{{ number_format($totalRevenue, 0) }}</p>
                <p class="mt-1 text-xs text-zinc-400">From completed customer orders</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Units Sold</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ number_format($totalUnitsSold) }}</p>
                <p class="mt-1 text-xs text-zinc-400">Across {{ $totalOrdersCount }} orders</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Current Stock</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-zinc-950">{{ $product->quantity }}</p>
                    @if($product->quantity === 0)
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Out of Stock</span>
                    @elseif($product->quantity <= $product->low_stock_threshold)
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">Low Stock (Threshold: {{ $product->low_stock_threshold }})</span>
                    @else
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">In Stock</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-zinc-400">Threshold: {{ $product->low_stock_threshold }} units</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Pricing & Rating</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">₹{{ number_format($product->price, 0) }}</p>
                <p class="mt-1 text-xs text-zinc-400">★ {{ number_format($product->rating, 1) }} Community Rating</p>
            </div>
        </div>

        {{-- 14-Day Sales Chart --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h2 class="text-base font-bold text-zinc-950">14-Day Performance History</h2>
                <p class="text-xs text-zinc-500">Daily units sold (bars) and gross revenue generated (line).</p>
            </div>
            <div class="p-6">
                <div class="h-[260px] w-full">
                    <canvas id="productSalesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Orders for this Product --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Recent Orders Containing This Product</h2>
                    <p class="text-xs text-zinc-500">Customer transactions including this spice item.</p>
                </div>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                    {{ $recentOrders->count() }} Orders
                </span>
            </div>

            @if($recentOrders->isEmpty())
                <div class="p-8 text-center text-sm text-zinc-500">No order history for this product yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3 pl-6 pr-3">Order Number</th>
                                <th class="px-3 py-3">Customer</th>
                                <th class="px-3 py-3 text-center">Placed</th>
                                <th class="px-3 py-3 text-right">Quantity</th>
                                <th class="px-3 py-3 text-right">Item Total</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="py-3 pl-3 pr-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($recentOrders as $order)
                                @php
                                    $item = $order->items->firstWhere('product_id', $product->id);
                                @endphp
                                <tr class="transition hover:bg-zinc-50/70">
                                    <td class="py-3.5 pl-6 pr-3 text-sm font-bold text-zinc-900">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-red-700">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3.5 text-sm text-zinc-600">
                                        <div class="font-medium text-zinc-900">{{ $order->user?->name ?? 'Customer' }}</div>
                                        <div class="text-xs text-zinc-400">{{ $order->user?->email ?? '—' }}</div>
                                    </td>
                                    <td class="px-3 py-3.5 text-center text-xs text-zinc-500">
                                        {{ $order->created_at->format('M d, Y') }} ({{ $order->created_at->diffForHumans() }})
                                    </td>
                                    <td class="px-3 py-3.5 text-right text-sm font-bold text-zinc-900">
                                        {{ $item?->quantity ?? 1 }} pcs
                                    </td>
                                    <td class="px-3 py-3.5 text-right text-sm font-bold text-emerald-700">
                                        ₹{{ number_format($item?->total_price ?? $product->price, 0) }}
                                    </td>
                                    <td class="px-3 py-3.5 text-center">
                                        @if($order->status === 'completed')
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Completed</span>
                                        @elseif($order->status === 'processing')
                                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">Processing</span>
                                        @elseif($order->status === 'pending')
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                        @else
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-600">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 pl-3 pr-6 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-200">
                                            View Order →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</x-admin.layout>

