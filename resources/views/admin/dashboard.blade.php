<x-admin.layout title="Products">
    <x-admin.header active="products" />

    <main class="mx-auto grid w-full max-w-[90rem] gap-8 px-6 py-8 lg:grid-cols-[24rem_1fr] lg:px-8">
        <section class="self-start rounded-lg border border-zinc-200 bg-white p-6">
            <p class="text-sm font-semibold text-red-700">New product</p>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-950">Add to catalog</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-600">Create the product, set stock levels, and control whether it appears on the storefront.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    Please review the highlighted fields.
                </div>
            @endif

            <x-admin.product-form
                :action="route('admin.products.store')"
                submit-label="Add product"
            />
        </section>

        <section class="min-w-0">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Catalog control</p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950">Products</h2>
                    <p class="mt-2 text-sm text-zinc-600">Manage pricing, visibility, inventory, and homepage placement.</p>
                </div>
                <a class="text-sm font-semibold text-zinc-700 transition hover:text-red-700" href="{{ route('home') }}" target="_blank" rel="noreferrer">View live site &rarr;</a>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Total products</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950">{{ $inventory->total }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Active</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $inventory->active }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Low stock</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $inventory->low_stock }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-zinc-500">Out of stock</p>
                    <p class="mt-2 text-2xl font-semibold text-red-700">{{ $inventory->out_of_stock }}</p>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form class="mt-6 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 md:grid-cols-[1fr_11rem_11rem_auto]" method="GET" action="{{ route('admin.index') }}">
                <div>
                    <label class="sr-only" for="search">Search products</label>
                    <input class="admin-input mt-0" id="search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="Search name, SKU, category">
                </div>
                <div>
                    <label class="sr-only" for="status">Stock status</label>
                    <select class="admin-input mt-0" id="status" name="status">
                        <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
                        <option value="active" @selected($filters['status'] === 'active')>Active</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                        <option value="low_stock" @selected($filters['status'] === 'low_stock')>Low stock</option>
                        <option value="out_of_stock" @selected($filters['status'] === 'out_of_stock')>Out of stock</option>
                    </select>
                </div>
                <div>
                    <label class="sr-only" for="sort">Sort products</label>
                    <select class="admin-input mt-0" id="sort" name="sort">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                        <option value="price_high" @selected($filters['sort'] === 'price_high')>Price: high to low</option>
                        <option value="price_low" @selected($filters['sort'] === 'price_low')>Price: low to high</option>
                        <option value="stock_low" @selected($filters['sort'] === 'stock_low')>Lowest stock</option>
                        <option value="priority" @selected($filters['sort'] === 'priority')>Hero priority</option>
                    </select>
                </div>
                <button class="min-h-12 rounded-lg bg-zinc-950 px-5 text-sm font-semibold text-white transition hover:bg-red-700" type="submit">Apply</button>
            </form>

            @if ($filters['search'] || $filters['status'] !== 'all' || $filters['sort'] !== 'newest')
                <div class="mt-3 flex justify-end">
                    <a class="text-sm font-semibold text-zinc-500 transition hover:text-red-700" href="{{ route('admin.index') }}">Clear filters</a>
                </div>
            @endif

            <div class="mt-6 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                @forelse ($products as $product)
                    <article class="grid gap-4 border-b border-zinc-100 p-4 last:border-b-0 md:grid-cols-[5rem_1fr_auto] md:items-center">
                        <img class="aspect-square h-20 w-20 rounded-lg object-cover" src="{{ asset($product->image_path ?: 'images/flavourflow-mark.png') }}" alt="{{ $product->name }}">

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-zinc-950">{{ $product->name }}</h3>
                                @if ($product->is_featured)
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Hero</span>
                                @endif
                                @unless ($product->is_active)
                                    <span class="rounded-lg bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600">Inactive</span>
                                @endunless
                                @if ($product->quantity === 0)
                                    <span class="rounded-lg bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Out of stock</span>
                                @elseif ($product->isLowStock())
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Low stock</span>
                                @endif
                            </div>

                            <p class="mt-1 truncate text-sm text-zinc-500">
                                {{ $product->sku }} &middot; {{ $product->category }} &middot; {{ $product->unit }}
                            </p>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>

                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold text-zinc-600">
                                <span>Stock: {{ $product->quantity }}</span>
                                <span>Alert at: {{ $product->low_stock_threshold }}</span>
                                <span>Priority: {{ $product->priority }}</span>
                                <span>Rating: {{ $product->rating }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-5 md:flex-col md:items-end">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-zinc-950">Rs. {{ $product->price }}</p>
                                @if ($product->compare_at_price)
                                    <p class="mt-1 text-xs text-zinc-400 line-through">Rs. {{ $product->compare_at_price }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a class="inline-flex min-h-10 items-center justify-center rounded-lg border border-zinc-300 px-4 text-sm font-semibold text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" href="{{ route('admin.products.edit', $product) }}">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm-delete data-item-name="{{ $product->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex min-h-10 items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50" type="submit">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-10 text-center">
                        <p class="font-semibold text-zinc-950">No matching products</p>
                        <p class="mt-2 text-sm text-zinc-500">Change the filters or add a new product.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </section>
    </main>
</x-admin.layout>
