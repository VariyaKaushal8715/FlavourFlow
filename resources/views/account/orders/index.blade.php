<x-site.layout :site="$site" page-title="My Orders | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <p class="text-sm font-semibold text-brand-primary">Purchase History</p>
                <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">My Orders</h1>
                <p class="mt-4 text-base leading-7 text-zinc-600">Track and manage your past spice selections and delivery states.</p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            @if($orders->isEmpty())
                <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-12 text-center shadow-[0_24px_70px_rgba(120,53,15,0.06)]" data-reveal>
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-brand-primary">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-zinc-950">No Orders Found</h2>
                    <p class="mt-2 text-sm text-zinc-600">You haven't placed any spice orders yet. Start exploring our rich collection!</p>
                    <a href="{{ route('home') }}#products" class="mt-6 inline-flex rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary">
                        Browse Spices
                    </a>
                </div>
            @else
                <div class="space-y-6" data-reveal>
                    @foreach($orders as $order)
                        <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] transition hover:border-amber-300">
                            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 pb-4">
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Order ID</span>
                                    <p class="font-bold text-zinc-950 tracking-wide text-sm sm:text-base">{{ $order->order_id }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Date</span>
                                    <p class="font-semibold text-zinc-950 text-sm">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Total</span>
                                    <p class="font-bold text-brand-primary text-sm">Rs. {{ number_format($order->total, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-zinc-400">Status</span>
                                    <p class="mt-0.5">
                                        <span @class([
                                            'rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                            'bg-emerald-100 text-emerald-800' => $order->status === 'Confirmed' || $order->status === 'Delivered',
                                            'bg-amber-100 text-amber-800' => $order->status === 'Shipped' || $order->status === 'Out for Delivery',
                                        ])>
                                            {{ $order->status }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                                <p class="text-xs text-zinc-500">
                                    Contains <span class="font-semibold text-zinc-700">{{ $order->items_count }} item(s)</span> | Paid via <span class="font-semibold text-zinc-700">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</span>
                                </p>

                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('account.orders.show', $order->order_id) }}"
                                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                    >
                                        View Details
                                    </a>
                                    <a
                                        href="{{ route('account.orders.track', $order->order_id) }}"
                                        class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-primary"
                                    >
                                        Track Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-site.layout>

