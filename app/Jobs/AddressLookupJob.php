<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Class\Geolocation\WeatherService;
use App\Class\Geolocation\LocationRepository;
use App\Class\Geolocation\CoreLocationService;

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
     * Execute the job.
     */
    public function handle(
        WeatherService $weatherService,
        LocationRepository $locationRepository,
        CoreLocationService $coreService
    ): void {
        try {
            Log::info('Background address lookup started', [
                'user_id' => $this->userId,
                'coordinates' => [$this->lat, $this->lng],
                'wita_time' => $coreService->formatWitaTime(),
                'attempt' => $this->attempts()
            ]);

            $locationData = $weatherService->getLocationDataFromDe4aApi($this->lat, $this->lng);

            if ($locationData) {
                // Update location details with address and weather data
                $locationRepository->updateLocationDetails(
                    $this->userId,
                    $locationData['location'] ?? [],
                    $locationData['weather_data'] ?? null
                );

                Log::info('Background address lookup completed successfully', [
                    'user_id' => $this->userId,
                    'address' => $coreService->formatLocationName($locationData['location'] ?? []),
                    'province' => $locationData['location']['province'] ?? '',
                    'village' => $locationData['location']['village'] ?? '',
                    'wita_time' => $coreService->formatWitaTime(),
                    'has_weather' => isset($locationData['weather_data'])
                ]);

            } else {
                Log::warning('Background address lookup returned no data', [
                    'user_id' => $this->userId,
                    'coordinates' => [$this->lat, $this->lng],
                    'wita_time' => $coreService->formatWitaTime(),
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
                'wita_time' => $coreService->formatWitaTime(),
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
        $coreService = app(CoreLocationService::class);

        Log::error('AddressLookupJob failed permanently', [
            'user_id' => $this->userId,
            'coordinates' => [$this->lat, $this->lng],
            'error' => $exception->getMessage(),
            'wita_time' => $coreService->formatWitaTime(),
            'total_attempts' => $this->tries
        ]);
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
