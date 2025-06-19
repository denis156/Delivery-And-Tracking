<?php

namespace App\Livewire\Components;

use Mary\Traits\Toast;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GeolocationButton extends Component
{
    use Toast;

    public string $status = 'waiting'; // waiting, getting, success, error
    public float $latitude = 0;
    public float $longitude = 0;
    public string $address = '';
    public ?string $lastUpdated = null;
    public bool $autoUpdate = false;
    public int $pollInterval = 300;
    public string $buttonClass = 'btn-circle btn-md border-primary border-2';
    public string $iconName = 'phosphor.map-pin';
    public bool $showToast = true;
    public bool $showBadge = true;
    public bool $clickToOpenOnly = false;

    // Simple flag to track if user manually triggered the request
    public bool $isManualRequest = false;

    // Flag to control if polling is currently active
    public bool $isPollingActive = false;

    public function mount(
        bool $autoUpdate = false,
        int $pollInterval = 300,
        string $buttonClass = 'btn-circle btn-md border-primary border-2',
        string $iconName = 'phosphor.map-pin',
        bool $showToast = true,
        bool $showBadge = true,
        bool $clickToOpenOnly = false
    ): void {
        $this->autoUpdate = $autoUpdate;
        $this->pollInterval = $pollInterval;
        $this->buttonClass = $buttonClass;
        $this->iconName = $iconName;
        $this->showToast = $showToast;
        $this->showBadge = $showBadge;
        $this->clickToOpenOnly = $clickToOpenOnly;

        $this->loadCachedLocation();

        // Load polling state from cache - if user manually stopped, keep it stopped
        $this->loadPollingState();

        // Auto-request location on mount (silent) only if autoUpdate is enabled, no cached location, and polling is active
        if ($this->autoUpdate && $this->status === 'waiting' && $this->isPollingActive && !$this->clickToOpenOnly) {
            $this->requestLocationSilent();
        }
    }

    /**
     * Handle navbar button click
     */
    public function handleNavbarClick(): void
    {
        // For clickToOpenOnly mode, just open dropdown
        if ($this->clickToOpenOnly) {
            return;
        }

        // For other modes, silently request location
        $this->requestLocationSilent();
    }

    /**
     * Manual location request (triggered by user buttons)
     */
    public function requestLocation(): void
    {
        $this->isManualRequest = true;
        $this->status = 'getting';

        // Activate polling when user manually requests location
        if ($this->autoUpdate) {
            $this->isPollingActive = true;
            $this->savePollingState();
        }

        $this->dispatch('request-geolocation');
    }

    /**
     * Silent location request (for auto-update/polling)
     */
    private function requestLocationSilent(): void
    {
        $this->isManualRequest = false;
        // Don't change status for silent requests
        $this->dispatch('request-geolocation');
    }

    /**
     * Auto refresh (called by wire:poll)
     */
    public function autoRefreshLocation(): void
    {
        // Only run if polling is active, autoUpdate is enabled, and not currently getting location
        if ($this->autoUpdate && $this->isPollingActive && $this->status !== 'getting') {
            $this->requestLocationSilent();
        }
    }

    /**
     * Refresh location (manual button)
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
        $this->lastUpdated = now()->format('H:i');

        // Update location in cache (this will get comprehensive data from de4a.space API)
        app('geolocation')->updateUserLocation(Auth::id(), $lat, $lng);
        $this->updateAddress();

        // Dispatch global event
        $this->dispatch('location-updated', [
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $this->address,
            'accuracy' => $accuracy
        ]);

        // Show toast ONLY for manual requests
        if ($this->showToast && $this->isManualRequest) {
            $this->success('Lokasi berhasil diperbarui!');
        }

        // Reset manual flag
        $this->isManualRequest = false;

        Log::info('User location updated', [
            'user_id' => Auth::id(),
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'address' => $this->address
        ]);
    }

    /**
     * Handle location error from browser
     */
    public function handleLocationError(string $errorMessage): void
    {
        // Only update status and show toast for manual requests
        if ($this->isManualRequest) {
            $this->status = 'error';

            if ($this->showToast) {
                $this->warning('Gagal mengambil lokasi: ' . $errorMessage);
            }
        }

        // Reset manual flag
        $this->isManualRequest = false;

        Log::warning('Geolocation error', [
            'user_id' => Auth::id(),
            'error' => $errorMessage,
            'manual' => $this->isManualRequest
        ]);
    }

    /**
     * Stop location tracking
     */
    public function stopLocation(): void
    {
        $this->status = 'waiting';
        $this->latitude = 0;
        $this->longitude = 0;
        $this->address = '';
        $this->lastUpdated = null;

        // Stop polling when user stops location
        $this->isPollingActive = false;
        $this->savePollingState();

        app('geolocation')->clearUserLocation(Auth::id());
        $this->dispatch('location-cleared');

        if ($this->showToast) {
            $this->info('Lokasi telah dihentikan dan data dihapus');
        }

        Log::info('User location stopped', ['user_id' => Auth::id()]);
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
                $this->lastUpdated = \Carbon\Carbon::parse($location['last_updated'])->format('H:i');
            }
        }
    }

    /**
     * Load polling state from cache
     */
    protected function loadPollingState(): void
    {
        $cacheKey = "user_polling_state_" . Auth::id();
        $savedState = Cache::get($cacheKey);

        if ($savedState !== null) {
            // Use saved state from cache
            $this->isPollingActive = $savedState && $this->autoUpdate;
        } else {
            // Default behavior for new users
            $this->isPollingActive = $this->autoUpdate && $this->status === 'success';
            $this->savePollingState();
        }
    }

    /**
     * Save polling state to cache
     */
    protected function savePollingState(): void
    {
        $cacheKey = "user_polling_state_" . Auth::id();
        Cache::put($cacheKey, $this->isPollingActive, now()->addDays(30));
    }

    /**
     * Update address from coordinates
     */
    protected function updateAddress(): void
    {
        $location = app('geolocation')->getUserLocation(Auth::id());
        $this->address = $location['city'] ?? 'Lokasi tidak diketahui';
    }

    /**
     * Get weather info for current location
     */
    public function getWeatherInfo(): array
    {
        return app('geolocation')->getWeatherInfo(Auth::id());
    }

    /**
     * Get weather forecast for current location
     */
    public function getWeatherForecast(int $days = 3): array
    {
        return app('geolocation')->getWeatherForecast(Auth::id(), $days);
    }

    /**
     * Placeholder for proximity alerts
     */
    public function checkProximityAlerts(): void
    {
        Log::info('checkProximityAlerts called on GeolocationButton - should be on dashboard component');

        $this->dispatch('proximity-check-requested', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get status badge info
     */
    public function getStatusBadge(): array
    {
        return match ($this->status) {
            'waiting' => ['class' => 'badge-warning badge-xs', 'text' => 'OFF'],
            'success' => ['class' => 'badge-success badge-xs', 'text' => 'ON'],
            'error' => ['class' => 'badge-error badge-xs', 'text' => 'ERROR'],
            default => ['class' => '', 'text' => ''], // No badge for getting status
        };
    }

    /**
     * Get button classes
     */
    public function getButtonClasses(): string
    {
        $base = $this->buttonClass;

        return match ($this->status) {
            'waiting' => $base . ' btn-outline',
            'getting' => $base . ' btn-warning' . ($this->isManualRequest ? ' loading' : ''),
            'success' => $base . ' btn-success',
            'error' => $base . ' btn-error btn-outline',
        };
    }

    /**
     * Get icon name
     */
    public function getIconName(): string
    {
        return match ($this->status) {
            'getting' => $this->isManualRequest ? 'phosphor.spinner' : $this->iconName,
            'success' => 'phosphor.map-pin-area',
            'error' => 'phosphor.map-pin-simple',
            default => $this->iconName,
        };
    }

    /**
     * Check if location is recent
     */
    public function isLocationRecent(): bool
    {
        if (!$this->lastUpdated) return false;

        $updated = \Carbon\Carbon::createFromFormat('H:i', $this->lastUpdated);
        return $updated->diffInHours(now()) < 1;
    }

    /**
     * Get location accuracy status
     */
    public function getLocationAccuracy(): array
    {
        return app('geolocation')->getLocationAccuracyStatus(Auth::id());
    }

    public function render()
    {
        return view('livewire.components.geolocation-button');
    }
}
