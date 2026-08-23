<x-site.layout :site="$site" page-title="Order Details | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <div class="flex items-center gap-2">
                    <a href="{{ route('account.orders') }}" class="text-xs font-semibold text-brand-primary hover:underline">&larr; Back to My Orders</a>
                </div>
                <h1 class="mt-4 text-3xl font-semibold text-zinc-950 sm:text-4xl">Order Details</h1>
                <p class="mt-2 text-base leading-7 text-zinc-600">Review items, delivery destination, and tracking summary.</p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_24rem]" data-reveal>
                <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)] sm:p-8">
                    <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Ordered Items</h2>

                    <div class="divide-y divide-zinc-100">
                        @foreach ($order->items as $item)
                            <article class="flex gap-4 py-4 items-center">
                                @if($item->product && $item->product->image_path)
                                    <img class="aspect-square w-16 rounded-lg object-cover border border-zinc-200" src="{{ asset($item->product->image_path) }}" alt="{{ $item->product_name }}">
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-zinc-950 truncate">{{ $item->product_name }}</h3>
                                    <p class="mt-0.5 text-xs text-zinc-500">{{ $item->quantity }} × {{ $item->unit }}</p>
                                    <p class="mt-1 text-xs font-bold text-zinc-950">Rs. {{ number_format($item->unit_price, 2) }}</p>
                                </div>
                                <span class="text-sm font-semibold text-zinc-950">Rs. {{ number_format($item->total_price, 2) }}</span>
                            </article>
                        @endforeach
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Order Info</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Order ID:</span>
                                <span class="font-bold text-zinc-950">{{ $order->order_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Status:</span>
                                <span class="font-semibold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded text-xs">{{ $order->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Date:</span>
                                <span class="font-semibold text-zinc-950">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Payment:</span>
                                <span class="font-semibold text-zinc-950">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('account.orders.track', $order->order_number) }}" class="mt-5 block w-full rounded-2xl bg-zinc-950 py-3 text-center text-xs font-semibold text-white transition hover:bg-brand-primary">
                            Track Order Progress
                        </a>
                    </div>

                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Delivery Destination</h2>
                        <div class="mt-4 text-sm text-zinc-800 space-y-1">
                            <p class="font-semibold text-zinc-950">{{ $order->name }}</p>
                            <p>{{ $order->address }}</p>
                            <p>{{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                            <p>{{ $order->country }}</p>
                            <p class="mt-2 text-xs text-zinc-500">Mobile: {{ $order->mobile }}</p>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.06)]">
                        <h2 class="text-lg font-semibold text-zinc-950 border-b border-zinc-100 pb-4">Order Summary</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Subtotal:</span>
                                <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Delivery Charge:</span>
                                <span>{{ $order->delivery_charge > 0 ? 'Rs. '.number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                            </div>
                            <div class="flex justify-between border-t border-zinc-100 pt-3 text-base font-bold text-zinc-950">
                                <span>Total:</span>
                                <span>Rs. {{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</x-site.layout>

