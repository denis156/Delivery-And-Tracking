<?php

namespace App\Class\Geolocation;

/**
 * Distance Calculator - Handle all distance calculations using Haversine formula
 */
class DistanceCalculator
{
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
     * Calculate distance from start location to current location
     */
    public function calculateDistanceFromStart(array $currentLocation, ?array $startLocation): ?float
    {
        // Return null if start location is null or empty
        if (!$startLocation || !isset($startLocation['latitude']) || !isset($startLocation['longitude'])) {
            return null;
        }

        // Return null if current location is invalid
        if (!$currentLocation['latitude'] || !$currentLocation['longitude']) {
            return null;
        }

        return $this->calculateDistance(
            $startLocation['latitude'],
            $startLocation['longitude'],
            $currentLocation['latitude'],
            $currentLocation['longitude']
        );
    }

    /**
     * Calculate distance for specific user to target location
     */
    public function calculateDistanceToTarget(array $userLocation, float $targetLat, float $targetLng): ?float
    {
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
     * Check if user is near a location with tolerance
     */
    public function isNearLocation(array $userLocation, float $targetLat, float $targetLng, float $radiusKm = 1.0): bool
    {
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
     * Format distance for display
     */
    public function formatDistance(?float $distance): ?string
    {
        if (!$distance) return null;

        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        }

        return round($distance, 1) . ' km';
    }

    /**
     * Format distance from start for display
     */
    public function formatDistanceFromStart(?float $distance): ?string
    {
        if (!$distance) return null;

        if ($distance < 1) {
            return round($distance * 1000) . ' m dari start';
        }

        return round($distance, 1) . ' km dari start';
    }

    /**
     * Get multiple distances from one point to multiple targets
     */
    public function calculateMultipleDistances(array $fromLocation, array $targets): array
    {
        $distances = [];

        if (!$fromLocation['latitude'] || !$fromLocation['longitude']) {
            return $distances;
        }

        foreach ($targets as $key => $target) {
            if (isset($target['latitude']) && isset($target['longitude'])) {
                $distances[$key] = $this->calculateDistance(
                    $fromLocation['latitude'],
                    $fromLocation['longitude'],
                    $target['latitude'],
                    $target['longitude']
                );
            }
        }

        return $distances;
    }

    /**
     * Find nearest location from array of locations
     */
    public function findNearest(array $fromLocation, array $locations): ?array
    {
        if (!$fromLocation['latitude'] || !$fromLocation['longitude'] || empty($locations)) {
            return null;
        }

        $nearest = null;
        $shortestDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            if (!isset($location['latitude']) || !isset($location['longitude'])) {
                continue;
            }

            $distance = $this->calculateDistance(
                $fromLocation['latitude'],
                $fromLocation['longitude'],
                $location['latitude'],
                $location['longitude']
            );

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $nearest = array_merge($location, ['distance' => $distance]);
            }
        }

        return $nearest;
    }

    /**
     * Calculate bearing between two points
     */
    public function calculateBearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLng = deg2rad($lng2 - $lng1);

        $y = sin($deltaLng) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($deltaLng);

        $bearing = atan2($y, $x);
        $bearing = rad2deg($bearing);

        return fmod($bearing + 360, 360);
    }

    /**
     * Get compass direction from bearing
     */
    public function getCompassDirection(float $bearing): string
    {
        $directions = [
            'Utara', 'Timur Laut', 'Timur', 'Tenggara',
            'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'
        ];

        $index = round($bearing / 45) % 8;
        return $directions[$index];
    }

    /**
     * Calculate estimated travel time (rough estimate based on average speed)
     */
    public function estimateTravelTime(float $distance, float $averageSpeedKmh = 50): array
    {
        $hours = $distance / $averageSpeedKmh;
        $totalMinutes = $hours * 60;

        $hoursInt = floor($totalMinutes / 60);
        $minutesInt = round($totalMinutes % 60);

        return [
            'hours' => $hoursInt,
            'minutes' => $minutesInt,
            'total_minutes' => round($totalMinutes),
            'formatted' => $hoursInt > 0 ? "{$hoursInt}j {$minutesInt}m" : "{$minutesInt}m"
        ];
    }
}
