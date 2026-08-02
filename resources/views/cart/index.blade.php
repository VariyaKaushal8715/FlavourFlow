<x-site.layout :site="$site" page-title="My Cart | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    @php($subtotal = $items->sum(fn ($item) => (float) $item['product']->price * $item['quantity']))
    <section class="min-h-[65vh] bg-zinc-50 py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <p class="text-sm font-semibold text-brand-primary">Your order</p>
            <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">Shopping cart</h1>

            @if ($items->isEmpty())
                <div class="mt-8 rounded-lg border border-zinc-200 bg-white p-8 text-center">
                    <p class="text-lg font-semibold text-zinc-950">Your cart is empty.</p>
                    <a class="mt-5 inline-flex rounded-lg bg-zinc-950 px-4 py-2 text-sm font-semibold text-white" href="{{ route('home').'#products' }}">Browse products</a>
                </div>
            @else
                <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
                    <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
                        @foreach ($items as $item)
                            <article class="grid gap-5 p-5 sm:grid-cols-[7rem_1fr_auto] sm:items-center" data-cart-item data-cart-slug="{{ $item['product']->slug }}">
                                <img class="aspect-square w-28 rounded-lg object-cover" src="{{ asset($item['product']->image_path ?: 'images/flavourflow-mark.png') }}" alt="{{ $item['product']->name }}">
                                <div>
                                    <h2 class="text-lg font-semibold text-zinc-950">{{ $item['product']->name }}</h2>
                                    <p class="mt-1 text-sm text-zinc-500">{{ number_format((float) $item['product']->rating, 1) }} / 5 rating</p>
                                    <p class="mt-2 text-sm font-semibold text-zinc-950">{{ $item['product']->formattedPrice() }}</p>
                                    @if ($item['selected_options'])
                                        <p class="mt-1 text-xs text-zinc-500">{{ collect($item['selected_options'])->map(fn ($value, $key) => ucfirst($key).': '.$value)->join(', ') }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-3 sm:flex-col sm:items-end">
                                    <div class="flex items-center rounded-lg border border-zinc-300">
                                        <button class="px-3 py-2 text-lg" type="button" data-cart-decrease aria-label="Decrease {{ $item['product']->name }} quantity">&minus;</button>
                                        <span class="min-w-8 text-center text-sm font-semibold" data-cart-quantity>{{ $item['quantity'] }}</span>
                                        <button class="px-3 py-2 text-lg" type="button" data-cart-increase aria-label="Increase {{ $item['product']->name }} quantity">+</button>
                                    </div>
                                    <p class="text-sm font-semibold text-zinc-950" data-cart-line-total>Rs. {{ number_format((float) $item['product']->price * $item['quantity'], 2) }}</p>
                                    <button class="text-sm font-medium text-red-700 hover:text-red-900" type="button" data-cart-remove>Remove</button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-6">
                        <h2 class="text-lg font-semibold text-zinc-950">Order summary</h2>
                        <div class="mt-5 flex items-center justify-between border-t border-zinc-200 pt-5 text-sm text-zinc-600">
                            <span>Subtotal</span><span data-cart-subtotal>Rs. {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-lg font-semibold text-zinc-950">
                            <span>Total</span><span data-cart-total>Rs. {{ number_format($subtotal, 2) }}</span>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</x-site.layout>
