<?php

namespace App\Livewire\Driver\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\DeliveryOrder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\TrackingLocation;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.driver')]
#[Title('Dashboard Driver')]
class Dashboard extends Component
{
    use Toast;

    public ?int $selectedOrderId = null;
    public float $currentLatitude = 0;
    public float $currentLongitude = 0;
    public string $locationAddress = '';
    public bool $locationPermissionGranted = false;
    public string $geolocationStatus = 'waiting'; // waiting, getting, success, error

    public function mount(): void
    {
        // Ensure user is driver - redirect if not
        if (!Auth::user()->isDriver()) {
            $this->redirect(route('app.dashboard'), navigate: true);
        }

        // Load any cached location data
        $this->loadCachedLocationData();
    }

    /**
     * Current driver statistics - hanya satu pengiriman aktif
     */
    #[Computed]
    public function todayStats(): array
    {
        return app('dashboard.stats')->getTodayStats(Auth::id());
    }

    /**
     * Recent activities using service provider
     */
    #[Computed]
    public function recentActivities(): array
    {
        return app('dashboard.stats')->getRecentActivities(Auth::id());
    }

    /**
     * Current tasks - hanya untuk order aktif saat ini
     */
    #[Computed]
    public function todayTasks(): array
    {
        return app('dashboard.stats')->getTodayTasks(Auth::id());
    }

    /**
     * Overall performance statistics
     */
    #[Computed]
    public function performanceStats(): array
    {
        return app('dashboard.stats')->getPerformanceStats(Auth::id());
    }

    /**
     * Working hours using service provider
     */
    #[Computed]
    public function workingHours(): array
    {
        return app('dashboard.stats')->getWorkingHours();
    }

    /**
     * Weather info with user location using new geolocation service
     */
    #[Computed]
    public function weatherInfo(): array
    {
        return app('dashboard.stats')->getWeatherInfo(Auth::id());
    }

    /**
     * Get location accuracy status from geolocation service
     */
    #[Computed]
    public function locationAccuracy(): array
    {
        return app('geolocation')->getLocationAccuracyStatus(Auth::id());
    }

    /**
     * Listen for global location updates from GeolocationButton component
     */
    #[On('location-updated')]
    public function handleGlobalLocationUpdate(array $locationData): void
    {
        $this->currentLatitude = $locationData['latitude'];
        $this->currentLongitude = $locationData['longitude'];
        $this->locationAddress = $locationData['address'] ?? '';
        $this->geolocationStatus = 'success';
        $this->locationPermissionGranted = true;

        // Clear weather cache to force refresh with new location
        unset($this->weatherInfo);

        Log::info('Dashboard received location update', [
            'user_id' => Auth::id(),
            'latitude' => $this->currentLatitude,
            'longitude' => $this->currentLongitude
        ]);

        // If there's a selected order waiting for location, update it
        if ($this->selectedOrderId) {
            $this->updateLocationForOrder();
        }
    }

    /**
     * Listen for location cleared event
     */
    #[On('location-cleared')]
    public function handleLocationCleared(): void
    {
        $this->currentLatitude = 0;
        $this->currentLongitude = 0;
        $this->locationAddress = '';
        $this->geolocationStatus = 'waiting';
        $this->locationPermissionGranted = false;

        // Clear weather cache to use default location
        unset($this->weatherInfo);
        unset($this->locationAccuracy);

        Log::info('Dashboard location cleared', ['user_id' => Auth::id()]);
    }

    /**
     * Event listeners for real-time updates
     */
    #[On('delivery-order-updated')]
    public function refreshOrderData($orderId = null): void
    {
        // Clear specific computed properties cache
        unset($this->todayStats);
        unset($this->todayTasks);
        unset($this->recentActivities);

        $this->success(
            'Data pengiriman telah diperbarui',
            position: 'toast-top toast-end',
            timeout: 3000
        );
    }

    #[On('task-completed')]
    public function refreshAfterTaskCompletion($message = 'Tugas berhasil diselesaikan'): void
    {
        // Clear all relevant caches
        unset($this->todayStats);
        unset($this->todayTasks);
        unset($this->recentActivities);
        unset($this->performanceStats);

        $this->success(
            $message,
            position: 'toast-top toast-end',
            timeout: 3000
        );
    }

    /**
     * Manual refresh method (for refresh button)
     */
    public function refreshData(): void
    {
        // Clear dashboard cache for current driver
        app('dashboard.stats')->clearDriverCache(Auth::id());

        // Clear computed properties
        unset($this->todayStats);
        unset($this->recentActivities);
        unset($this->todayTasks);
        unset($this->performanceStats);
        unset($this->weatherInfo);
        unset($this->locationAccuracy);

        $this->success(
            'Data berhasil diperbarui',
            position: 'toast-top toast-end',
            timeout: 2000
        );
    }

    /**
     * Task completion methods
     */
    public function markTaskComplete(int $orderId, string $taskType): void
    {
        try {
            $order = DeliveryOrder::where('id', $orderId)
                                 ->where('driver_id', Auth::id())
                                 ->first();

            if (!$order) {
                $this->error(
                    'Surat jalan tidak ditemukan',
                    position: 'toast-top toast-end',
                    timeout: 5000
                );
                return;
            }

            // For location update task, check if we have location data from GeolocationButton
            if ($taskType === 'update_location') {
                $this->selectedOrderId = $orderId;

                // Check if we have recent location data from GeolocationButton component
                if ($this->currentLatitude && $this->currentLongitude) {
                    $this->updateLocationForOrder();
                } else {
                    // Check if we have cached location data
                    $cachedLocation = app('geolocation')->getUserLocation(Auth::id());

                    if ($cachedLocation['latitude'] && $cachedLocation['longitude'] &&
                        !app('geolocation')->isLocationStale(Auth::id(), 30)) {

                        // Use cached location if it's not stale
                        $this->currentLatitude = $cachedLocation['latitude'];
                        $this->currentLongitude = $cachedLocation['longitude'];
                        $this->locationAddress = $cachedLocation['city'] ?? '';
                        $this->updateLocationForOrder();
                    } else {
                        // Request fresh location from GeolocationButton component
                        $this->dispatch('request-geolocation');

                        $this->info(
                            'Mohon izinkan akses lokasi untuk memperbarui posisi pengiriman',
                            position: 'toast-top toast-end',
                            timeout: 4000
                        );
                    }
                }
                return;
            }

            $this->processTaskCompletion($order, $taskType);

            // Clear cache and refresh data
            app('dashboard.stats')->clearDriverCache(Auth::id());
            unset($this->todayStats, $this->todayTasks, $this->recentActivities);

            // Dispatch event for other components
            $this->dispatch('task-completed', message: $this->getTaskCompletionMessage($taskType));

        } catch (\Exception $e) {
            Log::error('Task completion error', [
                'driver_id' => Auth::id(),
                'order_id' => $orderId,
                'task_type' => $taskType,
                'error' => $e->getMessage()
            ]);

            $this->error(
                'Terjadi kesalahan saat memproses tugas',
                position: 'toast-top toast-end',
                timeout: 5000
            );
        }
    }

    /**
     * Update location for selected order
     */
    public function updateLocationForOrder(): void
    {
        if (!$this->selectedOrderId || !$this->currentLatitude || !$this->currentLongitude) {
            $this->error('Data lokasi tidak lengkap');
            return;
        }

        try {
            $order = DeliveryOrder::find($this->selectedOrderId);

            if ($order && $order->driver_id === Auth::id()) {
                // Record tracking location
                $order->updateLocation(
                    $this->currentLatitude,
                    $this->currentLongitude,
                    $this->locationAddress ?: 'Lokasi driver diperbarui otomatis'
                );

                // Update order status if needed
                if ($order->status === DeliveryOrder::STATUS_DISPATCHED) {
                    $order->markAsInTransit($this->currentLatitude, $this->currentLongitude);
                }

                // Clear cache and refresh
                app('dashboard.stats')->clearDriverCache(Auth::id());
                unset($this->recentActivities, $this->todayTasks);

                // Reset selected order
                $this->selectedOrderId = null;

                // Dispatch events
                $this->dispatch('location-updated');
                $this->dispatch('task-completed', message: 'Lokasi berhasil diperbarui');

                $this->success(
                    'Lokasi pengiriman berhasil diperbarui',
                    position: 'toast-top toast-end',
                    timeout: 3000
                );
            }
        } catch (\Exception $e) {
            Log::error('Location update error', [
                'driver_id' => Auth::id(),
                'order_id' => $this->selectedOrderId,
                'error' => $e->getMessage()
            ]);

            $this->error('Gagal memperbarui lokasi');
        }
    }

    /**
     * Check if driver is near destination for any active order
     */
    public function checkProximityAlerts(): void
    {
        $geolocation = app('geolocation');

        // Get active orders for this driver
        $activeOrders = DeliveryOrder::where('driver_id', Auth::id())
            ->whereIn('status', [
                DeliveryOrder::STATUS_DISPATCHED,
                DeliveryOrder::STATUS_IN_TRANSIT
            ])
            ->get();

        foreach ($activeOrders as $order) {
            if ($order->destination_latitude && $order->destination_longitude) {
                $isNear = $geolocation->isUserNearLocation(
                    Auth::id(),
                    $order->destination_latitude,
                    $order->destination_longitude,
                    0.5 // 500 meters
                );

                if ($isNear && $order->status !== DeliveryOrder::STATUS_ARRIVED) {
                    // Auto-mark as arrived
                    $order->markAsArrived($this->currentLatitude, $this->currentLongitude);

                    $this->info(
                        "Anda telah sampai di tujuan pengiriman {$order->order_number}",
                        position: 'toast-top toast-end',
                        timeout: 5000
                    );

                    // Refresh data
                    unset($this->todayStats, $this->todayTasks, $this->recentActivities);
                }
            }
        }
    }

    /**
     * Auto-refresh methods for polling (silent updates)
     */
    public function pollActivities(): void
    {
        // Silent refresh for activities - no toast
        unset($this->recentActivities);
    }

    public function pollStats(): void
    {
        // Silent refresh for stats - no toast
        unset($this->todayStats);

        // Also check proximity alerts if we have location
        if ($this->currentLatitude && $this->currentLongitude) {
            $this->checkProximityAlerts();
        }
    }

    /**
     * Public method to trigger proximity check from JavaScript
     */
    public function triggerProximityCheck(): void
    {
        if ($this->currentLatitude && $this->currentLongitude) {
            $this->checkProximityAlerts();
        }
    }

    /**
     * Load cached location data on mount using new geolocation service
     */
    protected function loadCachedLocationData(): void
    {
        $location = app('geolocation')->getUserLocation(Auth::id());

        if ($location['latitude'] && $location['longitude']) {
            $this->currentLatitude = $location['latitude'];
            $this->currentLongitude = $location['longitude'];
            $this->locationAddress = $location['city'] ?? '';

            if ($location['last_updated']) {
                $this->geolocationStatus = 'success';
                $this->locationPermissionGranted = true;
            }
        }
    }

    /**
     * Get distance to destination for active order
     */
    public function getDistanceToDestination(): ?float
    {
        if (!$this->currentLatitude || !$this->currentLongitude) {
            return null;
        }

        $activeOrder = DeliveryOrder::where('driver_id', Auth::id())
            ->whereIn('status', [
                DeliveryOrder::STATUS_DISPATCHED,
                DeliveryOrder::STATUS_IN_TRANSIT,
                DeliveryOrder::STATUS_ARRIVED
            ])
            ->first();

        if (!$activeOrder || !$activeOrder->destination_latitude || !$activeOrder->destination_longitude) {
            return null;
        }

        return app('geolocation')->calculateDistance(
            $this->currentLatitude,
            $this->currentLongitude,
            $activeOrder->destination_latitude,
            $activeOrder->destination_longitude
        );
    }

    /**
     * Private helper methods
     */
    private function processTaskCompletion(DeliveryOrder $order, string $taskType): void
    {
        switch ($taskType) {
            case 'confirm_receipt':
                if ($order->status === DeliveryOrder::STATUS_VERIFIED) {
                    $order->dispatch(Auth::user());
                }
                break;

            case 'update_location':
                // This is now handled separately in updateLocationForOrder()
                break;

            case 'mark_delivered':
                if ($order->canBeCompleted()) {
                    $order->complete(Auth::user(), 'Pengiriman diselesaikan oleh driver');
                }
                break;

            default:
                throw new \InvalidArgumentException("Unknown task type: {$taskType}");
        }
    }

    private function getTaskCompletionMessage(string $taskType): string
    {
        return match($taskType) {
            'confirm_receipt' => 'Penerimaan barang dikonfirmasi, perjalanan dimulai',
            'update_location' => 'Lokasi berhasil diperbarui',
            'mark_delivered' => 'Pengiriman berhasil diselesaikan',
            default => 'Tugas berhasil diselesaikan'
        };
    }

    public function render()
    {
        return view('livewire.driver.pages.dashboard');
    }
}
