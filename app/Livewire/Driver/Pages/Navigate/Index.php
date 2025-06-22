<?php

namespace App\Livewire\Driver\Pages\Navigate;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.driver')]
#[Title('Navigasi Driver')]
class Index extends Component
{
    public function mount(): void
    {
        // Data akan diambil langsung di render method
    }

    /**
     * Listen for location updates from geolocation button
     * Component akan auto-refresh karena wire:poll.visible di Maps component
     */
    #[On('location-updated')]
    public function handleLocationUpdate(): void
    {
        // Tidak perlu action khusus, Maps component akan handle sendiri
        // via wire:poll.visible dan event dispatcher
    }

    /**
     * Listen for location cleared event
     */
    #[On('location-cleared')]
    public function handleLocationCleared(): void
    {
        // Tidak perlu action khusus, Maps component akan handle sendiri
        // via wire:poll.visible dan event dispatcher
    }

    public function render()
    {
        // Ambil data fresh dari service
        $location = app('geolocation')->getUserLocation(Auth::id());
        $weatherInfo = app('geolocation')->getWeatherInfo(Auth::id());

        // Tentukan apakah menggunakan lokasi aktual atau default
        $hasLocation = $location['latitude'] && $location['longitude'] && $location['last_updated'];

        // Siapkan data untuk view
        $data = [
            'latitude' => $hasLocation ? $location['latitude'] : null,
            'longitude' => $hasLocation ? $location['longitude'] : null,
            'address' => $hasLocation ? ($location['city'] ?? null) : null,
            'hasLocation' => $hasLocation,
            'lastUpdated' => $hasLocation ? \Carbon\Carbon::parse($location['last_updated'])->format('H:i') : null,
            'weatherInfo' => $hasLocation ? $weatherInfo : [],
            'badgeTopLeft' => $hasLocation ? sprintf("Cuaca: %s %d°C", $weatherInfo['condition'] ?? 'Cerah', $weatherInfo['temperature'] ?? 28) : null,
            'badgeTopRight' => "Waktu: " . now()->format('H:i') . " WITA",
        ];

        return view('livewire.driver.pages.navigate.index', $data);
    }
}
