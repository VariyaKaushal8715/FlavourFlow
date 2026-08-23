<x-admin.layout title="Inventory Control">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <span class="text-zinc-900">Inventory Control & Stock Alerts</span>
        </div>

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Inventory Control</h1>
                <p class="mt-1 text-sm text-zinc-500">Real-time stock levels, restock alerts, and warehouse thresholds.</p>
            </div>
            <div>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-950 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                    + Add New Product
                </a>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Units In Stock</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ number_format($stats['total_units']) }}</p>
                <p class="mt-1 text-xs text-zinc-400">Across {{ $stats['total_items'] }} catalog products</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Healthy Stock</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['healthy'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">Above alert threshold</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Low Stock Warnings</p>
                <p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['low_stock'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">At or below reorder point</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Out of Stock</p>
                <p class="mt-2 text-2xl font-bold text-rose-700">{{ $stats['out_of_stock'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">Requires urgent restock</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="mt-6 grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_12rem_12rem_auto]">
            <div>
                <label for="search" class="sr-only">Search</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search by product name or SKU..."
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
            </div>

            <div>
                <label for="status" class="sr-only">Stock Status</label>
                <select
                    id="status"
                    name="status"
                    class="h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                >
                    <option value="all" @selected($filters['status'] === 'all')>All Stock Levels</option>
                    <option value="healthy" @selected($filters['status'] === 'healthy')>Healthy Stock</option>
                    <option value="low_stock" @selected($filters['status'] === 'low_stock')>Low Stock Alert</option>
                    <option value="out_of_stock" @selected($filters['status'] === 'out_of_stock')>Out of Stock</option>
                </select>
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

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-950 px-4 text-xs font-semibold text-white transition hover:bg-zinc-800">
                    Filter
                </button>
                @if($filters['search'] || $filters['status'] !== 'all' || $filters['category'] !== 'all')
                    <a href="{{ route('admin.inventory.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Inventory Table --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Stock Roster</h2>
                    <p class="text-xs text-zinc-500">Real-time inventory levels</p>
                </div>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                    {{ $products->total() }} Products
                </span>
            </div>

            @if($products->isEmpty())
                <div class="p-12 text-center text-sm text-zinc-500">
                    No products match your inventory criteria.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3.5 pl-6 pr-3">Product</th>
                                <th class="px-3 py-3.5">Category</th>
                                <th class="px-3 py-3.5 text-right">Price</th>
                                <th class="px-3 py-3.5 text-center">Available Stock</th>
                                <th class="px-3 py-3.5 text-center">Reorder Threshold</th>
                                <th class="px-3 py-3.5 text-center">Health Status</th>
                                <th class="py-3.5 pl-3 pr-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($products as $product)
                                <tr class="transition hover:bg-zinc-50/70">
                                    <td class="py-4 pl-6 pr-3">
                                        <div class="flex items-center gap-3">
                                            @if($product->image_path)
                                                <img class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-zinc-200" src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 ring-1 ring-zinc-200">
                                                    <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.analytics.products.show', $product) }}" class="font-semibold text-sm text-zinc-900 hover:text-red-700 transition">
                                                    {{ $product->name }}
                                                </a>
                                                <p class="text-xs text-zinc-400">SKU: {{ $product->sku ?? '—' }} · {{ $product->unit }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-xs font-medium text-zinc-600 capitalize">
                                        {{ $product->category }}
                                    </td>
                                    <td class="px-3 py-4 text-right text-sm font-medium text-zinc-700">
                                        ₹{{ number_format($product->price, 0) }}
                                    </td>
                                    <td class="px-3 py-4 text-center text-sm font-bold text-zinc-950">
                                        {{ $product->quantity }} units
                                    </td>
                                    <td class="px-3 py-4 text-center text-xs font-semibold text-zinc-500">
                                        {{ $product->low_stock_threshold }} units
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        @if($product->quantity === 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span> Out of Stock
                                            </span>
                                        @elseif($product->quantity <= $product->low_stock_threshold)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Low Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Healthy
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 pl-3 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center rounded-md bg-zinc-900 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-zinc-800">
                                                Update Stock
                                            </a>
                                            <a href="{{ route('admin.analytics.products.show', $product) }}" class="inline-flex items-center rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50">
                                                Analytics
                                            </a>
                                        </div>
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

