@php
    $pickCount = $offer->suggestedProductCount();
    $productCount = count($suggestedProducts);
@endphp

<x-site.layout
    :site="$site"
    :page-title="$offer->title.' - '.$site['brand']['name']"
    :page-description="$offer->description"
>
    <div class="bg-brand-ink">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-t border-white/10 bg-brand-ink text-white">
        <div class="mx-auto w-full max-w-7xl px-6 py-8 lg:px-8 lg:py-10">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-white/50" aria-label="Breadcrumb">
                <a class="transition hover:text-white" href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">/</span>
                <a class="transition hover:text-white" href="{{ route('home').'#offers' }}">Offers</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">{{ $offer->eyebrow }}</span>
            </nav>

            <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-end lg:gap-16">
                <div data-reveal>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="inline-flex min-h-12 items-center rounded-lg bg-brand-accent px-4 py-2 text-lg font-semibold text-brand-ink">
                            {{ $offer->discount_label }}
                        </span>
                        <span class="text-xs font-semibold uppercase text-brand-accent">{{ $offer->eyebrow }}</span>
                    </div>

                    <h1 class="mt-6 max-w-4xl text-4xl font-semibold leading-tight sm:text-5xl">{{ $offer->title }}</h1>
                    <p class="mt-5 max-w-3xl text-base leading-8 text-white/70 sm:text-lg">{{ $offer->description }}</p>

                    @if ($offer->terms)
                        <p class="mt-5 max-w-3xl border-l-2 border-brand-primary pl-4 text-sm leading-7 text-white/55">{{ $offer->terms }}</p>
                    @endif
                </div>

                <dl class="divide-y divide-white/15 border-y border-white/15" data-reveal>
                    <div class="flex items-center justify-between gap-6 py-4">
                        <dt class="text-sm text-white/50">Offer value</dt>
                        <dd class="text-right text-sm font-semibold text-brand-accent">{{ $offer->discount_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-6 py-4">
                        <dt class="text-sm text-white/50">Coupon code</dt>
                        <dd class="text-right text-sm font-semibold text-white">{{ $offer->coupon_code ?: 'No code required' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-6 py-4">
                        <dt class="text-sm text-white/50">Availability</dt>
                        <dd class="text-right text-sm font-semibold text-white">{{ $offer->dateRangeLabel() }}</dd>
                    </div>
                    @if ($productCount > 0)
                        <div class="flex items-center justify-between gap-6 py-4">
                            <dt class="text-sm text-white/50">Recommended set</dt>
                            <dd class="text-right text-sm font-semibold text-brand-accent">{{ $productCount }} products</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </section>

    @if ($suggestedProducts)
        <x-site.products
            :products="$suggestedProducts"
            :wishlist-product-ids="$wishlistProductIds"
            section-id="offer-picks"
            :eyebrow="$pickCount > 0 ? 'Build your offer set' : 'Recommended for this offer'"
            :title="$pickCount > 0 ? 'Pick these '.$pickCount.' products.' : 'Start with these customer favourites.'"
            :description="$pickCount > 0
                ? 'A balanced combination of everyday essentials selected for flavour, versatility, and customer ratings.'
                : 'Popular in-stock products that pair naturally with this limited-time offer.'"
            tone="offer"
        />
    @endif
</x-site.layout>
