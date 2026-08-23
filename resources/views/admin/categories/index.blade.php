<x-admin.layout title="Categories">
    <main class="mx-auto w-full max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb & Back --}}
        <div class="mb-4 flex items-center gap-2 text-xs font-semibold text-zinc-500">
            <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1 text-zinc-600 hover:text-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Dashboard
            </a>
            <span>/</span>
            <span class="text-zinc-900">Categories</span>
        </div>

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">Categories Performance</h1>
                <p class="mt-1 text-sm text-zinc-500">Overview of spice blend categories, sales contribution, and product allocation.</p>
            </div>
            <div>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Manage Products →
                </a>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Categories</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ $stats['total_categories'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">Spice blend classifications</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Catalog Products</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ $stats['total_products'] }}</p>
                <p class="mt-1 text-xs text-zinc-400">Assigned across categories</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Total Revenue</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">₹{{ number_format($stats['total_revenue'], 0) }}</p>
                <p class="mt-1 text-xs text-zinc-400">From verified completed orders</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Units Sold</p>
                <p class="mt-2 text-2xl font-bold text-zinc-950">{{ number_format($stats['total_units_sold']) }}</p>
                <p class="mt-1 text-xs text-zinc-400">Across all catalog lines</p>
            </div>
        </div>

        {{-- Categories Grid --}}
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($categories as $category)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-zinc-300 hover:shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="rounded-md bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 capitalize">
                                {{ $category->category }}
                            </span>
                            <span class="text-xs font-semibold text-zinc-400">
                                {{ $category->total_products }} Products
                            </span>
                        </div>

                        <h2 class="mt-4 text-xl font-bold text-zinc-950 capitalize">{{ $category->category }}</h2>

                        <div class="mt-4 grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-3 text-xs">
                            <div>
                                <p class="text-zinc-500 font-medium">Category Revenue</p>
                                <p class="mt-1 text-sm font-bold text-zinc-900">₹{{ number_format($category->total_revenue, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500 font-medium">Units Sold</p>
                                <p class="mt-1 text-sm font-bold text-zinc-900">{{ number_format($category->units_sold) }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500 font-medium">Stock On Hand</p>
                                <p class="mt-1 text-sm font-bold text-zinc-900">{{ number_format($category->total_stock) }} units</p>
                            </div>
                            <div>
                                <p class="text-zinc-500 font-medium">Active Items</p>
                                <p class="mt-1 text-sm font-bold text-emerald-700">{{ $category->active_products }} of {{ $category->total_products }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between">
                        <a href="{{ route('admin.categories.show', ['category' => $category->category]) }}" class="text-xs font-bold text-red-700 hover:text-red-800 transition">
                            Category Analytics & Items →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-zinc-200 bg-white p-12 text-center text-sm text-zinc-500">
                    No categories found in database.
                </div>
            @endforelse
        </div>
    </main>
</x-admin.layout>

