<?php

use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\UserProfileController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserSessionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactEmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OfferDetailsController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\PreventAdminResponseCaching;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('/contact-email', ContactEmailController::class)
    ->middleware(['web', 'throttle:10,1'])
    ->name('contact.email');
Route::middleware(['guest', PreventAdminResponseCaching::class])->group(function () {
    Route::get('/login', [UserSessionController::class, 'create'])->name('login');
    Route::post('/login', [UserSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.submit');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [UserSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/language/{locale}', LocaleController::class)->name('language.switch');

Route::get('/products/{product:slug}', ProductDetailsController::class)->name('products.show');
Route::get('/offers/{offer}', OfferDetailsController::class)->name('offers.show');

Route::middleware('auth')->group(function () {
    Route::prefix('account')
        ->name('account.')
        ->group(function () {
            Route::get('/', [UserProfileController::class, 'edit'])->name('profile');
            Route::post('/', [UserProfileController::class, 'store'])->name('profile.store');
            Route::put('/', [UserProfileController::class, 'update'])->name('profile.update');
            Route::patch('/mobile-number', [UserProfileController::class, 'updateMobileNumber'])->name('profile.mobile_number.update');
            Route::patch('/email-address', [UserProfileController::class, 'updateEmailAddress'])->name('profile.email.update');
            Route::delete('/', [UserProfileController::class, 'destroy'])->name('profile.destroy');
            Route::get('/orders', [OrderController::class, 'index'])->name('orders');
            Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{order:order_number}/track', [OrderController::class, 'track'])->name('orders.track');
            Route::get('/orders/{order:order_number}/receipt', [OrderController::class, 'downloadReceipt'])->name('orders.receipt');
            Route::get('/orders/notifications/sse', [OrderController::class, 'sse'])->name('orders.notifications.sse');
            Route::post('/orders/{order:order_number}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('/orders/{order:order_number}/return', [OrderController::class, 'requestReturn'])->name('orders.return');
            Route::post('/orders/{order:order_number}/refund', [OrderController::class, 'requestRefund'])->name('orders.refund');
        });

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/{product:slug}', [CartController::class, 'store'])->name('cart.store');
    Route::match(['put', 'patch'], '/cart/{product:slug}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product:slug}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/coupon/apply', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::get('/wishlist/products', [WishlistController::class, 'products'])->name('wishlist.products');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product:slug}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::get('/reviews/pending', [ReviewController::class, 'pending'])->name('reviews.pending');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(PreventAdminResponseCaching::class)
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
        Route::post('/login', [AdminSessionController::class, 'store'])
            ->middleware(['guest', 'throttle:5,1'])
            ->name('login');

        Route::middleware(['auth', 'can:access-admin'])->group(function () {
            // Analytics
            Route::prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/sales', [AdminAnalyticsController::class, 'sales'])->name('sales');
                Route::get('/products/{product:slug}', [AdminAnalyticsController::class, 'product'])->name('products.show');
            });

            // Orders
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
            Route::patch('/return-requests/{returnRequest}/status', [AdminOrderController::class, 'updateReturnStatus'])->name('returnRequests.updateStatus');
            Route::patch('/refund-requests/{refundRequest}/status', [AdminOrderController::class, 'updateRefundStatus'])->name('refundRequests.updateStatus');
            Route::get('/orders/{order}/receipt', [AdminOrderController::class, 'downloadReceipt'])->name('orders.receipt.download');
            Route::get('/api/new-orders', [AdminOrderController::class, 'newOrders'])->name('api.newOrders');

            // Inventory
            Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');

            // Categories
            Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/{category}', [AdminCategoryController::class, 'show'])->name('categories.show');

            // Products
            Route::resource('products', AdminProductController::class)
                ->except(['show']);

            // Offers
            Route::resource('offers', AdminOfferController::class)
                ->only(['index', 'store', 'edit', 'update', 'destroy']);

            // Profile
            Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

            Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('logout');
        });
    });
