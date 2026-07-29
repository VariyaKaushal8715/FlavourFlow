<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfferDetailsController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Middleware\PreventAdminResponseCaching;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/products/{product:slug}', ProductDetailsController::class)->name('products.show');
Route::get('/offers/{offer}', OfferDetailsController::class)->name('offers.show');

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
