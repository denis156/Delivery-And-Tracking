<?php

namespace App\Providers;

use App\Class\GeolocationService;
use Illuminate\Support\ServiceProvider;
use App\Class\Geolocation\WeatherService;
use App\Class\Geolocation\TrackingManager;
use App\Class\Geolocation\DistanceCalculator;
use App\Class\Geolocation\LocationRepository;
use App\Class\Geolocation\CoreLocationService;

class GeolocationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register individual service components as singletons
        $this->app->singleton(CoreLocationService::class, function ($app) {
            return new CoreLocationService();
        });

        $this->app->singleton(DistanceCalculator::class, function ($app) {
            return new DistanceCalculator();
        });

        $this->app->singleton(LocationRepository::class, function ($app) {
            return new LocationRepository($app->make(CoreLocationService::class));
        });

        $this->app->singleton(TrackingManager::class, function ($app) {
            return new TrackingManager(
                $app->make(CoreLocationService::class),
                $app->make(LocationRepository::class),
                $app->make(DistanceCalculator::class)
            );
        });

        $this->app->singleton(WeatherService::class, function ($app) {
            return new WeatherService(
                $app->make(CoreLocationService::class),
                $app->make(LocationRepository::class)
            );
        });

        // Register main GeolocationService as singleton
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
            CoreLocationService::class,
            DistanceCalculator::class,
            LocationRepository::class,
            TrackingManager::class,
            WeatherService::class,
        ];
    }
}
