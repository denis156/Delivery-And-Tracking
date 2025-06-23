<?php

namespace App\Livewire\Driver\Pages\Navigate;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.driver')]
#[Title('Navigasi Rute Tujuan')]
class Route extends Component
{
    // Static destination coordinates (nanti bisa dari database/surat jalan)
    public $destinationLat = -3.943944;
    public $destinationLng = 122.1837957;
    public $destinationAddress = 'Wawoone, Kec. Wonggeduku, Kabupaten Konawe';

    // Map configuration
    public $zoom = 12;

    public function mount()
    {
        // Tidak perlu logic kompleks, semua akan ditangani oleh MapsRoute component
    }

    /**
     * Set destination (nanti bisa dipanggil dari surat jalan/database)
     */
    public function setDestination(float $lat, float $lng, string $address = null): void
    {
        $this->destinationLat = $lat;
        $this->destinationLng = $lng;
        $this->destinationAddress = $address ?? 'Lokasi Tujuan';
    }

    /**
     * Get destination coordinates for passing to MapsRoute component
     */
    public function getDestinationCoordinates(): array
    {
        return [
            'lat' => $this->destinationLat,
            'lng' => $this->destinationLng,
            'address' => $this->destinationAddress
        ];
    }

    public function render()
    {
        return view('livewire.driver.pages.navigate.route', [
            'destination' => $this->getDestinationCoordinates(),
            'zoom' => $this->zoom
        ]);
    }
}
