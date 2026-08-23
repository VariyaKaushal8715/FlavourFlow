<x-site.layout :site="$site" page-title="Order Confirmed | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="min-h-[70vh] bg-zinc-50 py-16 sm:py-24">
        <div class="mx-auto max-w-3xl px-6 text-center lg:px-8" data-reveal>
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 shadow-sm">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl">Thank You for Your Order!</h1>
            <p class="mt-4 text-base text-zinc-600 leading-relaxed">
                Your order has been placed successfully. We are preparing to dispatch your premium selection of handpicked Indian spices.
            </p>

            <div class="mt-8 rounded-3xl border border-amber-200/70 bg-white p-6 shadow-sm text-left sm:p-8">
                <h2 class="text-lg font-semibold text-zinc-950">Order Summary</h2>
                
                <div class="mt-4 grid gap-4 border-t border-zinc-100 pt-4 text-sm sm:grid-cols-2">
                    <div>
                        <p class="font-medium text-zinc-400">Customer Name</p>
                        <p class="mt-1 font-semibold text-zinc-950">{{ $order['name'] }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Contact Number</p>
                        <p class="mt-1 font-semibold text-zinc-950">{{ $order['mobile'] }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Shipping Address</p>
                        <p class="mt-1 font-semibold text-zinc-950">
                            {{ $order['address'] }}, {{ $order['city'] }}, {{ $order['state'] }} - {{ $order['pincode'] }}, {{ $order['country'] }}
                        </p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Payment Method</p>
                        <p class="mt-1 font-semibold text-zinc-950">
                            {{ $order['payment_method'] === 'cod' ? 'Cash on Delivery (COD)' : 'Online Payment' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-t border-zinc-100 pt-4 flex items-center justify-between text-base font-bold text-zinc-950">
                    <span>Total Amount Paid / Payable</span>
                    <span>Rs. {{ number_format($order['total'], 2) }}</span>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-primary"
                >
                    Continue Shopping
                </a>
                <a
                    href="{{ route('account.profile') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                    View Account
                </a>
            </div>
        </div>
    </section>
</x-site.layout>

