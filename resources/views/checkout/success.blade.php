<x-site.layout :site="$site" page-title="Order Confirmed | {{ $site['brand']['name'] }}" :preserve-on-refresh="true">
    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <!-- Floating Confirmation Toast Notification -->
    <div
        id="rating-toast-notification"
        class="fixed top-6 right-6 z-[100] hidden items-center gap-3 rounded-2xl bg-emerald-800 px-6 py-4 text-white shadow-2xl border border-emerald-600 transition-all duration-300 transform -translate-y-4 opacity-0"
        role="status"
    >
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div>
            <p class="font-bold text-base text-white">Thank you for your feedback!</p>
            <p class="text-xs text-emerald-100 mt-0.5">Your rating has been submitted successfully.</p>
        </div>
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
                        <p class="mt-1 font-bold text-zinc-950 text-base tracking-wider">{{ $order->order_id }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Order Date</p>
                        <p class="mt-1 font-semibold text-zinc-950">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Total Amount</p>
                        <p class="mt-1 font-bold text-brand-primary text-base">Rs. {{ number_format($order->total, 2) }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-400">Payment Method</p>
                        <p class="mt-1 font-semibold text-zinc-950">
                            {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : 'Online Payment' }}
                        </p>
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

                <!-- Rating Status Banner in Summary Card -->
                <div id="summary-rating-banner" class="mt-6 pt-6 border-t border-zinc-100 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500 fill-amber-400 flex-shrink-0" viewBox="0 0 24 24">
                            <path fill="#f59e0b" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                        <span id="summary-rating-text" class="font-medium text-zinc-700">
                            @if($order->rating)
                                You rated this order {{ $order->rating }}/5 stars — Thank you for your feedback!
                            @else
                                How was your order experience?
                            @endif
                        </span>
                    </div>
                    <button type="button" id="open-rating-modal-btn" class="text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline focus:outline-none cursor-pointer">
                        @if($order->rating) Update Rating @else Rate Now @endif
                    </button>
                </div>
            </div>

            <!-- Track Order, My Orders, Continue Shopping buttons -->
            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a
                    href="{{ route('account.orders.track', $order->order_id) }}"
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

    <!-- Rating Pop-Up Modal -->
    <div
        id="rating-modal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0 pointer-events-none"
        aria-hidden="true"
        role="dialog"
        aria-labelledby="rating-modal-title"
    >
        <div
            id="rating-modal-card"
            class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-amber-100 transform transition-all duration-300 scale-95 opacity-0 text-center"
        >
            <!-- Close Button -->
            <button
                type="button"
                id="close-rating-modal"
                class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-800 transition focus:outline-none cursor-pointer"
                aria-label="Close rating pop-up"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Rating Form Interface -->
            <div id="rating-form-state">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 shadow-inner">
                    <svg class="h-8 w-8" viewBox="0 0 24 24">
                        <path fill="#f59e0b" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                    </svg>
                </div>

                <h2 id="rating-modal-title" class="mt-4 text-2xl font-bold tracking-tight text-zinc-950">
                    Rate Your Experience
                </h2>
                <p class="mt-1 text-sm text-zinc-600">
                    How satisfied are you with your order experience today?
                </p>

                <!-- Interactive 5-Star Rating Section -->
                <div class="mt-6 flex flex-col items-center">
                    <div class="flex items-center justify-center gap-2" id="star-rating-container">
                        @for ($i = 1; $i <= 5; $i++)
                            <button
                                type="button"
                                data-star-index="{{ $i }}"
                                class="star-btn p-1.5 hover:scale-125 focus:scale-125 transition-transform duration-200 focus:outline-none cursor-pointer"
                                aria-label="Rate {{ $i }} out of 5 stars"
                            >
                                <svg class="h-10 w-10 star-svg transition-colors duration-200" viewBox="0 0 24 24">
                                    <path class="star-path" fill="#f59e0b" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>
                    
                    <!-- Rating Description Label -->
                    <p id="star-rating-label" class="mt-2 h-6 text-xs font-bold uppercase tracking-wider text-amber-600">
                        5 STARS - EXCELLENT!
                    </p>
                </div>

                <!-- Optional Feedback Textarea -->
                <div class="mt-4 text-left">
                    <label for="rating-feedback-text" class="block text-xs font-semibold text-zinc-700 mb-1">
                        Additional feedback <span class="text-zinc-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="rating-feedback-text"
                        rows="3"
                        placeholder="Tell us what you liked or how we can improve..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-sm text-zinc-900 placeholder-zinc-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
                    ></textarea>
                </div>

                <!-- Submit and Skip Actions -->
                <div class="mt-6 flex flex-col gap-3">
                    <button
                        type="button"
                        id="submit-rating-btn"
                        style="background-color: #d97706; color: #ffffff;"
                        class="w-full rounded-2xl py-3.5 px-6 text-sm font-bold text-white shadow-lg transition-all duration-200 hover:brightness-110 active:scale-[0.98] cursor-pointer flex items-center justify-center focus:outline-none"
                    >
                        <span id="submit-btn-text">Submit Rating</span>
                    </button>
                    <button
                        type="button"
                        id="skip-rating-btn"
                        class="py-1 text-xs font-semibold text-zinc-400 hover:text-zinc-600 transition cursor-pointer"
                    >
                        Maybe later
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Rating Modal & Instant Submission Closing -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('rating-modal');
            const card = document.getElementById('rating-modal-card');
            const closeBtn = document.getElementById('close-rating-modal');
            const openBtn = document.getElementById('open-rating-modal-btn');
            const skipBtn = document.getElementById('skip-rating-btn');
            const submitBtn = document.getElementById('submit-rating-btn');
            const submitBtnText = document.getElementById('submit-btn-text');
            const starBtns = Array.from(document.querySelectorAll('.star-btn'));
            const ratingLabel = document.getElementById('star-rating-label');
            const feedbackText = document.getElementById('rating-feedback-text');
            const summaryRatingText = document.getElementById('summary-rating-text');
            const toastNotification = document.getElementById('rating-toast-notification');

            const orderId = {{ $order->id }};
            const rateUrl = "{{ route('orders.rate', $order->id) }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const ratingLabels = {
                1: '1 STAR - NEEDS IMPROVEMENT',
                2: '2 STARS - FAIR',
                3: '3 STARS - GOOD',
                4: '4 STARS - VERY GOOD',
                5: '5 STARS - EXCELLENT!'
            };

            // Pre-select rating (defaults to 5 stars or saved rating)
            let selectedRating = {{ $order->rating ?? 5 }};

            const updateStarDisplay = (highlightIndex) => {
                starBtns.forEach((btn, idx) => {
                    const starVal = idx + 1;
                    const path = btn.querySelector('.star-path');
                    if (path) {
                        path.setAttribute('fill', starVal <= highlightIndex ? '#f59e0b' : '#d4d4d8');
                    }
                });

                if (highlightIndex > 0) {
                    ratingLabel.textContent = ratingLabels[highlightIndex] || `${highlightIndex} STARS`;
                }
            };

            // Initial star render
            updateStarDisplay(selectedRating);

            // Star hover and click interactions
            starBtns.forEach((btn) => {
                const starVal = parseInt(btn.dataset.starIndex, 10);

                btn.addEventListener('mouseenter', () => {
                    updateStarDisplay(starVal);
                });

                btn.addEventListener('mouseleave', () => {
                    updateStarDisplay(selectedRating);
                });

                btn.addEventListener('click', () => {
                    selectedRating = starVal;
                    updateStarDisplay(selectedRating);
                });
            });

            const openModal = () => {
                modal.classList.remove('pointer-events-none', 'opacity-0');
                modal.classList.add('opacity-100');
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                card.classList.remove('scale-100', 'opacity-100');
                card.classList.add('scale-95', 'opacity-0');
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('pointer-events-none');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                }, 200);
            };

            const showConfirmationToast = () => {
                if (!toastNotification) return;
                toastNotification.classList.remove('hidden');
                setTimeout(() => {
                    toastNotification.classList.remove('-translate-y-4', 'opacity-0');
                    toastNotification.classList.add('translate-y-0', 'opacity-100');
                }, 30);

                setTimeout(() => {
                    toastNotification.classList.remove('translate-y-0', 'opacity-100');
                    toastNotification.classList.add('-translate-y-4', 'opacity-0');
                    setTimeout(() => toastNotification.classList.add('hidden'), 300);
                }, 4000);
            };

            // Automatically open rating pop-up upon page load
            setTimeout(() => {
                openModal();
            }, 500);

            openBtn?.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            skipBtn?.addEventListener('click', closeModal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('pointer-events-none')) {
                    closeModal();
                }
            });

            // Handle Submit: Immediately close modal and return to underlying screen with confirmation
            submitBtn.addEventListener('click', () => {
                const ratingToSubmit = selectedRating || 5;
                const feedbackToSubmit = feedbackText ? feedbackText.value.trim() : '';

                // 1. Immediately close the pop-up modal and return user to underlying screen
                closeModal();

                // 2. Display confirmation toast message on the underlying screen
                showConfirmationToast();

                // 3. Update summary rating text on the page
                if (summaryRatingText) {
                    summaryRatingText.textContent = `You rated this order ${ratingToSubmit}/5 stars — Thank you for your feedback!`;
                }
                if (openBtn) {
                    openBtn.textContent = 'Update Rating';
                }

                // 4. Send background request to save the rating and feedback
                fetch(rateUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        rating: ratingToSubmit,
                        feedback: feedbackToSubmit
                    })
                }).catch((err) => {
                    console.error('Rating submission failed:', err);
                });
            });
        });
    </script>
</x-site.layout>
