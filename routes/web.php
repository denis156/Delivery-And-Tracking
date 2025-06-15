<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Landing Page Routes
|--------------------------------------------------------------------------
*/

Route::get('/', App\Livewire\LandingPage\Index::class)->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Guest only routes - untuk user yang belum login
Route::middleware('guest')->group(function () {
    // Login utama - untuk semua user
    Route::get('/login', App\Livewire\Auth\Pages\Login::class)->name('login');

    // Password Reset Routes
    Route::get('/forgot-password', App\Livewire\Auth\Pages\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', App\Livewire\Auth\Pages\ResetPassword::class)->name('password.reset');
});

// Authenticated routes - untuk user yang sudah login
Route::middleware('auth')->group(function () {
    // Email Verification Routes
    Route::get('/email/verify', App\Livewire\Auth\Pages\EmailVerification::class)
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', App\Livewire\Auth\Pages\EmailVerificationHandler::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Resend verification email
    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

// Logout global
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Application Routes (/app) - untuk non-driver
|--------------------------------------------------------------------------
*/

Route::prefix('app')->name('app.')->group(function () {
    // Authenticated routes - menggunakan custom auth middleware dengan parameter 'app'
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', App\Livewire\App\Pages\Dashboard::class)->name('dashboard');
        Route::get('/user', App\Livewire\App\Pages\User\Index::class)->name('user');
        Route::get('/user/create', App\Livewire\App\Pages\User\Create::class)->name('user.create');
        Route::get('/user/{user}/view', App\Livewire\App\Pages\User\View::class)->name('user.view');
        Route::get('/user/{user}/edit', App\Livewire\App\Pages\User\Edit::class)->name('user.edit');
    });
});

/*
|--------------------------------------------------------------------------
| Driver Routes (/driver) - untuk driver saja
|--------------------------------------------------------------------------
*/

Route::prefix('driver')->name('driver.')->group(function () {
    // Authenticated routes - menggunakan custom auth middleware dengan parameter 'driver'
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', function () {
            return redirect()->route('driver.dashboard');
        });

        Route::get('/dashboard', function () {
            return 'Driver Dashboard - Coming Soon';
        })->name('dashboard');
    });
});
