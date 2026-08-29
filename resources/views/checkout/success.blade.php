<x-site.layout :site="$site" page-title="Order Confirmed | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="min-h-[75vh] bg-[radial-gradient(circle_at_top,rgba(244,185,66,0.12),transparent_38%),linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-16 sm:py-24">
        <div class="mx-auto max-w-3xl px-6 text-center lg:px-8" data-reveal>
            <!-- Checkmark Animation container -->
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 shadow-md animate-bounce">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl">Order Placed Successfully!</h1>
            <p class="mt-4 text-base text-zinc-600 leading-relaxed">
                Thank you for shopping with us! Your order has been registered and is now being prepared for shipping.
            </p>

            <div class="mt-8 rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] text-left sm:p-8">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                    <h2 class="text-lg font-semibold text-zinc-950">Order Summary</h2>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 shadow-sm">
                        {{ $order->status }}
                    </span>
                </div>
                
                <div class="mt-6 grid gap-6 border-b border-zinc-100 pb-6 text-sm sm:grid-cols-2">
                    <div>
                        <p class="font-medium text-zinc-400">Order ID</p>
                        <p class="mt-1 font-bold text-zinc-950 text-base tracking-wider">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Order Date</p>
                        <p class="mt-1 font-semibold text-zinc-950">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Total Amount</p>
                        <p class="mt-1 font-bold text-brand-primary text-base">Rs. {{ number_format($order->total_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Payment Method</p>
                        <p class="mt-1 font-semibold text-zinc-950">
                            {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : 'Online Payment' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="font-medium text-zinc-400">Delivery Details</p>
                    <div class="mt-2 text-sm text-zinc-800 space-y-1">
                        <p class="font-semibold text-zinc-950">{{ $order->name }}</p>
                        <p>{{ $order->address }}</p>
                        <p>{{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                        <p>{{ $order->country }}</p>
                        <p class="mt-2 font-medium">Contact: {{ $order->mobile }} | {{ $order->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Track Order, My Orders, Continue Shopping buttons -->
            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a
                    href="{{ route('account.orders.track', $order->order_number) }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-6 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-primary"
                >
                    Track Order
                </a>
                <a
                    href="{{ route('account.orders') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                    My Orders
                </a>
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-transparent bg-amber-100 px-6 py-3.5 text-sm font-semibold text-brand-primary transition hover:bg-amber-200"
                >
                    Continue Shopping
                </a>
            </div>
        </div>
    </section>
</x-site.layout>
