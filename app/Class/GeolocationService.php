<?php

namespace App\Class;

use App\Class\Geolocation\CoreLocationService;
use App\Class\Geolocation\DistanceCalculator;
use App\Class\Geolocation\LocationRepository;
use App\Class\Geolocation\TrackingManager;
use App\Class\Geolocation\WeatherService;

/**
 * Geolocation Service - Main facade for all geolocation functionality
 *
 * This is the main entry point that coordinates all geolocation services.
 * It delegates functionality to specialized service classes.
 */
class GeolocationService
{
    private CoreLocationService $coreService;
    private DistanceCalculator $distanceCalculator;
    private LocationRepository $locationRepository;
    private TrackingManager $trackingManager;
    private WeatherService $weatherService;

    public function __construct()
    {
        $this->coreService = new CoreLocationService();
        $this->locationRepository = new LocationRepository($this->coreService);
        $this->distanceCalculator = new DistanceCalculator();
        $this->trackingManager = new TrackingManager(
            $this->coreService,
            $this->locationRepository,
            $this->distanceCalculator
        );
        $this->weatherService = new WeatherService($this->coreService, $this->locationRepository);
    }

    // ========== USER LOCATION METHODS ==========

    /**
     * Get user location from cache or default to Kendari
     */
    public function getUserLocation(int $userId): array
    {
        return $this->locationRepository->getUserLocation($userId);
    }

    /**
     * Update user location IMMEDIATELY for real-time tracking
     */
    public function updateUserLocationImmediate(int $userId, float $lat, float $lng): void
    {
        // Update location with tracking session handling
        $result = $this->trackingManager->updateLocationWithTracking($userId, $lat, $lng);

        // Dispatch background job for address lookup
        \App\Jobs\AddressLookupJob::dispatch($userId, $lat, $lng)->afterResponse();
    }

    /**
     * Legacy update method for backward compatibility
     */
    public function updateUserLocation(int $userId, float $lat, float $lng, ?string $address = null): void
    {
        $this->updateUserLocationImmediate($userId, $lat, $lng);
    }

    /**
     * Clear user location
     */
    public function clearUserLocation(int $userId): bool
    {
        // Clear tracking data first
        $this->trackingManager->clearTrackingData($userId);

        // Then clear location data
        return $this->locationRepository->clearUserLocation($userId);
    }

    /**
     * Get multiple users locations
     */
    public function getMultipleUserLocations(array $userIds): array
    {
        return $this->locationRepository->getMultipleUserLocations($userIds);
    }

    // ========== TRACKING METHODS ==========

    /**
     * Set tracking state
     */
    public function setUserTrackingState(int $userId, bool $isTracking): void
    {
        $this->trackingManager->setUserTrackingState($userId, $isTracking);
    }

    /**
     * Get start location
     */
    public function getStartLocation(int $userId): ?array
    {
        return $this->trackingManager->getStartLocation($userId);
    }

    /**
     * Clear start location
     */
    public function clearStartLocation(int $userId): void
    {
        $this->trackingManager->clearStartLocation($userId);
    }

    /**
     * Get tracking session info
     */
    public function getTrackingSessionInfo(int $userId): array
    {
        return $this->trackingManager->getTrackingSessionInfo($userId);
    }

    // ========== DISTANCE METHODS ==========

    /**
     * Calculate distance between coordinates
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return $this->distanceCalculator->calculateDistance($lat1, $lng1, $lat2, $lng2);
    }

    /**
     * Calculate distance from start location
     */
    public function calculateDistanceFromStart(int $userId): ?float
    {
        $currentLocation = $this->getUserLocation($userId);
        $startLocation = $this->getStartLocation($userId);

        // Return null if no start location exists
        if (!$startLocation) {
            return null;
        }

        return $this->distanceCalculator->calculateDistanceFromStart($currentLocation, $startLocation);
    }

    /**
     * Calculate distance for specific user to target
     */
    public function calculateDistanceForUser(int $userId, float $targetLat, float $targetLng): ?float
    {
        $userLocation = $this->getUserLocation($userId);
        return $this->distanceCalculator->calculateDistanceToTarget($userLocation, $targetLat, $targetLng);
    }

    /**
     * Check if user is near location
     */
    public function isUserNearLocation(int $userId, float $targetLat, float $targetLng, float $radiusKm = 1.0): bool
    {
        $userLocation = $this->getUserLocation($userId);
        return $this->distanceCalculator->isNearLocation($userLocation, $targetLat, $targetLng, $radiusKm);
    }

    // ========== WEATHER METHODS ==========

    /**
     * Get weather info
     */
    public function getWeatherInfo(?int $userId = null): array
    {
        return $this->weatherService->getWeatherInfo($userId);
    }

    // ========== STATUS AND UTILITY METHODS ==========

    /**
     * Get location accuracy status
     */
    public function getLocationAccuracyStatus(int $userId): array
    {
        $location = $this->getUserLocation($userId);
        return $this->coreService->getLocationAccuracyStatus($location);
    }

    /**
     * Check if location is stale
     */
    public function isLocationStale(int $userId, int $maxAgeSeconds = 60): bool
    {
        $location = $this->getUserLocation($userId);
        return $this->coreService->isLocationStale($location, $maxAgeSeconds);
    }

    /**
     * Get formatted WITA time
     */
    public function getFormattedWitaTime(?string $timestamp = null): string
    {
        return $this->coreService->getFormattedWitaTime($timestamp);
    }

    /**
     * Get WITA date and time
     */
    public function getWitaDateTime(): array
    {
        return $this->coreService->getWitaDateTime();
    }

    /**
     * Get location statistics
     */
    public function getLocationStats(array $userIds): array
    {
        $locations = $this->getMultipleUserLocations($userIds);
        return $this->coreService->getLocationStats($locations);
    }

    // ========== DIRECT ACCESS TO SERVICES (if needed) ==========

    /**
     * Get core service instance
     */
    public function getCoreService(): CoreLocationService
    {
        return $this->coreService;
    }

    /**
     * Get distance calculator instance
     */
    public function getDistanceCalculator(): DistanceCalculator
    {
        return $this->distanceCalculator;
    }

    /**
     * Get location repository instance
     */
    public function getLocationRepository(): LocationRepository
    {
        return $this->locationRepository;
    }

    /**
     * Get tracking manager instance
     */
    public function getTrackingManager(): TrackingManager
    {
        return $this->trackingManager;
    }

    /**
     * Get weather service instance
     */
    public function getWeatherService(): WeatherService
    {
        return $this->weatherService;
    }
}
