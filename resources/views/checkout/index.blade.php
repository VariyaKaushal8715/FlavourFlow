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

                        <div class="mt-5 space-y-4">
                            <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                    checked
                                    onclick="document.getElementById('online-payment-ui').classList.add('hidden')"
                                >
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-950">Cash on Delivery (COD)</p>
                                    <p class="mt-1 text-xs text-zinc-500">Pay with cash when your premium spices are delivered to your door.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/10 p-4 transition hover:bg-amber-50/20">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="online"
                                    class="mt-1 h-4 w-4 text-brand-primary focus:ring-brand-primary"
                                    onclick="document.getElementById('online-payment-ui').classList.remove('hidden')"
                                >
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-950">Online Payment</p>
                                    <p class="mt-1 text-xs text-zinc-500">Pay securely online using credit/debit card, UPI, or mobile wallets.</p>
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
                                    <span class="inline-flex items-center justify-center text-[10px] font-semibold text-zinc-400">Locked Demo Mode</span>
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
                        class="mt-6 block w-full rounded-2xl bg-zinc-950 py-3.5 text-center text-sm font-semibold text-white shadow-lg transition hover:bg-brand-primary"
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

