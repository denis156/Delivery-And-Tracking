<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DeliveryOrder;
use App\Models\StatusHistory;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('dashboard.stats', function ($app) {
            return new class {

                /**
                 * Get current driver status - hanya satu pengiriman aktif
                 */
                public function getTodayStats(int $driverId): array
                {
                    $cacheKey = "driver_current_stats_{$driverId}";

                    return Cache::remember($cacheKey, 300, function () use ($driverId) {
                        // Driver hanya bisa punya 1 order aktif pada satu waktu
                        $currentOrder = DeliveryOrder::where('driver_id', $driverId)
                            ->whereIn('status', [
                                DeliveryOrder::STATUS_VERIFIED,
                                DeliveryOrder::STATUS_DISPATCHED,
                                DeliveryOrder::STATUS_IN_TRANSIT,
                                DeliveryOrder::STATUS_ARRIVED
                            ])
                            ->first();

                        // Status berdasarkan order aktif saat ini
                        $activeOrders = $currentOrder ? 1 : 0;
                        $inTransit = ($currentOrder && in_array($currentOrder->status, [
                            DeliveryOrder::STATUS_DISPATCHED,
                            DeliveryOrder::STATUS_IN_TRANSIT
                        ])) ? 1 : 0;

                        $pending = ($currentOrder && $currentOrder->status === DeliveryOrder::STATUS_VERIFIED) ? 1 : 0;

                        // Total completed hari ini
                        $completed = DeliveryOrder::where('driver_id', $driverId)
                            ->whereDate('completed_at', now()->toDateString())
                            ->where('status', DeliveryOrder::STATUS_COMPLETED)
                            ->count();

                        return [
                            'active_orders' => $activeOrders,
                            'in_transit' => $inTransit,
                            'completed' => $completed,
                            'pending' => $pending,
                            'current_order' => $currentOrder
                        ];
                    });
                }

                /**
                 * Get recent activities untuk driver ini
                 */
                public function getRecentActivities(int $driverId, int $limit = 8): array
                {
                    $cacheKey = "driver_activities_{$driverId}";

                    return Cache::remember($cacheKey, 180, function () use ($driverId, $limit) {
                        return StatusHistory::whereHas('deliveryOrder', function ($query) use ($driverId) {
                            $query->where('driver_id', $driverId);
                        })
                            ->with('deliveryOrder:id,order_number,status')
                            ->whereIn('action_type', [
                                StatusHistory::ACTION_DISPATCHED,
                                StatusHistory::ACTION_LOCATION_UPDATED,
                                StatusHistory::ACTION_MILESTONE_REACHED,
                                StatusHistory::ACTION_ARRIVED,
                                StatusHistory::ACTION_COMPLETED,
                                StatusHistory::ACTION_VERIFIED
                            ])
                            ->orderBy('occurred_at', 'desc')
                            ->take($limit)
                            ->get()
                            ->map(function ($history) {
                                return [
                                    'id' => $history->id,
                                    'order_number' => $history->deliveryOrder->order_number,
                                    'action_type' => $history->action_type,
                                    'description' => $history->description,
                                    'time_ago' => $history->time_from_now,
                                    'color_class' => $history->color_class,
                                    'icon' => $history->icon,
                                    'is_recent' => $history->is_recent
                                ];
                            })
                            ->toArray();
                    });
                }

                /**
                 * Get current tasks - hanya untuk order yang sedang aktif
                 */
                public function getTodayTasks(int $driverId): array
                {
                    $cacheKey = "driver_current_tasks_{$driverId}";

                    return Cache::remember($cacheKey, 120, function () use ($driverId) {
                        $tasks = [];

                        // Ambil order aktif saat ini
                        $currentOrder = DeliveryOrder::where('driver_id', $driverId)
                            ->whereIn('status', [
                                DeliveryOrder::STATUS_VERIFIED,
                                DeliveryOrder::STATUS_DISPATCHED,
                                DeliveryOrder::STATUS_IN_TRANSIT,
                                DeliveryOrder::STATUS_ARRIVED
                            ])
                            ->first();

                        if (!$currentOrder) {
                            return $tasks; // Tidak ada tugas jika tidak ada order aktif
                        }

                        // Generate tasks berdasarkan status order saat ini
                        switch ($currentOrder->status) {
                            case DeliveryOrder::STATUS_VERIFIED:
                                $tasks[] = [
                                    'id' => 'confirm_' . $currentOrder->id,
                                    'order_id' => $currentOrder->id,
                                    'type' => 'confirm_receipt',
                                    'title' => "Konfirmasi penerimaan {$currentOrder->order_number}",
                                    'deadline' => $currentOrder->estimated_arrival?->format('H:i') . ' WIB' ?? 'Segera',
                                    'priority' => 'urgent',
                                    'completed' => false,
                                    'can_complete' => true
                                ];
                                break;

                            case DeliveryOrder::STATUS_DISPATCHED:
                            case DeliveryOrder::STATUS_IN_TRANSIT:
                                $tasks[] = [
                                    'id' => 'location_' . $currentOrder->id,
                                    'order_id' => $currentOrder->id,
                                    'type' => 'update_location',
                                    'title' => "Update lokasi pengiriman {$currentOrder->order_number}",
                                    'deadline' => $currentOrder->estimated_arrival?->format('H:i') . ' WIB' ?? 'Berkala',
                                    'priority' => 'normal',
                                    'completed' => false,
                                    'can_complete' => true
                                ];
                                break;

                            case DeliveryOrder::STATUS_ARRIVED:
                                $tasks[] = [
                                    'id' => 'deliver_' . $currentOrder->id,
                                    'order_id' => $currentOrder->id,
                                    'type' => 'mark_delivered',
                                    'title' => "Selesaikan pengiriman {$currentOrder->order_number}",
                                    'deadline' => 'Segera',
                                    'priority' => 'urgent',
                                    'completed' => false,
                                    'can_complete' => true
                                ];
                                break;
                        }

                        // Tambahkan tugas yang sudah selesai hari ini untuk display
                        $completedToday = DeliveryOrder::where('driver_id', $driverId)
                            ->whereDate('completed_at', now()->toDateString())
                            ->where('status', DeliveryOrder::STATUS_COMPLETED)
                            ->orderBy('completed_at', 'desc')
                            ->take(2)
                            ->get();

                        foreach ($completedToday as $order) {
                            $tasks[] = [
                                'id' => 'completed_' . $order->id,
                                'order_id' => $order->id,
                                'type' => 'completed',
                                'title' => "Pengiriman {$order->order_number} selesai",
                                'deadline' => "Selesai: " . $order->completed_at->format('H:i') . ' WIB',
                                'priority' => 'completed',
                                'completed' => true,
                                'can_complete' => false
                            ];
                        }

                        return $tasks;
                    });
                }

                /**
                 * Get driver performance stats - lifetime/overall stats
                 */
                public function getPerformanceStats(int $driverId): array
                {
                    $cacheKey = "driver_performance_{$driverId}";

                    return Cache::remember($cacheKey, 900, function () use ($driverId) {
                        $totalDeliveries = DeliveryOrder::where('driver_id', $driverId)
                            ->where('status', DeliveryOrder::STATUS_COMPLETED)
                            ->count();

                        if ($totalDeliveries === 0) {
                            return [
                                'on_time_percentage' => 0,
                                'delivery_success' => 0,
                                'total_deliveries' => 0,
                                'avg_distance' => 0
                            ];
                        }

                        // On-time delivery percentage
                        $onTimeDeliveries = DeliveryOrder::where('driver_id', $driverId)
                            ->where('status', DeliveryOrder::STATUS_COMPLETED)
                            ->whereRaw('completed_at <= CONCAT(planned_delivery_date, " ", TIME(COALESCE(planned_delivery_time, "17:00:00")))')
                            ->count();

                        $onTimePercentage = round(($onTimeDeliveries / $totalDeliveries) * 100);

                        // Success rate (completed vs cancelled)
                        $allOrders = DeliveryOrder::where('driver_id', $driverId)
                            ->whereIn('status', [
                                DeliveryOrder::STATUS_COMPLETED,
                                DeliveryOrder::STATUS_CANCELLED
                            ])
                            ->count();

                        $successRate = $allOrders > 0 ? round(($totalDeliveries / $allOrders) * 100) : 100;

                        // Average distance
                        $avgDistance = DeliveryOrder::where('driver_id', $driverId)
                            ->where('status', DeliveryOrder::STATUS_COMPLETED)
                            ->whereNotNull('actual_distance')
                            ->avg('actual_distance') ?? 0;

                        return [
                            'on_time_percentage' => min($onTimePercentage, 100),
                            'delivery_success' => min($successRate, 100),
                            'total_deliveries' => $totalDeliveries,
                            'avg_distance' => round($avgDistance, 1)
                        ];
                    });
                }

                /**
                 * Get working hours info
                 */
                public function getWorkingHours(): array
                {
                    $startTime = '08:00';
                    $endTime = '17:00';
                    $totalHours = 9; // 8 jam kerja + 1 jam istirahat

                    $currentTime = now();
                    $workStart = now()->setTimeFromTimeString($startTime);
                    $workEnd = now()->setTimeFromTimeString($endTime);

                    if ($currentTime->lt($workStart)) {
                        $progress = 0;
                        $status = 'Belum Mulai';
                    } elseif ($currentTime->gt($workEnd)) {
                        $progress = 100;
                        $status = 'Selesai';
                    } else {
                        $workedMinutes = $currentTime->diffInMinutes($workStart);
                        $totalMinutes = $workEnd->diffInMinutes($workStart);
                        $progress = round(($workedMinutes / $totalMinutes) * 100);
                        $status = 'Sedang Bekerja';
                    }

                    return [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'total_hours' => $totalHours,
                        'progress_percentage' => $progress,
                        'status' => $status,
                        'current_time' => $currentTime->format('H:i')
                    ];
                }

                /**
                 * Get weather info using de4a.space API and fallback options
                 * Now uses geolocation service for user location
                 */
                public function getWeatherInfo(?int $userId = null): array
                {
                    $location = $userId ?
                        app('geolocation')->getUserLocation($userId) :
                        app('geolocation')->getUserLocation(1);

                    $cacheKey = "weather_info_{$location['latitude']}_{$location['longitude']}";

                    return Cache::remember($cacheKey, 1800, function () use ($location) { // Cache 30 menit
                        // Try de4a.space API first - using correct endpoint
                        try {
                            $response = Http::timeout(15)
                                ->withHeaders([
                                    'User-Agent' => 'DeliveryTrackingApp/1.0',
                                    'Accept' => 'application/json'
                                ])
                                ->get('https://openapi.de4a.space/api/weather/forecast', [
                                    'lat' => (string) $location['latitude'],
                                    'long' => (string) $location['longitude']
                                ]);

                            if ($response->successful()) {
                                $data = $response->json();

                                // Check if response structure is correct
                                if (
                                    isset($data['status']) &&
                                    $data['status'] == 1 &&
                                    isset($data['data'][0]['weather'][0][0])
                                ) {
                                    $weatherData = $data['data'][0]['weather'][0][0];
                                    $locationData = $data['data'][0]['location'] ?? [];

                                    $temperature = $weatherData['t'] ?? 28;
                                    $weatherDesc = $weatherData['weather_desc'] ?? 'Cerah';
                                    $humidity = $weatherData['rh'] ?? 70; // Relative humidity
                                    $windSpeed = $weatherData['ws'] ?? 5; // Wind speed

                                    // Get location from API response or fallback
                                    $cityName = $this->formatLocationName($locationData, $location['city']);

                                    return [
                                        'temperature' => round($temperature),
                                        'condition' => $weatherDesc,
                                        'humidity' => round($humidity),
                                        'wind_speed' => round($windSpeed, 1),
                                        'location' => $cityName,
                                        'icon' => $this->mapWeatherCodeToIcon($weatherData['weather'] ?? 1),
                                        'last_updated' => now()->format('H:i'),
                                        'source' => 'de4a.space',
                                        'weather_code' => $weatherData['weather'] ?? 1
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('de4a.space Weather API failed', [
                                'error' => $e->getMessage(),
                                'location' => $location,
                                'response_status' => $response->status() ?? 'unknown'
                            ]);
                        }

                        // Fallback to Open-Meteo API
                        try {
                            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                                'timezone' => 'Asia/Makassar',
                                'forecast_days' => 1
                            ]);

                            if ($response->successful()) {
                                $data = $response->json();
                                $current = $data['current'] ?? [];

                                $temperature = round($current['temperature_2m'] ?? 28);
                                $humidity = round($current['relative_humidity_2m'] ?? 70);
                                $windSpeed = round($current['wind_speed_10m'] ?? 5, 1);
                                $weatherCode = $current['weather_code'] ?? 0;

                                return [
                                    'temperature' => $temperature,
                                    'condition' => $this->getWeatherCondition($weatherCode),
                                    'humidity' => $humidity,
                                    'wind_speed' => $windSpeed,
                                    'location' => $location['city'],
                                    'icon' => $this->getWeatherIcon($weatherCode),
                                    'last_updated' => now()->format('H:i'),
                                    'source' => 'open-meteo'
                                ];
                            }
                        } catch (\Exception $e) {
                            Log::warning('Open-Meteo Weather API failed', ['error' => $e->getMessage()]);
                        }

                        // Ultimate fallback data
                        return [
                            'temperature' => rand(26, 32),
                            'condition' => 'Umumnya Berawan',
                            'humidity' => rand(65, 80),
                            'wind_speed' => rand(3, 8),
                            'location' => $location['city'],
                            'icon' => 'cloud',
                            'last_updated' => now()->format('H:i'),
                            'source' => 'fallback'
                        ];
                    });
                }

                /**
                 * Format location name from de4a.space API response
                 */
                private function formatLocationName(array $locationData, string $fallback): string
                {
                    if (empty($locationData)) {
                        return $fallback;
                    }

                    $village = $locationData['village'] ?? '';
                    $subdistrict = $locationData['subdistrict'] ?? '';
                    $city = $locationData['city'] ?? '';
                    $province = $locationData['province'] ?? '';

                    // Build location string with available components
                    $locationParts = [];

                    if ($village) {
                        $locationParts[] = $village;
                    }

                    if ($subdistrict && $subdistrict !== $village) {
                        $locationParts[] = $subdistrict;
                    }

                    if ($city && $city !== $subdistrict) {
                        $locationParts[] = $city;
                    }

                    if ($province && $province !== $city) {
                        $locationParts[] = $province;
                    }

                    // Return formatted location string
                    if (!empty($locationParts)) {
                        return implode(', ', $locationParts);
                    }

                    return $fallback;
                }

                /**
                 * Map de4a.space weather code to phosphor icons
                 */
                private function mapWeatherCodeToIcon(int $weatherCode): string
                {
                    return match ($weatherCode) {
                        1 => 'sun', // Cerah/Sunny
                        2 => 'cloud-sun', // Cerah Berawan/Partly Cloudy
                        3 => 'cloud', // Berawan/Cloudy
                        4 => 'cloud', // Berawan Tebal/Overcast
                        60, 61, 62, 63 => 'cloud-fog', // Hujan ringan/Light rain
                        80, 81, 82 => 'cloud-rain', // Hujan/Rain
                        95, 96, 97 => 'cloud-lightning', // Badai petir/Thunderstorm
                        71, 73, 75 => 'cloud-snow', // Salju/Snow
                        45, 48 => 'cloud', // Kabut/Fog
                        default => 'cloud' // Default
                    };
                }

                /**
                 * Convert Open-Meteo weather code to Indonesian condition (fallback)
                 */
                private function getWeatherCondition(int $code): string
                {
                    return match (true) {
                        $code === 0 => 'Cerah',
                        $code >= 1 && $code <= 3 => 'Berawan Sebagian',
                        $code >= 45 && $code <= 48 => 'Berkabut',
                        $code >= 51 && $code <= 57 => 'Gerimis',
                        $code >= 61 && $code <= 67 => 'Hujan',
                        $code >= 71 && $code <= 77 => 'Salju',
                        $code >= 80 && $code <= 82 => 'Hujan Deras',
                        $code >= 95 && $code <= 99 => 'Badai Petir',
                        default => 'Umumnya Berawan'
                    };
                }

                /**
                 * Get weather icon name for Open-Meteo codes (fallback)
                 */
                private function getWeatherIcon(int $code): string
                {
                    return match (true) {
                        $code === 0 => 'sun',
                        $code >= 1 && $code <= 3 => 'cloud-sun',
                        $code >= 45 && $code <= 48 => 'cloud',
                        $code >= 51 && $code <= 57 => 'cloud-fog',
                        $code >= 61 && $code <= 67 => 'cloud-rain',
                        $code >= 71 && $code <= 77 => 'cloud-snow',
                        $code >= 80 && $code <= 82 => 'cloud-rain-heavy',
                        $code >= 95 && $code <= 99 => 'cloud-lightning',
                        default => 'cloud'
                    };
                }

                /**
                 * Clear all cache for driver
                 */
                public function clearDriverCache(int $driverId): void
                {
                    $cacheKeys = [
                        "driver_current_stats_{$driverId}",
                        "driver_activities_{$driverId}",
                        "driver_current_tasks_{$driverId}",
                        "driver_performance_{$driverId}",
                    ];

                    foreach ($cacheKeys as $key) {
                        Cache::forget($key);
                    }

                    // Also clear weather cache if user location changed
                    $location = app('geolocation')->getUserLocation($driverId);
                    Cache::forget("weather_info_{$location['latitude']}_{$location['longitude']}");
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
