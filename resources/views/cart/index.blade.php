<x-site.layout :site="$site" page-title="My Cart | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    @php($subtotal = $items->sum(fn ($item) => (float) ($item['line_total'] ?? ($item['unit_price'] * $item['quantity']))))
    <section class="min-h-[65vh] bg-zinc-50 py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-brand-primary">{{ __('ui.your_order') }}</p>
                    <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">{{ __('ui.shopping_cart') }}</h1>
                </div>
                @if (! $items->isEmpty())
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                        type="button"
                        data-cart-clear-trigger
                        aria-label="Clear all items from cart"
                    >
                        {{ __('ui.clear_cart') }}
                    </button>
                @endif
            </div>

            <div id="cart-empty-message" @class(['mt-8 rounded-lg border border-zinc-200 bg-white p-8 text-center', 'hidden' => ! $items->isEmpty()])>
                <p class="text-lg font-semibold text-zinc-950">{{ __('ui.cart_empty') }}</p>
                <a class="mt-5 inline-flex rounded-lg bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-primary" href="{{ route('home').'#products' }}">{{ __('ui.browse_products') }}</a>
            </div>

            @if (! $items->isEmpty())
                <div id="cart-content" class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
                    <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
                        @foreach ($items as $item)
                            <article class="grid gap-5 p-5 sm:grid-cols-[7rem_1fr_auto] sm:items-center" data-cart-item data-cart-slug="{{ $item['product']->slug }}">
                                <img class="aspect-square w-28 rounded-lg object-cover" src="{{ asset($item['product']->image_path ?: 'images/flavourflow-mark.png') }}" alt="{{ $item['product']->name }}">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-lg font-semibold text-zinc-950">{{ $item['product']->name }}</h2>
                                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700">{{ $item['unit'] }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-500">{{ number_format((float) $item['product']->rating, 1) }} / 5 {{ __('ui.rating') }}</p>
                                    <p class="mt-2 text-sm font-semibold text-zinc-950">Rs. {{ number_format((float) $item['unit_price'], 2) }}</p>
                                    @if ($item['selected_options'])
                                        <p class="mt-1 text-xs text-zinc-500">{{ collect($item['selected_options'])->map(fn ($value, $key) => ucfirst($key).': '.$value)->join(', ') }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-3 sm:flex-col sm:items-end">
                                    <div class="flex items-center rounded-lg border border-zinc-300">
                                        <button class="px-3 py-2 text-lg hover:bg-zinc-100" type="button" data-cart-decrease aria-label="Decrease quantity">&minus;</button>
                                        <span class="min-w-8 text-center text-sm font-semibold" data-cart-quantity>{{ $item['quantity'] }}</span>
                                        <button class="px-3 py-2 text-lg hover:bg-zinc-100" type="button" data-cart-increase aria-label="Increase quantity">+</button>
                                    </div>
                                    <p class="text-sm font-semibold text-zinc-950" data-cart-line-total>Rs. {{ number_format((float) ($item['line_total'] ?? ($item['unit_price'] * $item['quantity'])), 2) }}</p>
                                    <button class="text-sm font-medium text-red-700 hover:text-red-900" type="button" data-cart-remove>{{ __('ui.remove') }}</button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-6">
                        <h2 class="text-lg font-semibold text-zinc-950">{{ __('ui.order_summary') }}</h2>
                        <div class="mt-5 flex items-center justify-between border-t border-zinc-200 pt-5 text-sm text-zinc-600">
                            <span>{{ __('ui.subtotal') }}</span><span data-cart-subtotal>Rs. {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-lg font-semibold text-zinc-950">
                            <span>{{ __('ui.total') }}</span><span data-cart-total>Rs. {{ number_format($subtotal, 2) }}</span>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>

    <!-- Clear Cart Confirmation Modal -->
    <div
        id="clear-cart-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/70 p-4 backdrop-blur-sm transition-opacity"
        aria-hidden="true"
        role="dialog"
        aria-modal="true"
        aria-labelledby="clear-cart-modal-title"
    >
        <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl transition-all sm:p-8">
            <div class="flex items-center justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <button
                    type="button"
                    data-close-clear-modal
                    class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 transition"
                    aria-label="Close popup"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4">
                <h3 id="clear-cart-modal-title" class="text-lg font-semibold text-zinc-950">{{ __('ui.clear_cart_modal_title') }}</h3>
                <p class="mt-2 text-sm text-zinc-600 leading-relaxed">
                    {{ __('ui.clear_cart_modal_desc') }}
                </p>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    data-close-clear-modal
                    class="inline-flex w-full justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 sm:w-auto"
                >
                    {{ __('ui.keep_items') }}
                </button>
                <form method="POST" action="{{ route('cart.clear') }}" data-cart-clear-form class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        data-cart-clear-confirm
                        class="inline-flex w-full justify-center rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800 sm:w-auto"
                    >
                        {{ __('ui.confirm_clear_cart') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-site.layout>
