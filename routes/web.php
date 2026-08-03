<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSessionController;
use App\Http\Controllers\Auth\UserSessionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OfferDetailsController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\PreventAdminResponseCaching;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [UserSessionController::class, 'create'])->name('login');
    Route::post('/login', [UserSessionController::class, 'store'])->name('login.submit');
});

Route::post('/logout', [UserSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/language/{locale}', LocaleController::class)->name('language.switch');

Route::get('/products/{product:slug}', ProductDetailsController::class)->name('products.show');
Route::get('/offers/{offer}', OfferDetailsController::class)->name('offers.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
    Route::post('/cart/{product:slug}', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{product:slug}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product:slug}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::get('/wishlist/products', [WishlistController::class, 'products'])->name('wishlist.products');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product:slug}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
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
            Route::view('/categories', 'admin.placeholder', [
                'title' => 'Categories',
                'headline' => 'Categories',
                'description' => 'Category management will live here next. For now, this menu is ready in the sidebar.',
            ])->name('categories.index');

            Route::view('/inventory', 'admin.placeholder', [
                'title' => 'Inventory',
                'headline' => 'Inventory',
                'description' => 'Inventory control will live here next. For now, this menu is ready in the sidebar.',
            ])->name('inventory.index');

            Route::resource('products', AdminProductController::class)
                ->only(['store', 'edit', 'update', 'destroy']);
            Route::resource('offers', AdminOfferController::class)
                ->only(['index', 'store', 'edit', 'update', 'destroy']);
            Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('logout');
        });
    });
