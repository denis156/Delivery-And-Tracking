<?php

namespace App\Class\Geolocation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Location Repository - Handle all location data storage and retrieval
 */
class LocationRepository
{
    private CoreLocationService $coreService;

    public function __construct(CoreLocationService $coreService)
    {
        $this->coreService = $coreService;
    }

    /**
     * Get default location data
     */
    public function getDefaultLocation(): array
    {
        return [
            'latitude' => -4.0011471,
            'longitude' => 122.5040029,
            'city' => 'Bonggoeya, Wua-Wua, Kota Kendari',
            'province' => 'Sulawesi Tenggara',
            'village' => 'Bonggoeya',
            'subdistrict' => 'Wua-Wua',
            'last_updated' => null,
            'start_location' => null,
            'tracking_session_id' => null,
            'tracking_start_time' => null,
            'weather_data' => null,
            'weather' => null, // Backward compatibility
            'real_time_mode' => false,
            'timezone' => 'WITA'
        ];
    }

    /**
     * Get user location from cache or default to Kendari
     */
    public function getUserLocation(int $userId): array
    {
        $cacheKey = "user_location_{$userId}";

        $location = Cache::get($cacheKey, $this->getDefaultLocation());

        // Ensure all required keys exist with fallback values
        $defaultLocation = $this->getDefaultLocation();
        foreach ($defaultLocation as $key => $value) {
            if (!isset($location[$key])) {
                $location[$key] = $value;
            }
        }

        return $location;
    }

    /**
     * Update user location IMMEDIATELY for real-time tracking
     */
    public function updateUserLocationImmediate(int $userId, float $lat, float $lng): void
    {
        $cacheKey = "user_location_{$userId}";

        // Get existing data to preserve address and start location
        $existing = $this->getUserLocation($userId);

        // Update with new coordinates immediately (WITA timezone)
        $locationData = array_merge($existing, [
            'latitude' => $lat,
            'longitude' => $lng,
            'last_updated' => $this->coreService->getWitaTime()->toISOString(),
            'last_updated_wita' => $this->coreService->formatWitaTime(),
            'real_time_mode' => true,
            'timezone' => 'WITA'
        ]);

        // Cache for shorter time for real-time updates
        Cache::put($cacheKey, $locationData, now()->addHours(1));

        Log::info('Real-time location updated', [
            'user_id' => $userId,
            'latitude' => $lat,
            'longitude' => $lng,
            'wita_time' => $this->coreService->formatWitaTime(),
            'mode' => 'immediate'
        ]);
    }

    /**
     * Update location address and weather data (background job)
     */
    public function updateLocationDetails(int $userId, array $locationData, ?array $weatherData = null): void
    {
        $cacheKey = "user_location_{$userId}";
        $existing = $this->getUserLocation($userId);

        // Update only address info, preserve coordinates and timestamp
        $updated = array_merge($existing, [
            'city' => $this->coreService->formatLocationName($locationData),
            'province' => $locationData['province'] ?? $existing['province'],
            'village' => $locationData['village'] ?? $existing['village'],
            'subdistrict' => $locationData['subdistrict'] ?? $existing['subdistrict'],
            'weather_data' => $weatherData,
            'weather' => $weatherData, // Backward compatibility
            'address_updated' => $this->coreService->getWitaTime()->toISOString(),
            'address_updated_wita' => $this->coreService->formatWitaTime(),
            'timezone' => 'WITA'
        ]);

        Cache::put($cacheKey, $updated, now()->addHours(1));

        Log::info('Location details updated', [
            'user_id' => $userId,
            'address' => $updated['city'],
            'has_weather' => isset($updated['weather_data']),
            'wita_time' => $this->coreService->formatWitaTime()
        ]);
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
     * Clear user location
     */
    public function clearUserLocation(int $userId): bool
    {
        try {
            $cacheKey = "user_location_{$userId}";
            Cache::forget($cacheKey);

            // Clear weather cache
            $location = $this->getUserLocation($userId);
            if ($location['latitude'] && $location['longitude']) {
                $weatherCacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";
                Cache::forget($weatherCacheKey);
            }

            Log::info('User location cleared', [
                'user_id' => $userId,
                'wita_time' => $this->coreService->formatWitaTime()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear user location', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'wita_time' => $this->coreService->formatWitaTime()
            ]);
            return false;
        }
    }

    /**
     * Set weather data for location
     */
    public function setWeatherData(int $userId, array $weatherData): void
    {
        $cacheKey = "user_location_{$userId}";
        $existing = $this->getUserLocation($userId);

        $updated = array_merge($existing, [
            'weather_data' => $weatherData,
            'weather' => $weatherData, // Backward compatibility
            'weather_updated' => $this->coreService->getWitaTime()->toISOString(),
            'weather_updated_wita' => $this->coreService->formatWitaTime()
        ]);

        Cache::put($cacheKey, $updated, now()->addHours(1));
    }

    /**
     * Check if user has valid location data
     */
    public function hasValidLocation(int $userId): bool
    {
        $location = $this->getUserLocation($userId);

        return $location['latitude'] &&
               $location['longitude'] &&
               $location['last_updated'] &&
               !$this->coreService->isLocationStale($location, 300); // 5 minutes
    }

    /**
     * Get location cache key
     */
    public function getLocationCacheKey(int $userId): string
    {
        return "user_location_{$userId}";
    }

    /**
     * Backup location data
     */
    public function backupLocationData(int $userId): ?array
    {
        $location = $this->getUserLocation($userId);

        if ($location['latitude'] && $location['longitude']) {
            $backupKey = "user_location_backup_{$userId}";
            Cache::put($backupKey, $location, now()->addDays(1));
            return $location;
        }

        return null;
    }

    /**
     * Restore location data from backup
     */
    public function restoreLocationData(int $userId): bool
    {
        $backupKey = "user_location_backup_{$userId}";
        $backup = Cache::get($backupKey);

        if ($backup) {
            $cacheKey = "user_location_{$userId}";
            Cache::put($cacheKey, $backup, now()->addHours(1));

            Log::info('Location data restored from backup', [
                'user_id' => $userId,
                'wita_time' => $this->coreService->formatWitaTime()
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get location history count (if tracking multiple sessions)
     */
    public function getLocationHistoryCount(int $userId): int
    {
        // This could be expanded to track location history
        $historyKey = "user_location_history_count_{$userId}";
        return Cache::get($historyKey, 0);
    }

    /**
     * Increment location update count
     */
    public function incrementLocationUpdateCount(int $userId): void
    {
        $countKey = "user_location_updates_{$userId}";
        $currentCount = Cache::get($countKey, 0);
        Cache::put($countKey, $currentCount + 1, now()->addDay());
    }
}
