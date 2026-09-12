<x-site.layout :site="$site" page-title="Checkout | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <p class="text-sm font-semibold text-brand-primary">Secure Checkout</p>
                <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">Complete Your Order</h1>
                <p class="mt-4 text-base leading-7 text-zinc-600">Please provide your delivery and contact details below.</p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <form action="{{ route('checkout.store') }}" method="POST" class="grid gap-8 lg:grid-cols-[1fr_24rem]">
        @if ($errors->any())
            <div class="mt-4 rounded-xl bg-red-100 border border-red-200 p-4 text-red-800">
                <p class="font-semibold">Please check your address details and try again.</p>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
                @csrf

                <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.10)] ring-1 ring-white sm:p-8" data-reveal>
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-zinc-100 pb-5">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">Delivery Address</p>
                            <h2 class="mt-1 text-2xl font-semibold text-zinc-950">Shipping Details</h2>
                        </div>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-zinc-800">Full Name</span>
                                <input
                                    class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $profile?->full_name ?? $user?->name) }}"
                                    required
                                >
                                @error('name')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-zinc-800">Mobile Number</span>
                                <input
                                    class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="tel"
                                    name="mobile"
                                    value="{{ old('mobile', $profile?->mobile_number) }}"
                                    placeholder="e.g. +91 9999999999"
                                    required
                                >
                                @error('mobile')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>
                        </div>

                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">Email Address</span>
                            <input
                                class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                type="email"
                                name="email"
                                value="{{ old('email', $profile?->email ?? $user?->email) }}"
                                required
                            >
                            @error('email')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-zinc-800">Address Line</span>
                            <textarea
                                class="mt-2 min-h-24 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                name="address"
                                required
                            >{{ old('address', $profile?->address) }}</textarea>
                            @error('address')
                                <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <label class="block">
                                <span class="text-sm font-semibold text-zinc-800">City</span>
                                <input
                                    class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="text"
                                    name="city"
                                    id="checkout-city"
                                    value="{{ old('city', $profile?->city) }}"
                                    required
                                >
                                @error('city')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-zinc-800">State</span>
                                <input
                                    class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="text"
                                    name="state"
                                    id="checkout-state"
                                    value="{{ old('state', $profile?->state) }}"
                                    required
                                >
                                @error('state')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-zinc-800">Pincode</span>
                                <input
                                    class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="text"
                                    name="pincode"
                                    value="{{ old('pincode', $profile?->postal_code) }}"
                                    required
                                >
                                @error('pincode')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-zinc-800">Country</span>
                                <input
                                    class="mt-2 w-full rounded-2xl border border-amber-200/80 bg-amber-50/30 px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition hover:border-amber-300 hover:bg-white focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/15"
                                    type="text"
                                    name="country"
                                    id="checkout-country"
                                    value="{{ old('country', $profile?->country ?? 'India') }}"
                                    required
                                >
                                @error('country')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>
                        </div>

                        {{-- Real-time Delivery Status Feedback --}}
                        <div id="checkout-delivery-feedback" class="mt-4 hidden rounded-2xl p-3.5 text-xs font-semibold transition-all">
                            {{-- Populated dynamically --}}
                        </div>
                    </div>

                    {{-- Delivery Options Section --}}
                    <div class="mt-8 border-t border-zinc-100 pt-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">Delivery Method</p>
                        <h3 class="mt-1 text-xl font-semibold text-zinc-950">Select Delivery Option</h3>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label id="delivery-card-standard" class="relative flex cursor-pointer flex-col rounded-2xl border-2 border-brand-primary bg-amber-50/20 p-4 transition shadow-sm hover:border-brand-primary">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="radio"
                                            name="delivery_option"
                                            value="standard"
                                            class="h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                            checked
                                            id="delivery-standard-radio"
                                        >
                                        <div>
                                            <p class="text-sm font-semibold text-zinc-950">Standard Delivery</p>
                                            <p class="text-xs text-zinc-500">Regular shipping</p>
                                        </div>
                                    </div>
                                    @if ($subtotal < 300)
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">+ ₹30</span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">FREE (₹0)</span>
                                    @endif
                                </div>
                                <div class="mt-3 flex items-center justify-between border-t border-amber-200/60 pt-2.5 text-xs">
                                    <span class="text-zinc-500">Estimated Delivery:</span>
                                    <span class="font-bold text-zinc-900 flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        4–5 Days
                                    </span>
                                </div>
                            </label>

                            <label id="delivery-card-express" class="relative flex cursor-pointer flex-col rounded-2xl border-2 border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20 hover:border-amber-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="radio"
                                            name="delivery_option"
                                            value="express"
                                            class="h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                            id="delivery-express-radio"
                                        >
                                        <div>
                                            <p class="text-sm font-semibold text-zinc-950">Express Delivery</p>
                                            <p class="text-xs text-zinc-500">Priority fast dispatch</p>
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">+ ₹99</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between border-t border-amber-200/60 pt-2.5 text-xs">
                                    <span class="text-zinc-500">Estimated Delivery:</span>
                                    <span class="font-bold text-brand-primary flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                                        1–2 Days
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-zinc-100 pt-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">Payment Options</p>
                        <h3 class="mt-1 text-xl font-semibold text-zinc-950">Select Payment Method</h3>

                        <div class="mt-5 space-y-4">
                            <label id="label-payment-cod" class="flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    id="payment-cod-radio"
                                    value="cod"
                                    class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                    checked
                                >
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-zinc-950">Cash on Delivery (COD)</p>
                                    </div>
                                    <p class="mt-1 text-xs text-zinc-500">Pay with cash when your premium spices are delivered to your door.</p>
                                </div>
                            </label>

                            <label id="label-payment-online" class="flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    id="payment-online-radio"
                                    value="online"
                                    class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                >
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-zinc-950">Online Payment</p>
                                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-brand-primary animate-pulse">
                                            🎁 Get Reward Coupon
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-zinc-500">Pay securely online using credit/debit card, UPI, or mobile wallets and earn a discount voucher on ₹1,000+.</p>
                                </div>
                            </label>

                            <div id="online-payment-ui" class="hidden rounded-2xl border border-dashed border-amber-300/60 bg-amber-50/30 p-5 space-y-4 transition">
                                <p class="text-xs font-semibold uppercase tracking-wider text-brand-primary">Simulated Secure Payment Gateway</p>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <input
                                        type="text"
                                        placeholder="Card Number"
                                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs outline-none focus:border-brand-primary"
                                        disabled
                                    >
                                    <input
                                        type="text"
                                        placeholder="Name on Card"
                                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs outline-none focus:border-brand-primary"
                                        disabled
                                    >
                                </div>
                                <div class="grid gap-4 grid-cols-3">
                                    <input
                                        type="text"
                                        placeholder="Expiry MM/YY"
                                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs outline-none focus:border-brand-primary"
                                        disabled
                                    >
                                    <input
                                        type="text"
                                        placeholder="CVV"
                                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs outline-none focus:border-brand-primary"
                                        disabled
                                    >
                                    <span class="inline-flex items-center justify-center text-[10px] font-semibold text-emerald-700 bg-emerald-100 rounded-lg">Instant Online Demo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="h-fit rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.10)] ring-1 ring-white">
                    <h2 class="text-lg font-semibold text-zinc-950">Order Items</h2>
                    
                    <div class="mt-4 max-h-60 overflow-y-auto divide-y divide-zinc-100 pr-1">
                        @foreach ($items as $item)
                            <div class="flex items-center justify-between py-3">
                                <div class="min-w-0 pr-4">
                                    <p class="text-sm font-semibold text-zinc-950 truncate">{{ $item['product']->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $item['quantity'] }} × {{ $item['unit'] }}</p>
                                </div>
                                <span class="text-sm font-medium text-zinc-950">Rs. {{ number_format($item['line_total'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Coupon Input --}}
                    <div class="mt-6 border-t border-zinc-200 pt-5">
                        <label class="block text-sm font-semibold text-zinc-800">Discount Coupon</label>
                        <div class="mt-2 flex gap-2">
                            <input
                                type="text"
                                id="coupon-code-input"
                                class="flex-1 rounded-xl border border-amber-200/80 bg-amber-50/20 px-3 py-2 text-sm text-zinc-950 uppercase shadow-sm outline-none focus:border-brand-primary"
                                placeholder="Enter coupon code"
                            >
                            <button
                                type="button"
                                id="apply-coupon-btn"
                                class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-brand-primary"
                            >
                                Apply
                            </button>
                        </div>
                        <p id="coupon-feedback" class="mt-1.5 hidden text-xs font-semibold"></p>
                        
                        <div id="applied-coupon-pill" class="mt-2 hidden items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 border border-emerald-100">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21a3.745 3.745 0 01-3.12-1.593 3.745 3.745 0 01-3.297-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                                <span class="text-xs font-semibold text-emerald-800" id="applied-coupon-text"></span>
                            </div>
                            <button type="button" id="remove-coupon-btn" class="text-xs font-bold text-red-600 hover:text-red-700">Remove</button>
                        </div>
                        
                        <input type="hidden" name="coupon_code" id="applied-coupon-hidden-input">

                        {{-- Available Coupons Quick Drawer --}}
                        @if (isset($availableCoupons) && $availableCoupons->count() > 0)
                            <div class="mt-4 rounded-2xl border border-amber-200/80 bg-amber-50/40 p-3.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-zinc-900 flex items-center gap-1.5">
                                        <span>🎁</span> Available For You ({{ $availableCoupons->count() }})
                                    </span>
                                    <button type="button" id="toggle-available-coupons-btn" class="text-[11px] font-bold text-brand-primary hover:underline">
                                        View &darr;
                                    </button>
                                </div>
                                <div id="available-coupons-list" class="mt-3 space-y-2 hidden max-h-48 overflow-y-auto pr-1">
                                    @foreach ($availableCoupons as $availCoupon)
                                        <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-white p-2.5 text-xs shadow-2xs">
                                            <div class="min-w-0 pr-2">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-mono font-bold text-zinc-950">{{ $availCoupon->code }}</span>
                                                    <span @class([
                                                        'rounded px-1.5 py-0.5 text-[9px] font-semibold',
                                                        'bg-sky-100 text-sky-800' => $availCoupon->payment_method_eligibility === 'online',
                                                        'bg-amber-100 text-amber-800' => $availCoupon->payment_method_eligibility === 'cod',
                                                        'bg-zinc-100 text-zinc-700' => $availCoupon->payment_method_eligibility === 'both',
                                                    ])>
                                                        {{ $availCoupon->paymentMethodLabel() }}
                                                    </span>
                                                </div>
                                                <p class="text-[10px] font-semibold text-brand-primary">{{ $availCoupon->formattedDiscount() }}</p>
                                                @if ((float)$availCoupon->min_order_amount > 0)
                                                    <p class="text-[10px] text-zinc-400">Min: ₹{{ number_format($availCoupon->min_order_amount, 2) }}</p>
                                                @endif
                                            </div>
                                            <button
                                                type="button"
                                                onclick="applyQuickCoupon('{{ $availCoupon->code }}')"
                                                class="shrink-0 rounded-lg bg-zinc-950 px-2.5 py-1 text-[11px] font-bold text-white transition hover:bg-brand-primary"
                                            >
                                                Apply
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-zinc-200 pt-5 space-y-3 text-sm text-zinc-600">
                        <div class="flex items-center justify-between">
                            <span>Subtotal</span>
                            <span>Rs. {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between hidden text-emerald-700 font-semibold" id="coupon-discount-row">
                            <span>Coupon Discount</span>
                            <span id="coupon-discount-value">Rs. 0.00</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Delivery Charges</span>
                            <span id="delivery-charge-value">{{ $deliveryCharge > 0 ? 'Rs. '.number_format($deliveryCharge, 2) : 'FREE' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-zinc-200 pt-4 text-lg font-semibold text-zinc-950">
                            <span>Total</span>
                            <span id="grand-total-value">Rs. {{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        id="checkout-submit-btn"
                        class="mt-6 block w-full rounded-2xl bg-zinc-950 py-3.5 text-center text-sm font-semibold text-white shadow-lg transition hover:bg-brand-primary disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Confirm and Place Order
                    </button>
                </aside>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const applyBtn = document.getElementById('apply-coupon-btn');
                        const removeBtn = document.getElementById('remove-coupon-btn');
                        const input = document.getElementById('coupon-code-input');
                        const feedback = document.getElementById('coupon-feedback');
                        const pill = document.getElementById('applied-coupon-pill');
                        const pillText = document.getElementById('applied-coupon-text');
                        const hiddenInput = document.getElementById('applied-coupon-hidden-input');
                        
                        const discountRow = document.getElementById('coupon-discount-row');
                        const discountVal = document.getElementById('coupon-discount-value');
                        const deliveryVal = document.getElementById('delivery-charge-value');
                        const totalVal = document.getElementById('grand-total-value');
                        
                        const subtotal = parseFloat("{{ $subtotal }}");
                        const standardDeliveryCharge = subtotal < 300 ? 30.0 : 0.0;
                        let currentDeliveryCharge = standardDeliveryCharge;
                        let currentDiscount = 0.0;
                        let appliedCouponCode = null;
                        let appliedCouponEligibility = null;
                        
                        const standardRadio = document.getElementById('delivery-standard-radio');
                        const expressRadio = document.getElementById('delivery-express-radio');
                        const standardCard = document.getElementById('delivery-card-standard');
                        const expressCard = document.getElementById('delivery-card-express');

                        // Delivery Check Variables
                        const countryInput = document.getElementById('checkout-country');
                        const stateInput = document.getElementById('checkout-state');
                        const cityInput = document.getElementById('checkout-city');
                        const deliveryFeedback = document.getElementById('checkout-delivery-feedback');
                        const submitBtn = document.getElementById('checkout-submit-btn');
                        let isLocationDeliverable = true;
                        let checkTimeout = null;

                        async function checkCheckoutDelivery() {
                            const country = (countryInput?.value || '').trim();
                            const state = (stateInput?.value || '').trim();
                            const city = (cityInput?.value || '').trim();

                            if (!country) return;

                            try {
                                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                                const res = await fetch("{{ route('delivery.check') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: JSON.stringify({ country, state, city })
                                });

                                const data = await res.json();
                                deliveryFeedback.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-900', 'border-emerald-200', 'bg-red-50', 'text-red-900', 'border-red-200', 'border');
                                deliveryFeedback.classList.add('border');

                                if (data.deliverable) {
                                    isLocationDeliverable = true;
                                    deliveryFeedback.classList.add('bg-emerald-50', 'text-emerald-900', 'border-emerald-200');
                                    deliveryFeedback.innerHTML = `
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-bold">&check;</span>
                                            <div>
                                                <p class="font-bold text-emerald-950">${data.message || 'Deliverable to this location!'}</p>
                                                <p class="text-[11px] text-emerald-700 font-normal">Shipping available for your order.</p>
                                            </div>
                                        </div>
                                    `;
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                    }
                                } else {
                                    isLocationDeliverable = false;
                                    deliveryFeedback.classList.add('bg-red-50', 'text-red-900', 'border-red-200');
                                    deliveryFeedback.innerHTML = `
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-700 font-bold">&times;</span>
                                            <div>
                                                <p class="font-bold text-red-950">Sorry, we don’t deliver to this location.</p>
                                                <p class="text-[11px] text-red-700 font-normal">Please adjust your shipping country, state, or city to proceed.</p>
                                            </div>
                                        </div>
                                    `;
                                    if (submitBtn) {
                                        submitBtn.disabled = true;
                                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                    }
                                }
                            } catch (e) {
                                console.error('Checkout delivery check failed:', e);
                            }
                        }

                        function debounceDeliveryCheck() {
                            clearTimeout(checkTimeout);
                            checkTimeout = setTimeout(checkCheckoutDelivery, 350);
                        }

                        [countryInput, stateInput, cityInput].forEach(el => {
                            if (el) {
                                el.addEventListener('input', debounceDeliveryCheck);
                                el.addEventListener('change', debounceDeliveryCheck);
                            }
                        });

                        // Initial check on page load
                        if (countryInput && countryInput.value) {
                            checkCheckoutDelivery();
                        }

                        const updateTotals = () => {
                            const total = Math.max(0, subtotal - currentDiscount + currentDeliveryCharge);
                            totalVal.textContent = 'Rs. ' + total.toFixed(2);
                        };

                        const setDeliveryOption = (option) => {
                            if (option === 'express') {
                                expressRadio.checked = true;
                                currentDeliveryCharge = 99.0;
                                deliveryVal.textContent = 'Rs. 99.00';
                                
                                expressCard.className = "relative flex cursor-pointer flex-col rounded-2xl border-2 border-brand-primary bg-amber-50/20 p-4 transition shadow-sm hover:border-brand-primary";
                                standardCard.className = "relative flex cursor-pointer flex-col rounded-2xl border-2 border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20 hover:border-amber-300";
                            } else {
                                standardRadio.checked = true;
                                currentDeliveryCharge = standardDeliveryCharge;
                                deliveryVal.textContent = standardDeliveryCharge > 0 ? ('Rs. ' + standardDeliveryCharge.toFixed(2)) : 'FREE';
                                
                                standardCard.className = "relative flex cursor-pointer flex-col rounded-2xl border-2 border-brand-primary bg-amber-50/20 p-4 transition shadow-sm hover:border-brand-primary";
                                expressCard.className = "relative flex cursor-pointer flex-col rounded-2xl border-2 border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20 hover:border-amber-300";
                            }
                            updateTotals();
                        };

                        standardRadio?.addEventListener('change', () => setDeliveryOption('standard'));
                        expressRadio?.addEventListener('change', () => setDeliveryOption('express'));

                        // COD Modal & Payment Handling
                        const codRadio = document.getElementById('payment-cod-radio');
                        const onlineRadio = document.getElementById('payment-online-radio');
                        const onlineUi = document.getElementById('online-payment-ui');
                        const codModal = document.getElementById('cod-incentive-modal');
                        const btnSwitchOnline = document.getElementById('btn-switch-to-online');
                        const btnContinueCod = document.getElementById('btn-continue-cod');

                        let hasPromptedCodIncentive = false;

                        function handlePaymentChange() {
                            const isOnline = onlineRadio && onlineRadio.checked;
                            if (isOnline) {
                                onlineUi?.classList.remove('hidden');
                            } else {
                                onlineUi?.classList.add('hidden');
                            }

                            // Dynamic validation: check eligibility of currently applied coupon
                            if (appliedCouponCode && appliedCouponEligibility) {
                                if (!isOnline && appliedCouponEligibility === 'online') {
                                    removeAppliedCoupon();
                                    showFeedback('This coupon is valid only for online payment. Please select online payment to use this coupon.', false);
                                } else if (isOnline && appliedCouponEligibility === 'cod') {
                                    removeAppliedCoupon();
                                    showFeedback('This coupon is valid only for Cash on Delivery. Please select Cash on Delivery to use this coupon.', false);
                                }
                            }
                        }

                        function removeAppliedCoupon() {
                            discountRow.classList.add('hidden');
                            currentDiscount = 0.0;
                            updateTotals();
                            
                            pill.classList.add('hidden');
                            pill.classList.remove('flex');
                            hiddenInput.value = '';
                            input.disabled = false;
                            applyBtn.disabled = false;
                            appliedCouponCode = null;
                            appliedCouponEligibility = null;
                        }

                        codRadio?.addEventListener('click', (e) => {
                            if (!hasPromptedCodIncentive) {
                                hasPromptedCodIncentive = true;
                                codModal.classList.remove('hidden');
                            }
                            handlePaymentChange();
                        });

                        onlineRadio?.addEventListener('change', () => {
                            handlePaymentChange();
                        });

                        btnSwitchOnline?.addEventListener('click', () => {
                            onlineRadio.checked = true;
                            handlePaymentChange();
                            codModal.classList.add('hidden');
                        });

                        btnContinueCod?.addEventListener('click', () => {
                            codRadio.checked = true;
                            handlePaymentChange();
                            codModal.classList.add('hidden');
                        });

                        // Available Coupons Drawer Toggle
                        const toggleCouponsBtn = document.getElementById('toggle-available-coupons-btn');
                        const availableCouponsList = document.getElementById('available-coupons-list');
                        toggleCouponsBtn?.addEventListener('click', () => {
                            if (availableCouponsList.classList.contains('hidden')) {
                                availableCouponsList.classList.remove('hidden');
                                toggleCouponsBtn.innerHTML = 'Hide &uarr;';
                            } else {
                                availableCouponsList.classList.add('hidden');
                                toggleCouponsBtn.innerHTML = 'View &darr;';
                            }
                        });

                        window.applyQuickCoupon = function(code) {
                            if (input) {
                                input.value = code;
                                applyBtn.click();
                            }
                        };

                        const showFeedback = (text, isSuccess) => {
                            feedback.textContent = text;
                            feedback.className = `mt-1.5 text-xs font-semibold ${isSuccess ? 'text-emerald-600' : 'text-red-600'}`;
                            feedback.classList.remove('hidden');
                        };

                        const hideFeedback = () => {
                            feedback.classList.add('hidden');
                        };

                        applyBtn?.addEventListener('click', () => {
                            const code = input.value.trim();
                            if (!code) {
                                showFeedback('Please enter a coupon code.', false);
                                return;
                            }

                            hideFeedback();
                            applyBtn.disabled = true;
                            applyBtn.textContent = 'Applying...';

                            const selectedDelivery = expressRadio.checked ? 'express' : 'standard';
                            const selectedPaymentMethod = onlineRadio && onlineRadio.checked ? 'online' : 'cod';

                            fetch("{{ route('checkout.coupon.apply') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    coupon_code: code,
                                    delivery_option: selectedDelivery,
                                    payment_method: selectedPaymentMethod
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                applyBtn.disabled = false;
                                applyBtn.textContent = 'Apply';

                                if (data.success) {
                                    currentDiscount = parseFloat(data.discount);
                                    discountVal.textContent = 'Rs. -' + currentDiscount.toFixed(2);
                                    discountRow.classList.remove('hidden');
                                    
                                    pillText.textContent = `Applied: ${data.coupon.code}`;
                                    pill.classList.remove('hidden');
                                    pill.classList.add('flex');
                                    hiddenInput.value = data.coupon.code;
                                    appliedCouponCode = data.coupon.code;
                                    appliedCouponEligibility = data.coupon.payment_method_eligibility;
                                    input.value = '';
                                    input.disabled = true;
                                    applyBtn.disabled = true;
                                    
                                    updateTotals();
                                    showFeedback(data.message, true);
                                } else {
                                    showFeedback(data.message || 'Error applying coupon.', false);
                                }
                            })
                            .catch(err => {
                                applyBtn.disabled = false;
                                applyBtn.textContent = 'Apply';
                                showFeedback('Error contacting server. Please try again.', false);
                                console.error(err);
                            });
                        });

                        removeBtn?.addEventListener('click', () => {
                            removeAppliedCoupon();
                            hideFeedback();
                        });
                    });
                </script>
            </form>
        </div>

        {{-- COD Incentive Modal --}}
        <div id="cod-incentive-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-zinc-950/70 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-amber-300/80 bg-gradient-to-b from-white via-amber-50/40 to-white p-6 shadow-2xl">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-amber-100 text-3xl shadow-inner">
                    🎁
                </div>
                <div class="mt-4 text-center">
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-brand-primary">
                        Special Reward Offer
                    </span>
                    <h3 class="mt-2 text-xl font-bold text-zinc-950">Pay Online & Get a Discount Coupon 🎁</h3>
                    <p class="mt-2 text-xs leading-relaxed text-zinc-600">
                        Pay online securely with Card, UPI, or Wallets on orders above ₹1,000 to automatically earn an exclusive <strong>10% OFF discount coupon</strong> for your next purchase!
                    </p>
                    <p class="mt-2 text-[11px] font-semibold text-amber-900 bg-amber-100/70 rounded-xl p-2.5">
                        ⚡ Valid for online payments. COD orders do not receive reward discount coupons.
                    </p>
                </div>

                <div class="mt-6 space-y-2.5">
                    <button
                        type="button"
                        id="btn-switch-to-online"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-zinc-950 py-3.5 text-xs font-bold text-white shadow-lg transition hover:bg-brand-primary"
                    >
                        <span>⚡ Switch to Online & Claim Coupon</span>
                    </button>
                    <button
                        type="button"
                        id="btn-continue-cod"
                        class="flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white py-3 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-50"
                    >
                        <span>Continue with COD</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</x-site.layout>

