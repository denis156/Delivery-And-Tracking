<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Class\GeolocationService;

class GeolocationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind GeolocationService as singleton
        $this->app->singleton('geolocation', function ($app) {
            return new GeolocationService();
        });

        // Alternative binding (optional)
        $this->app->singleton(GeolocationService::class, function ($app) {
            return new GeolocationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set default timezone to WITA for geolocation services
        if (config('app.timezone') !== 'Asia/Makassar') {
            // Log timezone configuration for debugging
            \Illuminate\Support\Facades\Log::info('GeolocationService: Using WITA timezone', [
                'app_timezone' => config('app.timezone'),
                'geolocation_timezone' => 'Asia/Makassar'
            ]);
        }

        // Optional: Register console commands if needed
        if ($this->app->runningInConsole()) {
            // Future: Register geolocation-related commands
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'geolocation',
            GeolocationService::class,
        ];
    }
}
