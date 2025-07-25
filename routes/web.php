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
| Authentication Routes - Guest Only with Rate Limiting
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', App\Livewire\Auth\Pages\Login::class)
        ->middleware('throttle:60,1')
        ->name('login');

    Route::get('/forgot-password', App\Livewire\Auth\Pages\ForgotPassword::class)
        ->middleware('throttle:30,1')
        ->name('password.request');

    Route::get('/reset-password/{token}', App\Livewire\Auth\Pages\ResetPassword::class)
        ->middleware('throttle:30,1')
        ->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes - Authenticated Only with Rate Limiting
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', App\Livewire\Auth\Pages\EmailVerification::class)
        ->middleware('throttle:30,1')
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', App\Livewire\Auth\Pages\EmailVerificationHandler::class)
        ->middleware(['signed', 'throttle:10,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Global Logout Route
|--------------------------------------------------------------------------
*/

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Application Routes (/app) - untuk non-driver
| Semua role kecuali driver bisa akses
|--------------------------------------------------------------------------
*/

Route::prefix('app')
    ->name('app.')
    ->middleware(['auth', 'verified', 'role:manager|admin|client|petugas-lapangan|petugas-ruangan|petugas-gudang'])
    ->group(function () {
        Route::get('/', App\Livewire\App\Pages\Dashboard::class)->name('dashboard');

        // User Management Routes
        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/', App\Livewire\App\Pages\User\Index::class)->name('index');
            Route::get('/create', App\Livewire\App\Pages\User\Create::class)->name('create');
            Route::get('/{user}/view', App\Livewire\App\Pages\User\View::class)->name('view');
            Route::get('/{user}/edit', App\Livewire\App\Pages\User\Edit::class)->name('edit');
        });

        // Driver Management Routes
        Route::prefix('driver')->name('driver.')->group(function () {
            Route::get('/', App\Livewire\App\Pages\Driver\Index::class)->name('index');
            Route::get('/create', App\Livewire\App\Pages\Driver\Create::class)->name('create');
            Route::get('/{user}/view', App\Livewire\App\Pages\Driver\View::class)->name('view');
            Route::get('/{user}/edit', App\Livewire\App\Pages\Driver\Edit::class)->name('edit');
        });

        // Client Management Routes
        Route::prefix('client')->name('client.')->group(function () {
            Route::get('/', App\Livewire\App\Pages\Client\Index::class)->name('index');
            Route::get('/create', App\Livewire\App\Pages\Client\Create::class)->name('create');
            Route::get('/{user}/view', App\Livewire\App\Pages\Client\View::class)->name('view');
            Route::get('/{user}/edit', App\Livewire\App\Pages\Client\Edit::class)->name('edit');
        });


        // Permission Management Routes
        Route::prefix('permission')->name('permission.')->group(function () {
            Route::get('/', App\Livewire\App\Pages\Permission\Index::class)->name('index');
            Route::get('/create', App\Livewire\App\Pages\Permission\Create::class)->name('create');
            Route::get('/{permission}/view', App\Livewire\App\Pages\Permission\View::class)->name('view');
            Route::get('/{permission}/edit', App\Livewire\App\Pages\Permission\Edit::class)->name('edit');
        });

        // DeliveryOrder Management Routes
        Route::prefix('delivery-order')->name('delivery-order.')->group(function () {
            Route::get('/', App\Livewire\App\Pages\DeliveryOrder\Index::class)->name('index');
        });
    });

/*
|--------------------------------------------------------------------------
| Driver Routes (/driver) - untuk driver saja
| Hanya role driver yang bisa akses
|--------------------------------------------------------------------------
*/

Route::prefix('driver')
    ->name('driver.')
    ->middleware(['auth', 'verified', 'role:driver'])
    ->group(function () {
        Route::get('/', App\Livewire\Driver\Pages\Dashboard::class)->name('dashboard');
        Route::get('/delivery-orders', App\Livewire\Driver\Pages\DeliveryOrder\Index::class)->name('delivery-orders');
        Route::get('/profile', App\Livewire\Driver\Pages\Profile::class)->name('profile');

        // Navigate routes - menggunakan prefix untuk konsistensi
        Route::prefix('navigate')->name('navigate.')->group(function () {
            Route::get('/', App\Livewire\Driver\Pages\Navigate\Index::class)->name('index');
            Route::get('/route', App\Livewire\Driver\Pages\Navigate\Route::class)->name('route');
        });
    });
