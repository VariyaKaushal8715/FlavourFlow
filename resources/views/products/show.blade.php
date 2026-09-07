<x-site.layout
    :site="$site"
    :page-title="$product->name.' - '.$site['brand']['name']"
    :page-description="$product->description"
    :preserve-on-refresh="true"
>

    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="bg-white py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-zinc-500" aria-label="Breadcrumb">
                <a class="transition hover:text-red-700" href="{{ route('home') }}">{{ __('ui.home') }}</a>
                <span aria-hidden="true">/</span>
                <a class="transition hover:text-red-700" href="{{ route('home').'#products' }}">{{ __('ui.products') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-zinc-950">{{ $product->name }}</span>
            </nav>

            <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_0.92fr] lg:gap-16">
                <div class="lg:sticky lg:top-6 lg:self-start" data-reveal>
                    <div class="relative aspect-square overflow-hidden rounded-lg bg-zinc-100">
                        <img class="h-full w-full object-cover" src="{{ asset($product->image_path ?: 'images/flavourflow-mark.png') }}" alt="{{ $product->name }}">
                        <span class="absolute left-5 top-5 rounded-lg bg-white/90 px-3 py-2 text-xs font-semibold text-zinc-950 backdrop-blur">{{ $product->badge }}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-3">
                        <div class="rounded-lg border border-zinc-200 p-4">
                            <p class="text-xs text-zinc-500">{{ __('ui.pack_size') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-950">{{ $product->unit }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4">
                            <p class="text-xs text-zinc-500">{{ __('ui.rating') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-950">{{ number_format((float) $product->rating, 1) }} / 5</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4">
                            <p class="text-xs text-zinc-500">{{ __('ui.availability') }}</p>
                            <p @class([
                                'mt-1 text-sm font-semibold',
                                'text-emerald-700' => $product->quantity > 0,
                                'text-red-700' => $product->quantity === 0,
                            ])>{{ $product->quantity > 0 ? __('ui.in_stock') : __('ui.out_of_stock') }}</p>
                        </div>
                    </div>
                </div>

                <div data-reveal>
                    <p class="text-sm font-semibold text-red-700">{{ $product->category }}</p>
                    <h1 class="mt-3 text-4xl font-semibold leading-tight text-zinc-950 sm:text-5xl">{{ $product->name }}</h1>
                    <div class="mt-5 flex flex-wrap items-center gap-3 text-sm">
                        <span class="rounded-lg bg-emerald-100 px-3 py-2 font-semibold text-emerald-800">{{ number_format((float) $product->rating, 1) }} customer {{ __('ui.rating') }}</span>
                        <span class="text-zinc-500">SKU: {{ $product->sku }}</span>
                    </div>

                    <p class="mt-7 text-base leading-8 text-zinc-600">{{ $product->description }}</p>

                    <div class="mt-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('ui.select_pack_size') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2.5" data-variant-selector>
                            @foreach ($product->availableVariants() as $vKey => $vOpt)
                                <button
                                    type="button"
                                    data-variant-pill
                                    data-weight="{{ $vOpt['weight'] }}"
                                    data-price="{{ $vOpt['price'] }}"
                                    data-formatted-price="{{ $vOpt['formatted_price'] }}"
                                    @class([
                                        'variant-pill rounded-lg border px-4 py-2 text-sm font-semibold transition',
                                        'border-zinc-950 bg-zinc-950 text-white' => $vKey === '100g',
                                        'border-zinc-300 bg-white text-zinc-800 hover:border-zinc-950' => $vKey !== '100g',
                                    ])
                                >
                                    {{ $vOpt['weight'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 border-y border-zinc-200 py-6">
                        <div class="flex flex-wrap items-end gap-3">
                            <p class="text-3xl font-semibold text-zinc-950" data-product-price-display>{{ $product->formattedPrice() }}</p>
                            @if ($product->formattedComparePrice())
                                <p class="pb-1 text-base text-zinc-400 line-through">{{ $product->formattedComparePrice() }}</p>
                            @endif
                            <span class="pb-1 text-sm text-zinc-500" data-product-unit-display>per {{ $product->unit }}</span>
                        </div>
                        <p class="mt-3 text-xs text-zinc-500">{{ __('ui.inclusive_taxes') }}</p>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <button
                            class="inline-flex rounded-lg bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary disabled:cursor-not-allowed disabled:opacity-60"
                            type="button"
                            data-add-to-cart
                            data-product-slug="{{ $product->slug }}"
                            data-selected-weight="100g"
                            id="btn-product-add-to-cart"
                            @disabled($product->quantity === 0)
                        >{{ $product->quantity > 0 ? __('ui.add_to_cart') : __('ui.out_of_stock') }}</button>

                        <button
                            class="wishlist-button inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 hover:text-zinc-950 disabled:cursor-not-allowed disabled:opacity-60"
                            type="button"
                            data-wishlist-button
                            data-product-id="{{ $product->id }}"
                            data-product-slug="{{ $product->slug }}"
                            data-wishlisted="{{ in_array($product->id, $wishlistProductIds, true) ? 'true' : 'false' }}"
                            aria-pressed="{{ in_array($product->id, $wishlistProductIds, true) ? 'true' : 'false' }}"
                            aria-label="{{ in_array($product->id, $wishlistProductIds, true) ? 'Remove '.$product->name.' from wishlist' : 'Add '.$product->name.' to wishlist' }}"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z" />
                            </svg>
                            <span>{{ __('ui.wishlist') }}</span>
                        </button>
                    </div>

                    {{-- Customer Delivery Availability Checker --}}
                    <div class="mt-8 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 sm:p-5">
                        <div class="flex items-center gap-2.5 text-zinc-900">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.215-9.13A2.25 2.25 0 0016.5 7.5h-3.75V3.75a1.125 1.125 0 00-1.125-1.125H3.375A1.125 1.125 0 002.25 3.75v10.5m17.25 4.5v-3.75a2.25 2.25 0 00-2.25-2.25h-3.75m0 0V7.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900">Check Delivery Availability</h3>
                                <p class="text-[11px] text-zinc-500">Enter your location to verify shipping availability & speed</p>
                            </div>
                        </div>

                        <div class="mt-3.5 space-y-2.5">
                            <div class="grid gap-2 sm:grid-cols-3">
                                <div>
                                    <select
                                        id="prod-check-country"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                                    >
                                        <option value="India" selected>India</option>
                                        <option value="United States">United States</option>
                                        <option value="Canada">Canada</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="France">France</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Japan">Japan</option>
                                    </select>
                                </div>
                                <div id="prod-check-state-wrapper" class="transition-all duration-200">
                                    <input
                                        type="text"
                                        id="prod-check-state"
                                        placeholder="State (e.g. Gujarat)"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                                    />
                                </div>
                                <div id="prod-check-city-wrapper" class="transition-all duration-200">
                                    <input
                                        type="text"
                                        id="prod-check-city"
                                        placeholder="City (e.g. Surat)"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-900 shadow-sm focus:border-red-600 focus:outline-none focus:ring-1 focus:ring-red-600"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-1">
                                <span class="text-[11px] text-zinc-400" id="prod-delivery-hint">Fast dispatch & secure shipping</span>
                                <button
                                    type="button"
                                    id="btn-check-delivery-prod"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-950 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-zinc-800 active:scale-95"
                                >
                                    <span id="prod-check-spinner" class="hidden h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                    <span>Check</span>
                                </button>
                            </div>

                            {{-- Result Message Box --}}
                            <div id="prod-delivery-result" class="hidden rounded-xl p-3 text-xs font-semibold transition-all">
                                {{-- Populated dynamically --}}
                            </div>
                        </div>
                    </div>

                    @if ($product->highlights)
                        <div class="mt-8">
                            <h2 class="text-lg font-semibold text-zinc-950">{{ __('ui.product_highlights') }}</h2>
                            <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                                @foreach ($product->highlights as $highlight)
                                    <li class="grid grid-cols-[1.25rem_1fr] gap-3 text-sm leading-6 text-zinc-700">
                                        <span class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-800" aria-hidden="true">&#10003;</span>
                                        {{ $highlight }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-9 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-zinc-100 p-4">
                            <p class="text-xs font-semibold text-zinc-950">{{ __('ui.freshly_packed') }}</p>
                            <p class="mt-2 text-xs leading-5 text-zinc-500">{{ __('ui.small_batch_care') }}</p>
                        </div>
                        <div class="rounded-lg bg-zinc-100 p-4">
                            <p class="text-xs font-semibold text-zinc-950">{{ __('ui.secure_packaging') }}</p>
                            <p class="mt-2 text-xs leading-5 text-zinc-500">{{ __('ui.protect_flavour') }}</p>
                        </div>
                        <div class="rounded-lg bg-zinc-100 p-4">
                            <p class="text-xs font-semibold text-zinc-950">{{ __('ui.kitchen_ready') }}</p>
                            <p class="mt-2 text-xs leading-5 text-zinc-500">{{ __('ui.clear_pack_usage') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-zinc-200 bg-zinc-50 py-16 sm:py-20">
        <div class="mx-auto grid w-full max-w-7xl gap-10 px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
            <div data-reveal>
                <p class="text-sm font-semibold text-red-700">{{ __('ui.view_details') }}</p>
                <h2 class="mt-3 text-3xl font-semibold text-zinc-950">{{ __('ui.know_ingredients') }}</h2>
                <p class="mt-6 whitespace-pre-line text-base leading-8 text-zinc-600">{{ $product->long_description ?: $product->description }}</p>
            </div>

            <dl class="divide-y divide-zinc-200 border-y border-zinc-200" data-reveal>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">{{ __('ui.ingredients') }}</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ $product->ingredients ?: 'See the product pack for ingredient information.' }}</dd>
                </div>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">{{ __('ui.how_to_use') }}</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ $product->usage_instructions ?: 'Add to taste while cooking.' }}</dd>
                </div>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">{{ __('ui.origin') }}</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ $product->origin ?: 'India' }}</dd>
                </div>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">{{ __('ui.storage') }}</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ __('ui.storage_info') }}</dd>
                </div>
            </dl>
        </div>
    </section>

    @if ($relatedProducts)
        <x-site.products
            :products="$relatedProducts"
            section-id="related-products"
            :eyebrow="__('ui.you_may_also_like')"
            :title="__('ui.more_from_collection')"
            description="Explore related products selected from the same category."
        />
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const countrySelect = document.getElementById('prod-check-country');
            const stateWrapper = document.getElementById('prod-check-state-wrapper');
            const stateInput = document.getElementById('prod-check-state');
            const cityWrapper = document.getElementById('prod-check-city-wrapper');
            const cityInput = document.getElementById('prod-check-city');
            const btnCheck = document.getElementById('btn-check-delivery-prod');
            const spinner = document.getElementById('prod-check-spinner');
            const resultBox = document.getElementById('prod-delivery-result');
            const btnAddToCart = document.getElementById('btn-product-add-to-cart');
            const originalAddToCartText = btnAddToCart ? btnAddToCart.innerText : '';

            function updateCountryFields() {
                const country = (countrySelect ? countrySelect.value : '').toLowerCase();
                const isIndia = country === 'india';

                if (isIndia) {
                    stateWrapper.style.display = 'block';
                    cityWrapper.style.display = 'block';
                } else {
                    stateWrapper.style.display = 'none';
                    cityWrapper.style.display = 'none';
                }
            }

            if (countrySelect) {
                countrySelect.addEventListener('change', () => {
                    updateCountryFields();
                    if (resultBox) resultBox.classList.add('hidden');
                });
            }

            async function performDeliveryCheck() {
                const country = countrySelect ? countrySelect.value.trim() : '';
                const isIndia = country.toLowerCase() === 'india';
                const state = isIndia && stateInput ? stateInput.value.trim() : '';
                const city = isIndia && cityInput ? cityInput.value.trim() : '';

                if (!country) return;

                if (spinner) spinner.classList.remove('hidden');
                if (btnCheck) btnCheck.disabled = true;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route('delivery.check') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ country, state, city })
                    });

                    const data = await response.json();

                    resultBox.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-900', 'border-emerald-200', 'bg-red-50', 'text-red-900', 'border-red-200', 'border');
                    resultBox.classList.add('border');

                    if (data.deliverable) {
                        resultBox.classList.add('bg-emerald-50', 'text-emerald-900', 'border-emerald-200');
                        resultBox.innerHTML = `
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-bold">&check;</span>
                                <div>
                                    <p class="font-bold text-emerald-950">${data.message || 'Deliverable to this location!'}</p>
                                    <p class="text-[11px] text-emerald-700 font-normal">Standard delivery available (2-4 business days).</p>
                                </div>
                            </div>
                        `;

                        if (btnAddToCart && {{ $product->quantity > 0 ? 'true' : 'false' }}) {
                            btnAddToCart.disabled = false;
                            btnAddToCart.innerText = originalAddToCartText;
                            btnAddToCart.classList.remove('opacity-50', 'cursor-not-allowed');
                        }

                        // Store in localStorage
                        localStorage.setItem('flavourflow_delivery_location', JSON.stringify({ country, state, city, deliverable: true }));
                    } else {
                        resultBox.classList.add('bg-red-50', 'text-red-900', 'border-red-200');
                        resultBox.innerHTML = `
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-700 font-bold">&times;</span>
                                <div>
                                    <p class="font-bold text-red-950">Sorry, we don’t deliver to this location.</p>
                                    <p class="text-[11px] text-red-700 font-normal">Please choose another shipping location.</p>
                                </div>
                            </div>
                        `;

                        if (btnAddToCart) {
                            btnAddToCart.disabled = true;
                            btnAddToCart.innerText = 'Unavailable for this location';
                            btnAddToCart.classList.add('opacity-50', 'cursor-not-allowed');
                        }

                        localStorage.setItem('flavourflow_delivery_location', JSON.stringify({ country, state, city, deliverable: false }));
                    }
                } catch (err) {
                    console.error('Delivery check error:', err);
                } finally {
                    if (spinner) spinner.classList.add('hidden');
                    if (btnCheck) btnCheck.disabled = false;
                }
            }

            if (btnCheck) {
                btnCheck.addEventListener('click', performDeliveryCheck);
            }

            [stateInput, cityInput].forEach(inp => {
                if (inp) {
                    inp.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            performDeliveryCheck();
                        }
                    });
                }
            });

            // Restore from localStorage if exists
            const savedLocationStr = localStorage.getItem('flavourflow_delivery_location');
            if (savedLocationStr) {
                try {
                    const saved = JSON.parse(savedLocationStr);
                    if (saved.country && countrySelect) {
                        countrySelect.value = saved.country;
                    }
                    if (saved.state && stateInput) {
                        stateInput.value = saved.state;
                    }
                    if (saved.city && cityInput) {
                        cityInput.value = saved.city;
                    }
                    updateCountryFields();
                } catch (e) {}
            } else {
                updateCountryFields();
            }
        });
    </script>
</x-site.layout>
