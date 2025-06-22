<?php

namespace App\Livewire\Components;

use Mary\Traits\Toast;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GeolocationButton extends Component
{
    use Toast;

    public string $status = 'waiting'; // waiting, getting, success, error
    public float $latitude = 0;
    public float $longitude = 0;
    public string $address = '';
    public ?string $lastUpdated = null;
    public bool $isTracking = false;
    public string $buttonClass = 'btn-circle btn-md border-primary border-2';
    public string $iconName = 'phosphor.map-pin';
    public bool $showToast = true;
    public bool $showBadge = true;

    /**
     * Get current WITA time
     */
    private function getWitaTime(): Carbon
    {
        return Carbon::now('Asia/Makassar'); // WITA timezone (UTC+8)
    }

    /**
     * Format time to WITA string
     */
    private function formatWitaTime(?Carbon $time = null): string
    {
        $time = $time ?: $this->getWitaTime();
        return $time->format('H:i:s');
    }

    public function mount(
        string $buttonClass = 'btn-circle btn-md border-primary border-2',
        string $iconName = 'phosphor.map-pin',
        bool $showToast = true,
        bool $showBadge = true
    ): void {
        $this->buttonClass = $buttonClass;
        $this->iconName = $iconName;
        $this->showToast = $showToast;
        $this->showBadge = $showBadge;

        $this->loadCachedLocation();
        $this->loadTrackingState();
    }

    /**
     * Method yang dipanggil oleh wire:poll.5000ms untuk real-time tracking
     * Hanya berjalan ketika tracking aktif
     */
    public function updateLocation(): void
    {
        // Hanya update jika tracking aktif
        if (!$this->isTracking) {
            return;
        }

        // Trigger browser geolocation tanpa loading state
        $this->dispatch('request-geolocation-silent');
    }

    /**
     * Start tracking - dipanggil dari menu item
     */
    public function startTracking(): void
    {
        $this->isTracking = true;
        $this->status = 'getting';

        // Trigger immediate location request
        $this->dispatch('request-geolocation');

        if ($this->showToast) {
            $this->success('Live tracking dimulai');
        }

        $this->saveTrackingState();

        Log::info('Location tracking started', [
            'user_id' => Auth::id(),
            'wita_time' => $this->formatWitaTime()
        ]);
    }

    /**
     * Stop tracking - dipanggil dari menu item
     */
    public function stopTracking(): void
    {
        $this->isTracking = false;
        $this->status = 'waiting';

        if ($this->showToast) {
            $this->info('Live tracking dihentikan');
        }

        $this->saveTrackingState();

        Log::info('Location tracking stopped', [
            'user_id' => Auth::id(),
            'wita_time' => $this->formatWitaTime()
        ]);
    }

    /**
     * Manual location request - dipanggil dari menu item
     */
    public function requestLocation(): void
    {
        $this->status = 'getting';
        $this->dispatch('request-geolocation');

        if ($this->showToast) {
            $this->info('Mengambil lokasi...');
        }
    }

    /**
     * Refresh location data - dipanggil dari menu item
     */
    public function refreshLocation(): void
    {
        $this->requestLocation();
    }

    /**
     * Handle successful location from browser
     */
    public function handleLocationSuccess(float $lat, float $lng, ?string $accuracy = null): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->status = 'success';
        $this->lastUpdated = $this->formatWitaTime(); // Use WITA time

        // Update location in cache with immediate processing
        app('geolocation')->updateUserLocationImmediate(Auth::id(), $lat, $lng);
        $this->updateAddress();

        // Dispatch global event untuk real-time updates
        $this->dispatch('location-updated', [
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $this->address,
            'accuracy' => $accuracy,
            'timestamp' => $this->getWitaTime()->toISOString(),
            'wita_time' => $this->formatWitaTime(),
            'user_id' => Auth::id(),
            'timezone' => 'WITA'
        ]);

        // Show toast untuk manual requests atau start tracking
        if ($this->showToast && $this->status === 'getting') {
            $this->success('Lokasi berhasil diperbarui');
        }

        Log::info('Real-time location updated', [
            'user_id' => Auth::id(),
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'tracking_mode' => $this->isTracking ? 'active' : 'inactive',
            'wita_time' => $this->formatWitaTime()
        ]);
    }

    /**
     * Handle location error from browser
     */
    public function handleLocationError(string $errorMessage): void
    {
        // Update status untuk manual requests
        if ($this->status === 'getting') {
            $this->status = 'error';

            if ($this->showToast) {
                $this->warning('Gagal mengambil lokasi: ' . $errorMessage);
            }
        }

        Log::warning('Geolocation error', [
            'user_id' => Auth::id(),
            'error' => $errorMessage,
            'tracking_active' => $this->isTracking,
            'wita_time' => $this->formatWitaTime()
        ]);
    }

    /**
     * Clear all location data - dipanggil dari menu item
     */
    public function clearLocation(): void
    {
        $this->status = 'waiting';
        $this->latitude = 0;
        $this->longitude = 0;
        $this->address = '';
        $this->lastUpdated = null;
        $this->isTracking = false;

        app('geolocation')->clearUserLocation(Auth::id());
        $this->dispatch('location-cleared');

        if ($this->showToast) {
            $this->info('Data lokasi dihapus');
        }

        $this->saveTrackingState();

        Log::info('User location cleared', [
            'user_id' => Auth::id(),
            'wita_time' => $this->formatWitaTime()
        ]);
    }

    /**
     * Load cached location
     */
    protected function loadCachedLocation(): void
    {
        $location = app('geolocation')->getUserLocation(Auth::id());

        if ($location['latitude'] && $location['longitude']) {
            $this->latitude = $location['latitude'];
            $this->longitude = $location['longitude'];
            $this->address = $location['city'] ?? '';

            if ($location['last_updated']) {
                $this->status = 'success';
                // Convert to WITA time for display
                $this->lastUpdated = app('geolocation')->getFormattedWitaTime($location['last_updated']);
            }
        }
    }

    /**
     * Load tracking state from cache
     */
    protected function loadTrackingState(): void
    {
        $cacheKey = "user_tracking_state_" . Auth::id();
        $this->isTracking = Cache::get($cacheKey, false);
    }

    /**
     * Save tracking state to cache
     */
    protected function saveTrackingState(): void
    {
        $cacheKey = "user_tracking_state_" . Auth::id();
        Cache::put($cacheKey, $this->isTracking, now()->addHours(24));
    }

    /**
     * Update address from coordinates
     */
    protected function updateAddress(): void
    {
        $location = app('geolocation')->getUserLocation(Auth::id());

        // Gunakan alamat dari API atau fallback
        $this->address = $location['city'] ?? 'Mengambil alamat...';

        // Jika alamat masih default, coba ambil dari cache yang lebih lama
        if ($this->address === 'Mengambil alamat...' || $this->address === 'Alamat tidak tersedia') {
            // Coba ambil dari default location
            $defaultLocation = app('geolocation')->getUserLocation(1);
            if (isset($defaultLocation['city']) && !in_array($defaultLocation['city'], ['Mengambil alamat...', 'Alamat tidak tersedia'])) {
                $this->address = $defaultLocation['city'];
            }
        }
    }

    /**
     * Get status badge info - hanya muncul saat live tracking
     */
    public function getStatusBadge(): array
    {
        if (!$this->showBadge) return ['class' => '', 'text' => ''];

        // Badge hanya muncul saat live tracking aktif
        if ($this->isTracking && $this->status === 'success') {
            return [
                'class' => 'badge-success badge-xs animate-pulse',
                'text' => 'LIVE'
            ];
        }

        // Tidak ada badge untuk status lainnya
        return ['class' => '', 'text' => ''];
    }

    /**
     * Get button classes - tanpa animate pulse di button
     */
    public function getButtonClasses(): string
    {
        $base = $this->buttonClass;

        return match ($this->status) {
            'waiting' => $base . ' btn-outline',
            'getting' => $base . ' btn-info loading',
            'success' => $base . ' btn-success', // Removed animate-pulse from button
            'error' => $base . ' btn-error btn-outline',
        };
    }

    /**
     * Get icon name based on status and tracking mode
     */
    public function getIconName(): string
    {
        return match ($this->status) {
            'getting' => 'phosphor.spinner',
            'success' => $this->isTracking ? 'phosphor.broadcast' : 'phosphor.map-pin-area',
            'error' => 'phosphor.map-pin-simple',
            default => $this->iconName,
        };
    }

    /**
     * Check if location is recent (dalam 30 detik terakhir untuk real-time, WITA timezone)
     */
    public function isLocationRecent(): bool
    {
        if (!$this->lastUpdated) return false;

        try {
            // Parse WITA time
            $updated = Carbon::createFromFormat('H:i:s', $this->lastUpdated, 'Asia/Makassar');
            $updated->setDate($this->getWitaTime()->year, $this->getWitaTime()->month, $this->getWitaTime()->day);

            return $updated->diffInSeconds($this->getWitaTime()) < 30; // 30 detik untuk real-time
        } catch (\Exception $e) {
            Log::warning('Failed to parse location time', [
                'last_updated' => $this->lastUpdated,
                'error' => $e->getMessage(),
                'wita_time' => $this->formatWitaTime()
            ]);
            return false;
        }
    }

    /**
     * Get tracking status text
     */
    public function getTrackingStatusText(): string
    {
        if (!$this->isTracking) {
            return 'Tracking nonaktif';
        }

        if ($this->status === 'success' && $this->isLocationRecent()) {
            return 'Live tracking aktif';
        }

        return 'Tracking aktif';
    }

    /**
     * Get tracking status color
     */
    public function getTrackingStatusColor(): string
    {
        if (!$this->isTracking) return 'text-base-content/60';

        return $this->isLocationRecent() ? 'text-success' : 'text-warning';
    }

    /**
     * Get current WITA time for display
     */
    public function getCurrentWitaTime(): string
    {
        return $this->formatWitaTime();
    }

    public function render()
    {
        return view('livewire.components.geolocation-button');
    }
}
