<?php

namespace App\Class\Geolocation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tracking Manager - Handle all tracking session management and start locations
 */
class TrackingManager
{
    private CoreLocationService $coreService;
    private LocationRepository $locationRepository;
    private DistanceCalculator $distanceCalculator;

    public function __construct(
        CoreLocationService $coreService,
        LocationRepository $locationRepository,
        DistanceCalculator $distanceCalculator
    ) {
        $this->coreService = $coreService;
        $this->locationRepository = $locationRepository;
        $this->distanceCalculator = $distanceCalculator;
    }

    /**
     * Check if user is currently in tracking mode
     */
    public function isUserCurrentlyTracking(int $userId): bool
    {
        $trackingCacheKey = "user_tracking_state_{$userId}";
        return Cache::get($trackingCacheKey, false);
    }

    /**
     * Set tracking state dengan timestamp
     */
    public function setUserTrackingState(int $userId, bool $isTracking): void
    {
        $trackingCacheKey = "user_tracking_state_{$userId}";
        $trackingStartTimeKey = "user_tracking_start_time_{$userId}";

        if ($isTracking) {
            // Set tracking active dengan timestamp
            Cache::put($trackingCacheKey, true, now()->addHours(24));
            Cache::put($trackingStartTimeKey, $this->coreService->getWitaTime()->toISOString(), now()->addHours(24));

            Log::info('User tracking state enabled with timestamp', [
                'user_id' => $userId,
                'start_time' => $this->coreService->getWitaTime()->toISOString(),
                'wita_time' => $this->coreService->formatWitaTime()
            ]);
        } else {
            // Clear tracking state dan timestamp
            Cache::forget($trackingCacheKey);
            Cache::forget($trackingStartTimeKey);

            Log::info('User tracking state disabled', [
                'user_id' => $userId,
                'wita_time' => $this->coreService->formatWitaTime()
            ]);
        }
    }

    /**
     * Determine if current location should be set as start location
     */
    public function shouldSetAsStartLocation(int $userId, array $existingData, float $lat, float $lng): bool
    {
        // Check 1: Apakah user sedang dalam mode tracking?
        if (!$this->isUserCurrentlyTracking($userId)) {
            return false;
        }

        // Check 2: Apakah sudah ada start location untuk session yang aktif?
        if (isset($existingData['start_location']) &&
            isset($existingData['tracking_session_id']) &&
            !empty($existingData['start_location'])) {

            // Jika ada start location, cek apakah tracking session masih aktif
            $isStillTracking = $this->isUserCurrentlyTracking($userId);

            if ($isStillTracking) {
                Log::debug('Start location already exists for active session', [
                    'user_id' => $userId,
                    'existing_session' => $existingData['tracking_session_id'],
                    'existing_start' => $existingData['start_location']
                ]);
                return false;
            }
        }

        // Check 3: Apakah ini koordinat yang valid?
        if (!$this->coreService->validateCoordinates($lat, $lng)) {
            return false;
        }

        // Check 4: Apakah ini benar-benar lokasi baru (bukan default)?
        if ($this->coreService->isNearDefaultLocation($lat, $lng)) {
            Log::debug('Coordinates too close to default location, skipping start location set', [
                'user_id' => $userId,
                'lat' => $lat,
                'lng' => $lng
            ]);
            return false;
        }

        // Check 5: Cek timestamp tracking state - harus baru saja dimulai
        $trackingStateTimestamp = Cache::get("user_tracking_start_time_{$userId}");
        if ($trackingStateTimestamp) {
            $timeDiff = $this->coreService->getWitaTime()->diffInSeconds(\Carbon\Carbon::parse($trackingStateTimestamp));
            if ($timeDiff > 60) { // Lebih dari 1 menit
                Log::debug('Tracking started too long ago, not setting as start location', [
                    'user_id' => $userId,
                    'time_diff' => $timeDiff,
                    'tracking_start' => $trackingStateTimestamp
                ]);
                return false;
            }
        }

        Log::info('All checks passed - setting as start location', [
            'user_id' => $userId,
            'lat' => $lat,
            'lng' => $lng,
            'tracking_active' => true,
            'valid_coordinates' => true,
            'not_default_location' => true
        ]);

        return true;
    }

    /**
     * Set start location for tracking session
     */
    public function setStartLocation(int $userId, float $lat, float $lng, ?string $address = null): string
    {
        // Generate new tracking session ID
        $trackingSessionId = uniqid('track_', true);
        $trackingStartTime = $this->coreService->getWitaTime()->toISOString();

        // Set start location dengan koordinat PERTAMA
        $startLocation = [
            'latitude' => $lat,
            'longitude' => $lng,
            'timestamp' => $trackingStartTime,
            'timestamp_wita' => $this->coreService->formatWitaTime(),
            'session_id' => $trackingSessionId,
            'address' => $address ?? 'Mengambil alamat...'
        ];

        // Update location data with start location
        $cacheKey = "user_location_{$userId}";
        $existing = $this->locationRepository->getUserLocation($userId);

        $updated = array_merge($existing, [
            'start_location' => $startLocation,
            'tracking_session_id' => $trackingSessionId,
            'tracking_start_time' => $trackingStartTime
        ]);

        Cache::put($cacheKey, $updated, now()->addHours(1));

        Log::info('START LOCATION SET - New tracking session started', [
            'user_id' => $userId,
            'start_lat' => $lat,
            'start_lng' => $lng,
            'session_id' => $trackingSessionId,
            'start_time' => $trackingStartTime,
            'wita_time' => $this->coreService->formatWitaTime(),
            'reason' => 'First location after tracking enabled'
        ]);

        return $trackingSessionId;
    }

    /**
     * Get start location for current tracking session
     */
    public function getStartLocation(int $userId): ?array
    {
        $location = $this->locationRepository->getUserLocation($userId);
        return $location['start_location'] ?? null;
    }

    /**
     * Clear start location dan tracking session
     */
    public function clearStartLocation(int $userId): void
    {
        $cacheKey = "user_location_{$userId}";
        $existing = $this->locationRepository->getUserLocation($userId);

        if ($existing) {
            // Clear start location dan session ID
            $existing['start_location'] = null;
            $existing['tracking_session_id'] = null;
            $existing['tracking_start_time'] = null;

            Cache::put($cacheKey, $existing, now()->addHours(1));

            // Juga clear tracking state timestamp
            Cache::forget("user_tracking_start_time_{$userId}");

            Log::info('Start location and tracking session cleared completely', [
                'user_id' => $userId,
                'wita_time' => $this->coreService->formatWitaTime()
            ]);
        }
    }

    /**
     * Clear all tracking data including state
     */
    public function clearTrackingData(int $userId): bool
    {
        try {
            $cacheKeys = [
                "user_tracking_state_{$userId}",
                "user_tracking_start_time_{$userId}"
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Also clear start location
            $this->clearStartLocation($userId);

            Log::info('All tracking data cleared completely', [
                'user_id' => $userId,
                'cleared_keys' => $cacheKeys,
                'wita_time' => $this->coreService->formatWitaTime()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear tracking data', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'wita_time' => $this->coreService->formatWitaTime()
            ]);
            return false;
        }
    }

    /**
     * Get tracking session information
     */
    public function getTrackingSessionInfo(int $userId): array
    {
        $location = $this->locationRepository->getUserLocation($userId);
        $startLocation = $this->getStartLocation($userId);
        $isTracking = $this->isUserCurrentlyTracking($userId);

        $info = [
            'is_tracking' => $isTracking,
            'session_id' => $location['tracking_session_id'] ?? null,
            'has_start_location' => !is_null($startLocation),
            'start_location' => $startLocation,
            'current_location' => [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'address' => $location['city'] ?? null,
                'last_updated' => $location['last_updated'] ?? null
            ],
            'distance_from_start' => null,
            'tracking_start_time' => $location['tracking_start_time'] ?? null,
            'timezone' => 'WITA'
        ];

        // Calculate distance dari start jika ada
        if ($startLocation && $location['latitude'] && $location['longitude']) {
            $info['distance_from_start'] = $this->distanceCalculator->calculateDistanceFromStart($location, $startLocation);
        }

        return $info;
    }

    /**
     * Update location with start location handling
     */
    public function updateLocationWithTracking(int $userId, float $lat, float $lng): array
    {
        // Get session info before update untuk comparison
        $sessionInfoBefore = $this->getTrackingSessionInfo($userId);
        $hadStartLocationBefore = $sessionInfoBefore['has_start_location'];

        // Get existing data
        $existing = $this->locationRepository->getUserLocation($userId);

        // Check apakah ini benar-benar first location untuk tracking session baru
        $shouldSetStartLocation = $this->shouldSetAsStartLocation($userId, $existing, $lat, $lng);

        // Start location data dari tracking session atau baru
        $startLocation = $existing['start_location'] ?? null;
        $trackingSessionId = $existing['tracking_session_id'] ?? null;
        $trackingStartTime = $existing['tracking_start_time'] ?? null;

        if ($shouldSetStartLocation) {
            $trackingSessionId = $this->setStartLocation($userId, $lat, $lng, $existing['city'] ?? null);
            $startLocation = $this->getStartLocation($userId);
        }

        // Update immediate location
        $this->locationRepository->updateUserLocationImmediate($userId, $lat, $lng);

        // Get session info after update untuk check perubahan
        $sessionInfoAfter = $this->getTrackingSessionInfo($userId);
        $hasStartLocationAfter = $sessionInfoAfter['has_start_location'];

        // Detect if this was the first location update yang set start location
        $justSetStartLocation = !$hadStartLocationBefore && $hasStartLocationAfter && $this->isUserCurrentlyTracking($userId);

        return [
            'just_set_start_location' => $justSetStartLocation,
            'session_info' => $sessionInfoAfter,
            'tracking_session_id' => $trackingSessionId
        ];
    }

    /**
     * Get active tracking sessions count
     */
    public function getActiveTrackingCount(): int
    {
        // This would require a more complex implementation to track all active sessions
        // For now, return 0 as placeholder
        return 0;
    }

    /**
     * Validate tracking session
     */
    public function validateTrackingSession(int $userId, string $sessionId): bool
    {
        $location = $this->locationRepository->getUserLocation($userId);
        return ($location['tracking_session_id'] ?? null) === $sessionId;
    }

    /**
     * Get tracking duration
     */
    public function getTrackingDuration(int $userId): ?int
    {
        $trackingStartTime = Cache::get("user_tracking_start_time_{$userId}");

        if (!$trackingStartTime) {
            return null;
        }

        return $this->coreService->getWitaTime()->diffInSeconds(\Carbon\Carbon::parse($trackingStartTime));
    }

    /**
     * Check if tracking session is expired
     */
    public function isTrackingSessionExpired(int $userId, int $maxHours = 24): bool
    {
        $duration = $this->getTrackingDuration($userId);

        if (!$duration) {
            return false;
        }

        return $duration > ($maxHours * 3600);
    }

    /**
     * Auto cleanup expired tracking sessions
     */
    public function cleanupExpiredSessions(): int
    {
        // This would require tracking all active sessions
        // For now, return 0 as placeholder
        return 0;
    }
}
