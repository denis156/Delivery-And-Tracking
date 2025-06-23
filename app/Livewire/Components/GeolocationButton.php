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
     * Method yang dipanggil oleh wire:poll.1500ms untuk real-time tracking
     * Hanya berjalan ketika tracking aktif dan polling diaktifkan
     */
    public function updateLocation(): void
    {
        try {
            // PENTING: Double check - hanya update jika tracking benar-benar aktif
            if (!$this->isTracking) {
                // Log jika polling masih berjalan padahal tracking off
                Log::warning('GeolocationButton: Polling called but tracking is inactive', [
                    'user_id' => Auth::id(),
                    'tracking_state' => $this->isTracking,
                    'wita_time' => $this->formatWitaTime()
                ]);
                return;
            }

            // Verifikasi tracking state dari cache juga
            $cacheKey = "user_tracking_state_" . Auth::id();
            $cachedTrackingState = Cache::get($cacheKey, false);

            if (!$cachedTrackingState) {
                // Sync component state dengan cache state
                $this->isTracking = false;
                Log::info('GeolocationButton: Tracking state synced from cache (disabled)', [
                    'user_id' => Auth::id(),
                    'component_state' => $this->isTracking,
                    'cache_state' => $cachedTrackingState,
                    'wita_time' => $this->formatWitaTime()
                ]);
                return;
            }

            // Trigger browser geolocation tanpa loading state
            $this->dispatch('request-geolocation-silent');

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error in updateLocation', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);
        }
    }

    /**
     * Start tracking - dipanggil dari menu item
     * ENHANCED: Proper start location initialization
     */
    public function startTracking(): void
    {
        try {
            // STEP 1: Clear semua data lokasi dan tracking session lama
            app('geolocation')->clearUserLocation(Auth::id());

            // STEP 2: Set tracking state dengan timestamp
            app('geolocation')->setUserTrackingState(Auth::id(), true);

            // STEP 3: Update component state
            $this->isTracking = true;
            $this->status = 'getting';

            // STEP 4: Trigger immediate location request untuk set start location
            $this->dispatch('request-geolocation');

            if ($this->showToast) {
                $this->success('Live tracking dimulai - menentukan titik start...');
            }

            Log::info('GeolocationButton: Location tracking started - ready for start location', [
                'user_id' => Auth::id(),
                'wita_time' => $this->formatWitaTime(),
                'tracking_state_set' => true,
                'cache_cleared' => true
            ]);

            // Force component refresh untuk memulai polling
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error starting tracking', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);

            if ($this->showToast) {
                $this->error('Gagal memulai tracking');
            }
        }
    }

    /**
     * Stop tracking - dipanggil dari menu item
     * ENHANCED: Proper cleanup tracking session
     */
    public function stopTracking(): void
    {
        try {
            // STEP 1: Set tracking state off
            app('geolocation')->setUserTrackingState(Auth::id(), false);

            // STEP 2: Clear start location dan tracking session
            app('geolocation')->clearStartLocation(Auth::id());

            // STEP 3: Update component state
            $this->isTracking = false;
            $this->status = 'waiting';

            if ($this->showToast) {
                $this->info('Live tracking dihentikan');
            }

            Log::info('GeolocationButton: Location tracking stopped and session cleared', [
                'user_id' => Auth::id(),
                'wita_time' => $this->formatWitaTime(),
                'tracking_state_cleared' => true
            ]);

            // Force component refresh untuk menghentikan polling
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error stopping tracking', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);
        }
    }

    /**
     * Manual location request - dipanggil dari menu item
     */
    public function requestLocation(): void
    {
        try {
            $this->status = 'getting';
            $this->dispatch('request-geolocation');

            if ($this->showToast) {
                $this->info('Mengambil lokasi...');
            }

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error requesting location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);

            if ($this->showToast) {
                $this->error('Gagal mengambil lokasi');
            }
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
     * ENHANCED: Better start location detection dan feedback
     */
    public function handleLocationSuccess(float $lat, float $lng, ?string $accuracy = null): void
    {
        try {
            // Update component properties
            $this->latitude = $lat;
            $this->longitude = $lng;
            $this->status = 'success';
            $this->lastUpdated = $this->formatWitaTime(); // Use WITA time

            // Get session info before update untuk comparison
            $sessionInfoBefore = app('geolocation')->getTrackingSessionInfo(Auth::id());
            $hadStartLocationBefore = $sessionInfoBefore['has_start_location'];

            // Update location in cache with immediate processing
            app('geolocation')->updateUserLocationImmediate(Auth::id(), $lat, $lng);

            // Get session info after update untuk check perubahan
            $sessionInfoAfter = app('geolocation')->getTrackingSessionInfo(Auth::id());
            $hasStartLocationAfter = $sessionInfoAfter['has_start_location'];

            // Detect if this was the first location update yang set start location
            $justSetStartLocation = !$hadStartLocationBefore && $hasStartLocationAfter && $this->isTracking;

            $this->updateAddress();

            // Dispatch global event untuk real-time updates dengan info start location
            $this->dispatch('location-updated', [
                'latitude' => $lat,
                'longitude' => $lng,
                'address' => $this->address,
                'accuracy' => $accuracy,
                'timestamp' => $this->getWitaTime()->toISOString(),
                'wita_time' => $this->formatWitaTime(),
                'user_id' => Auth::id(),
                'timezone' => 'WITA',
                'is_start_location' => $justSetStartLocation,
                'tracking_session' => $sessionInfoAfter
            ]);

            // Show appropriate toast based on context
            if ($this->showToast) {
                if ($justSetStartLocation) {
                    $this->success('🎯 Titik start berhasil ditetapkan! Route akan dibuat dari lokasi ini.');
                } elseif ($this->status === 'getting') {
                    $this->success('Lokasi berhasil diperbarui');
                }
            }

            Log::info('GeolocationButton: Location updated with start location context', [
                'user_id' => Auth::id(),
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => $accuracy,
                'tracking_mode' => $this->isTracking ? 'active' : 'inactive',
                'just_set_start_location' => $justSetStartLocation,
                'had_start_before' => $hadStartLocationBefore,
                'has_start_after' => $hasStartLocationAfter,
                'session_id' => $sessionInfoAfter['session_id'],
                'wita_time' => $this->formatWitaTime()
            ]);

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error handling location success', [
                'user_id' => Auth::id(),
                'latitude' => $lat,
                'longitude' => $lng,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);

            if ($this->showToast) {
                $this->error('Gagal memproses lokasi');
            }
        }
    }

    /**
     * Handle location error from browser
     */
    public function handleLocationError(string $errorMessage): void
    {
        try {
            // Update status untuk manual requests
            if ($this->status === 'getting') {
                $this->status = 'error';

                if ($this->showToast) {
                    $this->warning('Gagal mengambil lokasi: ' . $errorMessage);
                }
            }

            Log::warning('GeolocationButton: Geolocation error', [
                'user_id' => Auth::id(),
                'error' => $errorMessage,
                'tracking_active' => $this->isTracking,
                'wita_time' => $this->formatWitaTime()
            ]);

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error handling location error', [
                'user_id' => Auth::id(),
                'original_error' => $errorMessage,
                'handling_error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);
        }
    }

    /**
     * Clear all location data - dipanggil dari menu item
     * ENHANCED: Proper cleanup dengan tracking session reset
     */
    public function clearLocation(): void
    {
        try {
            // STEP 1: Stop tracking jika sedang aktif
            if ($this->isTracking) {
                app('geolocation')->setUserTrackingState(Auth::id(), false);
            }

            // STEP 2: Clear all location data dan tracking session
            app('geolocation')->clearUserLocation(Auth::id());

            // STEP 3: Reset component state
            $this->status = 'waiting';
            $this->latitude = 0;
            $this->longitude = 0;
            $this->address = '';
            $this->lastUpdated = null;
            $this->isTracking = false;

            // STEP 4: Dispatch clear event
            $this->dispatch('location-cleared');

            if ($this->showToast) {
                $this->info('Data lokasi dan tracking session dihapus');
            }

            Log::info('GeolocationButton: Complete location and tracking reset', [
                'user_id' => Auth::id(),
                'wita_time' => $this->formatWitaTime(),
                'tracking_stopped' => true,
                'location_cleared' => true,
                'component_reset' => true
            ]);

            // Force component refresh untuk menghentikan polling
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error clearing location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);

            if ($this->showToast) {
                $this->error('Gagal menghapus data lokasi');
            }
        }
    }

    /**
     * Load cached location
     */
    protected function loadCachedLocation(): void
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error loading cached location', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);
        }
    }

    /**
     * Load tracking state from cache
     */
    protected function loadTrackingState(): void
    {
        try {
            $cacheKey = "user_tracking_state_" . Auth::id();
            $this->isTracking = Cache::get($cacheKey, false);

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error loading tracking state', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);

            // Fallback to false if error
            $this->isTracking = false;
        }
    }

    /**
     * Update address from coordinates
     */
    protected function updateAddress(): void
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error updating address', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime()
            ]);

            // Fallback address on error
            $this->address = 'Alamat tidak tersedia';
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
            Log::warning('GeolocationButton: Failed to parse location time', [
                'user_id' => Auth::id(),
                'last_updated' => $this->lastUpdated,
                'error' => $e->getMessage(),
                'wita_time' => $this->formatWitaTime()
            ]);
            return false;
        }
    }

    /**
     * Get tracking status text
     * ENHANCED: Include start location context
     */
    public function getTrackingStatusText(): string
    {
        if (!$this->isTracking) {
            return 'Tracking nonaktif';
        }

        try {
            $trackingSession = app('geolocation')->getTrackingSessionInfo(Auth::id());

            if ($trackingSession['has_start_location']) {
                if ($this->status === 'success' && $this->isLocationRecent()) {
                    return 'Live tracking aktif (start point terkunci)';
                }
                return 'Tracking aktif (start point terkunci)';
            } else {
                if ($this->status === 'getting') {
                    return 'Menunggu lokasi untuk set start point...';
                }
                return 'Tracking aktif (belum ada start point)';
            }
        } catch (\Exception $e) {
            Log::warning('GeolocationButton: Error getting tracking session info', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'wita_time' => $this->formatWitaTime()
            ]);
            return 'Tracking aktif';
        }
    }

    /**
     * Get tracking status color
     */
    public function getTrackingStatusColor(): string
    {
        if (!$this->isTracking) return 'text-base-content/60';

        try {
            $trackingSession = app('geolocation')->getTrackingSessionInfo(Auth::id());

            if ($trackingSession['has_start_location']) {
                return $this->isLocationRecent() ? 'text-success' : 'text-warning';
            } else {
                return 'text-warning'; // Kuning jika belum ada start point
            }
        } catch (\Exception $e) {
            return $this->isLocationRecent() ? 'text-success' : 'text-warning';
        }
    }

    /**
     * Get current WITA time for display
     */
    public function getCurrentWitaTime(): string
    {
        return $this->formatWitaTime();
    }

    /**
     * Get tracking session information for display
     */
    public function getTrackingSessionInfo(): array
    {
        try {
            return app('geolocation')->getTrackingSessionInfo(Auth::id());
        } catch (\Exception $e) {
            Log::error('GeolocationButton: Error getting tracking session info', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'wita_time' => $this->formatWitaTime()
            ]);

            return [
                'is_tracking' => $this->isTracking,
                'session_id' => null,
                'has_start_location' => false,
                'start_location' => null,
                'current_location' => [],
                'distance_from_start' => null,
                'tracking_start_time' => null,
                'timezone' => 'WITA'
            ];
        }
    }

    /**
     * Get distance from start point for display
     */
    public function getDistanceFromStart(): ?string
    {
        try {
            $distance = app('geolocation')->calculateDistanceFromStart(Auth::id());

            if (!$distance) return null;

            if ($distance < 1) {
                return round($distance * 1000) . ' m dari start';
            }

            return round($distance, 1) . ' km dari start';
        } catch (\Exception $e) {
            Log::warning('GeolocationButton: Error calculating distance from start', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'wita_time' => $this->formatWitaTime()
            ]);
            return null;
        }
    }

    /**
     * Check if ready for start location
     */
    public function isReadyForStartLocation(): bool
    {
        return $this->isTracking && !$this->getTrackingSessionInfo()['has_start_location'];
    }

    /**
     * Get start location readiness status for UI
     */
    public function getStartLocationStatus(): array
    {
        $sessionInfo = $this->getTrackingSessionInfo();

        if (!$this->isTracking) {
            return [
                'status' => 'inactive',
                'text' => 'Tracking nonaktif',
                'color' => 'text-base-content/60',
                'icon' => 'phosphor.map-pin'
            ];
        }

        if ($sessionInfo['has_start_location']) {
            return [
                'status' => 'ready',
                'text' => 'Start point terkunci',
                'color' => 'text-success',
                'icon' => 'phosphor.flag'
            ];
        }

        if ($this->status === 'getting') {
            return [
                'status' => 'waiting',
                'text' => 'Menunggu GPS...',
                'color' => 'text-warning',
                'icon' => 'phosphor.spinner'
            ];
        }

        return [
            'status' => 'pending',
            'text' => 'Siap set start point',
            'color' => 'text-info',
            'icon' => 'phosphor.crosshair'
        ];
    }

    public function render()
    {
        return view('livewire.components.geolocation-button');
    }
}
