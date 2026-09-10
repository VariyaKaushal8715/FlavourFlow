@props([
    'products',
    'sectionId' => 'products',
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'tone' => 'default',
    'wishlistProductIds' => [],
    'sort' => 'featured',
    'minPrice' => null,
    'maxPrice' => null,
    'lowestPrice' => 0,
    'highestPrice' => 1000,
])

@php
    $displayEyebrow = $eyebrow ?? __('ui.collection_eyebrow');
    $displayTitle = $title ?? __('ui.collection_title');
    $displayDescription = $description ?? __('ui.collection_desc');
    $currentSort = $sort ?: 'featured';
    $lowest = (int) $lowestPrice;
    $highest = (int) $highestPrice;
    if ($highest <= $lowest) {
        $highest = $lowest + 1000;
    }
    $curMin = ($minPrice !== null && $minPrice >= $lowest) ? (int) $minPrice : $lowest;
    $curMax = ($maxPrice !== null && $maxPrice <= $highest && $maxPrice >= $lowest) ? (int) $maxPrice : $highest;

    $sortOptions = [
        'featured' => __('ui.featured'),
        'rating' => __('ui.top_rated'),
        'price_asc' => __('ui.price_low_high'),
        'price_desc' => __('ui.price_high_low'),
        'name' => __('ui.name_az'),
        'newest' => __('ui.newest_arrivals'),
        'price_range' => __('ui.price_range'),
    ];

    $hasActiveFilters = ($currentSort !== 'featured') || ($minPrice !== null && $minPrice > $lowest) || ($maxPrice !== null && $maxPrice < $highest);

    $triggerLabel = $sortOptions[$currentSort] ?? $sortOptions['featured'];
    if ($currentSort === 'price_range') {
        $triggerLabel = __('ui.price_range') . ': ₹' . $curMin . ' – ₹' . $curMax;
    }
@endphp

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
                <p class="text-sm font-semibold text-brand-primary">{{ $displayEyebrow }}</p>
                <h2 @class([
                    'mt-3 text-3xl font-semibold leading-tight text-zinc-950',
                    'sm:text-5xl' => $tone === 'default',
                    'sm:text-4xl' => $tone === 'offer',
                ])>{{ $displayTitle }}</h2>
            </div>
            <p class="max-w-2xl text-base leading-8 text-zinc-600 lg:justify-self-end" data-reveal>
                {{ $displayDescription }}
            </p>
            @if ($tone === 'default')
                <form
                    id="product-filter-form"
                    class="flex flex-col gap-2.5 lg:justify-self-end w-full lg:w-auto"
                    method="GET"
                    action="{{ route('home') }}#{{ $sectionId }}"
                    data-reveal
                >
                    @foreach (request()->except(['sort', 'min_price', 'max_price', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <div class="flex items-center justify-between gap-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500" for="custom-sort-trigger">
                            {{ __('ui.sort_products') }}
                        </label>
                        @if ($hasActiveFilters)
                            <a
                                href="{{ route('home') }}#{{ $sectionId }}"
                                class="text-xs font-semibold text-brand-primary hover:text-amber-700 underline transition-colors"
                            >
                                {{ __('ui.clear_filters') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                        <!-- Custom Animated Sort Dropdown with embedded Price Range Slider -->
                        <div class="relative w-full sm:w-72" id="sort-dropdown-container">
                            <input type="hidden" name="sort" id="product-sort-input" value="{{ $currentSort }}">
                            <input type="hidden" name="min_price" id="hidden-min-price" value="{{ $minPrice ?? '' }}">
                            <input type="hidden" name="max_price" id="hidden-max-price" value="{{ $maxPrice ?? '' }}">

                            <button
                                type="button"
                                id="custom-sort-trigger"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                class="flex w-full min-h-12 items-center justify-between rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition hover:border-amber-400 focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-amber-500/10"
                            >
                                <span id="selected-sort-label" class="truncate">{{ $triggerLabel }}</span>
                                <svg id="sort-chevron" class="ml-2 h-4 w-4 shrink-0 text-zinc-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu with animation and embedded slider -->
                            <div
                                id="sort-dropdown-menu"
                                role="listbox"
                                tabindex="-1"
                                class="absolute left-0 right-0 z-30 mt-2 origin-top rounded-2xl border border-amber-200/80 bg-white p-2 shadow-2xl transition-all duration-200 ease-out invisible opacity-0 scale-95 pointer-events-none"
                            >
                                <div class="space-y-1">
                                    @foreach ($sortOptions as $key => $label)
                                        <div
                                            role="option"
                                            data-value="{{ $key }}"
                                            aria-selected="{{ $currentSort === $key ? 'true' : 'false' }}"
                                            class="sort-option group flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 text-sm transition-all duration-150 {{ $currentSort === $key ? 'bg-amber-50/90 font-semibold text-brand-primary' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-950' }}"
                                        >
                                            <span class="flex items-center gap-2">
                                                @if ($key === 'price_range')
                                                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                                    </svg>
                                                @endif
                                                {{ $label }}
                                            </span>
                                            <svg class="h-4 w-4 text-brand-primary {{ $currentSort === $key ? 'opacity-100' : 'opacity-0 group-hover:opacity-30' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Embedded Horizontal Dual Range Slider Section -->
                                <div
                                    id="price-range-panel"
                                    class="transition-all duration-300 ease-out overflow-hidden {{ $currentSort === 'price_range' ? 'max-h-56 opacity-100 mt-2.5 pt-3 border-t border-amber-100 px-2' : 'max-h-0 opacity-0 px-2' }}"
                                >
                                    <div class="flex items-center justify-between text-xs font-semibold mb-2">
                                        <span class="text-zinc-500 uppercase tracking-wider text-[10px]">Price Filter:</span>
                                        <span class="text-brand-primary font-bold text-sm">
                                            ₹<span id="slider-min-text">{{ $curMin }}</span> – ₹<span id="slider-max-text">{{ $curMax }}</span>
                                        </span>
                                    </div>

                                    <!-- Dual range bar container -->
                                    <div class="relative w-full h-8 flex items-center my-1">
                                        <!-- Base Track -->
                                        <div class="relative h-2.5 w-full rounded-full bg-amber-100/90 border border-amber-200/80 shadow-inner">
                                            <!-- Highlight Track -->
                                            <div id="slider-highlight-bar" class="absolute inset-y-0 rounded-full bg-gradient-to-r from-red-600 via-amber-600 to-red-600 shadow-sm transition-all duration-75"></div>
                                        </div>

                                        <!-- Min Handle Input -->
                                        <input
                                            type="range"
                                            id="range-min-slider"
                                            min="{{ $lowest }}"
                                            max="{{ $highest }}"
                                            step="5"
                                            value="{{ $curMin }}"
                                            class="dual-range-input"
                                            aria-label="Minimum price"
                                        >

                                        <!-- Max Handle Input -->
                                        <input
                                            type="range"
                                            id="range-max-slider"
                                            min="{{ $lowest }}"
                                            max="{{ $highest }}"
                                            step="5"
                                            value="{{ $curMax }}"
                                            class="dual-range-input"
                                            aria-label="Maximum price"
                                        >
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] font-medium text-zinc-400 mt-1 pb-1">
                                        <span>₹{{ $lowest }}</span>
                                        <span>₹{{ $highest }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Apply & Reset Buttons -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button
                                id="filter-apply-btn"
                                class="inline-flex min-h-12 flex-1 sm:flex-initial items-center justify-center rounded-xl bg-zinc-950 px-6 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-brand-primary active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-primary"
                                type="submit"
                            >
                                {{ __('ui.apply') }}
                            </button>
                            @if ($hasActiveFilters)
                                <a
                                    href="{{ route('home') }}#{{ $sectionId }}"
                                    title="{{ __('ui.clear_filters') }}"
                                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-400 hover:bg-zinc-50 active:scale-95"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </div>

        @if (empty($products))
            <div class="mt-12 rounded-3xl border border-dashed border-amber-200 bg-amber-50/40 p-12 text-center" data-reveal>
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-brand-primary shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-950">{{ __('ui.no_products_found') }}</h3>
                <p class="mt-2 text-sm text-zinc-500">Try adjusting your price range or clearing active filters to view all spices.</p>
                <div class="mt-6">
                    <a
                        href="{{ route('home') }}#{{ $sectionId }}"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-brand-primary active:scale-95"
                    >
                        {{ __('ui.clear_filters') }}
                    </a>
                </div>
            </div>
        @else
            <div
                id="products-grid-container"
                @class([
                    'grid gap-5 sm:grid-cols-2 lg:grid-cols-3 transition-all duration-300 ease-out opacity-100',
                    'mt-10' => $tone === 'default',
                    'mt-6' => $tone === 'offer',
                ])
            >
                @foreach ($products as $index => $product)
                    @php
                        $productId = $product['id'] ?? null;
                        $isWishlisted = $productId && in_array($productId, $wishlistProductIds, true);
                    @endphp
                    <article class="product-tile group relative overflow-hidden rounded-lg border border-zinc-200 bg-white transition-all duration-300 hover:shadow-md" data-reveal data-reveal-delay="{{ ($index % 3) * 90 }}">
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
                                        {{ $product['in_stock'] ? __('ui.in_stock') : __('ui.out_of_stock') }}
                                    </p>
                                </div>
                                <div class="mt-4 flex items-center justify-between gap-2">
                                    <p class="text-xs font-semibold text-zinc-950">View full details <span class="ml-1 text-brand-primary" aria-hidden="true">&rarr;</span></p>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            data-wishlist-button
                                            data-product-id="{{ $product['id'] ?? '' }}"
                                            data-product-slug="{{ $product['slug'] ?? '' }}"
                                            data-wishlisted="{{ in_array($product['id'] ?? null, $wishlistProductIds, true) ? 'true' : 'false' }}"
                                            class="wishlist-button inline-flex h-9 w-9 items-center justify-center rounded-md border border-zinc-300 bg-white text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950"
                                            aria-label="Toggle wishlist"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            data-add-to-cart
                                            data-product-slug="{{ $product['slug'] ?? '' }}"
                                            @disabled(! $product['in_stock'])
                                            class="inline-flex items-center justify-center rounded-md bg-zinc-950 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:pointer-events-none disabled:opacity-50"
                                        >
                                            {{ __('Add to cart') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <button
                            class="wishlist-button absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-zinc-950 shadow-sm transition hover:bg-white hover:text-red-700 data-[wishlisted=true]:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="button"
                            data-wishlist-button
                            data-product-id="{{ $product['id'] ?? '' }}"
                            data-product-slug="{{ $product['slug'] ?? '' }}"
                            data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                            aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                            aria-label="{{ $isWishlisted ? 'Remove '.$product['name'].' from wishlist' : 'Add '.$product['name'].' to wishlist' }}"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z" />
                            </svg>
                        </button>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    @if ($tone === 'default')
        <style>
            .dual-range-input {
                pointer-events: none;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                left: 0;
                width: 100%;
                height: 24px;
                margin: 0;
                outline: none;
                appearance: none;
                -webkit-appearance: none;
                background: transparent;
                z-index: 20;
            }
            .dual-range-input::-webkit-slider-runnable-track {
                background: transparent;
                border: none;
                height: 100%;
            }
            .dual-range-input::-moz-range-track {
                background: transparent;
                border: none;
                height: 100%;
            }
            .dual-range-input::-webkit-slider-thumb {
                pointer-events: auto;
                appearance: none;
                -webkit-appearance: none;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: linear-gradient(135deg, #b42318 0%, #d97706 100%);
                border: 2px solid #ffffff;
                box-shadow: 0 2px 6px rgba(180, 35, 24, 0.4), 0 0 0 1px rgba(180, 35, 24, 0.15);
                cursor: grab;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .dual-range-input::-webkit-slider-thumb:hover {
                transform: scale(1.15);
                box-shadow: 0 4px 10px rgba(180, 35, 24, 0.5), 0 0 0 1px rgba(180, 35, 24, 0.3);
            }
            .dual-range-input::-webkit-slider-thumb:active {
                cursor: grabbing;
                transform: scale(1.2);
            }
            .dual-range-input::-moz-range-thumb {
                pointer-events: auto;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: linear-gradient(135deg, #b42318 0%, #d97706 100%);
                border: 2px solid #ffffff;
                box-shadow: 0 2px 6px rgba(180, 35, 24, 0.4), 0 0 0 1px rgba(180, 35, 24, 0.15);
                cursor: grab;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const trigger = document.getElementById('custom-sort-trigger');
                const menu = document.getElementById('sort-dropdown-menu');
                const chevron = document.getElementById('sort-chevron');
                const sortInput = document.getElementById('product-sort-input');
                const hiddenMinPrice = document.getElementById('hidden-min-price');
                const hiddenMaxPrice = document.getElementById('hidden-max-price');
                const selectedLabel = document.getElementById('selected-sort-label');
                const options = document.querySelectorAll('.sort-option');
                const form = document.getElementById('product-filter-form');
                const gridContainer = document.getElementById('products-grid-container');

                const priceRangePanel = document.getElementById('price-range-panel');
                const minSlider = document.getElementById('range-min-slider');
                const maxSlider = document.getElementById('range-max-slider');
                const minText = document.getElementById('slider-min-text');
                const maxText = document.getElementById('slider-max-text');
                const highlightBar = document.getElementById('slider-highlight-bar');

                const lowestBound = parseInt("{{ $lowest }}") || 0;
                const highestBound = parseInt("{{ $highest }}") || 1000;

                if (!trigger || !menu) return;

                let isOpen = false;

                const updateSliderTrack = () => {
                    if (!minSlider || !maxSlider || !highlightBar) return;
                    let v1 = parseInt(minSlider.value);
                    let v2 = parseInt(maxSlider.value);

                    if (v1 > v2) {
                        const tmp = v1;
                        v1 = v2;
                        v2 = tmp;
                    }

                    const range = Math.max(1, highestBound - lowestBound);
                    const leftPercent = Math.max(0, Math.min(100, ((v1 - lowestBound) / range) * 100));
                    const rightPercent = Math.max(0, Math.min(100, ((v2 - lowestBound) / range) * 100));
                    const widthPercent = Math.max(0, rightPercent - leftPercent);

                    highlightBar.style.left = leftPercent + '%';
                    highlightBar.style.width = widthPercent + '%';

                    if (minText) minText.textContent = v1;
                    if (maxText) maxText.textContent = v2;
                    if (hiddenMinPrice) hiddenMinPrice.value = v1;
                    if (hiddenMaxPrice) hiddenMaxPrice.value = v2;

                    if (sortInput.value === 'price_range') {
                        selectedLabel.textContent = `Price Range: ₹${v1} – ₹${v2}`;
                    }
                };

                minSlider?.addEventListener('input', () => {
                    if (parseInt(minSlider.value) > parseInt(maxSlider.value) - 5) {
                        minSlider.value = parseInt(maxSlider.value) - 5;
                    }
                    minSlider.style.zIndex = '25';
                    if (maxSlider) maxSlider.style.zIndex = '20';
                    updateSliderTrack();
                });

                maxSlider?.addEventListener('input', () => {
                    if (parseInt(maxSlider.value) < parseInt(minSlider.value) + 5) {
                        maxSlider.value = parseInt(minSlider.value) + 5;
                    }
                    maxSlider.style.zIndex = '25';
                    if (minSlider) minSlider.style.zIndex = '20';
                    updateSliderTrack();
                });

                updateSliderTrack();

                const openMenu = () => {
                    isOpen = true;
                    menu.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
                    menu.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
                    chevron?.classList.add('rotate-180');
                    trigger.setAttribute('aria-expanded', 'true');
                };

                const closeMenu = () => {
                    isOpen = false;
                    menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                    chevron?.classList.remove('rotate-180');
                    trigger.setAttribute('aria-expanded', 'false');
                    setTimeout(() => {
                        if (!isOpen) menu.classList.add('invisible');
                    }, 200);
                };

                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    isOpen ? closeMenu() : openMenu();
                });

                document.addEventListener('click', (e) => {
                    if (isOpen && !menu.contains(e.target) && !trigger.contains(e.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && isOpen) {
                        closeMenu();
                        trigger.focus();
                    }
                });

                options.forEach((opt) => {
                    opt.addEventListener('click', (e) => {
                        const val = opt.getAttribute('data-value');
                        const labelText = opt.querySelector('span')?.textContent?.trim() || '';
                        
                        sortInput.value = val;

                        options.forEach(o => {
                            const isSelected = o === opt;
                            o.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                            const icon = o.querySelectorAll('svg')[o.querySelectorAll('svg').length - 1];
                            if (isSelected) {
                                o.className = 'sort-option group flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 text-sm transition-all duration-150 bg-amber-50/90 font-semibold text-brand-primary';
                                if (icon) icon.className = 'h-4 w-4 text-brand-primary opacity-100';
                            } else {
                                o.className = 'sort-option group flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 text-sm transition-all duration-150 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-950';
                                if (icon) icon.className = 'h-4 w-4 text-brand-primary opacity-0 group-hover:opacity-30';
                            }
                        });

                        if (val === 'price_range') {
                            // Expand the embedded price range slider smoothly
                            priceRangePanel.classList.remove('max-h-0', 'opacity-0');
                            priceRangePanel.classList.add('max-h-56', 'opacity-100', 'mt-2.5', 'pt-3', 'border-t', 'border-amber-100');
                            updateSliderTrack();
                            // Keep menu open so user can drag handles or click Apply
                        } else {
                            // Collapse price range slider
                            priceRangePanel.classList.add('max-h-0', 'opacity-0');
                            priceRangePanel.classList.remove('max-h-56', 'opacity-100', 'mt-2.5', 'pt-3', 'border-t', 'border-amber-100');
                            selectedLabel.textContent = labelText;
                            if (hiddenMinPrice) hiddenMinPrice.value = '';
                            if (hiddenMaxPrice) hiddenMaxPrice.value = '';
                            closeMenu();
                        }
                    });
                });

                form?.addEventListener('submit', () => {
                    if (gridContainer) {
                        gridContainer.classList.add('opacity-50', 'scale-[0.99]');
                    }
                });
            });
        </script>
    @endif
</section>

