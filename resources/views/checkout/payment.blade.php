<x-site.layout :site="$site" page-title="Payment | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-10 sm:py-14">
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
            <div class="max-w-3xl" data-reveal>
                <p class="text-sm font-semibold text-brand-primary">Secure Payment</p>
                <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">Complete Payment</h1>
                <p class="mt-4 text-base leading-7 text-zinc-600">Your order <strong>{{ $order->order_id }}</strong> has been placed. Please complete the payment to confirm.</p>
            </div>
        </div>
    </section>

    <section class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-xl px-6 lg:px-8">
            <div class="rounded-3xl border border-amber-200/70 bg-white/95 p-6 shadow-[0_24px_70px_rgba(120,53,15,0.10)] ring-1 ring-white sm:p-8" data-reveal>
                <div class="border-b border-zinc-100 pb-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">Order Summary</p>
                    <h2 class="mt-1 text-2xl font-semibold text-zinc-950">{{ $order->order_id }}</h2>
                </div>

                <div class="mt-6 space-y-3 text-sm text-zinc-600">
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Delivery Charges</span>
                        <span>{{ $order->delivery_charge > 0 ? 'Rs. '.number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-200 pt-4 text-lg font-semibold text-zinc-950">
                        <span>Total</span>
                        <span>Rs. {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                {{-- Payment Status Messages --}}
                <div id="payment-status" class="mt-6 hidden rounded-2xl p-4 text-sm font-medium"></div>

                {{-- Pay Now Button --}}
                <button
                    type="button"
                    id="pay-now-btn"
                    class="mt-6 block w-full rounded-2xl bg-zinc-950 py-3.5 text-center text-sm font-semibold text-white shadow-lg transition hover:bg-brand-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Pay Rs. {{ number_format($order->total, 2) }}
                </button>

                {{-- Retry or Cancel Options --}}
                <div id="payment-actions" class="mt-4 hidden flex-col gap-3 sm:flex-row sm:justify-center">
                    <button
                        type="button"
                        id="retry-payment-btn"
                        class="w-full rounded-2xl border border-zinc-300 bg-white py-3 text-center text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Retry Payment
                    </button>
                    <a
                        href="{{ route('home') }}"
                        class="mt-2 block w-full rounded-2xl border border-transparent bg-amber-100 py-3 text-center text-sm font-semibold text-brand-primary transition hover:bg-amber-200 sm:mt-0"
                    >
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Razorpay Checkout SDK --}}
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        (function () {
            const orderId = {{ $order->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const payBtn = document.getElementById('pay-now-btn');
            const retryBtn = document.getElementById('retry-payment-btn');
            const statusDiv = document.getElementById('payment-status');
            const actionsDiv = document.getElementById('payment-actions');

            function showStatus(message, type) {
                statusDiv.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'bg-red-50', 'text-red-800', 'bg-amber-50', 'text-amber-800');
                if (type === 'success') {
                    statusDiv.classList.add('bg-emerald-50', 'text-emerald-800');
                } else if (type === 'error') {
                    statusDiv.classList.add('bg-red-50', 'text-red-800');
                } else {
                    statusDiv.classList.add('bg-amber-50', 'text-amber-800');
                }
                statusDiv.textContent = message;
                statusDiv.classList.remove('hidden');
            }

            function showRetryActions() {
                actionsDiv.classList.remove('hidden');
                actionsDiv.classList.add('flex');
            }

            async function initiatePayment() {
                payBtn.disabled = true;
                payBtn.textContent = 'Initiating Payment...';
                statusDiv.classList.add('hidden');
                actionsDiv.classList.add('hidden');
                actionsDiv.classList.remove('flex');

                try {
                    // Step 1: Create Razorpay Order from backend
                    const createRes = await fetch('{{ route("razorpay.create-order") }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ order_id: orderId })
                    });

                    const createData = await createRes.json();

                    if (!createRes.ok) {
                        showStatus(createData.error || 'Failed to create payment order. Please try again.', 'error');
                        showRetryActions();
                        payBtn.disabled = false;
                        payBtn.textContent = 'Pay Rs. {{ number_format($order->total, 2) }}';
                        return;
                    }

                    // Step 2: Open Razorpay Checkout
                    const options = {
                        key: createData.key_id,
                        amount: createData.amount,
                        currency: createData.currency,
                        name: '{{ addslashes($site["brand"]["name"]) }}',
                        description: 'Order ' + '{{ $order->order_id }}',
                        order_id: createData.razorpay_order_id,
                        prefill: createData.prefill || {},
                        theme: {
                            color: '#d97706'
                        },
                        handler: async function (response) {
                            // Step 3: Verify payment on backend
                            payBtn.disabled = true;
                            payBtn.textContent = 'Verifying Payment...';

                            try {
                                const verifyRes = await fetch('{{ route("razorpay.verify") }}', {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({
                                        razorpay_order_id: response.razorpay_order_id,
                                        razorpay_payment_id: response.razorpay_payment_id,
                                        razorpay_signature: response.razorpay_signature,
                                        order_id: orderId
                                    })
                                });

                                const verifyData = await verifyRes.json();

                                if (verifyRes.ok && verifyData.success) {
                                    showStatus('Payment successful! Redirecting...', 'success');
                                    window.location.href = verifyData.redirect;
                                } else {
                                    showStatus(verifyData.error || 'Payment verification failed. Please contact support.', 'error');
                                    showRetryActions();
                                    payBtn.disabled = false;
                                    payBtn.textContent = 'Pay Rs. {{ number_format($order->total, 2) }}';
                                }
                            } catch (err) {
                                console.error('Verification error:', err);
                                showStatus('Network error during verification. Please contact support if amount was deducted.', 'error');
                                showRetryActions();
                                payBtn.disabled = false;
                                payBtn.textContent = 'Pay Rs. {{ number_format($order->total, 2) }}';
                            }
                        },
                        modal: {
                            ondismiss: function () {
                                showStatus('Payment was cancelled. You can retry or continue shopping.', 'warning');
                                showRetryActions();
                                payBtn.disabled = false;
                                payBtn.textContent = 'Pay Rs. {{ number_format($order->total, 2) }}';
                            }
                        }
                    };

                    const rzp = new Razorpay(options);

                    rzp.on('payment.failed', async function (response) {
                        showStatus(
                            response.error?.description || 'Payment failed. Please try again.',
                            'error'
                        );
                        showRetryActions();
                        payBtn.disabled = false;
                        payBtn.textContent = 'Pay Rs. {{ number_format($order->total, 2) }}';

                        // Report failure to backend (non-authoritative)
                        try {
                            await fetch('{{ route("razorpay.failure") }}', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    order_id: orderId,
                                    error_description: response.error?.description || 'Unknown error'
                                })
                            });
                        } catch (e) {
                            // Silently ignore failure reporting errors
                        }
                    });

                    rzp.open();

                } catch (err) {
                    console.error('Payment initiation error:', err);
                    showStatus('Unable to initiate payment. Please check your connection and try again.', 'error');
                    showRetryActions();
                    payBtn.disabled = false;
                    payBtn.textContent = 'Pay Rs. {{ number_format($order->total, 2) }}';
                }
            }

            payBtn.addEventListener('click', initiatePayment);
            retryBtn?.addEventListener('click', initiatePayment);
        })();
    </script>
</x-site.layout>
