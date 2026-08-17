@props([
    'products',
    'sectionId' => 'products',
    'eyebrow' => 'The collection',
    'title' => 'Flavour worth slowing down for.',
    'description' => 'Carefully sourced, freshly packed, and balanced for the food you cook every day. Choose a blend and bring a deeper aroma to the table.',
    'tone' => 'default',
    'wishlistProductIds' => [],
    'sort' => 'featured',
])

<section
    id="{{ $sectionId }}"
    @class([
        'bg-zinc-50 py-20 sm:py-24' => $tone === 'default',
        'bg-brand-surface py-8 sm:py-10' => $tone === 'offer',
    ])
>
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <div @class([
            'grid gap-6 border-b border-zinc-200 lg:grid-cols-[0.8fr_1.2fr_auto] lg:items-end',
            'pb-10' => $tone === 'default',
            'pb-6' => $tone === 'offer',
        ])>
            <div data-reveal>
                <p class="text-sm font-semibold text-brand-primary">{{ $eyebrow }}</p>
                <h2 @class([
                    'mt-3 text-3xl font-semibold leading-tight text-zinc-950',
                    'sm:text-5xl' => $tone === 'default',
                    'sm:text-4xl' => $tone === 'offer',
                ])>{{ $title }}</h2>
            </div>
            <p class="max-w-2xl text-base leading-8 text-zinc-600 lg:justify-self-end" data-reveal>
                {{ $description }}
            </p>
            @if ($tone === 'default')
                <form class="flex flex-col gap-2 lg:justify-self-end" method="GET" action="{{ route('home') }}" data-reveal>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500" for="product-sort">{{ __('ui.sort_products') }}</label>
                    @foreach (request()->except('sort') as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="flex items-center gap-3">
                        <select
                            id="product-sort"
                            name="sort"
                            class="min-h-12 rounded-lg border border-zinc-300 bg-white px-4 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"
                        >
                            <option value="featured" @selected($sort === 'featured')>{{ __('ui.featured') }}</option>
                            <option value="rating" @selected($sort === 'rating')>{{ __('ui.top_rated') }}</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>{{ __('ui.price_low_high') }}</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>{{ __('ui.price_high_low') }}</option>
                            <option value="name" @selected($sort === 'name')>{{ __('ui.name_az') }}</option>
                        </select>
                        <button class="inline-flex min-h-12 items-center justify-center rounded-lg bg-zinc-950 px-4 text-sm font-semibold text-white transition hover:bg-red-700" type="submit">
                            {{ __('ui.apply') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <div @class([
            'grid gap-5 sm:grid-cols-2 lg:grid-cols-3',
            'mt-10' => $tone === 'default',
            'mt-6' => $tone === 'offer',
        ])>
            @foreach ($products as $index => $product)
                <article class="product-tile group relative overflow-hidden rounded-lg border border-zinc-200 bg-white" data-reveal data-reveal-delay="{{ ($index % 3) * 90 }}">
                    <a class="block h-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500" href="{{ $product['url'] ?? '#products' }}" aria-label="View {{ $product['name'] }} details">
                        <div class="relative aspect-[4/3] overflow-hidden bg-zinc-900">
                            <img class="h-full w-full object-cover transition duration-700 group-hover:scale-105" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}">
                            <span class="absolute left-4 top-4 rounded-lg bg-white/90 px-3 py-2 text-xs font-semibold text-zinc-950 backdrop-blur">
                                {{ $product['badge'] }}
                            </span>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-xs font-semibold uppercase text-brand-primary">{{ $product['category'] }}</p>
                                <p class="text-xs font-semibold text-emerald-700">{{ $product['metric'] }}</p>
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-zinc-950 transition group-hover:text-brand-primary">{{ $product['name'] }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-7 text-zinc-600">{{ $product['description'] }}</p>
                            <div class="mt-5 flex items-end justify-between gap-4 border-t border-zinc-100 pt-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-zinc-950">{{ $product['price'] }}</p>
                                        @if ($product['compare_at_price'])
                                            <p class="text-xs text-zinc-400 line-through">{{ $product['compare_at_price'] }}</p>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $product['unit'] }}</p>
                                </div>
                                <p @class([
                                    'text-xs font-semibold',
                                    'text-emerald-700' => $product['in_stock'],
                                    'text-red-700' => ! $product['in_stock'],
                                ])>
                                    {{ $product['stock_label'] }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-xs font-semibold text-zinc-950">View full details <span class="ml-1 text-brand-primary" aria-hidden="true">&rarr;</span></p>
                                <button
                                    type="button"
                                    data-add-to-cart
                                    data-product-slug="{{ $product['slug'] }}"
                                    @disabled(! $product['in_stock'])
                                    class="inline-flex items-center justify-center rounded-md bg-zinc-950 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:pointer-events-none disabled:opacity-50"
                                >
                                    {{ __('Add to cart') }}
                                </button>
                            </div>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
