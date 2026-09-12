<x-site.layout :site="$site" page-title="My Wishlist | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <script>document.body.dataset.wishlistPage = 'true';</script>

    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8" data-reveal>
            <p class="text-sm font-semibold text-brand-primary">Saved for later</p>
            <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">{{ __('ui.your_wishlist') }}</h1>
            <p class="mt-4 text-base leading-7 text-zinc-600">Products you've hearted, all in one place.</p>
        </div>
    </section>

    @if (count($products))
        <x-site.products
            :products="$products"
            :wishlist-product-ids="$wishlistProductIds"
            section-id="wishlist-products"
            eyebrow="Your saved products"
            title="All your favourites, in one place."
            description="Tap the heart on any item to remove it from your wishlist."
        />

        <div class="mx-auto w-full max-w-7xl px-6 py-12 text-center lg:px-8">
            <a class="inline-flex rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-primary" href="{{ route('home') }}#products">Explore More Products</a>
        </div>
    @else
        <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-20 sm:py-24">
            <div class="mx-auto max-w-xl px-6 text-center lg:px-8">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-brand-primary">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="m12 21-1.45-1.32C5.4 15 2 11.92 2 8.15 2 5.07 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.07 22 8.15c0 3.77-3.4 6.85-8.55 11.54L12 21Z" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-semibold text-brand-primary">Nothing saved yet</p>
                <h2 class="mt-3 text-3xl font-semibold text-zinc-950">{{ __('ui.wishlist_empty') }}</h2>
                <p class="mt-4 leading-7 text-zinc-600">Heart products you want to return to and they'll appear here.</p>
                <a class="mt-7 inline-flex rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary" href="{{ route('home') }}#products">Explore More Products</a>
            </div>
        </section>
    @endif
</x-site.layout>
