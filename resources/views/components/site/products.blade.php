@props(['products'])

<section id="products" class="bg-zinc-50 py-20 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
        <div class="grid gap-8 border-b border-zinc-200 pb-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
            <div data-reveal>
                <p class="text-sm font-semibold text-red-700">The collection</p>
                <h2 class="mt-3 text-3xl font-semibold leading-tight text-zinc-950 sm:text-5xl">Flavour worth slowing down for.</h2>
            </div>
            <p class="max-w-2xl text-base leading-8 text-zinc-600 lg:justify-self-end" data-reveal>
                Carefully sourced, freshly packed, and balanced for the food you cook every day. Choose a blend and bring a deeper aroma to the table.
            </p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $index => $product)
                <article class="product-tile group overflow-hidden rounded-lg border border-zinc-200 bg-white" data-reveal data-reveal-delay="{{ ($index % 3) * 90 }}">
                    <div class="relative aspect-[4/3] overflow-hidden bg-zinc-900">
                        <img class="h-full w-full object-cover transition duration-700 group-hover:scale-105" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}">
                        <span class="absolute left-4 top-4 rounded-lg bg-white/90 px-3 py-2 text-xs font-semibold text-zinc-950 backdrop-blur">
                            {{ $product['badge'] }}
                        </span>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-xs font-semibold uppercase text-red-700">{{ $product['category'] }}</p>
                            <p class="text-xs font-semibold text-emerald-700">{{ $product['metric'] }}</p>
                        </div>
                        <h3 class="mt-3 text-xl font-semibold text-zinc-950">{{ $product['name'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $product['description'] }}</p>
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
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
