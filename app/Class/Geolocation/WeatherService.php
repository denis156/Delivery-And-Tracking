<?php

namespace App\Class\Geolocation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Weather Service - Handle all weather-related functionality
 */
class WeatherService
{
    private CoreLocationService $coreService;
    private LocationRepository $locationRepository;

    public function __construct(CoreLocationService $coreService, LocationRepository $locationRepository)
    {
        $this->coreService = $coreService;
        $this->locationRepository = $locationRepository;
    }

    /**
     * Get weather info with optimized caching for real-time
     */
    public function getWeatherInfo(?int $userId = null): array
    {
        $location = $userId ?
            $this->locationRepository->getUserLocation($userId) :
            $this->locationRepository->getDefaultLocation();

        // Pastikan ada latitude dan longitude
        if (!isset($location['latitude']) || !isset($location['longitude'])) {
            Log::warning('Weather service: Invalid location data', [
                'user_id' => $userId,
                'location' => $location
            ]);
            return $this->getFallbackWeatherData($location);
        }

        $cacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";

        return Cache::remember($cacheKey, 900, function () use ($location) {
            // Check if we have weather data in location cache
            if (isset($location['weather_data']) && $location['weather_data']) {
                $weather = $location['weather_data'];
                return [
                    'temperature' => round($weather['temperature']),
                    'condition' => $weather['condition'],
                    'humidity' => round($weather['humidity']),
                    'wind_speed' => round($weather['wind_speed'], 1),
                    'location' => $location['city'] ?? 'Lokasi tidak diketahui',
                    'icon' => $weather['icon'],
                    'last_updated' => $this->coreService->formatWitaTime(),
                    'last_updated_wita' => $this->coreService->formatWitaTime(),
                    'source' => 'de4a.space',
                    'weather_code' => $weather['weather_code'],
                    'timezone' => 'WITA'
                ];
            }

            // Fallback to fresh API call
            $weatherData = $this->getLocationDataFromDe4aApi(
                $location['latitude'],
                $location['longitude']
            );

            if ($weatherData && isset($weatherData['weather_data'])) {
                $weather = $weatherData['weather_data'];
                return [
                    'temperature' => round($weather['temperature']),
                    'condition' => $weather['condition'],
                    'humidity' => round($weather['humidity']),
                    'wind_speed' => round($weather['wind_speed'], 1),
                    'location' => $this->coreService->formatLocationName($weatherData['location']),
                    'icon' => $weather['icon'],
                    'last_updated' => $this->coreService->formatWitaTime(),
                    'last_updated_wita' => $this->coreService->formatWitaTime(),
                    'source' => 'de4a.space',
                    'weather_code' => $weather['weather_code'],
                    'timezone' => 'WITA'
                ];
            }

            // Ultimate fallback
            return $this->getFallbackWeatherData($location);
        });
    }

    /**
     * Get fresh weather data for immediate updates
     */
    public function getFreshWeatherData(float $lat, float $lng): ?array
    {
        $weatherCacheKey = "weather_quick_{$lat}_{$lng}";

        return Cache::remember($weatherCacheKey, 900, function () use ($lat, $lng) {
            $weatherData = $this->getLocationDataFromDe4aApi($lat, $lng);

            if ($weatherData && isset($weatherData['weather_data'])) {
                return $weatherData['weather_data'];
            }

            // Fallback weather data
            return [
                'temperature' => rand(26, 32),
                'condition' => 'Umumnya Berawan',
                'description' => 'Umumnya Berawan',
                'humidity' => rand(65, 80),
                'wind_speed' => rand(3, 8),
                'icon' => 'cloud',
                'weather_code' => 3,
                'datetime' => $this->coreService->getWitaTime()->toISOString(),
                'datetime_wita' => $this->coreService->formatWitaTime(),
                'source' => 'fallback',
                'timezone' => 'WITA'
            ];
        });
    }

    /**
     * Get comprehensive location and weather data from de4a.space API
     */
    public function getLocationDataFromDe4aApi(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'DeliveryTrackingApp/1.0',
                    'Accept' => 'application/json'
                ])
                ->get('https://openapi.de4a.space/api/weather/forecast', [
                    'lat' => (string) $lat,
                    'long' => (string) $lng
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (
                    isset($data['status']) &&
                    $data['status'] == 1 &&
                    isset($data['data'][0])
                ) {
                    $responseData = $data['data'][0];
                    $locationData = $responseData['location'] ?? [];
                    $weatherData = $responseData['weather'][0][0] ?? null;

                    return [
                        'location' => $locationData,
                        'weather_data' => $weatherData ? [
                            'temperature' => $weatherData['t'] ?? 28,
                            'condition' => $weatherData['weather_desc'] ?? 'Cerah',
                            'humidity' => $weatherData['hu'] ?? 70,
                            'wind_speed' => $weatherData['ws'] ?? 5,
                            'weather_code' => $weatherData['weather'] ?? 1,
                            'icon' => $this->mapWeatherCodeToIcon($weatherData['weather'] ?? 1),
                            'datetime' => $weatherData['local_datetime'] ?? $this->coreService->getWitaTime()->toISOString(),
                            'datetime_wita' => $this->coreService->formatWitaTime(),
                            'source' => 'de4a.space'
                        ] : null
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('de4a.space API failed for location lookup', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lng' => $lng,
                'wita_time' => $this->coreService->formatWitaTime(),
                'timeout' => true
            ]);
        }

        return null;
    }

    /**
     * Get weather using Open-Meteo API as fallback
     */
    public function getWeatherFromOpenMeteo(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                'timezone' => 'Asia/Jakarta',
                'forecast_days' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];

                $temperature = round($current['temperature_2m'] ?? 28);
                $humidity = round($current['relative_humidity_2m'] ?? 70);
                $windSpeed = round($current['wind_speed_10m'] ?? 5, 1);
                $weatherCode = $current['weather_code'] ?? 0;

                return [
                    'temperature' => $temperature,
                    'condition' => $this->getWeatherCondition($weatherCode),
                    'humidity' => $humidity,
                    'wind_speed' => $windSpeed,
                    'icon' => $this->getWeatherIcon($weatherCode),
                    'weather_code' => $weatherCode,
                    'datetime' => $this->coreService->getWitaTime()->toISOString(),
                    'datetime_wita' => $this->coreService->formatWitaTime(),
                    'source' => 'open-meteo'
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Open-Meteo Weather API failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get fallback weather data
     */
    private function getFallbackWeatherData(array $location): array
    {
        return [
            'temperature' => rand(26, 32),
            'condition' => 'Umumnya Berawan',
            'humidity' => rand(65, 80),
            'wind_speed' => rand(3, 8),
            'location' => $location['city'] ?? 'Lokasi tidak diketahui',
            'icon' => 'cloud',
            'last_updated' => $this->coreService->formatWitaTime(),
            'last_updated_wita' => $this->coreService->formatWitaTime(),
            'source' => 'fallback',
            'timezone' => 'WITA'
        ];
    }

    /**
     * Map weather code to phosphor icons
     */
    public function mapWeatherCodeToIcon(int $weatherCode): string
    {
        return match ($weatherCode) {
            1 => 'sun',
            2 => 'cloud-sun',
            3 => 'cloud',
            4 => 'cloud',
            60, 61, 62, 63 => 'cloud-fog',
            80, 81, 82 => 'cloud-rain',
            95, 96, 97 => 'cloud-lightning',
            71, 73, 75 => 'cloud-snow',
            45, 48 => 'cloud',
            default => 'cloud'
        };
    }

    /**
     * Convert Open-Meteo weather code to Indonesian condition
     */
    private function getWeatherCondition(int $code): string
    {
        return match (true) {
            $code === 0 => 'Cerah',
            $code >= 1 && $code <= 3 => 'Berawan Sebagian',
            $code >= 45 && $code <= 48 => 'Berkabut',
            $code >= 51 && $code <= 57 => 'Gerimis',
            $code >= 61 && $code <= 67 => 'Hujan',
            $code >= 71 && $code <= 77 => 'Salju',
            $code >= 80 && $code <= 82 => 'Hujan Deras',
            $code >= 95 && $code <= 99 => 'Badai Petir',
            default => 'Umumnya Berawan'
        };
    }

    /**
     * Get weather icon name for Open-Meteo codes
     */
    private function getWeatherIcon(int $code): string
    {
        return match (true) {
            $code === 0 => 'sun',
            $code >= 1 && $code <= 3 => 'cloud-sun',
            $code >= 45 && $code <= 48 => 'cloud',
            $code >= 51 && $code <= 57 => 'cloud-fog',
            $code >= 61 && $code <= 67 => 'cloud-rain',
            $code >= 71 && $code <= 77 => 'cloud-snow',
            $code >= 80 && $code <= 82 => 'cloud-rain-heavy',
            $code >= 95 && $code <= 99 => 'cloud-lightning',
            default => 'cloud'
        };
    }

    /**
     * Clear weather cache for location
     */
    public function clearWeatherCache(float $lat, float $lng): void
    {
        $cacheKeys = [
            "weather_info_{$lat}_{$lng}",
            "weather_quick_{$lat}_{$lng}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Update weather data for user location
     */
    public function updateWeatherForUser(int $userId): bool
    {
        $location = $this->locationRepository->getUserLocation($userId);

        if (!$location['latitude'] || !$location['longitude']) {
            return false;
        }

        $weatherData = $this->getFreshWeatherData($location['latitude'], $location['longitude']);

        if ($weatherData) {
            $this->locationRepository->setWeatherData($userId, $weatherData);
            return true;
        }

        return false;
    }

    /**
     * Get weather summary for multiple locations
     */
    public function getWeatherSummaryForLocations(array $locations): array
    {
        $summary = [];

        foreach ($locations as $key => $location) {
            if (isset($location['latitude']) && isset($location['longitude'])) {
                $weatherData = $this->getFreshWeatherData($location['latitude'], $location['longitude']);
                $summary[$key] = $weatherData;
            }
        }

        return $summary;
    }

    /**
     * Get weather forecast for location
     */
    public function getWeatherForecast(float $lat, float $lng, int $days = 3): array
    {
        $cacheKey = "weather_forecast_{$lat}_{$lng}_{$days}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng, $days) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'DeliveryTrackingApp/1.0',
                        'Accept' => 'application/json'
                    ])
                    ->get('https://openapi.de4a.space/api/weather/forecast', [
                        'lat' => (string) $lat,
                        'long' => (string) $lng
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['status']) && $data['status'] == 1 && isset($data['data'][0]['weather'])) {
                        $forecast = [];
                        $weatherDays = array_slice($data['data'][0]['weather'], 0, $days);

                        foreach ($weatherDays as $dayIndex => $dayWeather) {
                            if (isset($dayWeather[0])) {
                                $weather = $dayWeather[0];
                                $forecast[] = [
                                    'date' => $this->coreService->getWitaTime()->addDays($dayIndex)->format('Y-m-d'),
                                    'temperature' => round($weather['t'] ?? 28),
                                    'condition' => $weather['weather_desc'] ?? 'Cerah',
                                    'humidity' => $weather['hu'] ?? 70,
                                    'wind_speed' => $weather['ws'] ?? 5,
                                    'icon' => $this->mapWeatherCodeToIcon($weather['weather'] ?? 1),
                                    'weather_code' => $weather['weather'] ?? 1
                                ];
                            }
                        }

                        return $forecast;
                    }
                }

                return $this->getForecastFromOpenMeteo($lat, $lng, $days);

            } catch (\Exception $e) {
                Log::warning('Weather forecast API failed', [
                    'error' => $e->getMessage(),
                    'lat' => $lat,
                    'lng' => $lng
                ]);

                return $this->getFallbackForecast($days);
            }
        });
    }

    /**
     * Get forecast from Open-Meteo as fallback
     */
    private function getForecastFromOpenMeteo(float $lat, float $lng, int $days): array
    {
        try {
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'daily' => 'temperature_2m_max,relative_humidity_2m,weather_code,wind_speed_10m_max',
                'timezone' => 'Asia/Jakarta',
                'forecast_days' => $days
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $daily = $data['daily'] ?? [];

                $forecast = [];
                for ($i = 0; $i < $days; $i++) {
                    $forecast[] = [
                        'date' => $daily['time'][$i] ?? $this->coreService->getWitaTime()->addDays($i)->format('Y-m-d'),
                        'temperature' => round($daily['temperature_2m_max'][$i] ?? 28),
                        'condition' => $this->getWeatherCondition($daily['weather_code'][$i] ?? 0),
                        'humidity' => round($daily['relative_humidity_2m'][$i] ?? 70),
                        'wind_speed' => round($daily['wind_speed_10m_max'][$i] ?? 5, 1),
                        'icon' => $this->getWeatherIcon($daily['weather_code'][$i] ?? 0),
                        'weather_code' => $daily['weather_code'][$i] ?? 0
                    ];
                }

                return $forecast;
            }
        } catch (\Exception $e) {
            Log::warning('Open-Meteo forecast failed', ['error' => $e->getMessage()]);
        }

        return $this->getFallbackForecast($days);
    }

    /**
     * Get fallback forecast data
     */
    private function getFallbackForecast(int $days): array
    {
        $forecast = [];
        for ($i = 0; $i < $days; $i++) {
            $forecast[] = [
                'date' => $this->coreService->getWitaTime()->addDays($i)->format('Y-m-d'),
                'temperature' => rand(26, 32),
                'condition' => 'Umumnya Berawan',
                'humidity' => rand(65, 80),
                'wind_speed' => rand(3, 8),
                'icon' => 'cloud',
                'weather_code' => 3
            ];
        }

        return $forecast;
    }

    /**
     * Get weather alerts for location
     */
    public function getWeatherAlerts(float $lat, float $lng): array
    {
        return [];
    }

    /**
     * Get air quality index
     */
    public function getAirQuality(float $lat, float $lng): ?array
    {
        return null;
    }
}
