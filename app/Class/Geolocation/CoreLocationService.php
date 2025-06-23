<?php

namespace App\Class\Geolocation;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Core Location Service - Handle timezone and basic location utilities
 */
class CoreLocationService
{
    /**
     * Get current WITA time
     */
    public function getWitaTime(): Carbon
    {
        return Carbon::now('Asia/Makassar'); // WITA timezone (UTC+8)
    }

    /**
     * Format time to WITA string
     */
    public function formatWitaTime(?Carbon $time = null): string
    {
        $time = $time ?: $this->getWitaTime();
        return $time->format('H:i:s');
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
     * Get location accuracy status optimized for real-time (WITA timezone)
     */
    public function getLocationAccuracyStatus(array $location): array
    {
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
     * Check if location data is stale (optimized for real-time, WITA timezone)
     */
    public function isLocationStale(array $location, int $maxAgeSeconds = 60): bool
    {
        if (!$location['last_updated']) {
            return true;
        }

        $lastUpdated = Carbon::parse($location['last_updated'])->setTimezone('Asia/Makassar');
        $currentWita = $this->getWitaTime();

        return $lastUpdated->diffInSeconds($currentWita) > $maxAgeSeconds;
    }

    /**
     * Format location name from API response
     */
    public function formatLocationName(array $locationData): string
    {
        if (empty($locationData)) {
            return 'Alamat tidak tersedia';
        }

        $village = trim($locationData['village'] ?? '');
        $subdistrict = trim($locationData['subdistrict'] ?? '');
        $city = trim($locationData['city'] ?? '');
        $province = trim($locationData['province'] ?? '');

        $locationParts = [];

        if (!empty($village)) {
            $locationParts[] = $village;
        }

        if (!empty($subdistrict) && $subdistrict !== $village) {
            $locationParts[] = $subdistrict;
        }

        if (!empty($city) && $city !== $subdistrict) {
            $locationParts[] = $city;
        }

        if (!empty($province) && $province !== $city) {
            $locationParts[] = $province;
        }

        // Limit to maximum 4 parts to avoid too long address
        $locationParts = array_slice($locationParts, 0, 4);

        return !empty($locationParts) ? implode(', ', $locationParts) : 'Alamat tidak tersedia';
    }

    /**
     * Get location statistics for real-time monitoring (WITA timezone)
     */
    public function getLocationStats(array $userLocations): array
    {
        $stats = [
            'total_users' => count($userLocations),
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

        foreach ($userLocations as $location) {
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
     * Validate coordinates
     */
    public function validateCoordinates(float $lat, float $lng): bool
    {
        return !($lat == 0 || $lng == 0 || abs($lat) > 90 || abs($lng) > 180);
    }

    /**
     * Check if coordinates are close to default location
     */
    public function isNearDefaultLocation(float $lat, float $lng, float $tolerance = 0.0001): bool
    {
        $defaultLat = -4.0011471;
        $defaultLng = 122.5040029;

        return abs($lat - $defaultLat) < $tolerance && abs($lng - $defaultLng) < $tolerance;
    }
}
