<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSessionController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfferDetailsController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\PreventAdminResponseCaching;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/products/{product:slug}', ProductDetailsController::class)->name('products.show');
Route::get('/offers/{offer}', OfferDetailsController::class)->name('offers.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::get('/wishlist/products', [WishlistController::class, 'products'])->name('wishlist.products');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
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
            Route::resource('products', AdminProductController::class)
                ->only(['store', 'edit', 'update', 'destroy']);
            Route::resource('offers', AdminOfferController::class)
                ->only(['index', 'store', 'edit', 'update', 'destroy']);
            Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('logout');
        });
    });
