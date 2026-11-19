<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GuestAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('web')->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/guest', [GuestAuthController::class, 'login'])->name('api.auth.guest');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
    });

    Route::post('/guest/validate', [GuestAuthController::class, 'validate'])->name('api.auth.guest.validate');
    Route::post('/guest/logout', [GuestAuthController::class, 'logout'])->name('api.auth.guest.logout');
});
