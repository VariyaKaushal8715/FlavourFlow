<x-admin.layout title="Dashboard">
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-950">Overview</h1>
        <p class="mt-2 text-sm text-zinc-500">Real-time metrics and performance for your business.</p>

        <!-- Stats Grid -->
        <dl class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-xl bg-white px-4 pb-12 pt-5 shadow-sm ring-1 ring-zinc-200 sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-lg bg-zinc-900 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-zinc-500">Total Revenue</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-zinc-950">@money($totalRevenue)</p>
                </dd>
            </div>
            
            <div class="relative overflow-hidden rounded-xl bg-white px-4 pb-12 pt-5 shadow-sm ring-1 ring-zinc-200 sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-lg bg-zinc-900 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-zinc-500">Total Orders</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-zinc-950">{{ $totalOrders }}</p>
                </dd>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white px-4 pb-12 pt-5 shadow-sm ring-1 ring-zinc-200 sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-lg bg-zinc-900 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-zinc-500">Total Customers</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-zinc-950">{{ $customersCount }}</p>
                </dd>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white px-4 pb-12 pt-5 shadow-sm ring-1 ring-zinc-200 sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-lg bg-zinc-900 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-zinc-500">Wishlist Saves</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-zinc-950">{{ $wishlistCount }}</p>
                </dd>
            </div>
        </dl>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            
            <!-- Products & Inventory Overview -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <h3 class="font-semibold text-zinc-950">Catalog & Inventory</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-zinc-50 p-4 ring-1 ring-zinc-200">
                            <p class="text-sm font-medium text-zinc-500">Total Products</p>
                            <p class="mt-1 text-2xl font-semibold text-zinc-950">{{ $inventory->total }}</p>
                            <p class="mt-1 text-xs text-zinc-500">{{ $inventory->active }} active</p>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 ring-1 ring-zinc-200">
                            <p class="text-sm font-medium text-zinc-500">Low Stock / Out</p>
                            <p class="mt-1 text-2xl font-semibold text-red-600">{{ $inventory->low_stock + $inventory->out_of_stock }}</p>
                            <p class="mt-1 text-xs text-zinc-500">{{ $inventory->out_of_stock }} entirely out</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-zinc-900 transition hover:text-zinc-600">View products &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <h3 class="font-semibold text-zinc-950">Top Categories</h3>
                </div>
                <div class="p-6">
                    <ul class="divide-y divide-zinc-100">
                        @forelse($bestCategories as $cat)
                        <li class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <span class="text-sm font-medium text-zinc-950 capitalize">{{ $cat->category }}</span>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600">{{ $cat->count }} items</span>
                        </li>
                        @empty
                        <li class="py-3 text-sm text-zinc-500">No categories found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Recent Orders placeholder -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200 lg:col-span-2">
                <div class="border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-semibold text-zinc-950">Recent Orders</h3>
                    <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10">Coming Soon</span>
                </div>
                <div class="p-6 text-center text-zinc-500 py-12">
                    <svg class="mx-auto h-12 w-12 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-zinc-900">No orders yet</h3>
                    <p class="mt-1 text-sm text-zinc-500">Order management is being developed.</p>
                </div>
            </div>

        </div>
    </div>
</x-admin.layout>
