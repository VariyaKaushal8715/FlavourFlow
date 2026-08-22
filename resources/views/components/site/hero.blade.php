@props(['brand', 'navigation', 'hero'])

@php
    $featuredProduct = $hero['product_showcase']['featured'];
    $supportingProducts = $hero['product_showcase']['supporting'];
    $tickerProducts = array_merge([$featuredProduct], $supportingProducts);
@endphp

<section id="top" class="hero-stage" data-hero>
    <div class="hero-media" aria-hidden="true">
        <img src="{{ asset($hero['image']) }}" alt="">
    </div>
    <div class="hero-shade" aria-hidden="true"></div>
    <div class="hero-grid" aria-hidden="true"></div>

    <x-site.nav :brand="$brand" :navigation="$navigation" />

    <div class="relative mx-auto grid min-h-[calc(100svh-5rem)] w-full max-w-7xl items-center gap-12 px-6 pb-28 pt-12 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:pb-32 lg:pt-16">
        <div class="relative z-10 max-w-3xl">
            <div class="hero-intro hero-intro-one inline-flex items-center gap-3 text-sm font-semibold text-brand-accent">
                <span class="h-px w-10 bg-brand-primary"></span>
                {{ $hero['eyebrow'] }}
            </div>

            <h1 class="hero-intro hero-intro-two mt-6 break-words text-6xl font-semibold leading-none text-white sm:text-7xl lg:text-[5.5rem]">
                {{ $hero['title'] }}
            </h1>
            <p class="hero-intro hero-intro-three mt-6 max-w-2xl text-2xl font-medium leading-snug text-white sm:text-4xl">
                {{ $hero['subtitle'] }}
            </p>
            <p class="hero-intro hero-intro-four mt-5 max-w-xl text-base leading-8 text-white/70 sm:text-lg">
                {{ $hero['description'] }}
            </p>

            <div class="hero-intro hero-intro-five mt-9 flex flex-col gap-3 sm:flex-row">
                <a class="inline-flex min-h-12 items-center justify-center rounded-lg bg-brand-primary px-6 text-sm font-semibold text-white shadow-lg shadow-black/30 transition duration-300 hover:-translate-y-0.5 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 focus:ring-offset-zinc-950" href="{{ $hero['primary_action']['href'] }}">
                    {{ $hero['primary_action']['label'] }}
                    <span class="ml-3 text-lg" aria-hidden="true">&rarr;</span>
                </a>
                <a class="inline-flex min-h-12 items-center justify-center rounded-lg border border-white/25 bg-black/20 px-6 text-sm font-semibold text-white backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/70 focus:ring-offset-2 focus:ring-offset-zinc-950" href="{{ $hero['secondary_action']['href'] }}">
                    {{ $hero['secondary_action']['label'] }}
                </a>
            </div>

            <div class="hero-intro hero-intro-six mt-10 grid max-w-2xl grid-cols-3 border-y border-white/15 py-5">
                @foreach ($hero['proof_points'] as $point)
                    <div class="border-white/15 px-3 first:pl-0 not-first:border-l sm:px-5">
                        <p class="text-lg font-semibold text-white sm:text-xl">{{ $point['value'] }}</p>
                        <p class="mt-1 text-xs leading-5 text-white/55 sm:text-sm">{{ $point['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="hero-product-stage relative z-10 mx-auto w-full max-w-2xl lg:ml-auto" data-reveal>
            <div class="hero-product-lines" aria-hidden="true"></div>

            <div class="relative ml-auto w-[min(86vw,25rem)]">
                <a class="hero-product-card block overflow-hidden rounded-lg border border-white/15 bg-zinc-950/80 shadow-2xl shadow-black/50 backdrop-blur-xl focus:outline-none focus:ring-2 focus:ring-brand-accent" href="{{ $featuredProduct['url'] ?? '#products' }}" aria-label="View {{ $featuredProduct['name'] }} details" data-tilt>
                <div class="relative aspect-[4/3] overflow-hidden bg-zinc-900">
                    <img class="h-full w-full object-cover transition duration-700" src="{{ asset($featuredProduct['image']) }}" alt="{{ $featuredProduct['name'] }}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                    <div class="absolute left-4 top-4 rounded-lg border border-brand-accent/30 bg-black/50 px-3 py-2 text-xs font-semibold text-brand-accent backdrop-blur">
                        {{ $featuredProduct['badge'] }}
                    </div>
                    <div class="absolute bottom-5 left-5 right-5">
                        <p class="text-xs font-semibold uppercase text-brand-accent">{{ $featuredProduct['category'] }}</p>
                        <h2 class="mt-2 text-2xl font-semibold leading-8 text-white">{{ $featuredProduct['name'] }}</h2>
                    </div>
                </div>

                <div class="grid grid-cols-[1fr_auto] items-end gap-4 p-5">
                    <p class="text-sm leading-6 text-white/60">{{ $featuredProduct['description'] }}</p>
                    <div class="text-right">
                        <p class="text-base font-semibold text-white">{{ $featuredProduct['price'] }}</p>
                        <p class="mt-1 text-xs text-emerald-300">{{ $featuredProduct['stock_label'] }} &middot; {{ $featuredProduct['unit'] }}</p>
                    </div>
                </div>
                </a>
            </div>

            <div class="hero-supporting-products absolute -left-2 top-10 hidden w-48 gap-3 sm:grid lg:-left-16">
                @foreach ($supportingProducts as $index => $product)
                    <article class="hero-mini-card rounded-lg border border-white/15 bg-black/65 p-4 text-white shadow-xl shadow-black/30 backdrop-blur-xl" data-card-delay="{{ $index * 700 }}">
                        <p class="text-[0.68rem] font-semibold uppercase text-brand-accent">{{ $product['category'] }}</p>
                        <h3 class="mt-2 text-sm font-semibold leading-5">{{ $product['name'] }}</h3>
                        <div class="mt-3 flex items-center justify-between gap-2 text-[0.68rem] text-white/55">
                            <span>{{ $product['badge'] }}</span>
                            <span>{{ $product['metric'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hero-rank-badge absolute -bottom-5 right-4 rounded-lg border border-white/15 bg-brand-accent px-4 py-3 text-brand-ink shadow-xl shadow-black/30">
                <p class="text-[0.65rem] font-semibold uppercase">House favourite</p>
                <p class="mt-1 text-sm font-semibold">Fresh small-batch selection</p>
            </div>
        </div>
    </div>

    <div class="hero-ticker absolute inset-x-0 bottom-0 overflow-hidden border-y border-white/10 bg-black/40 py-3 text-white backdrop-blur-md">
        <div class="hero-ticker-track">
            @foreach (array_merge($tickerProducts, $tickerProducts) as $product)
                <span class="flex shrink-0 items-center gap-4 text-xs font-semibold uppercase text-white/70">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-primary"></span>
                    {{ $product['name'] }}
                    <span class="text-brand-accent">{{ $product['metric'] }}</span>
                </span>
            @endforeach
        </div>
    </div>
</section>
