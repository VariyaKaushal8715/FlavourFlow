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
                .review-pending-active button:not(#review-submit-btn):not([data-star-value]) {
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
                        <h3 class="text-lg font-bold text-zinc-950">Review Your Purchased Products</h3>
                        <p class="mt-2 text-xs text-zinc-500">You must submit reviews for the items in your order before continuing.</p>
                        <p id="review-order-number" class="mt-1 text-sm font-bold text-brand-primary"></p>
                    </div>

                    <form id="mandatory-review-form" class="mt-6 space-y-6">
                        <input type="hidden" id="review-order-id">

                        <div class="space-y-6 max-h-[50vh] overflow-y-auto pr-2" id="reviews-list-container">
                            <!-- Dynamic products list with star ratings and reviews will be built here -->
                        </div>

                        <div id="review-error-message" class="hidden text-xs font-semibold text-red-600 bg-red-50 p-3 rounded-xl border border-red-100"></div>

                        <button type="submit" id="review-submit-btn" class="w-full rounded-2xl bg-zinc-950 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-primary disabled:opacity-50">
                            Submit Reviews
                        </button>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.getElementById('mandatory-review-modal');
                    const form = document.getElementById('mandatory-review-form');
                    const reviewsContainer = document.getElementById('reviews-list-container');
                    const orderIdInput = document.getElementById('review-order-id');
                    const orderNumDisplay = document.getElementById('review-order-number');
                    const errorMsg = document.getElementById('review-error-message');
                    const submitBtn = document.getElementById('review-submit-btn');
                    let isLocked = false;

                    const checkPendingReview = () => {
                        fetch('/reviews/pending')
                            .then(res => res.json())
                            .then(data => {
                                if (data.has_pending && data.products && data.products.length > 0) {
                                    isLocked = true;
                                    document.body.classList.add('review-pending-active', 'overflow-hidden');
                                    
                                    orderIdInput.value = data.order.id;
                                    orderNumDisplay.textContent = 'Order Number: ' + data.order.order_number;
                                    
                                    // Build dynamic list of products needing reviews
                                    reviewsContainer.innerHTML = '';
                                    data.products.forEach(product => {
                                        const wrapper = document.createElement('div');
                                        wrapper.className = 'border-b border-zinc-100 pb-5 last:border-0 last:pb-0 space-y-3';
                                        wrapper.innerHTML = `
                                            <div class="flex items-center gap-3">
                                                <img class="h-12 w-12 rounded-xl object-cover border border-zinc-200" src="${product.image}" alt="${product.name}">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="text-sm font-bold text-zinc-950 truncate">${product.name}</h4>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 text-center">Your Rating</label>
                                                <div class="mt-2 flex justify-center gap-2" data-rating-group="${product.id}">
                                                    ${[1,2,3,4,5].map(star => `
                                                        <button type="button" data-star-value="${star}" data-star-product="${product.id}" class="text-zinc-200 hover:scale-110 transition duration-155">
                                                            <svg class="h-7 w-7 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                        </button>
                                                    `).join('')}
                                                </div>
                                                <input type="hidden" id="rating-input-${product.id}" class="product-rating-input" data-product-id="${product.id}" required>
                                            </div>

                                            <div>
                                                <label for="review-text-${product.id}" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Review Comments</label>
                                                <textarea id="review-text-${product.id}" rows="2" required class="mt-2 block w-full rounded-xl border border-zinc-200 px-4 py-2 text-sm text-zinc-950 placeholder-zinc-400 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary focus:outline-none" placeholder="What did you like or dislike?" data-product-id="${product.id}"></textarea>
                                            </div>
                                        `;
                                        reviewsContainer.appendChild(wrapper);
                                    });

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

                    // Handle rating star clicks
                    reviewsContainer.addEventListener('click', (e) => {
                        const btn = e.target.closest('[data-star-value]');
                        if (btn) {
                            const rating = parseInt(btn.dataset.starValue);
                            const productId = btn.dataset.starProduct;
                            
                            document.getElementById(`rating-input-${productId}`).value = rating;
                            
                            const group = reviewsContainer.querySelector(`[data-rating-group="${productId}"]`);
                            const stars = group.querySelectorAll('[data-star-value]');
                            stars.forEach(star => {
                                const val = parseInt(star.dataset.starValue);
                                if (val <= rating) {
                                    star.classList.remove('text-zinc-200');
                                    star.classList.add('text-amber-400');
                                } else {
                                    star.classList.remove('text-amber-400');
                                    star.classList.add('text-zinc-200');
                                }
                            });
                        }
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
                        
                        const reviews = [];
                        const ratingInputs = reviewsContainer.querySelectorAll('.product-rating-input');
                        let hasValidationError = false;

                        ratingInputs.forEach(input => {
                            const productId = input.dataset.productId;
                            const rating = input.value;
                            const comment = document.getElementById(`review-text-${productId}`).value.trim();

                            if (!rating) {
                                errorMsg.textContent = 'Please select a rating star for all products.';
                                errorMsg.classList.remove('hidden');
                                hasValidationError = true;
                                return;
                            }

                            if (comment.length < 5) {
                                errorMsg.textContent = 'Each review comment must be at least 5 characters long.';
                                errorMsg.classList.remove('hidden');
                                hasValidationError = true;
                                return;
                            }

                            reviews.push({
                                product_id: parseInt(productId),
                                rating: parseInt(rating),
                                review_text: comment
                            });
                        });

                        if (hasValidationError) {
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
                                order_id: orderIdInput.value,
                                reviews: reviews
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit Reviews';

                            if (data.success) {
                                checkPendingReview();
                            } else {
                                errorMsg.textContent = data.message || 'An error occurred during submission.';
                                errorMsg.classList.remove('hidden');
                            }
                        })
                        .catch(err => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit Reviews';
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
