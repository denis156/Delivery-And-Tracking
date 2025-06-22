<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AddressLookupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected float $lat;
    protected float $lng;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, float $lat, float $lng)
    {
        $this->userId = $userId;
        $this->lat = $lat;
        $this->lng = $lng;

        // Set queue connection and queue name for better management
        $this->onQueue('geolocation');
    }

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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Background address lookup started', [
                'user_id' => $this->userId,
                'coordinates' => [$this->lat, $this->lng],
                'wita_time' => $this->formatWitaTime(),
                'attempt' => $this->attempts()
            ]);

            $locationData = $this->getLocationDataFromDe4aApi($this->lat, $this->lng);

            if ($locationData) {
                $cacheKey = "user_location_{$this->userId}";
                $existing = Cache::get($cacheKey, []);

                // Update only address info, preserve coordinates and timestamp
                $updated = array_merge($existing, [
                    'city' => $this->formatLocationName($locationData['location'] ?? []),
                    'province' => $locationData['location']['province'] ?? '',
                    'village' => $locationData['location']['village'] ?? '',
                    'subdistrict' => $locationData['location']['subdistrict'] ?? '',
                    'weather_data' => $locationData['weather_data'] ?? null,
                    'address_updated' => $this->getWitaTime()->toISOString(),
                    'address_updated_wita' => $this->formatWitaTime(),
                    'timezone' => 'WITA'
                ]);

                // Cache for 1 hour instead of 30 minutes
                Cache::put($cacheKey, $updated, now()->addHours(1));

                Log::info('Background address lookup completed successfully', [
                    'user_id' => $this->userId,
                    'address' => $updated['city'],
                    'province' => $updated['province'],
                    'village' => $updated['village'],
                    'wita_time' => $this->formatWitaTime(),
                    'has_weather' => isset($updated['weather_data'])
                ]);

            } else {
                Log::warning('Background address lookup returned no data', [
                    'user_id' => $this->userId,
                    'coordinates' => [$this->lat, $this->lng],
                    'wita_time' => $this->formatWitaTime(),
                    'attempt' => $this->attempts()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Background address lookup failed', [
                'user_id' => $this->userId,
                'coordinates' => [$this->lat, $this->lng],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'wita_time' => $this->formatWitaTime(),
                'attempt' => $this->attempts()
            ]);

            // Re-throw exception to trigger retry mechanism
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AddressLookupJob failed permanently', [
            'user_id' => $this->userId,
            'coordinates' => [$this->lat, $this->lng],
            'error' => $exception->getMessage(),
            'wita_time' => $this->formatWitaTime(),
            'total_attempts' => $this->tries
        ]);
    }

    /**
     * Get comprehensive location and weather data from de4a.space API
     */
    private function getLocationDataFromDe4aApi(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(10) // Slightly longer timeout for background job
                ->retry(2, 1000) // Retry twice with 1 second delay
                ->withHeaders([
                    'User-Agent' => 'DeliveryTrackingApp/1.0',
                    'Accept' => 'application/json'
                ])
                ->get('https://openapi.de4a.space/api/weather/forecast', [
                    'lat' => (string) $lat,
                    'long' => (string) $lng
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (
                    isset($data['status']) &&
                    $data['status'] == 1 &&
                    isset($data['data'][0])
                ) {
                    $responseData = $data['data'][0];
                    $locationData = $responseData['location'] ?? [];
                    $weatherData = $responseData['weather'][0][0] ?? null;

                    return [
                        'location' => $locationData,
                        'weather_data' => $weatherData ? [
                            'temperature' => $weatherData['t'] ?? 28,
                            'condition' => $weatherData['weather_desc'] ?? 'Cerah',
                            'humidity' => $weatherData['hu'] ?? 70,
                            'wind_speed' => $weatherData['ws'] ?? 5,
                            'weather_code' => $weatherData['weather'] ?? 1,
                            'icon' => $this->mapWeatherCodeToIcon($weatherData['weather'] ?? 1),
                            'datetime' => $weatherData['local_datetime'] ?? $this->getWitaTime()->toISOString(),
                            'datetime_wita' => $this->formatWitaTime(),
                            'source' => 'de4a.space',
                            'fetched_at_wita' => $this->formatWitaTime(),
                            'timezone' => 'WITA'
                        ] : null
                    ];
                }
            }

            Log::warning('de4a.space API returned unsuccessful response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'lat' => $lat,
                'lng' => $lng,
                'wita_time' => $this->formatWitaTime()
            ]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::warning('de4a.space API HTTP request failed', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lng' => $lng,
                'wita_time' => $this->formatWitaTime(),
                'timeout' => true
            ]);
        } catch (\Exception $e) {
            Log::error('de4a.space API unexpected error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'lat' => $lat,
                'lng' => $lng,
                'wita_time' => $this->formatWitaTime()
            ]);
        }

        return null;
    }

    /**
     * Format location name with improved logic
     */
    private function formatLocationName(array $locationData): string
    {
        if (empty($locationData)) {
            return 'Alamat tidak tersedia';
        }

        $village = trim($locationData['village'] ?? '');
        $subdistrict = trim($locationData['subdistrict'] ?? '');
        $city = trim($locationData['city'] ?? '');
        $province = trim($locationData['province'] ?? '');

        $locationParts = [];

        // Build location hierarchy
        if (!empty($village)) {
            $locationParts[] = $village;
        }

        if (!empty($subdistrict) && $subdistrict !== $village) {
            $locationParts[] = $subdistrict;
        }

        if (!empty($city) && $city !== $subdistrict) {
            $locationParts[] = $city;
        }

        if (!empty($province) && $province !== $city) {
            $locationParts[] = $province;
        }

        // Limit to maximum 4 parts to avoid too long address
        $locationParts = array_slice($locationParts, 0, 4);

        return !empty($locationParts) ? implode(', ', $locationParts) : 'Alamat tidak tersedia';
    }

    /**
     * Map weather code to phosphor icons
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
     * Calculate retry delay in seconds
     */
    public function retryAfter(): int
    {
        // Exponential backoff: 10s, 20s, 40s
        return 10 * pow(2, $this->attempts() - 1);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'geolocation',
            'address-lookup',
            "user:{$this->userId}",
            'background-processing'
        ];
    }
}
