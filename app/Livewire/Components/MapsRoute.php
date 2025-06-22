<?php

namespace App\Livewire\Components;

use Exception;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MapsRoute extends Component
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

    // Current location properties (NOT reactive anymore - controlled by parent)
    public $currentUserLat;
    public $currentUserLng;
    public $currentUserAddress;

    // Route properties
    public $destinationLat = null;
    public $destinationLng = null;
    public $destinationAddress = null;
    public $showRoute = false;
    public $routeColor = '#3B82F6'; // Default blue color
    public $routeWeight = 5;

    // Component state
    public $mapReady = false;
    public $isActualLocation = false;
    public $routeReady = false;
    public $useRealTimeTracking = false;

    // Badge properties
    public $address = null;
    public $badgeTopLeft = null;
    public $badgeTopRight = null;
    public $badgeBottomLeft = null;
    public $badgeBottomRight = null;

    // Event listeners for real-time updates from parent only
    protected $listeners = [
        'map-refresh-requested' => 'refreshMapData'
    ];

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
        $badgeBottomRight = null,
        // Route parameters
        $destinationLat = null,
        $destinationLng = null,
        $destinationAddress = null,
        $showRoute = false,
        $routeColor = '#3B82F6',
        $routeWeight = 5,
        $useRealTimeTracking = false
    ) {
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

        // Set route properties
        $this->destinationLat = $destinationLat;
        $this->destinationLng = $destinationLng;
        $this->destinationAddress = $destinationAddress;
        $this->showRoute = $showRoute;
        $this->routeColor = $routeColor;
        $this->routeWeight = $routeWeight;
        $this->useRealTimeTracking = $useRealTimeTracking;

        // Auto enable route if destination is provided
        if ($destinationLat && $destinationLng) {
            $this->showRoute = true;
        }

        // Initialize real-time tracking if enabled
        if ($this->useRealTimeTracking) {
            $this->initializeRealTimeTracking();
        }
    }

    /**
     * Initialize real-time tracking by loading current user location
     */
    protected function initializeRealTimeTracking(): void
    {
        if (Auth::check()) {
            $userLocation = app('geolocation')->getUserLocation(Auth::id());

            if ($userLocation['latitude'] && $userLocation['longitude']) {
                $this->currentUserLat = $userLocation['latitude'];
                $this->currentUserLng = $userLocation['longitude'];
                $this->currentUserAddress = $userLocation['city'] ?? 'Lokasi tidak diketahui';

                // Override static coordinates with real-time location
                $this->lat = $this->currentUserLat;
                $this->lng = $this->currentUserLng;
                $this->isActualLocation = true;

                if ($this->address === null) {
                    $this->address = $this->currentUserAddress;
                }
            }
        }
    }

    /**
     * Refresh map data (called by parent when location updates)
     */
    public function refreshMapData(): void
    {
        // Simply re-render to get fresh data from parent
        $this->dispatch('$refresh');
    }

    // Method untuk mendapatkan status lokasi
    public function getLocationStatus()
    {
        if ($this->useRealTimeTracking && $this->currentUserLat && $this->currentUserLng) {
            return 'real-time';
        }

        return $this->isActualLocation ? 'actual' : 'default';
    }

    // Method untuk mendapatkan text status
    public function getLocationStatusText()
    {
        return match($this->getLocationStatus()) {
            'real-time' => 'Tracking Real-time',
            'actual' => 'Lokasi Aktual',
            'default' => 'Lokasi Default'
        };
    }

    // Method untuk mendapatkan CSS class status
    public function getLocationStatusClass()
    {
        return match($this->getLocationStatus()) {
            'real-time' => 'status-info',
            'actual' => 'status-success',
            'default' => 'status-warning'
        };
    }

    // Method untuk mendapatkan text color class
    public function getLocationTextClass()
    {
        return match($this->getLocationStatus()) {
            'real-time' => 'text-info',
            'actual' => 'text-success',
            'default' => 'text-warning'
        };
    }

    // Method untuk menghitung jarak (akan coba gunakan real route distance jika tersedia)
    public function getDistanceText()
    {
        if (!$this->destinationLat || !$this->destinationLng) {
            return null;
        }

        // Use current user location if real-time tracking is enabled
        $originLat = $this->useRealTimeTracking && $this->currentUserLat ?
            $this->currentUserLat : $this->lat;
        $originLng = $this->useRealTimeTracking && $this->currentUserLng ?
            $this->currentUserLng : $this->lng;

        // Simple distance calculation (haversine formula) as fallback
        $earthRadius = 6371; // km
        $dLat = deg2rad($this->destinationLat - $originLat);
        $dLng = deg2rad($this->destinationLng - $originLng);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($originLat)) * cos(deg2rad($this->destinationLat)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        }

        return round($distance, 1) . ' km';
    }

    // Method untuk mendapatkan real route distance dari OSRM
    public function getRealRouteDistance()
    {
        if (!$this->destinationLat || !$this->destinationLng) {
            return null;
        }

        // Use current user location if real-time tracking is enabled
        $originLat = $this->useRealTimeTracking && $this->currentUserLat ?
            $this->currentUserLat : $this->lat;
        $originLng = $this->useRealTimeTracking && $this->currentUserLng ?
            $this->currentUserLng : $this->lng;

        try {
            // Call OSRM API untuk mendapatkan real distance
            $url = "https://router.project-osrm.org/route/v1/driving/{$originLng},{$originLat};{$this->destinationLng},{$this->destinationLat}?overview=false";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 5, // 5 seconds timeout
                    'method' => 'GET'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $data = json_decode($response, true);

                if (isset($data['routes'][0]['distance'])) {
                    $distanceMeters = $data['routes'][0]['distance'];
                    $distanceKm = $distanceMeters / 1000;

                    if ($distanceKm < 1) {
                        return round($distanceMeters) . ' m';
                    }

                    return round($distanceKm, 1) . ' km';
                }
            }
        } catch (Exception $e) {
            // Fallback to haversine calculation
        }

        // Fallback to simple distance calculation
        return $this->getDistanceText();
    }

    /**
     * Get current coordinates (real-time or static)
     */
    public function getCurrentCoordinates(): array
    {
        return [
            'lat' => $this->useRealTimeTracking && $this->currentUserLat ?
                $this->currentUserLat : $this->lat,
            'lng' => $this->useRealTimeTracking && $this->currentUserLng ?
                $this->currentUserLng : $this->lng,
            'address' => $this->useRealTimeTracking && $this->currentUserAddress ?
                $this->currentUserAddress : $this->address,
            'isRealTime' => $this->useRealTimeTracking
        ];
    }

    public function render()
    {
        return view('livewire.components.maps-route');
    }
}
