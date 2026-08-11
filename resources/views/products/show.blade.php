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
                <a class="transition hover:text-red-700" href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">/</span>
                <a class="transition hover:text-red-700" href="{{ route('home').'#products' }}">Products</a>
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
                            <p class="text-xs text-zinc-500">Pack size</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-950">{{ $product->unit }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4">
                            <p class="text-xs text-zinc-500">Rating</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-950">{{ number_format((float) $product->rating, 1) }} / 5</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4">
                            <p class="text-xs text-zinc-500">Availability</p>
                            <p @class([
                                'mt-1 text-sm font-semibold',
                                'text-emerald-700' => $product->quantity > 0,
                                'text-red-700' => $product->quantity === 0,
                            ])>{{ $product->stockLabel() }}</p>
                        </div>
                    </div>
                </div>

                <div data-reveal>
                    <p class="text-sm font-semibold text-red-700">{{ $product->category }}</p>
                    <h1 class="mt-3 text-4xl font-semibold leading-tight text-zinc-950 sm:text-5xl">{{ $product->name }}</h1>
                    <div class="mt-5 flex flex-wrap items-center gap-3 text-sm">
                        <span class="rounded-lg bg-emerald-100 px-3 py-2 font-semibold text-emerald-800">{{ number_format((float) $product->rating, 1) }} customer rating</span>
                        <span class="text-zinc-500">SKU: {{ $product->sku }}</span>
                    </div>

                    <p class="mt-7 text-base leading-8 text-zinc-600">{{ $product->description }}</p>

                    <div class="mt-8 border-y border-zinc-200 py-6">
                        <div class="flex flex-wrap items-end gap-3">
                            <p class="text-3xl font-semibold text-zinc-950">{{ $product->formattedPrice() }}</p>
                            @if ($product->formattedComparePrice())
                                <p class="pb-1 text-base text-zinc-400 line-through">{{ $product->formattedComparePrice() }}</p>
                            @endif
                            <span class="pb-1 text-sm text-zinc-500">per {{ $product->unit }}</span>
                        </div>
                        <p class="mt-3 text-xs text-zinc-500">Inclusive of applicable taxes.</p>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <button
                            class="inline-flex rounded-lg bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary disabled:cursor-not-allowed disabled:opacity-60"
                            type="button"
                            data-add-to-cart
                            data-product-slug="{{ $product->slug }}"
                            @disabled($product->quantity === 0)
                        >{{ $product->quantity > 0 ? 'Add to cart' : 'Out of stock' }}</button>

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
                            <span>Wishlist</span>
                        </button>
                    </div>

                    @if ($product->highlights)
                        <div class="mt-8">
                            <h2 class="text-lg font-semibold text-zinc-950">Product highlights</h2>
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
                            <p class="text-xs font-semibold text-zinc-950">Freshly packed</p>
                            <p class="mt-2 text-xs leading-5 text-zinc-500">Small-batch care for lively aroma.</p>
                        </div>
                        <div class="rounded-lg bg-zinc-100 p-4">
                            <p class="text-xs font-semibold text-zinc-950">Secure packaging</p>
                            <p class="mt-2 text-xs leading-5 text-zinc-500">Made to protect flavour and freshness.</p>
                        </div>
                        <div class="rounded-lg bg-zinc-100 p-4">
                            <p class="text-xs font-semibold text-zinc-950">Kitchen ready</p>
                            <p class="mt-2 text-xs leading-5 text-zinc-500">Clear pack size, usage, and ingredients.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-zinc-200 bg-zinc-50 py-16 sm:py-20">
        <div class="mx-auto grid w-full max-w-7xl gap-10 px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
            <div data-reveal>
                <p class="text-sm font-semibold text-red-700">Full product details</p>
                <h2 class="mt-3 text-3xl font-semibold text-zinc-950">Know what goes into every spoon.</h2>
                <p class="mt-6 whitespace-pre-line text-base leading-8 text-zinc-600">{{ $product->long_description ?: $product->description }}</p>
            </div>

            <dl class="divide-y divide-zinc-200 border-y border-zinc-200" data-reveal>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">Ingredients</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ $product->ingredients ?: 'See the product pack for ingredient information.' }}</dd>
                </div>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">How to use</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ $product->usage_instructions ?: 'Add to taste while cooking.' }}</dd>
                </div>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">Origin</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">{{ $product->origin ?: 'India' }}</dd>
                </div>
                <div class="py-5">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">Storage</dt>
                    <dd class="mt-2 text-sm leading-7 text-zinc-800">Keep sealed in a cool, dry place away from direct sunlight and moisture.</dd>
                </div>
            </dl>
        </div>
    </section>

    @if ($relatedProducts)
        <x-site.products
            :products="$relatedProducts"
            section-id="related-products"
            eyebrow="You may also like"
            title="More from this collection."
            description="Explore related products selected from the same category."
        />
    @endif
</x-site.layout>
