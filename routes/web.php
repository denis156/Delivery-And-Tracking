<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the landing page. These routes are publicly
| accessible and showcase the Delivery & Tracking Truck System.
|
*/


// Landing Page - Alternative Routes (for SEO & flexibility)
Route::get('/', App\Livewire\LandingPage\Index::class)->name('landing-page.home');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Routes for the main application (admin panel, dashboard, etc.)
| These routes are typically protected by authentication middleware.
|
*/

Route::prefix('app')->group(function () {
    Route::get('/', App\Livewire\App\Pages\Dashboard::class)->name('app.dashboard');
    Route::get('/user', App\Livewire\App\Pages\User\Index::class)->name('app.user');
    Route::get('/user/create', App\Livewire\App\Pages\User\Create::class)->name('app.user.create');
    Route::get('/user/{user}/view', App\Livewire\App\Pages\User\View::class)->name('app.user.view');
    Route::get('/user/{user}/edit', App\Livewire\App\Pages\User\Edit::class)->name('app.user.edit');
});

/*
|--------------------------------------------------------------------------
| Driver Routes
|--------------------------------------------------------------------------
|
| Routes for driver interface (mobile-optimized)
|
*/

Route::prefix('driver')->group(function () {
    // Route::get('/', App\Livewire\Driver\Pages\Dashboard::class)->name('driver.dashboard');
    // Route::get('/surat-jalan', App\Livewire\Driver\Pages\SuratJalan\Index::class)->name('driver.surat-jalan');
    // Route::get('/tracking', App\Livewire\Driver\Pages\Tracking::class)->name('driver.tracking');
});

