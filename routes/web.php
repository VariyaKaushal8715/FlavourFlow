<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSessionController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::post('/login', [AdminSessionController::class, 'store'])
        ->middleware(['guest', 'throttle:5,1'])
        ->name('login');

    Route::middleware(['auth', 'can:access-admin'])->group(function () {
        Route::resource('products', AdminProductController::class)
            ->only(['store', 'edit', 'update', 'destroy']);
        Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('logout');
    });
});
