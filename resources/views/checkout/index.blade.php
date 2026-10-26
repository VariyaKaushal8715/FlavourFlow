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
            <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" class="grid gap-8 lg:grid-cols-[1fr_24rem]">
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
                                    value="{{ old('state', $profile?->state ?: 'Gujarat') }}"
                                    readonly
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
                                    value="{{ old('country', $profile?->country ?? 'India') }}"
                                    required
                                >
                                @error('country')
                                    <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>
                                @enderror
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-zinc-100 pt-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">Payment Options</p>
                        <h3 class="mt-1 text-xl font-semibold text-zinc-950">Select Payment Method</h3>

                        @php
                            $defaultSelectedMethod = old('payment_method');
                            if (! $defaultSelectedMethod) {
                                if (($activeGateway ?? 'demo') === 'razorpay' && ($isRazorpayConfigured ?? false)) {
                                    $defaultSelectedMethod = 'razorpay';
                                } elseif (($activeGateway ?? 'demo') === 'cashfree' && ($isCashfreeConfigured ?? false)) {
                                    $defaultSelectedMethod = 'cashfree';
                                } elseif (($activeGateway ?? 'demo') === 'demo') {
                                    $defaultSelectedMethod = 'demo';
                                } elseif ($isRazorpayConfigured ?? false) {
                                    $defaultSelectedMethod = 'razorpay';
                                } elseif ($isCashfreeConfigured ?? false) {
                                    $defaultSelectedMethod = 'cashfree';
                                } else {
                                    $defaultSelectedMethod = 'demo';
                                }
                            }
                        @endphp

                        <div class="mt-5 space-y-4">
                            {{-- Option 1: Demo Payment (Zero Credential Demo Mode) --}}
                            <label class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 {{ $defaultSelectedMethod === 'demo' ? 'border-amber-400 bg-amber-50/50' : 'border-zinc-200 bg-white' }} p-4 transition hover:bg-amber-50/40">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="demo"
                                    class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                    @checked($defaultSelectedMethod === 'demo')
                                    onclick="showPaymentUI('demo')"
                                >
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-zinc-950">Demo Payment (Simulated Mode)</p>
                                        <span class="rounded bg-amber-200 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-amber-950 ring-1 ring-amber-400">DEMO — NO REAL PAYMENT</span>
                                    </div>
                                    <p class="mt-1 text-xs text-zinc-600">Completely credential-free evaluation. No banking credentials, API keys, OTPs, or merchant accounts needed.</p>
                                </div>
                            </label>

                            <div id="demo-payment-ui" class="{{ $defaultSelectedMethod === 'demo' ? '' : 'hidden' }} rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/70 p-5 space-y-4 transition">
                                <div class="flex items-center justify-between border-b border-amber-200/80 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-300 text-xs font-bold text-amber-950">⚡</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-amber-950">Simulated Outcome</span>
                                    </div>
                                    <span class="rounded bg-amber-200 px-2 py-0.5 text-[10px] font-extrabold uppercase text-amber-950">DEMO — NO REAL PAYMENT</span>
                                </div>
                                <p class="text-xs text-zinc-600">
                                    Select the simulated result to evaluate order fulfillment, inventory decrements, receipts, and coupon generation:
                                </p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-emerald-300 bg-white p-3 shadow-xs transition hover:bg-emerald-50/50">
                                        <input type="radio" name="demo_outcome" value="success" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <p class="text-xs font-bold text-emerald-950">Simulate Success</p>
                                            <p class="text-[11px] text-zinc-500">Instant order confirmation</p>
                                        </div>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-rose-300 bg-white p-3 shadow-xs transition hover:bg-rose-50/50">
                                        <input type="radio" name="demo_outcome" value="failure" class="h-4 w-4 text-rose-600 focus:ring-rose-500">
                                        <div>
                                            <p class="text-xs font-bold text-rose-950">Simulate Failure</p>
                                            <p class="text-[11px] text-zinc-500">Decline & test cart retention</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="rounded-xl bg-amber-100/80 p-3 text-[11px] text-amber-950 flex items-start gap-2">
                                    <svg class="h-4 w-4 text-amber-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span><strong>DEMO — NO REAL PAYMENT:</strong> Transactions are stored distinctly as simulated tests and will never charge real money.</span>
                                </div>
                            </div>

                            {{-- Option 2: Cash on Delivery (COD) --}}
                            <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                    @checked($defaultSelectedMethod === 'cod')
                                    onclick="showPaymentUI('cod')"
                                >
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-950">Cash on Delivery (COD)</p>
                                    <p class="mt-1 text-xs text-zinc-500">Pay with cash when your premium spices are delivered to your door.</p>
                                </div>
                            </label>

                            {{-- Option 3: Razorpay Payment Gateway --}}
                            @if ($isRazorpayConfigured ?? false)
                                <label class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 {{ $defaultSelectedMethod === 'razorpay' ? 'border-red-400 bg-red-50/30' : 'border-zinc-200 bg-white' }} p-4 transition hover:bg-red-50/20">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="razorpay"
                                        class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                        @checked($defaultSelectedMethod === 'razorpay')
                                        onclick="showPaymentUI('razorpay')"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-zinc-950">Razorpay Gateway (Online Payment)</p>
                                            <span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-900">RAZORPAY</span>
                                            <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-600">{{ strtoupper($gatewaySetting?->environment ?? 'TEST') }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500">Pay securely online using UPI, QR Code, Credit/Debit Cards, or NetBanking.</p>
                                    </div>
                                </label>
                            @else
                                <div class="flex items-start gap-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-4 opacity-75 cursor-not-allowed">
                                    <input type="radio" disabled class="mt-1 h-4 w-4 text-zinc-400">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-zinc-600">Razorpay Gateway (Online Payment)</p>
                                            <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold text-zinc-700">UNAVAILABLE</span>
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500">Gateway currently not configured by store administrator. Use Demo Mode or COD.</p>
                                    </div>
                                </div>
                            @endif

                            <div id="razorpay-payment-ui" class="{{ $defaultSelectedMethod === 'razorpay' ? '' : 'hidden' }} rounded-2xl border border-red-200 bg-red-50/40 p-4 space-y-2 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-red-900">Razorpay Standard Checkout</span>
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-900">{{ strtoupper($gatewaySetting?->environment ?? 'SANDBOX') }}</span>
                                </div>
                                <p class="text-xs text-zinc-600">
                                    You will be redirected to Razorpay's secure checkout window to complete your payment with UPI, cards, or bank transfer.
                                </p>
                            </div>

                            {{-- Option 4: Cashfree Payments Gateway --}}
                            @if ($isCashfreeConfigured ?? false)
                                <label class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 {{ $defaultSelectedMethod === 'cashfree' ? 'border-emerald-400 bg-emerald-50/30' : 'border-zinc-200 bg-white' }} p-4 transition hover:bg-emerald-50/20">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="cashfree"
                                        class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                        @checked($defaultSelectedMethod === 'cashfree')
                                        onclick="showPaymentUI('cashfree')"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-zinc-950">Cashfree Payments</p>
                                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-900">CASHFREE</span>
                                            <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-600">{{ strtoupper($gatewaySetting?->environment ?? 'SANDBOX') }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500">Pay securely online using Cards, UPI, Wallets, and NetBanking via Cashfree.</p>
                                    </div>
                                </label>
                            @else
                                <div class="flex items-start gap-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-4 opacity-75 cursor-not-allowed">
                                    <input type="radio" disabled class="mt-1 h-4 w-4 text-zinc-400">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-zinc-600">Cashfree Payments</p>
                                            <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold text-zinc-700">UNAVAILABLE</span>
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500">Gateway currently not configured by store administrator. Use Demo Mode or COD.</p>
                                    </div>
                                </div>
                            @endif

                            <div id="cashfree-payment-ui" class="{{ $defaultSelectedMethod === 'cashfree' ? '' : 'hidden' }} rounded-2xl border border-emerald-300/80 bg-emerald-50/40 p-4 space-y-2 transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Cashfree Payments Checkout</span>
                                    <span class="rounded bg-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-900">{{ strtoupper($gatewaySetting?->environment ?? 'SANDBOX') }}</span>
                                </div>
                                <p class="text-xs text-zinc-600">
                                    Cashfree secure checkout modal will launch upon confirming. Verification occurs server-side after completion.
                                </p>
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
                                class="flex-1 rounded-xl border border-amber-200/80 bg-amber-50/20 px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none focus:border-brand-primary"
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
                            <span>{{ $deliveryCharge > 0 ? 'Rs. '.number_format($deliveryCharge, 2) : 'FREE' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-zinc-200 pt-4 text-lg font-semibold text-zinc-950">
                            <span>Total</span>
                            <span id="grand-total-value">Rs. {{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        id="place-order-submit-btn"
                        class="mt-6 block w-full rounded-2xl bg-zinc-950 py-3.5 text-center text-sm font-semibold text-white shadow-lg transition hover:bg-brand-primary"
                    >
                        Confirm and Place Order
                    </button>
                </aside>

                <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const form = document.getElementById('checkout-form');
                        const submitBtn = document.getElementById('place-order-submit-btn');
                        const applyBtn = document.getElementById('apply-coupon-btn');
                        const removeBtn = document.getElementById('remove-coupon-btn');
                        const input = document.getElementById('coupon-code-input');
                        const feedback = document.getElementById('coupon-feedback');
                        const pill = document.getElementById('applied-coupon-pill');
                        const pillText = document.getElementById('applied-coupon-text');
                        const hiddenInput = document.getElementById('applied-coupon-hidden-input');
                        
                        const discountRow = document.getElementById('coupon-discount-row');
                        const discountVal = document.getElementById('coupon-discount-value');
                        const totalVal = document.getElementById('grand-total-value');
                        
                        const originalTotal = parseFloat("{{ $total }}");
                        
                        const showFeedback = (text, isSuccess) => {
                            feedback.textContent = text;
                            feedback.className = `mt-1.5 text-xs font-semibold ${isSuccess ? 'text-emerald-600' : 'text-red-600'}`;
                            feedback.classList.remove('hidden');
                        };

                        const hideFeedback = () => {
                            feedback.classList.add('hidden');
                        };

                        const updateSubmitBtnText = (method) => {
                            if (method === 'demo') {
                                submitBtn.textContent = 'Simulate Demo Payment (DEMO — NO REAL PAYMENT)';
                            } else if (method === 'razorpay') {
                                submitBtn.textContent = 'Confirm & Pay with Razorpay';
                            } else if (method === 'cashfree') {
                                submitBtn.textContent = 'Confirm & Pay with Cashfree';
                            } else if (method === 'online') {
                                submitBtn.textContent = 'Confirm & Pay with {{ ucfirst($activeGateway ?? "razorpay") }}';
                            } else {
                                submitBtn.textContent = 'Confirm and Place Order';
                            }
                        };

                        window.showPaymentUI = function (method) {
                            const demoUI = document.getElementById('demo-payment-ui');
                            const razorpayUI = document.getElementById('razorpay-payment-ui');
                            const cashfreeUI = document.getElementById('cashfree-payment-ui');
                            const onlineUI = document.getElementById('online-payment-ui');
                            if (demoUI) demoUI.classList.toggle('hidden', method !== 'demo');
                            if (razorpayUI) razorpayUI.classList.toggle('hidden', method !== 'razorpay');
                            if (cashfreeUI) cashfreeUI.classList.toggle('hidden', method !== 'cashfree');
                            if (onlineUI) onlineUI.classList.toggle('hidden', method !== 'online');
                            updateSubmitBtnText(method);
                        };

                        // Initialize button text and panels based on initial selection
                        const initialMethod = form.querySelector('input[name="payment_method"]:checked')?.value || 'demo';
                        window.showPaymentUI(initialMethod);

                        // Dynamic button text based on payment selection
                        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                            radio.addEventListener('change', (e) => {
                                window.showPaymentUI(e.target.value);
                            });
                        });

                        // Form submit handler
                        form.addEventListener('submit', (e) => {
                            const paymentMethod = form.querySelector('input[name="payment_method"]:checked')?.value;
                            
                            if (paymentMethod === 'demo' || paymentMethod === 'online' || paymentMethod === 'razorpay' || paymentMethod === 'cashfree') {
                                e.preventDefault();
                                submitBtn.disabled = true;
                                submitBtn.textContent = paymentMethod === 'demo' 
                                    ? 'Simulating Payment (DEMO — NO REAL PAYMENT)...' 
                                    : 'Initializing Secure Payment...';

                                const formData = new FormData(form);

                                fetch("{{ route('checkout.store') }}", {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: formData
                                })
                                .then(res => res.json())
                                .then(data => {
                                    // Handle Demo Mode outcome (Zero-Credential Simulated Flow)
                                    if (data.is_demo || data.gateway === 'demo') {
                                        if (data.success) {
                                            submitBtn.textContent = 'Demo Successful! Redirecting...';
                                            window.location.href = data.redirect_url;
                                        } else {
                                            submitBtn.disabled = false;
                                            updateSubmitBtnText(paymentMethod);
                                            alert(data.message || 'Simulated payment was declined as requested. Cart and stock remain untouched.');
                                        }
                                        return;
                                    }

                                    if (!data.success) {
                                        submitBtn.disabled = false;
                                        updateSubmitBtnText(paymentMethod);
                                        if (data.errors) {
                                            const errorMsg = Object.values(data.errors).flat().join('\n');
                                            alert(errorMsg);
                                        } else {
                                            alert(data.message || 'Validation or order placement failed.');
                                        }
                                        return;
                                    }

                                    // Handle Cashfree Gateway Flow
                                    if (data.gateway === 'cashfree' || data.payment_session_id) {
                                        if (!window.Cashfree) {
                                            submitBtn.disabled = false;
                                            updateSubmitBtnText(paymentMethod);
                                            alert('Cashfree SDK is currently unavailable. Please refresh and try again.');
                                            return;
                                        }

                                        const cashfree = window.Cashfree({
                                            mode: data.environment || 'sandbox'
                                        });

                                        submitBtn.textContent = 'Awaiting Payment...';

                                        cashfree.checkout({
                                            paymentSessionId: data.payment_session_id,
                                            redirectTarget: '_modal'
                                        }).then((result) => {
                                            if (result.error) {
                                                submitBtn.disabled = false;
                                                updateSubmitBtnText(paymentMethod);
                                                alert(result.error.message || 'Payment window was closed or cancelled.');
                                                return;
                                            }

                                            // Trigger server-side authoritative status verification
                                            submitBtn.textContent = 'Verifying Payment...';
                                            fetch("{{ route('checkout.cashfree.verify') }}", {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    order_id: data.order_id || data.order_number
                                                })
                                            })
                                            .then(vRes => vRes.json())
                                            .then(vData => {
                                                if (vData.success) {
                                                    window.location.href = vData.redirect_url;
                                                } else {
                                                    submitBtn.disabled = false;
                                                    updateSubmitBtnText(paymentMethod);
                                                    alert('Payment Status: ' + (vData.message || 'Payment could not be verified.'));
                                                }
                                            })
                                            .catch(err => {
                                                submitBtn.disabled = false;
                                                updateSubmitBtnText(paymentMethod);
                                                alert('Verification network error. If amount was debited, check your order history.');
                                            });
                                        }).catch(err => {
                                            submitBtn.disabled = false;
                                            updateSubmitBtnText(paymentMethod);
                                            console.error('Cashfree checkout modal error', err);
                                        });

                                        return;
                                    }

                                    // Handle Razorpay Gateway Flow
                                    if (data.gateway === 'razorpay' || data.razorpay_order_id) {
                                        if (!window.Razorpay) {
                                            submitBtn.disabled = false;
                                            updateSubmitBtnText(paymentMethod);
                                            alert('Razorpay Checkout SDK is currently unavailable. Please check your internet connection and refresh.');
                                            return;
                                        }

                                        const options = {
                                            key: data.key_id,
                                            amount: data.amount,
                                            currency: data.currency || 'INR',
                                            name: 'FlavourFlow Spices',
                                            description: 'Order #' + data.order_number,
                                            order_id: data.razorpay_order_id,
                                            handler: function (response) {
                                                submitBtn.textContent = 'Verifying Payment...';
                                                fetch("{{ route('checkout.razorpay.verify') }}", {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                        'Accept': 'application/json'
                                                    },
                                                    body: JSON.stringify({
                                                        razorpay_order_id: response.razorpay_order_id,
                                                        razorpay_payment_id: response.razorpay_payment_id,
                                                        razorpay_signature: response.razorpay_signature
                                                    })
                                                })
                                                .then(vRes => vRes.json())
                                                .then(vData => {
                                                    if (vData.success) {
                                                        window.location.href = vData.redirect_url;
                                                    } else {
                                                        submitBtn.disabled = false;
                                                        updateSubmitBtnText(paymentMethod);
                                                        alert('Payment Verification Failed: ' + (vData.message || 'Payment invalid.'));
                                                    }
                                                })
                                                .catch(err => {
                                                    submitBtn.disabled = false;
                                                    updateSubmitBtnText(paymentMethod);
                                                    alert('Verification request error. If amount was debited, check your order history.');
                                                });
                                            },
                                            prefill: {
                                                name: data.customer_name,
                                                email: data.customer_email,
                                                contact: data.customer_mobile
                                            },
                                            theme: {
                                                color: '#b42318'
                                            },
                                            modal: {
                                                ondismiss: function () {
                                                    submitBtn.disabled = false;
                                                    updateSubmitBtnText(paymentMethod);
                                                }
                                            }
                                        };

                                        const rzp = new Razorpay(options);
                                        rzp.on('payment.failed', function (response) {
                                            submitBtn.disabled = false;
                                            updateSubmitBtnText(paymentMethod);
                                            alert('Payment Failed: ' + (response.error?.description || 'Payment was declined or failed.'));
                                        });
                                        rzp.open();
                                        return;
                                    }
                                })
                                .catch(err => {
                                    submitBtn.disabled = false;
                                    updateSubmitBtnText(paymentMethod);
                                    alert('Failed to initialize payment checkout. Please check form details.');
                                });
                            }
                        });

                        applyBtn.addEventListener('click', () => {
                            const code = input.value.trim();
                            if (!code) {
                                showFeedback('Please enter a coupon code.', false);
                                return;
                            }

                            hideFeedback();
                            applyBtn.disabled = true;
                            applyBtn.textContent = 'Applying...';

                            fetch("{{ route('checkout.coupon.apply') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ coupon_code: code })
                            })
                            .then(res => res.json())
                            .then(data => {
                                applyBtn.disabled = false;
                                applyBtn.textContent = 'Apply';

                                if (data.success) {
                                    discountVal.textContent = 'Rs. -' + parseFloat(data.discount).toFixed(2);
                                    discountRow.classList.remove('hidden');
                                    totalVal.textContent = 'Rs. ' + parseFloat(data.total).toFixed(2);
                                    
                                    pillText.textContent = `Applied: ${data.coupon.code}`;
                                    pill.classList.remove('hidden');
                                    pill.classList.add('flex');
                                    hiddenInput.value = data.coupon.code;
                                    input.value = '';
                                    input.disabled = true;
                                    applyBtn.disabled = true;
                                    
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

                        removeBtn.addEventListener('click', () => {
                            discountRow.classList.add('hidden');
                            totalVal.textContent = 'Rs. ' + originalTotal.toFixed(2);
                            
                            pill.classList.add('hidden');
                            pill.classList.remove('flex');
                            hiddenInput.value = '';
                            input.disabled = false;
                            applyBtn.disabled = false;
                            hideFeedback();
                        });
                    });
                </script>
            </form>
        </div>
    </section>
</x-site.layout>

