<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Maps extends Component
{
    // Map configuration properties
    public $lat = '';
    public $lng = '';
    public $latDefult = -4.0011471;
    public $lngDefult = 122.5040029;
    public $zoom = 20;
    public $mapId;
    public $class = '';
    public $style = '';

    // Component state
    public $mapReady = false;
    public $isActualLocation = false;

    // Badge properties
    public $address = null;
    public $badgeTopLeft = null;
    public $badgeTopRight = null;
    public $badgeBottomLeft = null;
    public $badgeBottomRight = null;

    // Weather and time data from geolocation service
    public $weatherData = null;
    public $currentTime = null;

    // Real-time tracking properties
    public $isTracking = false;

    public function mount(
        $lat = null,
        $lng = null,
        $zoom = 20,
        $class = '',
        $style = null,
        $address = null,
        $badgeTopLeft = null,
        $badgeTopRight = null,
        $badgeBottomLeft = null,
        $badgeBottomRight = null
    )
    {
        // Cek apakah lat dan lng diberikan (bukan null dan bukan kosong)
        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            $this->lat = $lat;
            $this->lng = $lng;
            $this->isActualLocation = true; // Lokasi aktual
        } else {
            $this->lat = $this->latDefult;
            $this->lng = $this->lngDefult;
            $this->isActualLocation = false; // Lokasi default
        }

        $this->zoom = $zoom;
        $this->class = $class;
        $this->style = $style;
        $this->mapId = 'map-' . uniqid();
        $this->address = $address;

        // Set badge properties
        $this->badgeTopLeft = $badgeTopLeft;
        $this->badgeTopRight = $badgeTopRight;
        $this->badgeBottomLeft = $badgeBottomLeft;
        $this->badgeBottomRight = $badgeBottomRight;

        // Initialize dengan data geolocation yang ada
        $this->initializeLocationData();
    }

    /**
     * Initialize location data dari geolocation service
     */
    protected function initializeLocationData(): void
    {
        if (!Auth::id()) return;

        try {
            $location = app('geolocation')->getUserLocation(Auth::id());

            if ($location['latitude'] && $location['longitude']) {
                $this->lat = $location['latitude'];
                $this->lng = $location['longitude'];
                $this->address = $location['city'] ?? null;
                $this->isActualLocation = true;

                // Set weather and time data - FIX WITA timezone
                $this->weatherData = $location['weather_data'] ?? $location['weather'] ?? null;
                $this->currentTime = $this->formatWitaTime($location['last_updated']);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Maps: Error initializing location data', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Method untuk update lokasi real-time
     * Dipanggil oleh wire:poll.visible ketika tracking aktif
     * HANYA jalan jika live tracking aktif
     */
    public function updateMapLocation(): void
    {
        if (!Auth::id()) return;

        try {
            // Cek dulu apakah user sedang tracking atau tidak
            $trackingCacheKey = "user_tracking_state_" . Auth::id();
            $isUserTracking = Cache::get($trackingCacheKey, false);

            // Jika tidak tracking, STOP polling - jangan lakukan apa-apa
            if (!$isUserTracking) {
                return;
            }

            // Ambil data lokasi fresh dari geolocation service
            $location = app('geolocation')->getUserLocation(Auth::id());

            // Cek apakah ada lokasi aktual
            $hasLocation = $location['latitude'] && $location['longitude'] && $location['last_updated'];

            if ($hasLocation) {
                // Update coordinate properties
                $this->lat = $location['latitude'];
                $this->lng = $location['longitude'];
                $this->address = $location['city'] ?? null;
                $this->isActualLocation = true;

                // Update weather and time data untuk badges - FIX key mapping
                $this->weatherData = $location['weather_data'] ?? $location['weather'] ?? null;

                // FIX: Gunakan helper method untuk WITA time
                $this->currentTime = $this->formatWitaTime($location['last_updated']);

                // Set dynamic badges dari data geolocation - FIX struktur data
                $this->badgeTopLeft = $this->currentTime; // Waktu update terakhir
                $this->badgeTopRight = $this->weatherData ?
                    ($this->weatherData['condition'] ?? $this->weatherData['description'] ?? 'Tidak ada data') . ' ' . round($this->weatherData['temperature'] ?? 0) . '°C' : null;

                // Dispatch browser event untuk update marker position dengan mapId yang valid
                $this->dispatch('update-marker-position',
                    mapId: $this->mapId,
                    lat: $this->lat,
                    lng: $this->lng,
                    address: $this->address,
                    isActual: $this->isActualLocation,
                    weatherData: $this->weatherData,
                    currentTime: $this->currentTime
                );
            }
            // Note: Tidak ada fallback ke default saat tracking aktif
            // Jika tracking aktif tapi tidak ada data, biarkan marker di posisi terakhir

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Maps: Error updating location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Listen untuk location-updated event dari GeolocationButton
     */
    #[On('location-updated')]
    public function handleLocationUpdate(): void
    {
        try {
            // Trigger update map location
            $this->updateMapLocation();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Maps: Error handling location update event', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Method untuk center map ke lokasi user dengan zoom maksimal
     */
    public function goToMyLocation(): void
    {
        if ($this->isActualLocation && $this->lat && $this->lng) {
            $this->dispatch('center-map-to-location',
                mapId: $this->mapId,
                lat: $this->lat,
                lng: $this->lng,
                zoom: 20 // Max zoom
            );
        }
    }

    /**
     * Listen untuk location-cleared event
     */
    #[On('location-cleared')]
    public function handleLocationCleared(): void
    {
        // Reset ke lokasi default
        $this->lat = $this->latDefult;
        $this->lng = $this->lngDefult;
        $this->isActualLocation = false;
        $this->address = null;
        $this->weatherData = null;
        $this->currentTime = null;

        // Reset badges
        $this->badgeTopLeft = null;
        $this->badgeTopRight = null;

        // Update marker ke posisi default dengan mapId yang valid
        $this->dispatch('update-marker-position',
            mapId: $this->mapId,
            lat: $this->lat,
            lng: $this->lng,
            address: 'Lokasi Default - Kendari',
            isActual: $this->isActualLocation,
            weatherData: null,
            currentTime: null
        );
    }

    /**
     * Refresh location data manually
     */
    public function refreshLocationData(): void
    {
        try {
            $this->initializeLocationData();

            if ($this->isActualLocation) {
                $this->dispatch('update-marker-position',
                    mapId: $this->mapId,
                    lat: $this->lat,
                    lng: $this->lng,
                    address: $this->address,
                    isActual: $this->isActualLocation,
                    weatherData: $this->weatherData,
                    currentTime: $this->currentTime
                );
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Maps: Error refreshing location data', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Set custom weather data (untuk testing atau data manual)
     */
    public function setWeatherData($weatherData): void
    {
        $this->weatherData = $weatherData;
    }

    /**
     * Update time badge manually
     */
    public function updateTimeBadge(): void
    {
        $this->currentTime = $this->getCurrentWitaTime();
    }

    /**
     * Map initialization callback
     */
    public function mapInitialized()
    {
        $this->mapReady = true;
        $this->dispatch('map-ready', ['mapId' => $this->mapId]);
    }

    // ========== UTILITY METHODS ==========

    /**
     * Format timestamp to WITA time
     */
    private function formatWitaTime(?string $timestamp = null): ?string
    {
        if (!$timestamp) return null;

        return Carbon::parse($timestamp)->setTimezone('Asia/Makassar')->format('H:i') . ' WITA';
    }

    /**
     * Get current WITA time
     */
    private function getCurrentWitaTime(): string
    {
        return Carbon::now('Asia/Makassar')->format('H:i') . ' WITA';
    }

    /**
     * Get location status
     */
    public function getLocationStatus(): string
    {
        return $this->isActualLocation ? 'actual' : 'default';
    }

    /**
     * Get location status text
     */
    public function getLocationStatusText(): string
    {
        return $this->isActualLocation ? 'Lokasi Aktual' : 'Lokasi Default';
    }

    /**
     * Get location status CSS class
     */
    public function getLocationStatusClass(): string
    {
        return $this->isActualLocation ? 'status-success' : 'status-warning';
    }

    /**
     * Get location text color class
     */
    public function getLocationTextClass(): string
    {
        return $this->isActualLocation ? 'text-success' : 'text-warning';
    }

    /**
     * Check if location is recent (within last 5 minutes)
     */
    public function isLocationRecent(): bool
    {
        if (!$this->currentTime || !$this->isActualLocation) {
            return false;
        }

        if (!Auth::id()) return false;

        $location = app('geolocation')->getUserLocation(Auth::id());

        if (!$location['last_updated']) {
            return false;
        }

        $lastUpdate = Carbon::parse($location['last_updated']);
        return $lastUpdate->diffInMinutes(Carbon::now()) <= 5;
    }

    /**
     * Get formatted coordinate string
     */
    public function getCoordinateString(): string
    {
        if (!$this->lat || !$this->lng) {
            return 'Koordinat tidak tersedia';
        }

        return number_format($this->lat, 6) . ', ' . number_format($this->lng, 6);
    }

    /**
     * Get weather description for badge
     */
    public function getWeatherBadgeText(): ?string
    {
        if (!$this->weatherData) {
            return null;
        }

        $description = $this->weatherData['description'] ?? '';
        $temperature = round($this->weatherData['temperature'] ?? 0);

        return $description . ' ' . $temperature . '°C';
    }

    /**
     * Check if tracking is active
     */
    public function isTrackingActive(): bool
    {
        if (!Auth::id()) return false;

        $trackingCacheKey = "user_tracking_state_" . Auth::id();
        return Cache::get($trackingCacheKey, false);
    }

    /**
     * Get user location data
     */
    public function getUserLocationData(): array
    {
        if (!Auth::id()) return [];

        return app('geolocation')->getUserLocation(Auth::id());
    }

    /**
     * Get map center coordinates as array
     */
    public function getMapCenter(): array
    {
        return [
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'zoom' => (int) $this->zoom
        ];
    }

    /**
     * Set map center programmatically
     */
    public function setMapCenter($lat, $lng, $zoom = null): void
    {
        $this->lat = $lat;
        $this->lng = $lng;

        if ($zoom !== null) {
            $this->zoom = $zoom;
        }

        // Dispatch event untuk update map view
        $this->dispatch('center-map-to-location',
            mapId: $this->mapId,
            lat: $this->lat,
            lng: $this->lng,
            zoom: $this->zoom
        );
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.components.maps');
    }
}
