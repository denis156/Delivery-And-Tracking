<?php

namespace App\Providers;

use App\Foundation\Inspiring;
use Illuminate\Support\ServiceProvider;

class InspiringServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind our custom Inspiring class to override the default one
        $this->app->bind(\Illuminate\Foundation\Inspiring::class, Inspiring::class);

        // Bind singleton untuk akses mudah
        $this->app->singleton('inspiring', function ($app) {
            return new Inspiring();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
