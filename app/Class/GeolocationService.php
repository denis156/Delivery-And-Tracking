<?php

namespace App\Class;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GeolocationService
{
    /**
     * Get current WITA time
     */
    private function getWitaTime(): Carbon
    {
        return Carbon::now('Asia/Makassar'); // WITA timezone (UTC+8)
    }

    /**
     * Format time to WITA string
     */
    private function formatWitaTime(?Carbon $time = null): string
    {
        $time = $time ?: $this->getWitaTime();
        return $time->format('H:i:s');
    }

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
     * Update user location IMMEDIATELY for real-time tracking
     * No complex API calls, just update coordinates and basic info
     */
    public function updateUserLocationImmediate(int $userId, float $lat, float $lng): void
    {
        $cacheKey = "user_location_{$userId}";

        // Get existing data to preserve address if available
        $existing = Cache::get($cacheKey, []);

        // Update with new coordinates immediately (WITA timezone)
        $locationData = [
            'latitude' => $lat,
            'longitude' => $lng,
            'city' => $existing['city'] ?? 'Mengambil alamat...',
            'province' => $existing['province'] ?? '',
            'village' => $existing['village'] ?? '',
            'subdistrict' => $existing['subdistrict'] ?? '',
            'last_updated' => $this->getWitaTime()->toISOString(),
            'last_updated_wita' => $this->formatWitaTime(),
            'real_time_mode' => true,
            'timezone' => 'WITA',
            // Preserve existing weather data atau ambil fresh weather
            'weather_data' => $existing['weather_data'] ?? $this->getFreshWeatherData($lat, $lng),
            'weather' => $existing['weather_data'] ?? $this->getFreshWeatherData($lat, $lng) // Backward compatibility
        ];

        // Cache for shorter time for real-time updates
        Cache::put($cacheKey, $locationData, now()->addHours(1));

        // Dispatch proper job class for background address lookup
        \App\Jobs\AddressLookupJob::dispatch($userId, $lat, $lng)->afterResponse();

        Log::info('Real-time location updated immediately', [
            'user_id' => $userId,
            'latitude' => $lat,
            'longitude' => $lng,
            'has_weather' => !is_null($locationData['weather_data']),
            'wita_time' => $this->formatWitaTime(),
            'mode' => 'immediate'
        ]);
    }

    /**
     * Get fresh weather data for immediate updates
     */
    private function getFreshWeatherData(float $lat, float $lng): ?array
    {
        $weatherCacheKey = "weather_quick_{$lat}_{$lng}";

        return Cache::remember($weatherCacheKey, 900, function () use ($lat, $lng) { // 15 menit cache
            $weatherData = $this->getLocationDataFromDe4aApi($lat, $lng);

            if ($weatherData && isset($weatherData['weather_data'])) {
                return $weatherData['weather_data'];
            }

            // Fallback weather data
            return [
                'temperature' => rand(26, 32),
                'condition' => 'Umumnya Berawan',
                'description' => 'Umumnya Berawan', // Untuk backward compatibility
                'humidity' => rand(65, 80),
                'wind_speed' => rand(3, 8),
                'icon' => 'cloud',
                'weather_code' => 3,
                'datetime' => $this->getWitaTime()->toISOString(),
                'datetime_wita' => $this->formatWitaTime(),
                'source' => 'fallback',
                'timezone' => 'WITA'
            ];
        });
    }

    /**
     * Legacy update method for backward compatibility
     */
    public function updateUserLocation(int $userId, float $lat, float $lng, ?string $address = null): void
    {
        // Use immediate update for real-time compatibility
        $this->updateUserLocationImmediate($userId, $lat, $lng);
    }

    /**
     * Get comprehensive location and weather data from de4a.space API
     * Optimized with shorter timeout for real-time use
     */
    private function getLocationDataFromDe4aApi(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(8) // Reduced timeout untuk real-time
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
                            'datetime' => $weatherData['local_datetime'] ?? $this->getWitaTime()->toISOString(),
                            'datetime_wita' => $this->formatWitaTime(),
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
                'wita_time' => $this->formatWitaTime(),
                'timeout' => true
            ]);
        }

        return null;
    }

    /**
     * Get weather info with optimized caching for real-time
     */
    public function getWeatherInfo(?int $userId = null): array
    {
        $location = $userId ?
            $this->getUserLocation($userId) :
            $this->getUserLocation(1);

        $cacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";

        return Cache::remember($cacheKey, 900, function () use ($location) { // Cache 15 menit untuk real-time
            // Check if we have weather data in location cache
            if (isset($location['weather_data']) && $location['weather_data']) {
                $weather = $location['weather_data'];
                return [
                    'temperature' => round($weather['temperature']),
                    'condition' => $weather['condition'],
                    'humidity' => round($weather['humidity']),
                    'wind_speed' => round($weather['wind_speed'], 1),
                    'location' => $location['city'],
                    'icon' => $weather['icon'],
                    'last_updated' => $this->formatWitaTime(),
                    'last_updated_wita' => $this->formatWitaTime(),
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
                    'location' => $this->formatLocationName($weatherData['location']),
                    'icon' => $weather['icon'],
                    'last_updated' => $this->formatWitaTime(),
                    'last_updated_wita' => $this->formatWitaTime(),
                    'source' => 'de4a.space',
                    'weather_code' => $weather['weather_code'],
                    'timezone' => 'WITA'
                ];
            }

            // Ultimate fallback
            return [
                'temperature' => rand(26, 32),
                'condition' => 'Umumnya Berawan',
                'humidity' => rand(65, 80),
                'wind_speed' => rand(3, 8),
                'location' => $location['city'],
                'icon' => 'cloud',
                'last_updated' => $this->formatWitaTime(),
                'last_updated_wita' => $this->formatWitaTime(),
                'source' => 'fallback',
                'timezone' => 'WITA'
            ];
        });
    }

    /**
     * Format location name
     */
    private function formatLocationName(array $locationData): string
    {
        if (empty($locationData)) {
            return 'Alamat tidak tersedia';
        }

        $village = $locationData['village'] ?? '';
        $subdistrict = $locationData['subdistrict'] ?? '';
        $city = $locationData['city'] ?? '';
        $province = $locationData['province'] ?? '';

        $locationParts = [];

        if ($village) $locationParts[] = $village;
        if ($subdistrict && $subdistrict !== $village) $locationParts[] = $subdistrict;
        if ($city && $city !== $subdistrict) $locationParts[] = $city;
        if ($province && $province !== $city) $locationParts[] = $province;

        return !empty($locationParts) ? implode(', ', $locationParts) : 'Alamat tidak tersedia';
    }

    /**
     * Map weather code to phosphor icons
     */
    private function mapWeatherCodeToIcon(int $weatherCode): string
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
     * Clear user location with immediate effect
     */
    public function clearUserLocation(int $userId): bool
    {
        try {
            $cacheKeys = [
                "user_location_{$userId}",
                "user_tracking_state_{$userId}"
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Clear weather cache
            $location = $this->getUserLocation($userId);
            if ($location['latitude'] && $location['longitude']) {
                $weatherCacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";
                Cache::forget($weatherCacheKey);
            }

            Log::info('User location cleared immediately', [
                'user_id' => $userId,
                'wita_time' => $this->formatWitaTime()
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear user location', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'wita_time' => $this->formatWitaTime()
            ]);
            return false;
        }
    }

    /**
     * Calculate distance between coordinates (Haversine formula)
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
     * Check if user is near a location with tolerance for real-time
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
     * Get location accuracy status optimized for real-time (WITA timezone)
     */
    public function getLocationAccuracyStatus(int $userId): array
    {
        $location = $this->getUserLocation($userId);

        if (!$location['last_updated']) {
            return [
                'status' => 'no_data',
                'label' => 'Tidak ada data',
                'color' => 'gray',
                'wita_time' => $this->formatWitaTime()
            ];
        }

        // Parse timestamp in WITA timezone
        $lastUpdated = Carbon::parse($location['last_updated'])->setTimezone('Asia/Makassar');
        $currentWita = $this->getWitaTime();
        $secondsAgo = $lastUpdated->diffInSeconds($currentWita);

        // Optimized for real-time (5s intervals)
        if ($secondsAgo <= 15) {
            return [
                'status' => 'live',
                'label' => 'Live',
                'color' => 'green',
                'seconds_ago' => $secondsAgo,
                'wita_time' => $this->formatWitaTime($lastUpdated)
            ];
        } elseif ($secondsAgo <= 60) {
            return [
                'status' => 'fresh',
                'label' => 'Terbaru',
                'color' => 'blue',
                'seconds_ago' => $secondsAgo,
                'wita_time' => $this->formatWitaTime($lastUpdated)
            ];
        } elseif ($secondsAgo <= 300) {
            return [
                'status' => 'recent',
                'label' => 'Terkini',
                'color' => 'yellow',
                'seconds_ago' => $secondsAgo,
                'wita_time' => $this->formatWitaTime($lastUpdated)
            ];
        } else {
            return [
                'status' => 'stale',
                'label' => 'Perlu diperbarui',
                'color' => 'red',
                'seconds_ago' => $secondsAgo,
                'wita_time' => $this->formatWitaTime($lastUpdated)
            ];
        }
    }

    /**
     * Get multiple users locations for fleet tracking
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
     * Get location statistics for real-time monitoring (WITA timezone)
     */
    public function getLocationStats(array $userIds): array
    {
        $stats = [
            'total_users' => count($userIds),
            'users_with_location' => 0,
            'live_locations' => 0,
            'fresh_locations' => 0,
            'stale_locations' => 0,
            'average_age_seconds' => 0,
            'generated_at_wita' => $this->formatWitaTime(),
            'timezone' => 'WITA'
        ];

        $totalSeconds = 0;
        $locationsCount = 0;
        $currentWita = $this->getWitaTime();

        foreach ($userIds as $userId) {
            $location = $this->getUserLocation($userId);

            if ($location['last_updated']) {
                $stats['users_with_location']++;
                $locationsCount++;

                $lastUpdated = Carbon::parse($location['last_updated'])->setTimezone('Asia/Makassar');
                $secondsAgo = $lastUpdated->diffInSeconds($currentWita);
                $totalSeconds += $secondsAgo;

                if ($secondsAgo <= 15) {
                    $stats['live_locations']++;
                } elseif ($secondsAgo <= 60) {
                    $stats['fresh_locations']++;
                } else {
                    $stats['stale_locations']++;
                }
            }
        }

        if ($locationsCount > 0) {
            $stats['average_age_seconds'] = round($totalSeconds / $locationsCount);
        }

        return $stats;
    }

    /**
     * Calculate distance for specific user
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
     * Check if location data is stale (optimized for real-time, WITA timezone)
     */
    public function isLocationStale(int $userId, int $maxAgeSeconds = 60): bool
    {
        $location = $this->getUserLocation($userId);

        if (!$location['last_updated']) {
            return true;
        }

        $lastUpdated = Carbon::parse($location['last_updated'])->setTimezone('Asia/Makassar');
        $currentWita = $this->getWitaTime();

        return $lastUpdated->diffInSeconds($currentWita) > $maxAgeSeconds;
    }

    /**
     * Get formatted WITA time for display
     */
    public function getFormattedWitaTime(?string $timestamp = null): string
    {
        if ($timestamp) {
            return Carbon::parse($timestamp)->setTimezone('Asia/Makassar')->format('H:i:s');
        }

        return $this->formatWitaTime();
    }

    /**
     * Get WITA date and time
     */
    public function getWitaDateTime(): array
    {
        $wita = $this->getWitaTime();

        return [
            'date' => $wita->format('Y-m-d'),
            'time' => $wita->format('H:i:s'),
            'datetime' => $wita->format('Y-m-d H:i:s'),
            'timestamp' => $wita->toISOString(),
            'timezone' => 'WITA',
            'offset' => '+08:00'
        ];
    }
}
