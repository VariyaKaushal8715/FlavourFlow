<x-admin.layout title="{{ ucfirst($category) }} - Category Analytics">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <a href="{{ route('admin.categories.index') }}" class="text-zinc-600 hover:text-red-700 transition">
                Categories
            </a>
            <span>/</span>
            <span class="text-zinc-900 capitalize">{{ $category }}</span>
        </div>

        {{-- Category Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-md bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-700">Category Classification</span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl capitalize">{{ $category }}</h1>
                <p class="mt-1 text-sm text-zinc-500">All spice blends, sales contributions, and stock allocations under {{ $category }}.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    ← Back to All Categories
                </a>
            </div>
        </div>

        {{-- Category Metrics --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Products</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ $stats['total_products'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">{{ $stats['active_products'] }} active in catalog</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Stock On Hand</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ number_format($stats['total_stock']) }} units</p>
                <p class="mt-1 text-xs text-zinc-400">Available warehouse inventory</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Low Stock Warnings</p>
                <p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['low_stock'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">At or below reorder limit</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Out of Stock</p>
                <p class="mt-2 text-2xl font-bold text-rose-700">{{ $stats['out_of_stock'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">Requires immediate attention</p>
            </div>
        </div>

        {{-- Category Products Table --}}
        <div class="mt-6 rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950">Products in {{ ucfirst($category) }}</h2>
                    <p class="text-xs text-zinc-500">Ranked by sales performance</p>
                </div>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                    {{ $products->total() }} Items
                </span>
            </div>

            @if($products->isEmpty())
                <div class="p-12 text-center text-sm text-zinc-500">
                    No products assigned to this category yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200">
                        <thead>
                            <tr class="bg-zinc-50/75 text-left text-xs font-semibold text-zinc-500">
                                <th class="py-3.5 pl-6 pr-3">Product</th>
                                <th class="px-3 py-3.5 text-right">Price</th>
                                <th class="px-3 py-3.5 text-right">Units Sold</th>
                                <th class="px-3 py-3.5 text-right">Revenue</th>
                                <th class="px-3 py-3.5 text-center">Available Stock</th>
                                <th class="px-3 py-3.5 text-center">Status</th>
                                <th class="py-3.5 pl-3 pr-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($products as $product)
                                <tr class="transition hover:bg-zinc-50/70">
                                    <td class="py-4 pl-6 pr-3">
                                        <a href="{{ route('admin.analytics.products.show', $product) }}" class="flex items-center gap-3">
                                            @if($product->image_path)
                                                <img class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-zinc-200" src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 ring-1 ring-zinc-200">
                                                    <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-zinc-900 hover:text-red-700 transition">{{ $product->name }}</p>
                                                <p class="text-xs text-zinc-400">SKU: {{ $product->sku ?? '—' }} · {{ $product->unit }}</p>
                                            </div>
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
                                    <td class="px-3 py-4 text-center text-sm font-semibold text-zinc-900">
                                        {{ $product->quantity }} units
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        @if($product->quantity === 0)
                                            <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Out</span>
                                        @elseif($product->quantity <= $product->low_stock_threshold)
                                            <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">Low Stock</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Healthy</span>
                                        @endif
                                    </td>
                                    <td class="py-4 pl-3 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.analytics.products.show', $product) }}" class="inline-flex items-center rounded-md bg-zinc-900 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-zinc-800">
                                                Analytics →
                                            </a>
                                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50">
                                                Edit
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

