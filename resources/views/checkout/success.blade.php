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

            @if($order->isDemo())
                <div class="mt-6 rounded-2xl border-2 border-dashed border-amber-400 bg-amber-50/90 p-4 text-left shadow-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-amber-300 px-2.5 py-1 text-xs font-extrabold uppercase tracking-wider text-amber-950">DEMO — NO REAL PAYMENT</span>
                        <span class="text-xs font-semibold text-amber-900">Simulated Order Confirmation</span>
                    </div>
                    <p class="mt-2 text-xs text-amber-950 leading-relaxed">
                        This transaction was executed in credential-free Demo Mode. No funds were debited, no external payment gateway was contacted, and no real financial transaction took place.
                    </p>
                </div>
            @elseif($order->payment_method === 'upi')
                <div class="mt-6 rounded-2xl border-2 border-dashed border-amber-400 bg-amber-50/90 p-4 text-left shadow-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-amber-300 px-2.5 py-1 text-xs font-extrabold uppercase tracking-wider text-amber-950">UPI PAYMENT — MANUAL CONFIRMATION REQUIRED</span>
                        <span class="text-xs font-semibold text-amber-900">Status: Pending Verification</span>
                    </div>
                    <p class="mt-2 text-xs text-amber-950 leading-relaxed">
                        Your UPI order has been registered. Your order status remains <strong>Pending</strong> while our store administration manually verifies receipt of payment in our merchant account.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('checkout.upi.show', $order->order_number) }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-950 hover:text-brand-primary underline">
                            View UPI QR Code &amp; Payment Details &rarr;
                        </a>
                    </div>
                </div>
            @endif

            @if ($order->order_confirmation_otp)
                <div class="mt-8 rounded-3xl border-2 border-emerald-300 bg-emerald-50/80 p-6 text-center shadow-xs">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-900">Your Order Confirmation &amp; Delivery OTP</p>
                    <p class="mt-2 text-3xl sm:text-4xl font-black tracking-widest text-emerald-950 font-mono">
                        {{ $order->order_confirmation_otp }}
                    </p>
                    <p class="mt-1 text-xs text-emerald-800">
                        This confirmation code has been verified and emailed to <strong>{{ $order->email }}</strong>.
                    </p>
                </div>
            @endif

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
                        <div class="mt-1 flex flex-wrap items-center gap-1.5 font-semibold text-zinc-950">
                            @if ($order->payment_method === 'upi')
                                <span>UPI Payment</span>
                                @if ($order->isDemo())
                                    <span class="rounded bg-amber-200 px-2 py-0.5 text-[10px] font-extrabold uppercase text-amber-950">DEMO — NO REAL PAYMENT</span>
                                @endif
                                @if($order->payment_status === 'paid' || $order->status === 'Confirmed')
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold uppercase text-emerald-900 ring-1 ring-emerald-300">PAID &amp; VERIFIED</span>
                                @else
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold uppercase text-amber-900 ring-1 ring-amber-300">MANUAL CONFIRMATION REQUIRED</span>
                                @endif
                            @elseif ($order->isDemo())
                                <span>Demo Payment</span>
                                <span class="rounded bg-amber-200 px-2 py-0.5 text-[10px] font-extrabold uppercase text-amber-950">DEMO — NO REAL PAYMENT</span>
                            @elseif ($order->payment_method === 'cod')
                                <span>Cash on Delivery (COD)</span>
                            @else
                                <span>Online Payment ({{ ucfirst($order->payment?->gateway ?? 'Gateway') }})</span>
                            @endif
                        </div>
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

            {{-- Earned Reward Coupon Card --}}
            @if ($order->earnedCoupon || (isset($earnedCoupon) && $earnedCoupon))
                @php
                    $coupon = $order->earnedCoupon ?? $earnedCoupon;
                @endphp
                <div class="mt-8 overflow-hidden rounded-3xl border-2 border-dashed border-amber-400 bg-gradient-to-br from-amber-500/10 via-amber-100/40 to-red-500/10 p-6 sm:p-8 text-left shadow-lg">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400 text-3xl shadow-md">
                                🎁
                            </span>
                            <div>
                                <span class="rounded-full bg-amber-200 px-3 py-0.5 text-xs font-bold text-amber-900">
                                    🎉 Online Payment Bonus Earned!
                                </span>
                                <h3 class="mt-1 text-2xl font-bold text-zinc-950">
                                    {{ $coupon->formattedDiscount() }} Voucher
                                </h3>
                                <p class="text-xs text-zinc-600">
                                    Thank you for paying online! Here is your exclusive reward voucher for your next order.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col items-start sm:items-end gap-2">
                            <div class="flex items-center gap-2 rounded-2xl border-2 border-dashed border-brand-primary bg-white px-4 py-2.5 shadow-sm">
                                <span class="font-mono text-base font-bold text-zinc-950 tracking-wider" id="earned-coupon-code">{{ $coupon->code }}</span>
                                <button
                                    type="button"
                                    onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000)"
                                    class="rounded-xl bg-zinc-950 px-3 py-1 text-xs font-bold text-white transition hover:bg-brand-primary"
                                >
                                    Copy
                                </button>
                            </div>
                            <p class="text-[11px] font-semibold text-zinc-500">
                                Valid until {{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : '30 days' }}
                                @if ((float)$coupon->min_order_amount > 0)
                                    &middot; Min order Rs. {{ number_format($coupon->min_order_amount, 2) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end border-t border-amber-200/60 pt-3">
                        <a href="{{ route('account.coupons') }}" class="text-xs font-bold text-brand-primary hover:underline">
                            View All My Coupons &rarr;
                        </a>
                    </div>
                </div>
            @endif

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
