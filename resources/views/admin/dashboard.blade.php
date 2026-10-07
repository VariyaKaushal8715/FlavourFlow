<x-admin.layout title="Dashboard">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8" x-data="{
        chartRange: '7',
        chartType: 'revenue',
        data7: @js($chartData7Days),
        data30: @js($chartData30Days),
        chartInstance: null,
        init() {
            this.$nextTick(() => this.updateChart());
        },
        updateChart() {
            const data = this.chartRange === '7' ? this.data7 : this.data30;
            const labels = data.map(d => d.date);
            const isRevenue = this.chartType === 'revenue';
            const values = data.map(d => isRevenue ? d.revenue : d.orders);
            const color = isRevenue ? '#b42318' : '#2563eb';
            const bgColor = isRevenue ? 'rgba(180, 35, 24, 0.08)' : 'rgba(37, 99, 235, 0.08)';

            const ctx = document.getElementById('analyticsChart');
            if (!ctx) return;

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: isRevenue ? 'Revenue (₹)' : 'Orders',
                        data: values,
                        borderColor: color,
                        backgroundColor: bgColor,
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: color,
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: color,
                        pointHoverBorderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#18181b',
                            titleColor: '#ffffff',
                            bodyColor: '#e4e4e7',
                            padding: 10,
                            borderRadius: 8,
                            callbacks: {
                                label: (item) => isRevenue
                                    ? ' Revenue: ₹' + new Intl.NumberFormat('en-IN').format(item.raw)
                                    : ' Orders: ' + new Intl.NumberFormat('en-IN').format(item.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f4f4f5' },
                            ticks: {
                                font: { size: 11 },
                                color: '#71717a',
                                callback: (v) => isRevenue
                                    ? '₹' + new Intl.NumberFormat('en-IN', { notation: 'compact' }).format(v)
                                    : v
                            },
                            border: { dash: [4, 4] }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#71717a' }
                        }
                    }
                }
            });
        }
    }">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                        Real-time Database Analytics
                    </span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Executive Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-500">Live operational overview, revenue tracking, and inventory health.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.analytics.sales') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    Sales Analytics
                </a>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    Manage Orders
                </a>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800 active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Product
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- KPI Cards Row --}}
        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Revenue --}}
            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-zinc-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Revenue</span>
                    <div class="rounded-lg bg-red-50 p-2 text-red-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">₹{{ number_format($totalRevenue, 0) }}</p>
                    @if(!is_null($revenueGrowth))
                        <span @class([
                            'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold',
                            'bg-emerald-50 text-emerald-700' => $revenueGrowth >= 0,
                            'bg-rose-50 text-rose-700' => $revenueGrowth < 0,
                        ])>
                            {{ $revenueGrowth >= 0 ? '↑ +'.$revenueGrowth : '↓ '.$revenueGrowth }}%
                        </span>
                    @endif
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 text-xs text-zinc-500">
                    <span>Today: <strong class="font-semibold text-zinc-800">₹{{ number_format($todayRevenue, 0) }}</strong></span>
                    <span>Avg/Order: <strong class="font-semibold text-zinc-800">₹{{ number_format($averageOrderValue, 0) }}</strong></span>
                </div>
            </div>

            {{-- Orders --}}
            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-zinc-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Orders</span>
                    <div class="rounded-lg bg-blue-50 p-2 text-blue-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">{{ number_format($totalOrders) }}</p>
                    @if(!is_null($ordersGrowth))
                        <span @class([
                            'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold',
                            'bg-emerald-50 text-emerald-700' => $ordersGrowth >= 0,
                            'bg-rose-50 text-rose-700' => $ordersGrowth < 0,
                        ])>
                            {{ $ordersGrowth >= 0 ? '↑ +'.$ordersGrowth : '↓ '.$ordersGrowth }}%
                        </span>
                    @endif
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 text-xs text-zinc-500">
                    <span>Completed: <strong class="font-semibold text-emerald-700">{{ $completedOrders }}</strong></span>
                    <span>Pending: <strong class="font-semibold text-amber-700">{{ $pendingOrders }}</strong></span>
                </div>
            </div>

            {{-- Customers --}}
            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-zinc-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Registered Customers</span>
                    <div class="rounded-lg bg-emerald-50 p-2 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">{{ number_format($customersCount) }}</p>
                    @if($newCustomersThisWeek > 0)
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                            +{{ $newCustomersThisWeek }} new
                        </span>
                    @endif
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 text-xs text-zinc-500">
                    <span>Wishlist Saves: <strong class="font-semibold text-zinc-800">{{ $wishlistCount }}</strong></span>
                    <span>Engagement: <strong class="font-semibold text-zinc-800">High</strong></span>
                </div>
            </div>

            {{-- Products & Stock Health --}}
            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-zinc-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Catalog Health</span>
                    <div class="rounded-lg bg-amber-50 p-2 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">{{ $inventory->total }}</p>
                    <span class="text-xs font-medium text-zinc-500">Products ({{ $inventory->active }} active)</span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 text-xs">
                    <span class="text-amber-700">Low Stock: <strong>{{ $inventory->low_stock }}</strong></span>
                    <span class="text-red-700">Out of Stock: <strong>{{ $inventory->out_of_stock }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Order Status Strip --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50/30">
                <span class="h-3 w-3 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-zinc-500">Completed Orders</p>
                    <p class="text-lg font-bold text-zinc-950">{{ $completedOrders }}</p>
                </div>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:border-amber-300 hover:bg-amber-50/30">
                <span class="h-3 w-3 rounded-full bg-amber-400 ring-4 ring-amber-100"></span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-zinc-500">Pending Orders</p>
                    <p class="text-lg font-bold text-zinc-950">{{ $pendingOrders }}</p>
                </div>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/30">
                <span class="h-3 w-3 rounded-full bg-blue-500 ring-4 ring-blue-100"></span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-zinc-500">Processing</p>
                    <p class="text-lg font-bold text-zinc-950">{{ $processingOrders }}</p>
                </div>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50">
                <span class="h-3 w-3 rounded-full bg-zinc-300 ring-4 ring-zinc-100"></span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-zinc-500">Cancelled</p>
                    <p class="text-lg font-bold text-zinc-950">{{ $cancelledOrders }}</p>
                </div>
            </a>
        </div>

        {{-- Main Analytics Grid: Chart + Categories --}}
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Interactive Trend Chart (2 cols) --}}
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-zinc-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-zinc-950">Performance Timeline</h2>
                        <p class="text-xs text-zinc-500">Sales and order volume trajectory across the store.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Metric toggle --}}
                        <div class="inline-flex rounded-lg bg-zinc-100 p-0.5 text-xs font-semibold">
                            <button
                                type="button"
                                @click="chartType = 'revenue'; updateChart()"
                                :class="chartType === 'revenue' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-500 hover:text-zinc-900'"
                                class="rounded-md px-3 py-1 transition"
                            >
                                Revenue
                            </button>
                            <button
                                type="button"
                                @click="chartType = 'orders'; updateChart()"
                                :class="chartType === 'orders' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-500 hover:text-zinc-900'"
                                class="rounded-md px-3 py-1 transition"
                            >
                                Orders
                            </button>
                        </div>

                        {{-- Period toggle --}}
                        <div class="inline-flex rounded-lg bg-zinc-100 p-0.5 text-xs font-semibold">
                            <button
                                type="button"
                                @click="chartRange = '7'; updateChart()"
                                :class="chartRange === '7' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-500 hover:text-zinc-900'"
                                class="rounded-md px-3 py-1 transition"
                            >
                                7 Days
                            </button>
                            <button
                                type="button"
                                @click="chartRange = '30'; updateChart()"
                                :class="chartRange === '30' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-500 hover:text-zinc-900'"
                                class="rounded-md px-3 py-1 transition"
                            >
                                30 Days
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="h-[280px] w-full">
                        <canvas id="analyticsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Category Breakdown (1 col) --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-950">Top Categories</h2>
                        <p class="text-xs text-zinc-500">Distribution by volume</p>
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 transition">
                        View All →
                    </a>
                </div>

                <ul class="divide-y divide-zinc-100 p-6">
                    @forelse($bestCategories as $i => $cat)
                        <li class="py-3 first:pt-0 last:pb-0">
                            <a href="{{ route('admin.categories.show', ['category' => $cat->category]) }}" class="group block">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-zinc-900 capitalize group-hover:text-red-700 transition">
                                        {{ $cat->category }}
                                    </span>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-zinc-900">₹{{ number_format($cat->category_revenue, 0) }}</span>
                                        <span class="ml-1 text-xs text-zinc-400">({{ $cat->count }} items)</span>
                                    </div>
                                </div>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 {{ $i === 0 ? 'bg-red-600' : ($i === 1 ? 'bg-amber-500' : 'bg-zinc-400') }}"
                                        style="width: {{ min(100, round(($cat->count / $maxCategoryCount) * 100)) }}%"
                                    ></div>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-zinc-400">No categories found.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Second Grid: Top Selling Products + Inventory Alerts --}}
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Top Selling Products (2 cols) --}}
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-950">Top Selling Products</h2>
                        <p class="text-xs text-zinc-500">Ranked by verified units sold and gross revenue.</p>
                    </div>
                    <a href="{{ route('admin.analytics.sales') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 transition">
                        View All Sales Analytics →
                    </a>
                </div>

                @if($topProducts->isEmpty())
                    <div class="p-8 text-center text-sm text-zinc-500">No sales data recorded yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-100">
                            <thead>
                                <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                    <th class="py-3 pl-6 pr-3">Product</th>
                                    <th class="px-3 py-3 text-right">Units Sold</th>
                                    <th class="px-3 py-3 text-right">Gross Revenue</th>
                                    <th class="px-3 py-3 text-center">Stock</th>
                                    <th class="py-3 pl-3 pr-6 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach($topProducts as $p)
                                    <tr class="group transition hover:bg-zinc-50/60">
                                        <td class="py-3.5 pl-6 pr-3">
                                            <a href="{{ route('admin.analytics.products.show', $p) }}" class="flex items-center gap-3">
                                                @if($p->image_path)
                                                    <img class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-zinc-200" src="{{ asset($p->image_path) }}" alt="{{ $p->name }}">
                                                @else
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 ring-1 ring-zinc-200">
                                                        <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-zinc-900 group-hover:text-red-700 transition">{{ $p->name }}</p>
                                                    <p class="text-xs text-zinc-400">SKU: {{ $p->sku ?? '—' }} · {{ $p->category }}</p>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-3 py-3.5 text-right text-sm font-bold text-zinc-900">
                                            {{ number_format($p->units_sold) }}
                                        </td>
                                        <td class="px-3 py-3.5 text-right text-sm font-bold text-zinc-900">
                                            ₹{{ number_format($p->revenue_generated, 0) }}
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            @if($p->quantity === 0)
                                                <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">Out</span>
                                            @elseif($p->quantity <= $p->low_stock_threshold)
                                                <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ $p->quantity }} left</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ $p->quantity }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 pl-3 pr-6 text-right">
                                            <a href="{{ route('admin.analytics.products.show', $p) }}" class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-200">
                                                Analytics →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Inventory Alerts Widget (1 col) --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-950">Inventory Alerts</h2>
                        <p class="text-xs text-zinc-500">Restock priorities</p>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 transition">
                        Inventory →
                    </a>
                </div>

                @if($inventoryAlerts->isEmpty())
                    <div class="p-6 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-zinc-900">All Stock Levels Healthy</p>
                        <p class="text-xs text-zinc-500">No products are currently low or out of stock.</p>
                    </div>
                @else
                    <ul class="divide-y divide-zinc-100 p-6">
                        @foreach($inventoryAlerts as $item)
                            <li class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('admin.products.edit', $item) }}" class="truncate text-sm font-semibold text-zinc-900 hover:text-red-700 transition block">
                                            {{ $item->name }}
                                        </a>
                                        <p class="text-xs text-zinc-400">SKU: {{ $item->sku ?? '—' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($item->quantity === 0)
                                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Out of Stock</span>
                                        @else
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">{{ $item->quantity }} remaining</span>
                                        @endif
                                        <a href="{{ route('admin.products.edit', $item) }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Recent Orders Section --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Recent Orders</h2>
                    <p class="text-xs text-zinc-500">Latest customer purchases with real-time status.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 transition">
                    View All Orders →
                </a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="p-8 text-center text-sm text-zinc-500">No orders recorded in database.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-100">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3 pl-6 pr-3">Order Number</th>
                                <th class="px-3 py-3">Customer</th>
                                <th class="px-3 py-3 text-center">Placed</th>
                                <th class="px-3 py-3 text-center">Payment</th>
                                <th class="px-3 py-3 text-center">Fulfillment</th>
                                <th class="px-3 py-3 text-right">Amount</th>
                                <th class="py-3 pl-3 pr-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($recentOrders as $order)
                                <tr class="transition hover:bg-zinc-50/60">
                                    <td class="py-3.5 pl-6 pr-3 text-sm font-bold text-zinc-900">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-zinc-900 hover:text-red-700 transition">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3.5 text-sm text-zinc-600">
                                        <div class="font-medium text-zinc-900">{{ $order->user?->name ?? 'Guest / Deleted' }}</div>
                                        <div class="text-xs text-zinc-400">{{ $order->user?->email ?? '—' }}</div>
                                    </td>
                                    <td class="px-3 py-3.5 text-center text-xs text-zinc-500">
                                        {{ $order->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-3 py-3.5 text-center">
                                        @if($order->payment_status === 'paid')
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Paid</span>
                                        @elseif($order->payment_status === 'pending')
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ ucfirst($order->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-center">
                                        @if($order->status === 'completed')
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Completed</span>
                                        @elseif($order->status === 'processing')
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">Processing</span>
                                        @elseif($order->status === 'pending')
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-600">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-right text-sm font-bold text-zinc-900">
                                        ₹{{ number_format($order->total_amount, 0) }}
                                    </td>
                                    <td class="py-3.5 pl-3 pr-6 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-200">
                                            Details →
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

    {{-- Chart.js via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</x-admin.layout>
