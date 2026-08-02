<x-site.layout :site="$site" page-title="My Wishlist | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <script>document.body.dataset.wishlistPage = 'true';</script>
    <section class="bg-zinc-950 py-10 text-white sm:py-14">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-5 px-6 lg:px-8">
            <div>
                <p class="text-sm font-semibold text-brand-accent">Saved for later</p>
                <h1 class="mt-2 text-3xl font-semibold sm:text-4xl">My wishlist</h1>
            </div>
            <a class="rounded-lg border border-white/25 px-4 py-2 text-sm font-semibold transition hover:bg-white hover:text-zinc-950" href="{{ route('home') }}#products">Continue shopping</a>
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
    @else
        <section class="bg-zinc-50 py-20 sm:py-24">
            <div class="mx-auto max-w-xl px-6 text-center lg:px-8">
                <p class="text-sm font-semibold text-brand-primary">Nothing saved yet</p>
                <h2 class="mt-3 text-3xl font-semibold text-zinc-950">Build your perfect spice shelf.</h2>
                <p class="mt-4 leading-7 text-zinc-600">Heart products you want to return to and they’ll appear here.</p>
                <a class="mt-7 inline-flex rounded-lg bg-brand-primary px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95" href="{{ route('home') }}#products">Explore products</a>
            </div>
        </section>
    @endif
</x-site.layout>
