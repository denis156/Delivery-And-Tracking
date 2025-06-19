<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeolocationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('geolocation', function ($app) {
            return new class {

                /**
                 * Get user location from cache or default to Kendari
                 */
                public function getUserLocation(int $userId): array
                {
                    $cacheKey = "user_location_{$userId}";

                    return Cache::get($cacheKey, [
                        'latitude' => -4.0011471,
                        'longitude' => 122.5040029,
                        'city' => 'Bonggoeya, Wua-Wua, Kota Kendari',
                        'province' => 'Sulawesi Tenggara',
                        'last_updated' => null
                    ]);
                }

                /**
                 * Update user location in cache with data from de4a.space API
                 */
                public function updateUserLocation(int $userId, float $lat, float $lng, ?string $address = null): void
                {
                    $cacheKey = "user_location_{$userId}";

                    // Get comprehensive data from de4a.space API
                    $locationData = $this->getLocationDataFromDe4aApi($lat, $lng);

                    if (!$locationData) {
                        // Fallback if API fails
                        $locationData = [
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'city' => $address ?? 'Lokasi Tidak Diketahui',
                            'province' => '',
                            'last_updated' => now()->toISOString()
                        ];
                    }

                    Cache::put($cacheKey, $locationData, now()->addHours(24));

                    Log::info('User location updated in cache', [
                        'user_id' => $userId,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'address' => $locationData['city']
                    ]);
                }

                /**
                 * Get comprehensive location and weather data from de4a.space API
                 */
                private function getLocationDataFromDe4aApi(float $lat, float $lng): ?array
                {
                    try {
                        $response = Http::timeout(15)
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

                            // Check if response structure is correct
                            if (
                                isset($data['status']) &&
                                $data['status'] == 1 &&
                                isset($data['data'][0])
                            ) {
                                $responseData = $data['data'][0];
                                $locationData = $responseData['location'] ?? [];
                                $weatherData = $responseData['weather'][0][0] ?? null; // Current weather

                                return [
                                    'latitude' => $lat,
                                    'longitude' => $lng,
                                    'city' => $this->formatLocationName($locationData),
                                    'province' => $locationData['province'] ?? '',
                                    'village' => $locationData['village'] ?? '',
                                    'subdistrict' => $locationData['subdistrict'] ?? '',
                                    'last_updated' => now()->toISOString(),
                                    'weather_data' => $weatherData ? [
                                        'temperature' => $weatherData['t'] ?? 28,
                                        'condition' => $weatherData['weather_desc'] ?? 'Cerah',
                                        'humidity' => $weatherData['hu'] ?? 70,
                                        'wind_speed' => $weatherData['ws'] ?? 5,
                                        'weather_code' => $weatherData['weather'] ?? 1,
                                        'icon' => $this->mapWeatherCodeToIcon($weatherData['weather'] ?? 1),
                                        'datetime' => $weatherData['local_datetime'] ?? now()->toISOString(),
                                        'source' => 'de4a.space'
                                    ] : null
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('de4a.space API failed for location and weather lookup', [
                            'error' => $e->getMessage(),
                            'lat' => $lat,
                            'lng' => $lng
                        ]);
                    }

                    return null;
                }

                /**
                 * Get weather info for user location
                 */
                public function getWeatherInfo(?int $userId = null): array
                {
                    $location = $userId ?
                        $this->getUserLocation($userId) :
                        $this->getUserLocation(1);

                    $cacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";

                    return Cache::remember($cacheKey, 1800, function () use ($location) { // Cache 30 menit
                        // Try to get fresh weather data
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
                                'location' => $weatherData['city'],
                                'icon' => $weather['icon'],
                                'last_updated' => now()->format('H:i'),
                                'source' => 'de4a.space',
                                'weather_code' => $weather['weather_code']
                            ];
                        }

                        // Fallback to Open-Meteo if de4a.space fails
                        try {
                            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                                'timezone' => 'Asia/Singapore',
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
                                    'location' => $location['city'],
                                    'icon' => $this->getWeatherIcon($weatherCode),
                                    'last_updated' => now()->format('H:i'),
                                    'source' => 'open-meteo'
                                ];
                            }
                        } catch (\Exception $e) {
                            Log::warning('Open-Meteo Weather API failed', ['error' => $e->getMessage()]);
                        }

                        // Ultimate fallback data
                        return [
                            'temperature' => rand(26, 32),
                            'condition' => 'Umumnya Berawan',
                            'humidity' => rand(65, 80),
                            'wind_speed' => rand(3, 8),
                            'location' => $location['city'],
                            'icon' => 'cloud',
                            'last_updated' => now()->format('H:i'),
                            'source' => 'fallback'
                        ];
                    });
                }

                /**
                 * Get detailed weather forecast for user location
                 */
                public function getWeatherForecast(?int $userId = null, int $days = 3): array
                {
                    $location = $userId ?
                        $this->getUserLocation($userId) :
                        $this->getUserLocation(1);

                    $cacheKey = "weather_forecast_{$location['latitude']}_{$location['longitude']}_{$days}";

                    return Cache::remember($cacheKey, 3600, function () use ($location, $days) { // Cache 1 jam
                        try {
                            $response = Http::timeout(15)
                                ->withHeaders([
                                    'User-Agent' => 'DeliveryTrackingApp/1.0',
                                    'Accept' => 'application/json'
                                ])
                                ->get('https://openapi.de4a.space/api/weather/forecast', [
                                    'lat' => (string) $location['latitude'],
                                    'long' => (string) $location['longitude']
                                ]);

                            if ($response->successful()) {
                                $data = $response->json();

                                if (
                                    isset($data['status']) &&
                                    $data['status'] == 1 &&
                                    isset($data['data'][0]['weather'])
                                ) {
                                    $weatherDays = $data['data'][0]['weather'];
                                    $forecast = [];

                                    // Process weather data for requested days
                                    for ($i = 0; $i < min($days, count($weatherDays)); $i++) {
                                        $dayData = $weatherDays[$i];
                                        $forecast[] = [
                                            'date' => now()->addDays($i)->format('Y-m-d'),
                                            'day_name' => now()->addDays($i)->format('l'),
                                            'hourly' => array_map(function ($hour) {
                                                return [
                                                    'datetime' => $hour['local_datetime'],
                                                    'temperature' => $hour['t'],
                                                    'condition' => $hour['weather_desc'],
                                                    'humidity' => $hour['hu'],
                                                    'wind_speed' => $hour['ws'],
                                                    'weather_code' => $hour['weather'],
                                                    'icon' => $this->mapWeatherCodeToIcon($hour['weather']),
                                                    'precipitation' => $hour['tp'] ?? 0
                                                ];
                                            }, $dayData)
                                        ];
                                    }

                                    return [
                                        'location' => $this->formatLocationName($data['data'][0]['location'] ?? []),
                                        'forecast' => $forecast,
                                        'source' => 'de4a.space',
                                        'last_updated' => now()->toISOString()
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('Weather forecast API failed', [
                                'error' => $e->getMessage(),
                                'location' => $location
                            ]);
                        }

                        return [
                            'location' => $location['city'],
                            'forecast' => [],
                            'source' => 'unavailable',
                            'last_updated' => now()->toISOString()
                        ];
                    });
                }

                /**
                 * Format location name using the same format as dashboard weather service
                 */
                private function formatLocationName(array $locationData): string
                {
                    if (empty($locationData)) {
                        return 'Lokasi Tidak Diketahui';
                    }

                    $village = $locationData['village'] ?? '';
                    $subdistrict = $locationData['subdistrict'] ?? '';
                    $city = $locationData['city'] ?? '';
                    $province = $locationData['province'] ?? '';

                    // Build location string with available components
                    $locationParts = [];

                    if ($village) {
                        $locationParts[] = $village;
                    }

                    if ($subdistrict && $subdistrict !== $village) {
                        $locationParts[] = $subdistrict;
                    }

                    if ($city && $city !== $subdistrict) {
                        $locationParts[] = $city;
                    }

                    if ($province && $province !== $city) {
                        $locationParts[] = $province;
                    }

                    // Return formatted location string
                    if (!empty($locationParts)) {
                        return implode(', ', $locationParts);
                    }

                    return 'Lokasi Tidak Diketahui';
                }

                /**
                 * Map de4a.space weather code to phosphor icons
                 */
                private function mapWeatherCodeToIcon(int $weatherCode): string
                {
                    return match ($weatherCode) {
                        1 => 'sun', // Cerah/Sunny
                        2 => 'cloud-sun', // Cerah Berawan/Partly Cloudy
                        3 => 'cloud', // Berawan/Cloudy
                        4 => 'cloud', // Berawan Tebal/Overcast
                        60, 61, 62, 63 => 'cloud-fog', // Hujan ringan/Light rain
                        80, 81, 82 => 'cloud-rain', // Hujan/Rain
                        95, 96, 97 => 'cloud-lightning', // Badai petir/Thunderstorm
                        71, 73, 75 => 'cloud-snow', // Salju/Snow
                        45, 48 => 'cloud', // Kabut/Fog
                        default => 'cloud' // Default
                    };
                }

                /**
                 * Clear user location from cache
                 */
                public function clearUserLocation(int $userId): bool
                {
                    try {
                        $locationCacheKey = "user_location_{$userId}";
                        $pollStateCacheKey = "user_polling_state_{$userId}";

                        // Get location for weather cache cleanup
                        $location = $this->getUserLocation($userId);

                        // Clear location and polling state
                        Cache::forget($locationCacheKey);
                        Cache::forget($pollStateCacheKey);

                        // Also clear weather cache for this location
                        if ($location['latitude'] && $location['longitude']) {
                            $weatherCacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";
                            Cache::forget($weatherCacheKey);

                            // Clear forecast cache too
                            for ($i = 1; $i <= 7; $i++) {
                                $forecastCacheKey = "weather_forecast_{$location['latitude']}_{$location['longitude']}_{$i}";
                                Cache::forget($forecastCacheKey);
                            }
                        }

                        Log::info('User location and related weather cache cleared', ['user_id' => $userId]);

                        return true;
                    } catch (\Exception $e) {
                        Log::error('Failed to clear user location', [
                            'user_id' => $userId,
                            'error' => $e->getMessage()
                        ]);

                        return false;
                    }
                }

                /**
                 * Clear weather cache for specific location
                 */
                public function clearWeatherCache(float $lat, float $lng): void
                {
                    $weatherCacheKey = "weather_info_{$lat}_{$lng}";
                    Cache::forget($weatherCacheKey);

                    // Clear forecast cache too
                    for ($i = 1; $i <= 7; $i++) {
                        $forecastCacheKey = "weather_forecast_{$lat}_{$lng}_{$i}";
                        Cache::forget($forecastCacheKey);
                    }

                    Log::info('Weather cache cleared for location', [
                        'latitude' => $lat,
                        'longitude' => $lng
                    ]);
                }

                /**
                 * Get multiple users locations (for tracking multiple drivers)
                 */
                public function getMultipleUserLocations(array $userIds): array
                {
                    $locations = [];

                    foreach ($userIds as $userId) {
                        $locations[$userId] = $this->getUserLocation($userId);
                    }

                    return $locations;
                }

                /**
                 * Calculate distance between two coordinates (Haversine formula)
                 */
                public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
                {
                    $earthRadius = 6371; // kilometers

                    $dLat = deg2rad($lat2 - $lat1);
                    $dLng = deg2rad($lng2 - $lng1);

                    $a = sin($dLat / 2) * sin($dLat / 2) +
                         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                         sin($dLng / 2) * sin($dLng / 2);

                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

                    return $earthRadius * $c;
                }

                /**
                 * Check if user is within a certain radius of a location
                 */
                public function isUserNearLocation(int $userId, float $targetLat, float $targetLng, float $radiusKm = 1.0): bool
                {
                    $userLocation = $this->getUserLocation($userId);

                    if (!$userLocation['latitude'] || !$userLocation['longitude']) {
                        return false;
                    }

                    $distance = $this->calculateDistance(
                        $userLocation['latitude'],
                        $userLocation['longitude'],
                        $targetLat,
                        $targetLng
                    );

                    return $distance <= $radiusKm;
                }

                /**
                 * Get all users within a radius of a location
                 */
                public function getUsersNearLocation(array $userIds, float $targetLat, float $targetLng, float $radiusKm = 5.0): array
                {
                    $nearbyUsers = [];

                    foreach ($userIds as $userId) {
                        if ($this->isUserNearLocation($userId, $targetLat, $targetLng, $radiusKm)) {
                            $nearbyUsers[] = [
                                'user_id' => $userId,
                                'location' => $this->getUserLocation($userId),
                                'distance' => $this->calculateDistanceForUser($userId, $targetLat, $targetLng)
                            ];
                        }
                    }

                    // Sort by distance
                    usort($nearbyUsers, fn($a, $b) => $a['distance'] <=> $b['distance']);

                    return $nearbyUsers;
                }

                /**
                 * Calculate distance for a specific user from target location
                 */
                public function calculateDistanceForUser(int $userId, float $targetLat, float $targetLng): ?float
                {
                    $userLocation = $this->getUserLocation($userId);

                    if (!$userLocation['latitude'] || !$userLocation['longitude']) {
                        return null;
                    }

                    return $this->calculateDistance(
                        $userLocation['latitude'],
                        $userLocation['longitude'],
                        $targetLat,
                        $targetLng
                    );
                }

                /**
                 * Convert Open-Meteo weather code to Indonesian condition (fallback)
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
                 * Get weather icon name for Open-Meteo codes (fallback)
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
                 * Check if location data is stale (older than specified minutes)
                 */
                public function isLocationStale(int $userId, int $maxAgeMinutes = 30): bool
                {
                    $location = $this->getUserLocation($userId);

                    if (!$location['last_updated']) {
                        return true;
                    }

                    $lastUpdated = \Carbon\Carbon::parse($location['last_updated']);
                    return $lastUpdated->diffInMinutes(now()) > $maxAgeMinutes;
                }

                /**
                 * Get location accuracy status
                 */
                public function getLocationAccuracyStatus(int $userId): array
                {
                    $location = $this->getUserLocation($userId);

                    if (!$location['last_updated']) {
                        return [
                            'status' => 'no_data',
                            'label' => 'Tidak ada data',
                            'color' => 'gray'
                        ];
                    }

                    $lastUpdated = \Carbon\Carbon::parse($location['last_updated']);
                    $minutesAgo = $lastUpdated->diffInMinutes(now());

                    if ($minutesAgo <= 5) {
                        return [
                            'status' => 'fresh',
                            'label' => 'Terbaru',
                            'color' => 'green'
                        ];
                    } elseif ($minutesAgo <= 30) {
                        return [
                            'status' => 'recent',
                            'label' => 'Terkini',
                            'color' => 'blue'
                        ];
                    } elseif ($minutesAgo <= 120) {
                        return [
                            'status' => 'stale',
                            'label' => 'Agak lama',
                            'color' => 'yellow'
                        ];
                    } else {
                        return [
                            'status' => 'old',
                            'label' => 'Perlu diperbarui',
                            'color' => 'red'
                        ];
                    }
                }

                /**
                 * Refresh location and weather data
                 */
                public function refreshLocationData(int $userId): bool
                {
                    $location = $this->getUserLocation($userId);

                    if (!$location['latitude'] || !$location['longitude']) {
                        return false;
                    }

                    try {
                        // Clear existing cache
                        $this->clearWeatherCache($location['latitude'], $location['longitude']);

                        // Update location with fresh data from API
                        $this->updateUserLocation(
                            $userId,
                            $location['latitude'],
                            $location['longitude']
                        );

                        Log::info('Location and weather data refreshed', [
                            'user_id' => $userId,
                            'latitude' => $location['latitude'],
                            'longitude' => $location['longitude']
                        ]);

                        return true;
                    } catch (\Exception $e) {
                        Log::error('Failed to refresh location data', [
                            'user_id' => $userId,
                            'error' => $e->getMessage()
                        ]);
                    }

                    return false;
                }

                /**
                 * Get location statistics for analytics
                 */
                public function getLocationStats(array $userIds): array
                {
                    $stats = [
                        'total_users' => count($userIds),
                        'users_with_location' => 0,
                        'fresh_locations' => 0,
                        'stale_locations' => 0,
                        'average_age_minutes' => 0
                    ];

                    $totalMinutes = 0;
                    $locationsCount = 0;

                    foreach ($userIds as $userId) {
                        $location = $this->getUserLocation($userId);

                        if ($location['last_updated']) {
                            $stats['users_with_location']++;
                            $locationsCount++;

                            $minutesAgo = \Carbon\Carbon::parse($location['last_updated'])->diffInMinutes(now());
                            $totalMinutes += $minutesAgo;

                            if ($minutesAgo <= 30) {
                                $stats['fresh_locations']++;
                            } else {
                                $stats['stale_locations']++;
                            }
                        }
                    }

                    if ($locationsCount > 0) {
                        $stats['average_age_minutes'] = round($totalMinutes / $locationsCount);
                    }

                    return $stats;
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
