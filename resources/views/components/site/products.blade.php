@props([
    'products',
    'sectionId' => 'products',
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'tone' => 'default',
    'wishlistProductIds' => [],
    'sort' => 'featured',
    'sortOptions' => null,
    'minPrice' => null,
    'maxPrice' => null,
    'lowestPrice' => 0,
    'highestPrice' => 1000,
])

@php
    $displayEyebrow = $eyebrow ?? __('ui.collection_eyebrow');
    $displayTitle = $title ?? __('ui.collection_title');
    $displayDescription = $description ?? __('ui.collection_desc');
    $lowest = (int) $lowestPrice;
    $highest = (int) $highestPrice;
    if ($highest <= $lowest) {
        $highest = $lowest + 1000;
    }
    $curMin = ($minPrice !== null && $minPrice >= $lowest) ? (int) $minPrice : $lowest;
    $curMax = ($maxPrice !== null && $maxPrice <= $highest && $maxPrice >= $lowest) ? (int) $maxPrice : $highest;

    // Build dynamic sort options mapping (key => label)
    $formattedOptions = [];
    if ($sortOptions instanceof \Illuminate\Support\Collection) {
        foreach ($sortOptions as $opt) {
            $formattedOptions[$opt->key] = $opt->label;
        }
    } elseif (is_array($sortOptions) && ! empty($sortOptions)) {
        foreach ($sortOptions as $key => $value) {
            if (is_object($value)) {
                $formattedOptions[$value->key] = $value->label;
            } elseif (is_array($value) && isset($value['key'], $value['label'])) {
                $formattedOptions[$value['key']] = $value['label'];
            } else {
                $formattedOptions[$key] = $value;
            }
        }
    } else {
        $formattedOptions = [
            'featured' => __('ui.featured'),
            'rating' => __('ui.top_rated'),
            'price_asc' => __('ui.price_low_high'),
            'price_desc' => __('ui.price_high_low'),
            'name' => __('ui.name_az'),
            'newest' => __('ui.newest_arrivals'),
            'best_selling' => 'Best Selling',
            'discount' => 'Biggest Discounts',
            'price_range' => __('ui.price_range'),
        ];
    }

    $firstOptionKey = array_key_first($formattedOptions) ?? 'featured';
    $defaultOptionKey = array_key_exists('featured', $formattedOptions) ? 'featured' : $firstOptionKey;

    $currentSort = $sort ?: $defaultOptionKey;
    if (! array_key_exists($currentSort, $formattedOptions)) {
        $currentSort = $defaultOptionKey;
    }

    $hasActiveFilters = ($currentSort !== $defaultOptionKey) || ($minPrice !== null && $minPrice > $lowest) || ($maxPrice !== null && $maxPrice < $highest);

    $triggerLabel = $formattedOptions[$currentSort] ?? ($formattedOptions[$defaultOptionKey] ?? 'Sort Products');
    if ($currentSort === 'price_range') {
        $triggerLabel = ($formattedOptions['price_range'] ?? __('ui.price_range')) . ': ₹' . $curMin . ' – ₹' . $curMax;
    }
@endphp

<section
    id="{{ $sectionId }}"
    @class([
        'bg-zinc-50 py-8 sm:py-20 max-sm:px-2' => $tone === 'default',
        'bg-brand-surface py-6 sm:py-10 max-sm:px-2' => $tone === 'offer',
    ])
>
    <div class="mx-auto w-full max-w-7xl px-2 sm:px-6 lg:px-8">
        <div @class([
            'grid gap-4 border-b border-zinc-200 lg:grid-cols-[0.8fr_1.2fr_auto] lg:items-end',
            'pb-4 sm:pb-10' => $tone === 'default',
            'pb-4 sm:pb-6' => $tone === 'offer',
        ])>
            <div data-reveal>
                <p class="text-xs sm:text-sm font-semibold text-brand-primary">{{ $displayEyebrow }}</p>
                <h2 @class([
                    'mt-1 sm:mt-3 text-xl sm:text-3xl font-semibold leading-tight text-zinc-950',
                    'sm:text-5xl' => $tone === 'default',
                    'sm:text-4xl' => $tone === 'offer',
                ])>{{ $displayTitle }}</h2>
            </div>
            <p class="max-w-2xl text-xs sm:text-base leading-snug sm:leading-8 text-zinc-600 lg:justify-self-end hidden sm:block" data-reveal>
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
                                    @foreach ($formattedOptions as $key => $label)
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
                                    <div class="relative w-full py-3">
                                        <!-- Base Track -->
                                        <div class="relative h-2 w-full rounded-full bg-zinc-200">
                                            <!-- Highlight Track -->
                                            <div id="slider-highlight-bar" class="absolute h-2 rounded-full bg-brand-primary transition-all duration-75"></div>
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

                                    <div class="flex justify-between text-[10px] text-zinc-400 font-medium px-1 mt-1">
                                        <span>₹{{ $lowest }}</span>
                                        <span>₹{{ $highest }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            id="filter-apply-btn"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl bg-zinc-950 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-primary active:scale-95 focus:outline-none focus:ring-4 focus:ring-zinc-900/20 shrink-0"
                            type="submit"
                        >
                            {{ __('ui.apply') }}
                        </button>
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
                    'grid grid-cols-2 gap-2.5 sm:gap-5 sm:grid-cols-2 lg:grid-cols-3 transition-all duration-300 ease-out opacity-100',
                    'mt-4 sm:mt-10' => $tone === 'default',
                    'mt-4 sm:mt-6' => $tone === 'offer',
                ])
            >
            @foreach ($products as $index => $product)
                @php
                    $productId = $product['id'] ?? null;
                    $isWishlisted = $productId && in_array($productId, $wishlistProductIds, true);
                @endphp
                <article class="product-tile group relative flex flex-col overflow-hidden rounded-xl border border-zinc-200/80 bg-white shadow-sm transition hover:shadow-md" data-reveal data-reveal-delay="{{ ($index % 3) * 90 }}">
                    <a class="flex h-full flex-col focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-primary" href="{{ $product['url'] ?? '#products' }}" aria-label="View {{ $product['name'] }} details">
                        <div class="relative aspect-[4/3] overflow-hidden bg-zinc-900">
                            <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" loading="lazy">
                            <span class="absolute left-1.5 top-1.5 sm:left-3 sm:top-3 rounded-md bg-white/95 px-1.5 py-0.5 sm:px-2.5 sm:py-1 text-[0.6rem] sm:text-xs font-semibold text-zinc-900 shadow-sm backdrop-blur">
                                {{ $product['badge'] }}
                            </span>
                        </div>

                        <div class="flex flex-1 flex-col p-2 sm:p-4">
                            <div class="flex items-center justify-between gap-1">
                                <p class="text-[0.6rem] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary truncate">{{ $product['category'] }}</p>
                                <p class="text-[0.6rem] sm:text-xs font-semibold text-emerald-700 shrink-0">{{ $product['metric'] }}</p>
                            </div>
                            <h3 class="mt-1 text-xs sm:text-base font-semibold text-zinc-950 transition group-hover:text-brand-primary line-clamp-1 leading-tight sm:leading-snug">{{ $product['name'] }}</h3>
                            <p class="mt-1 text-xs leading-relaxed text-zinc-500 line-clamp-2 hidden sm:block">{{ $product['description'] }}</p>
                            
                            <div class="mt-auto pt-2 sm:pt-3 border-t border-zinc-100">
                                <div class="flex items-baseline justify-between gap-1">
                                    <div>
                                        <div class="flex items-baseline gap-1">
                                            <p class="text-xs sm:text-base font-bold text-zinc-950">{{ $product['price'] }}</p>
                                            @if ($product['compare_at_price'])
                                                <p class="text-[0.6rem] sm:text-xs text-zinc-400 line-through">{{ $product['compare_at_price'] }}</p>
                                            @endif
                                        </div>
                                        <p class="text-[0.6rem] sm:text-xs text-zinc-500">{{ $product['unit'] }}</p>
                                    </div>
                                    <p @class([
                                        'text-[0.6rem] sm:text-xs font-semibold shrink-0',
                                        'text-emerald-700' => $product['in_stock'],
                                        'text-red-700' => ! $product['in_stock'],
                                    ])>
                                        {{ $product['in_stock'] ? __('ui.in_stock') : __('ui.out_of_stock') }}
                                    </p>
                                </div>
                                <div class="mt-2 sm:mt-3">
                                    <button
                                        type="button"
                                        data-add-to-cart
                                        data-product-slug="{{ $product['slug'] ?? '' }}"
                                        @disabled(! $product['in_stock'])
                                        class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-950 py-1.5 sm:py-2 px-2 text-xs sm:text-sm font-semibold text-white transition hover:bg-brand-primary active:scale-95 disabled:pointer-events-none disabled:opacity-50"
                                    >
                                        <span class="sm:hidden">+ Add</span>
                                        <span class="hidden sm:inline">Add to cart</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a>
                    <button
                        class="wishlist-button absolute right-1.5 top-1.5 sm:right-3 sm:top-3 z-10 flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-white/90 text-zinc-700 shadow-sm transition hover:bg-white hover:text-red-600 data-[wishlisted=true]:text-red-600 focus:outline-none focus:ring-2 focus:ring-brand-primary"
                        type="button"
                        data-wishlist-button
                        data-product-id="{{ $product['id'] ?? '' }}"
                        data-product-slug="{{ $product['slug'] ?? '' }}"
                        data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                        aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                        aria-label="{{ $isWishlisted ? 'Remove '.$product['name'].' from wishlist' : 'Add '.$product['name'].' to wishlist' }}"
                    >
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
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
            (() => {
                const initSortDropdown = () => {
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

                    const lowestBound = parseInt("{{ $lowest }}", 10) || 0;
                    const highestBound = parseInt("{{ $highest }}", 10) || 1000;

                    if (!trigger || !menu) return;

                    let isOpen = false;

                    const updateSliderTrack = () => {
                        if (!minSlider || !maxSlider || !highlightBar) return;
                        let v1 = parseInt(minSlider.value, 10);
                        let v2 = parseInt(maxSlider.value, 10);

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

                        if (sortInput && sortInput.value === 'price_range' && selectedLabel) {
                            selectedLabel.textContent = `Price Range: ₹${v1} – ₹${v2}`;
                        }
                    };

                    minSlider?.addEventListener('input', () => {
                        if (parseInt(minSlider.value, 10) > parseInt(maxSlider.value, 10) - 5) {
                            minSlider.value = parseInt(maxSlider.value, 10) - 5;
                        }
                        minSlider.style.zIndex = '25';
                        if (maxSlider) maxSlider.style.zIndex = '20';
                        updateSliderTrack();
                    });

                    maxSlider?.addEventListener('input', () => {
                        if (parseInt(maxSlider.value, 10) < parseInt(minSlider.value, 10) + 5) {
                            maxSlider.value = parseInt(minSlider.value, 10) + 5;
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
                            
                            if (sortInput) sortInput.value = val;

                            options.forEach(o => {
                                const isSelected = o === opt;
                                o.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                                const checkIcon = o.querySelector('svg:last-of-type');
                                if (isSelected) {
                                    o.className = 'sort-option group flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 text-sm transition-all duration-150 bg-amber-50/90 font-semibold text-brand-primary';
                                    if (checkIcon) {
                                        checkIcon.classList.remove('opacity-0', 'group-hover:opacity-30');
                                        checkIcon.classList.add('opacity-100');
                                    }
                                } else {
                                    o.className = 'sort-option group flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 text-sm transition-all duration-150 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-950';
                                    if (checkIcon) {
                                        checkIcon.classList.remove('opacity-100');
                                        checkIcon.classList.add('opacity-0', 'group-hover:opacity-30');
                                    }
                                }
                            });

                            if (val === 'price_range') {
                                if (priceRangePanel) {
                                    priceRangePanel.classList.remove('max-h-0', 'opacity-0');
                                    priceRangePanel.classList.add('max-h-56', 'opacity-100', 'mt-2.5', 'pt-3', 'border-t', 'border-amber-100');
                                }
                                updateSliderTrack();
                            } else {
                                if (priceRangePanel) {
                                    priceRangePanel.classList.add('max-h-0', 'opacity-0');
                                    priceRangePanel.classList.remove('max-h-56', 'opacity-100', 'mt-2.5', 'pt-3', 'border-t', 'border-amber-100');
                                }
                                if (selectedLabel) selectedLabel.textContent = labelText;
                                if (hiddenMinPrice) hiddenMinPrice.value = '';
                                if (hiddenMaxPrice) hiddenMaxPrice.value = '';
                                closeMenu();
                            }
                        });
                    });

                    form?.addEventListener('submit', () => {
                        if (sortInput && sortInput.value !== 'price_range') {
                            if (hiddenMinPrice) hiddenMinPrice.value = '';
                            if (hiddenMaxPrice) hiddenMaxPrice.value = '';
                        }
                        if (gridContainer) {
                            gridContainer.classList.add('opacity-50', 'scale-[0.99]');
                        }
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initSortDropdown);
                } else {
                    initSortDropdown();
                }
            })();
        </script>
    @endif
</section>
