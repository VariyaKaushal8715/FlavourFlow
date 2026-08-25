@props([
    'site',
    'pageTitle' => null,
    'pageDescription' => null,
    'preserveOnRefresh' => false,
    'showFooter' => true,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $pageDescription ?? $site['meta']['description'] }}">

        <title>{{ $pageTitle ?? $site['meta']['title'] }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/flavourflow-mark.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/flavourflow-mark.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        id="top"
        class="bg-white text-zinc-950 antialiased"
        data-brand-logo="{{ asset($site['brand']['logo']) }}"
        data-auto-theme="{{ ($site['theme']['auto_from_logo'] ?? false) ? 'true' : 'false' }}"
        data-public-page="true"
        data-refresh-policy="{{ $preserveOnRefresh ? 'preserve' : 'home' }}"
        data-home-url="{{ route('home') }}"
        data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
        data-wishlist-endpoint="{{ route('wishlist.products') }}"
        data-wishlist-store-url="{{ route('wishlist.store', ['product' => '__product__']) }}"
        data-wishlist-destroy-url="{{ route('wishlist.destroy', ['product' => '__product__']) }}"
        data-cart-summary-url="{{ route('cart.summary') }}"
        data-cart-store-url="{{ route('cart.store', ['product' => '__product__']) }}"
        data-cart-update-url="{{ route('cart.update', ['product' => '__product__']) }}"
        data-cart-destroy-url="{{ route('cart.destroy', ['product' => '__product__']) }}"
        data-cart-clear-url="{{ route('cart.clear') }}"
        data-login-url="{{ route('login') }}"
        style="
            --brand-primary: {{ $site['theme']['primary'] ?? '#b42318' }};
            --brand-accent: {{ $site['theme']['accent'] ?? '#f4b942' }};
            --brand-ink: {{ $site['theme']['ink'] ?? '#09090b' }};
            --brand-surface: {{ $site['theme']['surface'] ?? '#fff9ed' }};
        "
    >
        <main>
            {{ $slot }}
        </main>

        @if ($showFooter)
            <x-site.footer :site="$site" />
        @endif

        @auth
            <style>
                /* Lock navigation when review is pending */
                .review-pending-active a:not([data-star]),
                .review-pending-active button:not(#review-submit-btn):not([data-star]) {
                    pointer-events: none !important;
                    opacity: 0.3 !important;
                }
                .review-pending-active [href="/"],
                .review-pending-active [href="{{ route('home') }}"],
                .review-pending-active .nav-link,
                .review-pending-active [href*="orders"],
                .review-pending-active [href*="checkout"],
                .review-pending-active [href*="cart"],
                .review-pending-active #continue-shopping-btn {
                    display: none !important;
                }
            </style>

            <!-- Mandatory Review Modal -->
            <div id="mandatory-review-modal" class="hidden fixed inset-0 z-[9999] overflow-y-auto bg-zinc-950/90 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl border border-amber-200/60 bg-white p-6 text-left shadow-2xl transition-all sm:p-8">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-zinc-950">Review Your Purchased Product</h3>
                        <p class="mt-2 text-xs text-zinc-500">You must submit a review for your recently purchased product before continuing.</p>
                    </div>

                    <div class="mt-6 flex items-center gap-4 rounded-2xl border border-zinc-100 bg-zinc-50/50 p-3">
                        <img id="review-product-image" class="h-16 w-16 rounded-xl object-cover border border-zinc-200" src="" alt="Product">
                        <div class="min-w-0 flex-1">
                            <h4 id="review-product-name" class="text-sm font-bold text-zinc-950 truncate"></h4>
                            <p id="review-order-number" class="mt-0.5 text-xs text-zinc-400"></p>
                        </div>
                    </div>

                    <form id="mandatory-review-form" class="mt-6 space-y-4">
                        <input type="hidden" id="review-product-id">
                        <input type="hidden" id="review-order-id">

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 text-center">Your Rating</label>
                            <div class="mt-3 flex justify-center gap-2">
                                <button type="button" data-star="1" class="text-zinc-200 hover:scale-110 transition duration-150">
                                    <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                                <button type="button" data-star="2" class="text-zinc-200 hover:scale-110 transition duration-150">
                                    <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                                <button type="button" data-star="3" class="text-zinc-200 hover:scale-110 transition duration-150">
                                    <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                                <button type="button" data-star="4" class="text-zinc-200 hover:scale-110 transition duration-150">
                                    <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                                <button type="button" data-star="5" class="text-zinc-200 hover:scale-110 transition duration-150">
                                    <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </button>
                            </div>
                            <input type="hidden" id="review-rating-value" required>
                        </div>

                        <div>
                            <label for="review-text-input" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Review Comments</label>
                            <textarea id="review-text-input" rows="3" required class="mt-2 block w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm text-zinc-950 placeholder-zinc-400 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary focus:outline-none" placeholder="What did you like or dislike about this spice?"></textarea>
                        </div>

                        <div id="review-error-message" class="hidden text-xs font-semibold text-red-600 bg-red-50 p-3 rounded-xl border border-red-100"></div>

                        <button type="submit" id="review-submit-btn" class="w-full rounded-2xl bg-zinc-950 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-primary disabled:opacity-50">
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.getElementById('mandatory-review-modal');
                    const form = document.getElementById('mandatory-review-form');
                    const starButtons = document.querySelectorAll('[data-star]');
                    const ratingValue = document.getElementById('review-rating-value');
                    const textInput = document.getElementById('review-text-input');
                    const errorMsg = document.getElementById('review-error-message');
                    const submitBtn = document.getElementById('review-submit-btn');
                    let isLocked = false;

                    const checkPendingReview = () => {
                        fetch('/reviews/pending')
                            .then(res => res.json())
                            .then(data => {
                                if (data.has_pending) {
                                    isLocked = true;
                                    document.body.classList.add('review-pending-active', 'overflow-hidden');
                                    
                                    document.getElementById('review-product-image').src = data.product.image;
                                    document.getElementById('review-product-name').textContent = data.product.name;
                                    document.getElementById('review-order-number').textContent = 'From Order: ' + data.order.order_number;
                                    document.getElementById('review-product-id').value = data.product.id;
                                    document.getElementById('review-order-id').value = data.order.id;
                                    
                                    setRating(0);
                                    textInput.value = '';
                                    errorMsg.classList.add('hidden');
                                    modal.classList.remove('hidden');

                                    // Block back/forward navigation state
                                    history.pushState(null, null, window.location.href);
                                } else {
                                    isLocked = false;
                                    document.body.classList.remove('review-pending-active', 'overflow-hidden');
                                    modal.classList.add('hidden');
                                }
                            })
                            .catch(err => console.error("Error fetching reviews status:", err));
                    };

                    const setRating = (rating) => {
                        ratingValue.value = rating || '';
                        starButtons.forEach(btn => {
                            const val = parseInt(btn.dataset.star);
                            if (val <= rating) {
                                btn.classList.remove('text-zinc-200');
                                btn.classList.add('text-amber-400');
                            } else {
                                btn.classList.remove('text-amber-400');
                                btn.classList.add('text-zinc-200');
                            }
                        });
                    };

                    starButtons.forEach(btn => {
                        btn.addEventListener('click', () => {
                            setRating(parseInt(btn.dataset.star));
                        });
                    });

                    // Lock escape & prevent back button navigation
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && isLocked) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }, true);

                    window.addEventListener('popstate', () => {
                        if (isLocked) {
                            history.pushState(null, null, window.location.href);
                        }
                    });

                    window.addEventListener('beforeunload', (e) => {
                        if (isLocked) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });

                    // Handle Form Submission
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();
                        errorMsg.classList.add('hidden');
                        
                        const rating = ratingValue.value;
                        const review_text = textInput.value.trim();

                        if (!rating) {
                            errorMsg.textContent = 'Please select a rating star.';
                            errorMsg.classList.remove('hidden');
                            return;
                        }

                        if (review_text.length < 5) {
                            errorMsg.textContent = 'Review text must be at least 5 characters long.';
                            errorMsg.classList.remove('hidden');
                            return;
                        }

                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Submitting...';

                        fetch('/reviews', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: document.getElementById('review-order-id').value,
                                product_id: document.getElementById('review-product-id').value,
                                rating: rating,
                                review_text: review_text
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit Review';

                            if (data.success) {
                                // Query again to check if another review is pending
                                checkPendingReview();
                            } else {
                                errorMsg.textContent = data.message || 'An error occurred during submission.';
                                errorMsg.classList.remove('hidden');
                            }
                        })
                        .catch(err => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit Review';
                            errorMsg.textContent = 'Network error. Please try again.';
                            errorMsg.classList.remove('hidden');
                            console.error(err);
                        });
                    });

                    checkPendingReview();
                });
            </script>
        @endauth
    </body>
</html>
