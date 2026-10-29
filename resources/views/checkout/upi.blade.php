<x-site.layout :site="$site" page-title="Scan & Pay via UPI | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    {{-- Hero Section --}}
    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.22),transparent_35%),linear-gradient(135deg,#fffbf5_0%,#fff_50%,#fff6ea_100%)] py-8 sm:py-12">
        <div class="mx-auto w-full max-w-3xl px-4 sm:px-6 lg:px-8 text-center" data-reveal>
            <div class="inline-flex items-center gap-2 rounded-full bg-amber-100/90 px-3.5 py-1 text-xs font-bold text-amber-900 border border-amber-300 shadow-xs mb-3">
                <span class="h-2 w-2 rounded-full bg-amber-600 animate-pulse"></span>
                UPI PAYMENT — MANUAL CONFIRMATION REQUIRED
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-zinc-950 sm:text-4xl">Scan &amp; Pay via UPI</h1>
            <p class="mt-2 text-sm text-zinc-600 max-w-md mx-auto">
                Pay using your preferred UPI app or scan the QR code. Once paid, submit your reference ID below to verify and confirm your order.
            </p>
        </div>
    </section>

    {{-- Main Checkout Container --}}
    <section class="bg-[linear-gradient(180deg,#fff_0%,#fffaf2_50%,#fff_100%)] py-8 sm:py-12">
        <div class="mx-auto w-full max-w-xl px-4 sm:px-6">
            <div class="overflow-hidden rounded-3xl border border-amber-200/90 bg-white p-6 sm:p-8 shadow-[0_20px_60px_rgba(180,83,9,0.08)] ring-1 ring-amber-100/60">

                {{-- Status / Warning Flash Notifications --}}
                @if(session('warning'))
                    <div class="mb-5 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-xs font-medium text-amber-900 shadow-xs">
                        <div class="flex items-start gap-2.5">
                            <svg class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <p>{{ session('warning') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-300 bg-red-50 p-4 text-xs font-medium text-red-900 shadow-xs">
                        <div class="flex items-start gap-2.5">
                            <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <p class="font-bold">Verification Error:</p>
                                <p class="mt-0.5">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Clean & Attractive Amount Banner --}}
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-b from-amber-500/10 via-amber-500/5 to-transparent p-5 sm:p-6 text-center border border-amber-200/80 shadow-xs">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-950/70">
                        Pay Amount to Confirm Order
                    </p>
                    <div class="mt-1.5 flex items-baseline justify-center">
                        <span class="text-4xl sm:text-5xl font-black text-zinc-950 tracking-tight" id="upi-display-amount">
                            {{ $paymentDetails['formatted_amount'] }}
                        </span>
                    </div>
                    <div class="mt-2.5 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-zinc-700 shadow-xs ring-1 ring-zinc-200/80">
                        <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span>Verified Payee: <strong class="text-zinc-950">{{ $paymentDetails['payee_name'] }}</strong></span>
                    </div>
                </div>

                {{-- QR Code Display --}}
                <div class="mt-6 flex flex-col items-center justify-center">
                    <div class="relative rounded-3xl border-2 border-zinc-900/10 bg-white p-4 sm:p-5 shadow-lg ring-8 ring-amber-50/70">
                        {{-- Raw SVG QR Code --}}
                        <div class="h-60 w-60 sm:h-68 sm:w-68 flex items-center justify-center [&>svg]:h-full [&>svg]:w-full [&>svg]:max-w-full">
                            {!! $paymentDetails['qr_svg'] !!}
                        </div>

                        {{-- Scan Hint Corner Badges --}}
                        <div class="mt-2.5 flex items-center justify-center gap-1.5 text-center">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-zinc-500">
                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Scan with any UPI app on phone
                            </span>
                        </div>
                    </div>

                    {{-- Supported Apps Launch Links (Mobile / Desktop) --}}
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5 text-[11px] font-semibold text-zinc-600">
                        <a href="{{ $paymentDetails['app_launchers']['gpay']['uri'] ?? $paymentDetails['payment_uri'] }}" class="rounded-lg bg-zinc-100 hover:bg-zinc-200 px-2.5 py-1 text-zinc-800 transition">Google Pay</a>
                        <a href="{{ $paymentDetails['app_launchers']['phonepe']['uri'] ?? $paymentDetails['payment_uri'] }}" class="rounded-lg bg-zinc-100 hover:bg-zinc-200 px-2.5 py-1 text-zinc-800 transition">PhonePe</a>
                        <a href="{{ $paymentDetails['app_launchers']['paytm']['uri'] ?? $paymentDetails['payment_uri'] }}" class="rounded-lg bg-zinc-100 hover:bg-zinc-200 px-2.5 py-1 text-zinc-800 transition">Paytm</a>
                        <a href="{{ $paymentDetails['app_launchers']['bhim']['uri'] ?? $paymentDetails['payment_uri'] }}" class="rounded-lg bg-zinc-100 hover:bg-zinc-200 px-2.5 py-1 text-zinc-800 transition">BHIM</a>
                        <a href="{{ $paymentDetails['app_launchers']['cred']['uri'] ?? $paymentDetails['payment_uri'] }}" class="rounded-lg bg-zinc-100 hover:bg-zinc-200 px-2.5 py-1 text-zinc-800 transition">Cred</a>
                    </div>

                    {{-- Mobile "Open in UPI App" Action --}}
                    <div class="mt-4 w-full">
                        <a
                            href="{{ $paymentDetails['payment_uri'] }}"
                            id="open-upi-app-btn"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-primary px-5 py-3.5 text-sm font-bold text-white shadow-md transition hover:bg-red-700 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-brand-primary/20"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Open in UPI App (Mobile)
                        </a>
                        <p class="mt-1 text-center text-[11px] text-zinc-400">
                            On mobile devices, this opens your installed UPI app with the exact amount pre-filled.
                        </p>
                    </div>
                </div>

                {{-- Merchant VPA & Order Reference Details --}}
                <div class="mt-6 grid gap-3 sm:grid-cols-2 border-t border-zinc-100 pt-5">
                    {{-- Copy UPI ID --}}
                    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50/80 p-3.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Merchant UPI ID (VPA)</span>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-zinc-900 truncate" id="merchant-upi-id-text">{{ $paymentDetails['upi_id'] }}</span>
                            <button
                                type="button"
                                id="copy-upi-btn"
                                onclick="navigator.clipboard.writeText('{{ $paymentDetails['upi_id'] }}'); const el = document.getElementById('copy-upi-btn'); el.textContent = 'Copied!'; setTimeout(() => el.textContent = 'Copy', 2000);"
                                class="shrink-0 rounded-xl bg-zinc-950 px-2.5 py-1 text-xs font-bold text-white transition hover:bg-brand-primary"
                            >
                                Copy
                            </button>
                        </div>
                    </div>

                    {{-- Copy Order Reference --}}
                    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50/80 p-3.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Order Reference #</span>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-zinc-900 truncate" id="order-reference-text">{{ $paymentDetails['order_reference'] }}</span>
                            <button
                                type="button"
                                id="copy-ref-btn"
                                onclick="navigator.clipboard.writeText('{{ $paymentDetails['order_reference'] }}'); const el = document.getElementById('copy-ref-btn'); el.textContent = 'Copied!'; setTimeout(() => el.textContent = 'Copy', 2000);"
                                class="shrink-0 rounded-xl border border-zinc-300 bg-white px-2.5 py-1 text-xs font-bold text-zinc-800 transition hover:bg-zinc-100 shadow-xs"
                            >
                                Copy
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Prominent Policy Alert: Verification Required --}}
                <div class="mt-5 rounded-2xl border border-amber-300/80 bg-amber-50/70 p-4 text-amber-950">
                    <div class="flex items-start gap-2.5">
                        <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div class="space-y-1 text-xs leading-relaxed">
                            <p class="font-bold text-amber-950">
                                Real Payment Verification Required:
                            </p>
                            <p class="text-amber-900/90">
                                After completing your UPI transfer in your banking app, enter the <strong>12-digit UPI Reference Number / UTR</strong> below and click <strong>"I Have Paid — Verify Payment"</strong>.
                            </p>
                            <p class="text-[11px] text-amber-900/80">
                                Your order confirmation and email OTP will be issued only after your payment is successfully verified.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Customer Verification Form ("I Have Paid") --}}
                <form id="upi-confirm-form" action="{{ route('checkout.upi.confirm', $order->order_number) }}" method="POST" class="mt-5 border-t border-zinc-100 pt-5 space-y-3.5">
                    @csrf

                    <div>
                        <label for="transaction_ref" class="block text-xs font-bold uppercase tracking-wider text-zinc-700">
                            UPI Reference Number / UTR <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Enter the 12-digit UTR from your GPay, PhonePe, or Paytm payment receipt.</p>
                        <input
                            type="text"
                            id="transaction_ref"
                            name="transaction_ref"
                            required
                            minlength="6"
                            maxlength="100"
                            value="{{ old('transaction_ref') }}"
                            placeholder="e.g. 429812345678 or pay_..."
                            class="mt-1.5 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-950 shadow-xs outline-none transition placeholder:text-zinc-400 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 font-mono"
                        >
                    </div>

                    <button
                        type="submit"
                        id="confirm-paid-btn"
                        class="w-full rounded-2xl bg-zinc-950 py-3.5 text-center text-sm font-bold text-white shadow-md transition hover:bg-brand-primary focus:outline-none focus:ring-4 focus:ring-zinc-950/20 disabled:opacity-50"
                    >
                        I Have Paid — Verify Payment &rarr;
                    </button>
                    <p class="text-center text-[10px] text-zinc-400">
                        Order confirmation and email OTP trigger only upon verified payment success.
                    </p>
                </form>

                <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-3 text-xs text-zinc-500">
                    <a href="{{ route('account.orders.track', $order->order_number) }}" class="font-semibold text-zinc-600 hover:text-brand-primary hover:underline">
                        &larr; Track Order
                    </a>
                    <a href="{{ route('account.orders') }}" class="font-semibold text-zinc-600 hover:text-brand-primary hover:underline">
                        My Orders &rarr;
                    </a>
                </div>

            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('upi-confirm-form');
            const submitBtn = document.getElementById('confirm-paid-btn');
            const refInput = document.getElementById('transaction_ref');

            if (form && submitBtn && refInput) {
                form.addEventListener('submit', (e) => {
                    const val = refInput.value.trim();
                    if (!val || val.length < 6) {
                        e.preventDefault();
                        alert('Please enter a valid UPI transaction reference number / UTR (at least 6 characters).');
                        refInput.focus();
                        return;
                    }

                    // Prevent duplicate rapid clicks
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Verifying Payment with Server...';
                });
            }
        });
    </script>
</x-site.layout>
