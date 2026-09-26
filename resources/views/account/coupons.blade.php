<x-site.layout :site="$site" page-title="My Coupons | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    {{-- Hero Section --}}
    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" data-reveal>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs text-brand-primary">🎁</span>
                        <p class="text-sm font-semibold text-brand-primary">Rewards & Savings</p>
                    </div>
                    <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">My Coupons & Discounts</h1>
                    <p class="mt-3 text-base leading-7 text-zinc-600">Exclusive discount vouchers earned from online purchases and special promotions.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-brand-primary hover:shadow-lg"
                    >
                        Explore Spices
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content --}}
    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            {{-- Tabs --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-amber-200/70 pb-5" data-reveal>
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                    <a
                        href="{{ route('account.coupons', ['status' => 'all']) }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold transition',
                            'bg-zinc-950 text-white shadow-sm' => $currentFilter === 'all',
                            'border border-amber-200/80 bg-white text-zinc-700 hover:bg-amber-50/50' => $currentFilter !== 'all',
                        ])
                    >
                        <span>All</span>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $stats['all'] }}</span>
                    </a>
                    <a
                        href="{{ route('account.coupons', ['status' => 'available']) }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold transition',
                            'bg-emerald-700 text-white shadow-sm' => $currentFilter === 'available',
                            'border border-amber-200/80 bg-white text-zinc-700 hover:bg-emerald-50/50' => $currentFilter !== 'available',
                        ])
                    >
                        <span>Available</span>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $stats['available'] }}</span>
                    </a>
                    <a
                        href="{{ route('account.coupons', ['status' => 'used']) }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold transition',
                            'bg-zinc-800 text-white shadow-sm' => $currentFilter === 'used',
                            'border border-amber-200/80 bg-white text-zinc-700 hover:bg-zinc-50' => $currentFilter !== 'used',
                        ])
                    >
                        <span>Redeemed</span>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $stats['used'] }}</span>
                    </a>
                    <a
                        href="{{ route('account.coupons', ['status' => 'expired']) }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold transition',
                            'bg-red-800 text-white shadow-sm' => $currentFilter === 'expired',
                            'border border-amber-200/80 bg-white text-zinc-700 hover:bg-red-50' => $currentFilter !== 'expired',
                        ])
                    >
                        <span>Expired</span>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $stats['expired'] }}</span>
                    </a>
                </div>

                <div class="text-xs font-medium text-zinc-500">
                    💡 Tip: Pay online on orders above ₹1,000 to automatically earn reward vouchers!
                </div>
            </div>

            {{-- Toast Notification for Clipboard Copy --}}
            <div id="copy-toast" class="fixed bottom-6 right-6 z-50 hidden translate-y-4 rounded-2xl border border-emerald-300 bg-emerald-950 px-5 py-3 text-sm font-semibold text-white shadow-2xl transition-all duration-300">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <span>Coupon code copied to clipboard!</span>
                </div>
            </div>

            {{-- Coupon Cards Grid --}}
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal>
                @forelse ($coupons as $coupon)
                    @php
                        $isAvailable = $coupon->computed_status === 'available';
                        $isUsed = $coupon->computed_status === 'used';
                        $isExpired = $coupon->computed_status === 'expired';
                    @endphp

                    <div @class([
                        'relative flex flex-col justify-between overflow-hidden rounded-3xl border p-6 transition duration-200 shadow-sm',
                        'border-amber-300/80 bg-gradient-to-br from-white via-amber-50/30 to-amber-100/30 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(180,83,9,0.12)] ring-1 ring-amber-200/50' => $isAvailable,
                        'border-zinc-200 bg-zinc-50/80 opacity-75 grayscale-[20%]' => $isUsed,
                        'border-red-200 bg-red-50/40 opacity-70' => $isExpired,
                        'border-zinc-200 bg-zinc-100/60 opacity-60' => $coupon->computed_status === 'disabled',
                    ])>
                        {{-- Top Badge & Status --}}
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center rounded-xl bg-amber-100 p-2 text-brand-primary shadow-sm">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-brand-primary">
                                            {{ $coupon->discount_type === 'percent' ? 'Percentage Savings' : 'Flat Discount' }}
                                        </p>
                                        <h3 class="text-xl font-bold text-zinc-950">
                                            {{ $coupon->formattedDiscount() }}
                                        </h3>
                                    </div>
                                </div>

                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-bold shadow-sm',
                                    'bg-emerald-100 text-emerald-800' => $isAvailable,
                                    'bg-zinc-200 text-zinc-700' => $isUsed,
                                    'bg-red-100 text-red-800' => $isExpired,
                                    'bg-zinc-200 text-zinc-600' => $coupon->computed_status === 'disabled',
                                ])>
                                    {{ ucfirst($coupon->computed_status) }}
                                </span>
                            </div>

                            @if ($coupon->title)
                                <p class="mt-3 text-sm font-semibold text-zinc-900">{{ $coupon->title }}</p>
                            @endif

                            <div class="mt-2 flex items-center gap-1.5">
                                <span @class([
                                    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold',
                                    'bg-sky-100 text-sky-800' => $coupon->payment_method_eligibility === 'online',
                                    'bg-amber-100 text-amber-800' => $coupon->payment_method_eligibility === 'cod',
                                    'bg-emerald-100 text-emerald-800' => $coupon->payment_method_eligibility === 'both',
                                ])>
                                    @if ($coupon->payment_method_eligibility === 'online')
                                        💳 Online Payment Only
                                    @elseif ($coupon->payment_method_eligibility === 'cod')
                                        💵 COD Only
                                    @else
                                        ✨ Online & COD
                                    @endif
                                </span>
                            </div>

                            @if ($coupon->description)
                                <p class="mt-1 text-xs leading-5 text-zinc-600">{{ $coupon->description }}</p>
                            @endif

                            {{-- Terms & Constraints --}}
                            <div class="mt-4 space-y-1.5 rounded-2xl border border-amber-200/50 bg-white/80 p-3 text-xs text-zinc-600">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">Min Order:</span>
                                    <span class="font-semibold text-zinc-900">
                                        {{ (float) $coupon->min_order_amount > 0 ? 'Rs. '.number_format($coupon->min_order_amount, 2) : 'No Minimum' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">Eligibility:</span>
                                    <span class="font-semibold text-zinc-900">{{ $coupon->paymentMethodLabel() }}</span>
                                </div>
                                @if ($coupon->max_discount)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-500">Max Discount:</span>
                                        <span class="font-semibold text-zinc-900">Rs. {{ number_format($coupon->max_discount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">Valid Until:</span>
                                    <span @class([
                                        'font-semibold',
                                        'text-red-700' => $isExpired,
                                        'text-zinc-900' => ! $isExpired,
                                    ])>
                                        {{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'No Expiry' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Coupon Code Box & Actions --}}
                        <div class="mt-6 border-t border-dashed border-amber-300/80 pt-4">
                            <div class="flex items-center justify-between rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/50 px-3.5 py-2.5">
                                <span class="font-mono text-sm font-bold tracking-widest text-zinc-950">{{ $coupon->code }}</span>
                                <button
                                    type="button"
                                    onclick="copyCouponCode('{{ $coupon->code }}')"
                                    class="inline-flex items-center gap-1 rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-brand-primary shadow-sm ring-1 ring-amber-200 transition hover:bg-brand-primary hover:text-white"
                                    title="Copy coupon code"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" /></svg>
                                    <span>Copy</span>
                                </button>
                            </div>

                            @if ($isAvailable)
                                <a
                                    href="{{ route('cart.index') }}"
                                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-zinc-950 py-2.5 text-center text-xs font-semibold text-white shadow transition hover:bg-brand-primary"
                                >
                                    <span>Apply at Checkout &rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-3xl border border-amber-200/70 bg-white/95 p-12 text-center shadow-sm">
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-amber-100 text-3xl">🎁</span>
                        <h3 class="mt-4 text-lg font-bold text-zinc-950">No coupons found</h3>
                        <p class="mt-2 text-sm text-zinc-600">
                            {{ $currentFilter === 'all' ? 'You do not have any coupons yet. Complete an online payment above ₹1,000 to earn your first reward!' : 'No coupons matching the selected filter.' }}
                        </p>
                        <a
                            href="{{ route('home') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-brand-primary"
                        >
                            Shop Spices Now
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <script>
        function copyCouponCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                const toast = document.getElementById('copy-toast');
                toast.classList.remove('hidden', 'translate-y-4');
                toast.classList.add('translate-y-0');
                
                setTimeout(() => {
                    toast.classList.add('translate-y-4');
                    setTimeout(() => toast.classList.add('hidden'), 300);
                }, 2500);
            }).catch(err => {
                console.error('Could not copy coupon code: ', err);
            });
        }
    </script>
</x-site.layout>
