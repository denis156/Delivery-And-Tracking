<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;

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
    }

    /**
     * Method untuk update lokasi real-time
     * Dipanggil oleh wire:poll.visible ketika tracking aktif
     */
    public function updateMapLocation(): void
    {
        // Ambil data lokasi fresh dari geolocation service
        $location = app('geolocation')->getUserLocation(\Illuminate\Support\Facades\Auth::id());

        // Cek apakah ada lokasi aktual
        $hasLocation = $location['latitude'] && $location['longitude'] && $location['last_updated'];

        if ($hasLocation) {
            // Update coordinate properties
            $this->lat = $location['latitude'];
            $this->lng = $location['longitude'];
            $this->address = $location['city'] ?? null;
            $this->isActualLocation = true;

            // Dispatch browser event untuk update marker position dengan mapId yang valid
            $this->dispatch('update-marker-position',
                mapId: $this->mapId,
                lat: $this->lat,
                lng: $this->lng,
                address: $this->address,
                isActual: $this->isActualLocation
            );
        } else {
            // Fallback ke lokasi default jika tidak ada data
            $this->lat = $this->latDefult;
            $this->lng = $this->lngDefult;
            $this->isActualLocation = false;

            // Dispatch event untuk update ke default location dengan mapId yang valid
            $this->dispatch('update-marker-position',
                mapId: $this->mapId,
                lat: $this->lat,
                lng: $this->lng,
                address: 'Lokasi Default - Kendari',
                isActual: $this->isActualLocation
            );
        }
    }

    /**
     * Listen untuk location-updated event dari GeolocationButton
     */
    #[On('location-updated')]
    public function handleLocationUpdate(): void
    {
        // Trigger update map location
        $this->updateMapLocation();
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

        // Update marker ke posisi default dengan mapId yang valid
        $this->dispatch('update-marker-position',
            mapId: $this->mapId,
            lat: $this->lat,
            lng: $this->lng,
            address: 'Lokasi Default - Kendari',
            isActual: $this->isActualLocation
        );
    }

    public function mapInitialized()
    {
        $this->mapReady = true;
        $this->dispatch('map-ready', ['mapId' => $this->mapId]);
    }

    // Method untuk mendapatkan status lokasi
    public function getLocationStatus()
    {
        return $this->isActualLocation ? 'actual' : 'default';
    }

    // Method untuk mendapatkan text status
    public function getLocationStatusText()
    {
        return $this->isActualLocation ? 'Lokasi Aktual' : 'Lokasi Default';
    }

    // Method untuk mendapatkan CSS class status
    public function getLocationStatusClass()
    {
        return $this->isActualLocation ? 'status-success' : 'status-warning';
    }

    // Method untuk mendapatkan text color class
    public function getLocationTextClass()
    {
        return $this->isActualLocation ? 'text-success' : 'text-warning';
    }

    public function render()
    {
        return view('livewire.components.maps');
    }
}
