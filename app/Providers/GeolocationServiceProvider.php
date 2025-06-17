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
                 * Get user location from cache or default to Makassar
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
                 * Update user location in cache
                 */
                public function updateUserLocation(int $userId, float $lat, float $lng, ?string $address = null): void
                {
                    $cacheKey = "user_location_{$userId}";

                    // Try to get city name from coordinates if address not provided
                    if (!$address) {
                        $address = $this->reverseGeocode($lat, $lng);
                    }

                    $locationData = [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'city' => $address ?? 'Lokasi Tidak Diketahui',
                        'province' => '',
                        'last_updated' => now()->toISOString()
                    ];

                    Cache::put($cacheKey, $locationData, now()->addHours(24));

                    Log::info('User location updated in cache', [
                        'user_id' => $userId,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'address' => $address
                    ]);
                }

                /**
                 * Clear user location from cache
                 */
                public function clearUserLocation(int $userId): bool
                {
                    try {
                        $cacheKey = "user_location_{$userId}";
                        Cache::forget($cacheKey);

                        Log::info('User location cleared from cache', ['user_id' => $userId]);

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
                 * Reverse geocoding to get address from coordinates
                 */
                private function reverseGeocode(float $lat, float $lng): ?string
                {
                    try {
                        $response = Http::timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                            'lat' => $lat,
                            'lon' => $lng,
                            'format' => 'json',
                            'zoom' => 18,
                            'addressdetails' => 1
                        ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            $addressParts = $data['address'] ?? [];

                            // Extract detailed location components
                            $village = $addressParts['village'] ?? $addressParts['hamlet'] ?? '';
                            $subdistrict = $addressParts['suburb'] ?? $addressParts['neighbourhood'] ?? '';
                            $city = $addressParts['city'] ?? $addressParts['county'] ?? $addressParts['state_district'] ?? '';
                            $province = $addressParts['state'] ?? '';

                            // Build comprehensive address
                            $addressComponents = [];
                            if ($village) $addressComponents[] = $village;
                            if ($subdistrict && $subdistrict !== $village) $addressComponents[] = $subdistrict;
                            if ($city && $city !== $subdistrict) $addressComponents[] = $city;
                            if ($province && $province !== $city) $addressComponents[] = $province;

                            return !empty($addressComponents) ? implode(', ', $addressComponents) : null;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Reverse geocoding failed', [
                            'error' => $e->getMessage(),
                            'lat' => $lat,
                            'lng' => $lng
                        ]);
                    }

                    return null;
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
                 * Batch update locations (useful for bulk operations)
                 */
                public function batchUpdateLocations(array $locationUpdates): array
                {
                    $results = [];

                    foreach ($locationUpdates as $update) {
                        try {
                            $this->updateUserLocation(
                                $update['user_id'],
                                $update['latitude'],
                                $update['longitude'],
                                $update['address'] ?? null
                            );
                            $results[$update['user_id']] = true;
                        } catch (\Exception $e) {
                            $results[$update['user_id']] = false;
                            Log::error('Batch location update failed for user', [
                                'user_id' => $update['user_id'],
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    return $results;
                }

                /**
                 * Clear all location cache (admin function)
                 */
                public function clearAllLocationCache(): bool
                {
                    try {
                        $pattern = 'user_location_*';
                        $keys = Cache::get($pattern, []);

                        if (method_exists(Cache::store(), 'flush')) {
                            // For cache stores that support pattern deletion
                            foreach ($keys as $key) {
                                Cache::forget($key);
                            }
                        }

                        Log::info('All location cache cleared');
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Failed to clear all location cache', [
                            'error' => $e->getMessage()
                        ]);
                        return false;
                    }
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
