<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MapsRoute extends Component
{
    // Map configuration properties
    public $lat = '';
    public $lng = '';
    public $latDefult = -4.0011471;
    public $lngDefult = 122.5040029;
    public $zoom = 12;
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

    // Route properties - Static destination (nanti bisa dari database)
    public $destinationLat = -3.943944;
    public $destinationLng = 122.1837957;
    public $destinationAddress = 'Wawoone, Kec. Wonggeduku, Kabupaten Konawe';
    public $showRoute = true;
    public $routeColor = '#DC2626'; // Red color for delivery route
    public $routeWeight = 6;

    // Real-time tracking properties
    public $isTracking = false;

    // ENHANCED: Start location tracking
    public $hasStartLocation = false;
    public $startLocationData = null;
    public $trackingSessionId = null;

    public function mount(
        $lat = null,
        $lng = null,
        $zoom = 12,
        $class = '',
        $style = null,
        $address = null,
        $badgeTopLeft = null,
        $badgeTopRight = null,
        $badgeBottomLeft = null,
        $badgeBottomRight = null,
        // Route parameters
        $destinationLat = null,
        $destinationLng = null,
        $destinationAddress = null,
        $showRoute = true,
        $routeColor = '#DC2626',
        $routeWeight = 6
    ) {
        // Override destination jika diberikan parameter
        if ($destinationLat !== null && $destinationLng !== null) {
            $this->destinationLat = $destinationLat;
            $this->destinationLng = $destinationLng;
        }
        if ($destinationAddress !== null) {
            $this->destinationAddress = $destinationAddress;
        }

        $this->showRoute = $showRoute;
        $this->routeColor = $routeColor;
        $this->routeWeight = $routeWeight;

        // TIDAK menggunakan parameter lat/lng dari mount
        // Selalu ambil dari GeolocationService untuk real-time tracking
        $this->zoom = $zoom;
        $this->class = $class;
        $this->style = $style;
        $this->mapId = 'map-route-' . uniqid();

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
     * ENHANCED: Include start location data
     */
    protected function initializeLocationData(): void
    {
        if (!Auth::id()) return;

        try {
            $location = app('geolocation')->getUserLocation(Auth::id());
            $trackingSession = app('geolocation')->getTrackingSessionInfo(Auth::id());

            // Set current location
            if ($location['latitude'] && $location['longitude']) {
                $this->lat = $location['latitude'];
                $this->lng = $location['longitude'];
                $this->address = $location['city'] ?? null;
                $this->isActualLocation = true;

                // Set weather and time data - FIX WITA timezone
                $this->weatherData = $location['weather_data'] ?? $location['weather'] ?? null;
                $this->currentTime = $this->formatWitaTime($location['last_updated']);
            } else {
                // Use default location
                $this->lat = $this->latDefult;
                $this->lng = $this->lngDefult;
                $this->address = 'Lokasi Default - Kendari';
                $this->isActualLocation = false;
            }

            // ENHANCED: Set start location data
            $this->isTracking = $trackingSession['is_tracking'];
            $this->hasStartLocation = $trackingSession['has_start_location'];
            $this->startLocationData = $trackingSession['start_location'];
            $this->trackingSessionId = $trackingSession['session_id'];

            Log::info('MapsRoute: Location data initialized', [
                'user_id' => Auth::id(),
                'has_actual_location' => $this->isActualLocation,
                'is_tracking' => $this->isTracking,
                'has_start_location' => $this->hasStartLocation,
                'session_id' => $this->trackingSessionId
            ]);

        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error initializing location data', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'initializeLocationData'
            ]);
        }
    }

    /**
     * Method untuk update lokasi real-time
     * ENHANCED: Include start location handling
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
                $this->isTracking = false;
                return;
            }

            // Ambil data lokasi fresh dari geolocation service
            $location = app('geolocation')->getUserLocation(Auth::id());
            $trackingSession = app('geolocation')->getTrackingSessionInfo(Auth::id());

            // Cek apakah ada lokasi aktual
            $hasLocation = $location['latitude'] && $location['longitude'] && $location['last_updated'];

            if ($hasLocation) {
                // Update coordinate properties
                $this->lat = $location['latitude'];
                $this->lng = $location['longitude'];
                $this->address = $location['city'] ?? null;
                $this->isActualLocation = true;

                // Update weather and time data untuk badges
                $this->weatherData = $location['weather_data'] ?? $location['weather'] ?? null;
                $this->currentTime = $this->formatWitaTime($location['last_updated']);

                // ENHANCED: Update start location tracking state
                $this->isTracking = $trackingSession['is_tracking'];
                $this->hasStartLocation = $trackingSession['has_start_location'];
                $this->startLocationData = $trackingSession['start_location'];
                $this->trackingSessionId = $trackingSession['session_id'];

                // Set dynamic badges dari data geolocation
                $this->badgeTopLeft = $this->currentTime; // Waktu update terakhir
                $this->badgeTopRight = $this->weatherData ?
                    ($this->weatherData['condition'] ?? $this->weatherData['description'] ?? 'Tidak ada data') . ' ' . round($this->weatherData['temperature'] ?? 0) . '°C' : null;

                // ENHANCED: Dispatch dengan start location info
                $this->dispatch('update-route-marker-position',
                    mapId: $this->mapId,
                    lat: $this->lat,
                    lng: $this->lng,
                    address: $this->address,
                    isActual: $this->isActualLocation,
                    weatherData: $this->weatherData,
                    currentTime: $this->currentTime,
                    destinationLat: $this->destinationLat,
                    destinationLng: $this->destinationLng,
                    destinationAddress: $this->destinationAddress,
                    // Start location data
                    hasStartLocation: $this->hasStartLocation,
                    startLocationData: $this->startLocationData,
                    trackingSessionId: $this->trackingSessionId
                );
            }

        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error updating location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'updateMapLocation',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Listen untuk location-updated event dari GeolocationButton
     * ENHANCED: Handle start location events
     */
    #[On('location-updated')]
    public function handleLocationUpdate($eventData = null): void
    {
        try {
            // Check if this is start location event
            $isStartLocation = $eventData['is_start_location'] ?? false;
            $trackingSession = $eventData['tracking_session'] ?? null;

            if ($isStartLocation && $trackingSession) {
                Log::info('MapsRoute: Start location event received', [
                    'user_id' => Auth::id(),
                    'session_id' => $trackingSession['session_id'],
                    'has_start_location' => $trackingSession['has_start_location']
                ]);
            }

            // Trigger update map location
            $this->updateMapLocation();

        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error handling location update event', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'handleLocationUpdate'
            ]);
        }
    }

    /**
     * Listen untuk location-cleared event
     * ENHANCED: Clear start location data
     */
    #[On('location-cleared')]
    public function handleLocationCleared(): void
    {
        try {
            // Reset ke lokasi default
            $this->lat = $this->latDefult;
            $this->lng = $this->lngDefult;
            $this->isActualLocation = false;
            $this->address = null;
            $this->weatherData = null;
            $this->currentTime = null;

            // ENHANCED: Clear start location data
            $this->isTracking = false;
            $this->hasStartLocation = false;
            $this->startLocationData = null;
            $this->trackingSessionId = null;

            // Reset badges
            $this->badgeTopLeft = null;
            $this->badgeTopRight = null;

            // Update marker ke posisi default dengan mapId yang valid
            $this->dispatch('update-route-marker-position',
                mapId: $this->mapId,
                lat: $this->lat,
                lng: $this->lng,
                address: 'Lokasi Default - Kendari',
                isActual: $this->isActualLocation,
                weatherData: null,
                currentTime: null,
                destinationLat: $this->destinationLat,
                destinationLng: $this->destinationLng,
                destinationAddress: $this->destinationAddress,
                // Clear start location data
                hasStartLocation: false,
                startLocationData: null,
                trackingSessionId: null
            );

            Log::info('MapsRoute: Location and start location cleared', [
                'user_id' => Auth::id()
            ]);

        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error handling location cleared', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'handleLocationCleared'
            ]);
        }
    }

    /**
     * Method untuk center map ke lokasi user dengan zoom maksimal
     */
    public function goToMyLocation(): void
    {
        try {
            if ($this->isActualLocation && $this->lat && $this->lng) {
                $this->dispatch('center-map-to-location',
                    mapId: $this->mapId,
                    lat: $this->lat,
                    lng: $this->lng,
                    zoom: 15 // Max zoom
                );
            }
        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error going to my location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'goToMyLocation'
            ]);
        }
    }

    /**
     * ENHANCED: Method untuk center map ke start location
     */
    public function goToStartLocation(): void
    {
        try {
            if ($this->hasStartLocation && $this->startLocationData) {
                $this->dispatch('center-map-to-location',
                    mapId: $this->mapId,
                    lat: $this->startLocationData['latitude'],
                    lng: $this->startLocationData['longitude'],
                    zoom: 15
                );
            }
        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error going to start location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'goToStartLocation'
            ]);
        }
    }

    /**
     * Method untuk center map ke tujuan
     */
    public function goToDestination(): void
    {
        try {
            $this->dispatch('center-map-to-location',
                mapId: $this->mapId,
                lat: $this->destinationLat,
                lng: $this->destinationLng,
                zoom: 15
            );
        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error going to destination', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'goToDestination'
            ]);
        }
    }

    /**
     * Refresh location data manually
     * ENHANCED: Refresh start location data
     */
    public function refreshLocationData(): void
    {
        try {
            $this->initializeLocationData();

            if ($this->isActualLocation) {
                $this->dispatch('update-route-marker-position',
                    mapId: $this->mapId,
                    lat: $this->lat,
                    lng: $this->lng,
                    address: $this->address,
                    isActual: $this->isActualLocation,
                    weatherData: $this->weatherData,
                    currentTime: $this->currentTime,
                    destinationLat: $this->destinationLat,
                    destinationLng: $this->destinationLng,
                    destinationAddress: $this->destinationAddress,
                    // Include start location data
                    hasStartLocation: $this->hasStartLocation,
                    startLocationData: $this->startLocationData,
                    trackingSessionId: $this->trackingSessionId
                );
            }

        } catch (\Exception $e) {
            Log::error('MapsRoute Component: Error refreshing location data', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => 'refreshLocationData'
            ]);
        }
    }

    /**
     * Calculate distance between current location and destination
     */
    public function calculateDistance(): ?float
    {
        if (!$this->lat || !$this->lng || !$this->destinationLat || !$this->destinationLng) {
            return null;
        }

        return app('geolocation')->calculateDistance(
            $this->lat,
            $this->lng,
            $this->destinationLat,
            $this->destinationLng
        );
    }

    /**
     * ENHANCED: Calculate distance from start location to destination
     */
    public function calculateDistanceFromStart(): ?float
    {
        if (!$this->hasStartLocation || !$this->startLocationData) {
            return null;
        }

        return app('geolocation')->calculateDistance(
            $this->startLocationData['latitude'],
            $this->startLocationData['longitude'],
            $this->destinationLat,
            $this->destinationLng
        );
    }

    /**
     * ENHANCED: Calculate distance from start to current location
     */
    public function calculateDistanceFromStartToCurrent(): ?float
    {
        if (!$this->hasStartLocation || !$this->startLocationData || !$this->lat || !$this->lng) {
            return null;
        }

        return app('geolocation')->calculateDistance(
            $this->startLocationData['latitude'],
            $this->startLocationData['longitude'],
            $this->lat,
            $this->lng
        );
    }

    /**
     * Get distance text for display
     */
    public function getDistanceText(): ?string
    {
        $distance = $this->calculateDistance();
        if (!$distance) return null;

        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        }

        return round($distance, 1) . ' km';
    }

    /**
     * ENHANCED: Get distance from start text
     */
    public function getDistanceFromStartText(): ?string
    {
        $distance = $this->calculateDistanceFromStartToCurrent();
        if (!$distance) return null;

        if ($distance < 1) {
            return round($distance * 1000) . ' m dari start';
        }

        return round($distance, 1) . ' km dari start';
    }

    /**
     * Check if user is near destination
     */
    public function isNearDestination(float $radiusKm = 1.0): bool
    {
        if (!Auth::id() || !$this->destinationLat || !$this->destinationLng) {
            return false;
        }

        return app('geolocation')->isUserNearLocation(
            Auth::id(),
            $this->destinationLat,
            $this->destinationLng,
            $radiusKm
        );
    }

    /**
     * Handle JavaScript errors dari frontend (via Livewire event)
     */
    #[On('log-js-error')]
    public function logJavaScriptError($data)
    {
        try {
            Log::error('MapsRoute Component: JavaScript Error', [
                'user_id' => Auth::id(),
                'component' => $data['component'] ?? 'MapsRoute',
                'function' => $data['function'] ?? 'Unknown',
                'error' => $data['error'] ?? 'No error message',
                'mapId' => $data['mapId'] ?? null,
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'ip_address' => request()->ip(),
                'url' => request()->fullUrl(),
                'timestamp' => now(),
                'source' => 'javascript'
            ]);

        } catch (\Exception $e) {
            // Fallback jika logging gagal
            Log::critical('MapsRoute Component: Failed to log JavaScript error', [
                'original_error' => $data ?? 'No data',
                'logging_error' => $e->getMessage()
            ]);
        }
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
     * Check if tracking is active
     */
    public function isTrackingActive(): bool
    {
        if (!Auth::id()) return false;

        $trackingCacheKey = "user_tracking_state_" . Auth::id();
        return Cache::get($trackingCacheKey, false);
    }

    /**
     * ENHANCED: Get route information with start location
     */
    public function getRouteInfo(): array
    {
        return [
            'origin' => [
                'lat' => $this->lat,
                'lng' => $this->lng,
                'address' => $this->address ?? 'Lokasi tidak diketahui'
            ],
            'destination' => [
                'lat' => $this->destinationLat,
                'lng' => $this->destinationLng,
                'address' => $this->destinationAddress
            ],
            'start_location' => $this->hasStartLocation ? [
                'lat' => $this->startLocationData['latitude'],
                'lng' => $this->startLocationData['longitude'],
                'timestamp' => $this->startLocationData['timestamp'] ?? null,
                'session_id' => $this->trackingSessionId
            ] : null,
            'distance' => $this->getDistanceText(),
            'distance_from_start' => $this->getDistanceFromStartText(),
            'isNearDestination' => $this->isNearDestination(),
            'routeColor' => $this->routeColor,
            'routeWeight' => $this->routeWeight,
            'tracking_info' => [
                'is_tracking' => $this->isTracking,
                'has_start_location' => $this->hasStartLocation,
                'session_id' => $this->trackingSessionId
            ]
        ];
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.components.maps-route');
    }
}
