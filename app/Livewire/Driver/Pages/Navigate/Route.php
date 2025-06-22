<?php

namespace App\Livewire\Driver\Pages\Navigate;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.driver')]
#[Title('Navigasi Rute Tujuan')]
class Route extends Component
{
    // Properties untuk real-time location updates (NOT reactive - this is top-level component)
    public $currentUserLat;
    public $currentUserLng;
    public $currentUserAddress;

    // Static destination coordinates (nanti bisa dari database/surat jalan)
    public $destinationLat = -3.943944;
    public $destinationLng = 122.1837957;
    public $destinationAddress = 'Wawoone, Kec. Wonggeduku, Kabupaten Konawe';

    // Map configuration
    public $zoom = 12;
    public $routeColor = '#DC2626'; // Red color for delivery route
    public $routeWeight = 6;

    // Component state - REACTIVE terhadap GeolocationButton
    public $locationReady = false;
    public $isTracking = false;
    public $gpsStatus = 'Off'; // 'Off', 'Aktif', 'Error'
    public $trackingBadge = '📍 Static'; // Badge text untuk tracking status

    // Event listeners for GeolocationButton communication
    protected $listeners = [
        'location-updated' => 'handleLocationUpdate',
        'location-cleared' => 'handleLocationCleared',
        'refresh-location-requested' => 'refreshLocation'
    ];

    public function mount()
    {
        // Initialize dengan data lokasi user saat ini
        $this->initializeUserLocation();
    }

    /**
     * Initialize user location dari geolocation service
     */
    protected function initializeUserLocation(): void
    {
        if (Auth::check()) {
            $userLocation = app('geolocation')->getUserLocation(Auth::id());

            if ($userLocation['latitude'] && $userLocation['longitude']) {
                $this->currentUserLat = $userLocation['latitude'];
                $this->currentUserLng = $userLocation['longitude'];
                $this->currentUserAddress = $userLocation['city'] ?? 'Lokasi tidak diketahui';
                $this->locationReady = true;
                $this->isTracking = true;
                $this->gpsStatus = 'Aktif';
                $this->trackingBadge = '🔴 Live';

                // Log untuk debugging
                logger('Route initialized with user location:', [
                    'lat' => $this->currentUserLat,
                    'lng' => $this->currentUserLng,
                    'address' => $this->currentUserAddress,
                    'gps_status' => $this->gpsStatus
                ]);
            } else {
                // Gunakan default location jika belum ada GPS data
                $this->currentUserLat = -4.0011471; // Default Kendari
                $this->currentUserLng = 122.5040029;
                $this->currentUserAddress = 'Lokasi Default (Kendari)';
                $this->locationReady = false;
                $this->isTracking = false;
                $this->gpsStatus = 'Off';
                $this->trackingBadge = '📍 Static';
            }
        }
    }

    /**
     * Handle location updates dari GeolocationButton
     */
    public function handleLocationUpdate(array $locationData): void
    {
        $this->currentUserLat = $locationData['latitude'];
        $this->currentUserLng = $locationData['longitude'];
        $this->currentUserAddress = $locationData['address'] ?? 'Lokasi tidak diketahui';
        $this->locationReady = true;
        $this->isTracking = true;
        $this->gpsStatus = 'Aktif';
        $this->trackingBadge = '🔴 Live';

        // Dispatch JavaScript event untuk smooth marker movement di MapsRoute
        $this->dispatch('map-location-updated', [
            'lat' => $this->currentUserLat,
            'lng' => $this->currentUserLng,
            'address' => $this->currentUserAddress,
            'timestamp' => now()->toISOString()
        ]);

        // Force refresh MapsRoute component dengan data terbaru
        $this->dispatch('map-refresh-requested');

        // Log untuk debugging
        logger('Route location updated:', [
            'lat' => $this->currentUserLat,
            'lng' => $this->currentUserLng,
            'address' => $this->currentUserAddress,
            'gps_status' => $this->gpsStatus,
            'tracking_badge' => $this->trackingBadge
        ]);
    }

    /**
     * Handle location cleared dari GeolocationButton
     */
    public function handleLocationCleared(): void
    {
        // Reset ke default location
        $this->currentUserLat = -4.0011471;
        $this->currentUserLng = 122.5040029;
        $this->currentUserAddress = 'Lokasi Default (Kendari)';
        $this->locationReady = false;
        $this->isTracking = false;
        $this->gpsStatus = 'Off';
        $this->trackingBadge = '📍 Static';

        // Dispatch JavaScript event untuk update peta
        $this->dispatch('map-location-cleared', [
            'defaultLat' => $this->currentUserLat,
            'defaultLng' => $this->currentUserLng
        ]);

        // Force refresh MapsRoute component
        $this->dispatch('map-refresh-requested');

        logger('Route location cleared, using default location', [
            'gps_status' => $this->gpsStatus,
            'tracking_badge' => $this->trackingBadge
        ]);
    }

    /**
     * Manual refresh location
     */
    public function refreshLocation(): void
    {
        if (Auth::check()) {
            // Trigger geolocation refresh
            $this->dispatch('request-location-refresh');

            // Reload dari service
            $this->initializeUserLocation();

            // Show notification
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Memperbarui lokasi...'
            ]);
        }
    }

    /**
     * Set destination (nanti bisa dipanggil dari surat jalan)
     */
    public function setDestination(float $lat, float $lng, string $address = null): void
    {
        $this->destinationLat = $lat;
        $this->destinationLng = $lng;
        $this->destinationAddress = $address ?? 'Lokasi Tujuan';

        // Dispatch event untuk update peta
        $this->dispatch('destination-updated', [
            'lat' => $this->destinationLat,
            'lng' => $this->destinationLng,
            'address' => $this->destinationAddress
        ]);
    }

    /**
     * Calculate distance to destination
     */
    public function getDistanceToDestination(): ?string
    {
        if (!$this->currentUserLat || !$this->currentUserLng) {
            return null;
        }

        $earthRadius = 6371; // km
        $dLat = deg2rad($this->destinationLat - $this->currentUserLat);
        $dLng = deg2rad($this->destinationLng - $this->currentUserLng);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($this->currentUserLat)) * cos(deg2rad($this->destinationLat)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        }

        return round($distance, 1) . ' km';
    }

    /**
     * Check if user is near destination (proximity alert)
     */
    public function isNearDestination(float $radiusKm = 1.0): bool
    {
        if (!$this->currentUserLat || !$this->currentUserLng) {
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
     * Get current coordinates for map
     */
    public function getCurrentCoordinates(): array
    {
        return [
            'lat' => $this->currentUserLat,
            'lng' => $this->currentUserLng,
            'address' => $this->currentUserAddress,
            'isReady' => $this->locationReady,
            'isTracking' => $this->isTracking
        ];
    }

    /**
     * Get destination coordinates for map
     */
    public function getDestinationCoordinates(): array
    {
        return [
            'lat' => $this->destinationLat,
            'lng' => $this->destinationLng,
            'address' => $this->destinationAddress
        ];
    }

    /**
     * Get tracking status text
     */
    public function getTrackingStatusText(): string
    {
        if ($this->isTracking && $this->locationReady) {
            return 'Tracking Real-time';
        } elseif ($this->locationReady) {
            return 'Lokasi Driver';
        } else {
            return 'Lokasi Default';
        }
    }

    /**
     * Get tracking status badge class
     */
    public function getTrackingStatusClass(): string
    {
        if ($this->isTracking && $this->locationReady) {
            return 'badge-error'; // Red untuk live tracking
        } elseif ($this->locationReady) {
            return 'badge-success'; // Green untuk actual location
        } else {
            return 'badge-warning'; // Yellow untuk default location
        }
    }

    /**
     * Get GPS status badge class
     */
    public function getGpsStatusClass(): string
    {
        return match($this->gpsStatus) {
            'Aktif' => 'badge-success badge-soft badge-xs',
            'Off' => 'badge-warning badge-soft badge-xs',
            'Error' => 'badge-error badge-soft badge-xs',
            default => 'badge-neutral badge-soft badge-xs'
        };
    }

    /**
     * Get tracking badge class
     */
    public function getTrackingBadgeClass(): string
    {
        return match($this->trackingBadge) {
            '🔴 Live' => 'badge-error badge-soft badge-xs',
            '📍 Static' => 'badge-warning badge-soft badge-xs',
            default => 'badge-neutral badge-soft badge-xs'
        };
    }

    /**
     * Start proximity monitoring (called when user starts journey)
     */
    public function startProximityMonitoring(): void
    {
        if (!$this->isTracking) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Silakan aktifkan GPS terlebih dahulu'
            ]);
            return;
        }

        // Enable proximity alerts
        $this->dispatch('proximity-monitoring-started', [
            'destinationLat' => $this->destinationLat,
            'destinationLng' => $this->destinationLng,
            'radius' => 1.0 // 1km radius
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Monitoring kedekatan tujuan diaktifkan'
        ]);
    }

    public function render()
    {
        return view('livewire.driver.pages.navigate.route', [
            'currentLocation' => $this->getCurrentCoordinates(),
            'destination' => $this->getDestinationCoordinates(),
            'distance' => $this->getDistanceToDestination(),
            'trackingStatus' => $this->getTrackingStatusText(),
            'statusClass' => $this->getTrackingStatusClass(),
            'isNearDestination' => $this->isNearDestination()
        ]);
    }
}
