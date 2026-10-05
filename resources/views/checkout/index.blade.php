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

                    @php
                        $onlinePaymentMethods = ['upi', 'card', 'netbanking', 'wallet'];
                        $selectedPaymentMethod = old('payment_method', 'cod');
                        $onlinePaymentSelected = in_array($selectedPaymentMethod, $onlinePaymentMethods, true);
                    @endphp

                    <div class="mt-8 border-t border-zinc-100 pt-6" data-payment-section>
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">Payment Options</p>
                                <h3 class="mt-1 text-xl font-semibold text-zinc-950">Choose how you would like to pay</h3>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Secure checkout
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Payment method">
                            <label @class([
                                'relative flex cursor-pointer gap-3 rounded-2xl border p-4 transition duration-200 hover:-translate-y-0.5 hover:border-brand-primary hover:shadow-md focus-within:ring-4 focus-within:ring-brand-primary/15',
                                'border-brand-primary bg-amber-50/70 shadow-sm' => $selectedPaymentMethod === 'cod',
                                'border-zinc-200 bg-white' => $selectedPaymentMethod !== 'cod',
                            ]) data-payment-choice="cod">
                                <input class="peer sr-only" type="radio" name="payment_method" value="cod" @checked($selectedPaymentMethod === 'cod')>
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-zinc-100 text-zinc-700 peer-checked:bg-zinc-950 peer-checked:text-white" aria-hidden="true">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7.5h16v11H4z"/><path d="M7 7.5V5h10v2.5M8 12h4M8 15h2"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="flex items-center gap-2 text-sm font-semibold text-zinc-950">Cash on Delivery <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-500">COD</span></span>
                                    <span class="mt-1 block text-xs leading-5 text-zinc-500">Pay when your order arrives at your door.</span>
                                </span>
                                <span class="absolute right-4 top-4 hidden h-5 w-5 place-items-center rounded-full bg-brand-primary text-white peer-checked:grid" aria-hidden="true">✓</span>
                            </label>

                            <div @class([
                                'relative flex gap-3 rounded-2xl border p-4 transition duration-200',
                                'border-brand-primary bg-amber-50/70 shadow-sm' => $onlinePaymentSelected,
                                'border-zinc-200 bg-white' => ! $onlinePaymentSelected,
                            ]) data-payment-choice="online" role="button" tabindex="0" aria-label="Online Payment">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-brand-primary" aria-hidden="true">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7.5 12 4l8 3.5v9L12 20l-8-3.5v-9Z"/><path d="m8 10 4 2 4-2M12 12v5"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="text-sm font-semibold text-zinc-950">Online Payment</span>
                                    <span class="mt-1 block text-xs leading-5 text-zinc-500">UPI, cards, net banking, and wallets.</span>
                                </span>
                                <span class="absolute right-4 top-4 grid h-5 w-5 place-items-center rounded-full border-2 border-zinc-300 text-xs text-transparent" data-online-indicator aria-hidden="true">✓</span>
                            </div>
                        </div>

                        <div id="online-payment-ui" @class([
                            'mt-5 overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50/70 p-4 transition-all duration-300 sm:p-5',
                            'hidden' => ! $onlinePaymentSelected,
                        ]) data-online-payment-ui>
                            <div class="flex flex-col gap-3 border-b border-zinc-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-primary">Test / Demo Payment</p>
                                    <h4 class="mt-1 text-lg font-semibold text-zinc-950">Choose Payment Method</h4>
                                    <p class="mt-1 text-sm text-zinc-500">Select an option below to continue securely.</p>
                                </div>
                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-zinc-600 shadow-sm ring-1 ring-zinc-200">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 19 6v6c0 4.8-3.1 8.6-7 10-3.9-1.4-7-5.2-7-10V6l7-3Z"/><path d="m9.5 12.1 1.7 1.8 3.5-4"/></svg>
                                    No sensitive data stored
                                </span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" role="radiogroup" aria-label="Online payment method">
                                @foreach ([
                                    'upi' => ['UPI', 'Pay using your UPI ID', '↗'],
                                    'card' => ['Credit / Debit Card', 'Visa, Mastercard, or RuPay', '▣'],
                                    'netbanking' => ['Net Banking', 'Pay through your bank', '▤'],
                                    'wallet' => ['Wallets', 'Choose your preferred wallet', '◉'],
                                ] as $method => [$label, $description, $icon])
                                    <label @class([
                                        'group relative flex cursor-pointer items-start gap-3 rounded-2xl border bg-white p-3.5 transition duration-200 hover:-translate-y-0.5 hover:border-brand-primary hover:shadow-md focus-within:ring-4 focus-within:ring-brand-primary/15',
                                        'border-brand-primary bg-amber-50/70 shadow-sm' => $selectedPaymentMethod === $method,
                                        'border-zinc-200' => $selectedPaymentMethod !== $method,
                                    ]) data-method-card="{{ $method }}">
                                        <input class="peer sr-only" type="radio" name="payment_method" value="{{ $method }}" @checked($selectedPaymentMethod === $method)>
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-zinc-100 text-sm font-bold text-zinc-700 transition peer-checked:bg-brand-primary peer-checked:text-white">{{ $icon }}</span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-zinc-950">{{ $label }}</span>
                                            <span class="mt-1 block text-[11px] leading-4 text-zinc-500">{{ $description }}</span>
                                        </span>
                                        <span class="absolute right-3 top-3 hidden h-4 w-4 place-items-center rounded-full bg-brand-primary text-[10px] text-white peer-checked:grid" aria-hidden="true">✓</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-5 rounded-2xl border border-white bg-white p-4 shadow-sm sm:p-5" data-payment-panels>
                                <div class="hidden space-y-4" data-payment-panel="upi">
                                    <div><h5 class="text-base font-semibold text-zinc-950">Pay with UPI</h5><p class="mt-1 text-sm text-zinc-500">Enter your UPI ID to simulate a secure verification.</p></div>
                                    <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">UPI ID</span><div class="mt-2 flex flex-col gap-2 sm:flex-row"><input class="min-w-0 flex-1 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 hover:border-zinc-300 focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/10" type="text" name="upi_id" value="{{ old('upi_id') }}" placeholder="name@bank" autocomplete="off" data-payment-field required><button type="button" class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-800 transition hover:border-brand-primary hover:text-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15" data-upi-verify>Verify</button></div><p class="mt-2 hidden text-xs font-semibold text-emerald-700" data-upi-feedback>Demo UPI ID verified for this test payment.</p>@error('upi_id')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror</label>
                                </div>

                                <div class="hidden space-y-4" data-payment-panel="card">
                                    <div><h5 class="text-base font-semibold text-zinc-950">Card details</h5><p class="mt-1 text-sm text-zinc-500">Use any demo card details. Your CVV is never retained.</p></div>
                                    <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Card Number</span><input class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm tracking-[0.16em] text-zinc-950 outline-none transition placeholder:tracking-normal placeholder:text-zinc-400 hover:border-zinc-300 focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/10" type="text" inputmode="numeric" name="card_number" value="{{ old('card_number') }}" placeholder="1234 5678 9012 3456" autocomplete="cc-number" data-card-number data-payment-field required>@error('card_number')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror</label>
                                    <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Name on Card</span><input class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 hover:border-zinc-300 focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/10" type="text" name="card_name" value="{{ old('card_name') }}" placeholder="As printed on your card" autocomplete="cc-name" data-payment-field required>@error('card_name')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror</label>
                                    <div class="grid gap-4 sm:grid-cols-2"><label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Expiry Date</span><input class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 hover:border-zinc-300 focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/10" type="text" name="card_expiry" value="{{ old('card_expiry') }}" placeholder="MM/YY" autocomplete="cc-exp" maxlength="5" data-card-expiry data-payment-field required>@error('card_expiry')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror</label><label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">CVV</span><input class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 hover:border-zinc-300 focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/10" type="password" inputmode="numeric" name="card_cvv" placeholder="3 or 4 digits" autocomplete="cc-csc" maxlength="4" data-payment-field required>@error('card_cvv')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror</label></div>
                                </div>

                                <div class="hidden space-y-4" data-payment-panel="netbanking">
                                    <div><h5 class="text-base font-semibold text-zinc-950">Choose your bank</h5><p class="mt-1 text-sm text-zinc-500">Select a bank to continue in demo mode. No password or OTP is requested.</p></div>
                                    <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Select Your Bank</span><select class="mt-2 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-950 outline-none transition hover:border-zinc-300 focus:border-brand-primary focus:bg-white focus:ring-4 focus:ring-brand-primary/10" name="netbanking_bank" data-payment-field required><option value="">Choose your bank</option><option value="HDFC Bank" @selected(old('netbanking_bank') === 'HDFC Bank')>HDFC Bank</option><option value="ICICI Bank" @selected(old('netbanking_bank') === 'ICICI Bank')>ICICI Bank</option><option value="State Bank of India" @selected(old('netbanking_bank') === 'State Bank of India')>State Bank of India</option><option value="Axis Bank" @selected(old('netbanking_bank') === 'Axis Bank')>Axis Bank</option><option value="Kotak Mahindra Bank" @selected(old('netbanking_bank') === 'Kotak Mahindra Bank')>Kotak Mahindra Bank</option><option value="Other bank" @selected(old('netbanking_bank') === 'Other bank')>Other bank</option></select>@error('netbanking_bank')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror</label>
                                </div>

                                <div class="hidden space-y-4" data-payment-panel="wallet">
                                    <div><h5 class="text-base font-semibold text-zinc-950">Choose your wallet</h5><p class="mt-1 text-sm text-zinc-500">Select a wallet to simulate the payment handoff.</p></div>
                                    <div class="grid gap-3 sm:grid-cols-3"><label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-3 transition hover:border-brand-primary has-[:checked]:border-brand-primary has-[:checked]:bg-amber-50/70"><input class="h-4 w-4 accent-[var(--brand-primary)]" type="radio" name="wallet_provider" value="Google Pay" @checked(old('wallet_provider') === 'Google Pay') data-payment-field required><span class="text-sm font-semibold text-zinc-800">Google Pay</span></label><label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-3 transition hover:border-brand-primary has-[:checked]:border-brand-primary has-[:checked]:bg-amber-50/70"><input class="h-4 w-4 accent-[var(--brand-primary)]" type="radio" name="wallet_provider" value="PhonePe" @checked(old('wallet_provider') === 'PhonePe') data-payment-field required><span class="text-sm font-semibold text-zinc-800">PhonePe</span></label><label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-3 transition hover:border-brand-primary has-[:checked]:border-brand-primary has-[:checked]:bg-amber-50/70"><input class="h-4 w-4 accent-[var(--brand-primary)]" type="radio" name="wallet_provider" value="Paytm" @checked(old('wallet_provider') === 'Paytm') data-payment-field required><span class="text-sm font-semibold text-zinc-800">Paytm</span></label></div>@error('wallet_provider')<p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
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

                        @if (isset($availableCoupons) && $availableCoupons->count() > 0)
                            <div class="mt-4 rounded-2xl border border-amber-200/80 bg-amber-50/40 p-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-bold text-zinc-900">Available For You ({{ $availableCoupons->count() }})</span>
                                    <button type="button" id="toggle-available-coupons-btn" class="text-[11px] font-bold text-brand-primary hover:underline">
                                        View &darr;
                                    </button>
                                </div>
                                <div id="available-coupons-list" class="mt-3 hidden max-h-48 space-y-2 overflow-y-auto pr-1">
                                    @foreach ($availableCoupons as $availableCoupon)
                                        <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-white p-2.5 text-xs shadow-sm">
                                            <div class="min-w-0 pr-2">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-mono font-bold text-zinc-950">{{ $availableCoupon->code }}</span>
                                                    <span @class([
                                                        'rounded px-1.5 py-0.5 text-[9px] font-semibold',
                                                        'bg-sky-100 text-sky-800' => $availableCoupon->payment_method_eligibility === 'online',
                                                        'bg-amber-100 text-amber-800' => $availableCoupon->payment_method_eligibility === 'cod',
                                                        'bg-zinc-100 text-zinc-700' => $availableCoupon->payment_method_eligibility === 'both',
                                                    ])>
                                                        {{ $availableCoupon->paymentMethodLabel() }}
                                                    </span>
                                                </div>
                                                <p class="text-[10px] font-semibold text-brand-primary">{{ $availableCoupon->formattedDiscount() }}</p>
                                                @if ((float) $availableCoupon->min_order_amount > 0)
                                                    <p class="text-[10px] text-zinc-400">Min: Rs. {{ number_format($availableCoupon->min_order_amount, 2) }}</p>
                                                @endif
                                            </div>
                                            <button
                                                type="button"
                                                onclick="applyQuickCoupon('{{ $availableCoupon->code }}')"
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

                        const onlinePaymentUi = document.querySelector('[data-online-payment-ui]');
                        const onlinePaymentChoice = document.querySelector('[data-payment-choice="online"]');
                        const paymentChoices = [...document.querySelectorAll('[data-payment-choice]')];
                        const methodCards = [...document.querySelectorAll('[data-method-card]')];
                        const paymentMethodInputs = [...document.querySelectorAll('input[name="payment_method"]')];
                        const paymentPanels = [...document.querySelectorAll('[data-payment-panel]')];
                        const onlineMethods = ['upi', 'card', 'netbanking', 'wallet'];

                        const setPaymentCardState = (card, selected) => {
                            card.classList.toggle('border-brand-primary', selected);
                            card.classList.toggle('bg-amber-50/70', selected);
                            card.classList.toggle('shadow-sm', selected);
                            card.classList.toggle('border-zinc-200', !selected);
                        };

                        const setMethodCardState = (card, selected) => {
                            card.classList.toggle('border-brand-primary', selected);
                            card.classList.toggle('bg-amber-50/70', selected);
                            card.classList.toggle('shadow-sm', selected);
                            card.classList.toggle('border-zinc-200', !selected);
                        };

                        const setPaymentMethod = (method) => {
                            const isOnline = onlineMethods.includes(method);

                            paymentChoices.forEach((card) => setPaymentCardState(card, card.dataset.paymentChoice === (isOnline ? 'online' : method)));
                            methodCards.forEach((card) => setMethodCardState(card, card.dataset.methodCard === method));
                            onlinePaymentUi?.classList.toggle('hidden', !isOnline);
                            onlinePaymentChoice?.querySelector('[data-online-indicator]')?.classList.toggle('bg-brand-primary', isOnline);
                            onlinePaymentChoice?.querySelector('[data-online-indicator]')?.classList.toggle('border-brand-primary', isOnline);
                            onlinePaymentChoice?.querySelector('[data-online-indicator]')?.classList.toggle('text-white', isOnline);

                            paymentPanels.forEach((panel) => {
                                const isActive = panel.dataset.paymentPanel === method;
                                panel.classList.toggle('hidden', !isActive);
                                panel.querySelectorAll('[data-payment-field]').forEach((field) => {
                                    field.disabled = !isActive;
                                });
                            });

                            if (appliedCouponCode && appliedCouponEligibility) {
                                if (!isOnline && appliedCouponEligibility === 'online') {
                                    removeAppliedCoupon();
                                    showFeedback('This coupon is valid only for online payment. Please select online payment to use this coupon.', false);
                                } else if (isOnline && appliedCouponEligibility === 'cod') {
                                    removeAppliedCoupon();
                                    showFeedback('This coupon is valid only for Cash on Delivery. Please select Cash on Delivery to use this coupon.', false);
                                }
                            }
                        };

                        paymentMethodInputs.forEach((input) => {
                            input.addEventListener('change', () => setPaymentMethod(input.value));
                        });

                        onlinePaymentChoice?.addEventListener('click', () => {
                            const activeOnlineMethod = paymentMethodInputs.find((input) => onlineMethods.includes(input.value) && input.checked)?.value || 'card';
                            const activeInput = paymentMethodInputs.find((input) => input.value === activeOnlineMethod);

                            if (activeInput) {
                                activeInput.checked = true;
                                setPaymentMethod(activeOnlineMethod);
                            }
                        });

                        onlinePaymentChoice?.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault();
                                onlinePaymentChoice.click();
                            }
                        });

                        document.querySelector('[data-upi-verify]')?.addEventListener('click', () => {
                            const upiInput = document.querySelector('input[name="upi_id"]');
                            const upiFeedback = document.querySelector('[data-upi-feedback]');

                            if (!upiInput?.value.includes('@')) {
                                upiInput?.focus();
                                upiInput?.setCustomValidity('Enter a valid demo UPI ID, such as name@bank.');
                                upiInput?.reportValidity();
                                return;
                            }

                            upiInput.setCustomValidity('');
                            upiFeedback?.classList.remove('hidden');
                        });

                        const initialPaymentMethod = paymentMethodInputs.find((input) => input.checked)?.value || 'cod';
                        setPaymentMethod(initialPaymentMethod);

                        const toggleCouponsBtn = document.getElementById('toggle-available-coupons-btn');
                        const availableCouponsList = document.getElementById('available-coupons-list');
                        toggleCouponsBtn?.addEventListener('click', () => {
                            const isHidden = availableCouponsList?.classList.toggle('hidden');
                            toggleCouponsBtn.innerHTML = isHidden ? 'View &darr;' : 'Hide &uarr;';
                        });

                        window.applyQuickCoupon = function(code) {
                            if (input) {
                                input.value = code;
                                applyBtn?.click();
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

                        function selectedPaymentCategory() {
                            const selectedPaymentMethod = paymentMethodInputs.find((input) => input.checked)?.value || 'cod';

                            return onlineMethods.includes(selectedPaymentMethod) ? 'online' : 'cod';
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
                                    payment_method: selectedPaymentCategory()
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
    </section>
</x-site.layout>
