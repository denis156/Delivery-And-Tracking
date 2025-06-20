<?php

namespace App\Livewire\Components;

use Livewire\Component;

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
    public $isActualLocation = false; // Property baru untuk menentukan jenis lokasi

    // Badge properties
    public $address = null;
    public $badgeTopLeft = null;
    public $badgeTopRight = null;
    public $badgeBottomLeft = null;
    public $badgeBottomRight = null;

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
