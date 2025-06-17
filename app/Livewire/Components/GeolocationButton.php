<?php

namespace App\Livewire\Components;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class GeolocationButton extends Component
{
    use Toast;

    public string $status = 'waiting'; // waiting, getting, success, error
    public float $latitude = 0;
    public float $longitude = 0;
    public string $address = '';
    public ?string $lastUpdated = null;
    public bool $autoUpdate = false;
    public int $pollInterval = 300; // seconds - 5 minutes default
    public string $buttonClass = 'btn-circle btn-md border-primary border-2';
    public string $iconName = 'phosphor.map-pin';
    public bool $showToast = true;
    public bool $showBadge = true;
    public bool $clickToOpenOnly = false;

    // Track if request is from user interaction or polling
    private bool $isUserTriggered = false;
    // Track if request is from card button specifically
    private bool $isCardButtonTriggered = false;
    // Track if should show toast regardless of trigger type
    private bool $shouldShowToast = false;

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

        // Load cached location if exists
        $this->loadCachedLocation();

        // Auto-request location if enabled (but not if clickToOpenOnly is true)
        if ($this->autoUpdate && $this->status === 'waiting' && !$this->clickToOpenOnly) {
            $this->requestLocationSilently(); // Use silent method for initial load
        }
    }

    /**
     * Handle navbar button click - only opens dropdown, doesn't request location
     */
    public function handleNavbarClick(): void
    {
        if ($this->clickToOpenOnly) {
            // Just open dropdown, don't request location
            return;
        }

        // Original behavior for other instances - but no UI feedback
        $this->isUserTriggered = false;
        $this->isCardButtonTriggered = false;
        $this->shouldShowToast = false;
        $this->requestLocationSilently();
    }

    /**
     * Request user location via browser geolocation (with UI feedback for card buttons only)
     */
    public function requestLocation(): void
    {
        $this->isUserTriggered = true;
        $this->isCardButtonTriggered = true;
        $this->shouldShowToast = true; // Always show toast for manual requests
        $this->status = 'getting';
        $this->dispatch('request-geolocation');
    }

    /**
     * Request location silently (for polling and navbar - no UI changes)
     */
    private function requestLocationSilently(): void
    {
        $this->isUserTriggered = false;
        $this->isCardButtonTriggered = false;
        $this->shouldShowToast = false; // No toast for silent requests
        // Don't change status to 'getting' for silent requests
        $this->dispatch('request-geolocation');
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

        // Update location using new geolocation service
        app('geolocation')->updateUserLocation(Auth::id(), $lat, $lng);

        // Get updated address
        $this->updateAddress();

        // Dispatch global event that location was updated
        $this->dispatch('location-updated', [
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $this->address,
            'accuracy' => $accuracy
        ]);

        // Show toast based on multiple conditions
        if ($this->showToast && ($this->shouldShowToast || $this->isCardButtonTriggered || $this->isUserTriggered)) {
            $this->success(
                'Lokasi berhasil diperbarui!',
                position: 'toast-top toast-end',
                timeout: 3000
            );
        }

        Log::info('User location updated', [
            'user_id' => Auth::id(),
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'triggered_by' => $this->isCardButtonTriggered ? 'card_button' : ($this->isUserTriggered ? 'user' : 'polling'),
            'should_show_toast' => $this->shouldShowToast
        ]);

        // Reset trigger flags
        $this->resetTriggerFlags();
    }

    /**
     * Handle location error from browser
     */
    public function handleLocationError(string $errorMessage): void
    {
        // Change status to error if user triggered or should show toast
        if ($this->shouldShowToast || $this->isCardButtonTriggered || $this->isUserTriggered) {
            $this->status = 'error';

            if ($this->showToast) {
                $this->warning(
                    'Gagal mengambil lokasi: ' . $errorMessage,
                    position: 'toast-top toast-end',
                    timeout: 5000
                );
            }
        }

        Log::warning('Geolocation error', [
            'user_id' => Auth::id(),
            'error' => $errorMessage,
            'triggered_by' => $this->isCardButtonTriggered ? 'card_button' : ($this->isUserTriggered ? 'user' : 'polling'),
            'should_show_toast' => $this->shouldShowToast
        ]);

        // Reset trigger flags
        $this->resetTriggerFlags();
    }

    /**
     * Refresh location (manual trigger from card)
     */
    public function refreshLocation(): void
    {
        $this->isUserTriggered = true;
        $this->isCardButtonTriggered = true;
        $this->shouldShowToast = true; // Always show toast for manual refresh
        $this->requestLocation();
    }

    /**
     * Stop location tracking and clear data
     */
    public function stopLocation(): void
    {
        $this->status = 'waiting';
        $this->latitude = 0;
        $this->longitude = 0;
        $this->address = '';
        $this->lastUpdated = null;

        // Clear location using new geolocation service
        app('geolocation')->clearUserLocation(Auth::id());

        // Dispatch global event that location was cleared
        $this->dispatch('location-cleared');

        // Always show toast for stop action regardless of settings
        if ($this->showToast) {
            $this->info(
                'Lokasi telah dihentikan dan data dihapus',
                position: 'toast-top toast-end',
                timeout: 3000
            );
        }

        Log::info('User location stopped and cleared', [
            'user_id' => Auth::id()
        ]);

        // Reset trigger flags
        $this->resetTriggerFlags();
    }

    /**
     * Automatic location update via polling - SILENT
     */
    public function autoRefreshLocation(): void
    {
        if ($this->autoUpdate && $this->status !== 'getting') {
            $this->requestLocationSilently();
        }
    }

    /**
     * Load cached location from geolocation service
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
     * Update address from coordinates
     */
    protected function updateAddress(): void
    {
        $location = app('geolocation')->getUserLocation(Auth::id());
        $this->address = $location['city'] ?? 'Lokasi tidak diketahui';
    }

    /**
     * Reset all trigger flags
     */
    private function resetTriggerFlags(): void
    {
        $this->isUserTriggered = false;
        $this->isCardButtonTriggered = false;
        $this->shouldShowToast = false;
    }

    /**
     * Placeholder method for proximity alerts - this component doesn't handle proximity
     * This method exists to prevent JavaScript errors when called incorrectly
     */
    public function checkProximityAlerts(): void
    {
        // Log that this method was called on the wrong component
        Log::info('checkProximityAlerts called on GeolocationButton component - this method should be called on dashboard components', [
            'user_id' => Auth::id(),
            'component' => self::class
        ]);

        // Optionally dispatch an event to notify dashboard components
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
            'waiting' => [
                'class' => 'badge-warning badge-xs',
                'text' => 'OFF',
                'animate' => false
            ],
            'getting' => [
                'class' => 'badge-info badge-xs',
                'text' => 'GET',
                'animate' => false
            ],
            'success' => [
                'class' => 'badge-success badge-xs',
                'text' => 'ON',
                'animate' => false
            ],
            'error' => [
                'class' => 'badge-error badge-xs',
                'text' => 'ERROR',
                'animate' => false
            ]
        };
    }

    /**
     * Get button classes based on status
     */
    public function getButtonClasses(): string
    {
        $baseClass = $this->buttonClass;

        // If clickToOpenOnly is true, don't show loading state on main button
        if ($this->clickToOpenOnly) {
            return match ($this->status) {
                'waiting' => $baseClass . ' btn-outline',
                'success' => $baseClass . ' btn-success',
                'error' => $baseClass . ' btn-error btn-outline',
                default => $baseClass . ' btn-outline'
            };
        }

        // For card buttons, show loading if any user interaction triggered it
        return match ($this->status) {
            'waiting' => $baseClass . ' btn-outline',
            'getting' => $baseClass . ' btn-warning' . (($this->isCardButtonTriggered || $this->shouldShowToast) ? ' loading' : ''),
            'success' => $baseClass . ' btn-success',
            'error' => $baseClass . ' btn-error btn-outline'
        };
    }

    /**
     * Get icon name based on status
     */
    public function getIconName(): string
    {
        // If clickToOpenOnly is true, don't change icon on main button
        if ($this->clickToOpenOnly) {
            return match ($this->status) {
                'success' => 'phosphor.map-pin-area',
                'error' => 'phosphor.map-pin-simple',
                default => $this->iconName
            };
        }

        // For card buttons, show spinner if any user interaction triggered it
        return match ($this->status) {
            'getting' => ($this->isCardButtonTriggered || $this->shouldShowToast) ? 'phosphor.spinner' : $this->iconName,
            'success' => 'phosphor.map-pin-area',
            'error' => 'phosphor.map-pin-simple',
            default => $this->iconName
        };
    }

    /**
     * Check if location is recent (within last hour)
     */
    public function isLocationRecent(): bool
    {
        if (!$this->lastUpdated) return false;

        $updated = \Carbon\Carbon::createFromFormat('H:i', $this->lastUpdated);
        return $updated->diffInHours(now()) < 1;
    }

    /**
     * Get location accuracy status using geolocation service
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
