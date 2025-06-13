<?php

namespace Database\Factories;

use App\Models\TrackingLocation;
use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackingLocation>
 */
class TrackingLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Indonesian major cities coordinates
        $indonesianLocations = [
            'Jakarta' => [
                'lat' => fake()->randomFloat(8, -6.3, -6.1),
                'lng' => fake()->randomFloat(8, 106.7, 106.9),
                'addresses' => [
                    'Jl. Sudirman, Jakarta Pusat',
                    'Jl. Thamrin, Jakarta Pusat',
                    'Jl. Kuningan, Jakarta Selatan',
                    'Jl. Kemang Raya, Jakarta Selatan',
                    'Jl. Puri Indah, Jakarta Barat',
                    'Tol Jagorawi KM 15',
                    'Rest Area Cibubur',
                ]
            ],
            'Bogor' => [
                'lat' => fake()->randomFloat(8, -6.65, -6.55),
                'lng' => fake()->randomFloat(8, 106.75, 106.85),
                'addresses' => [
                    'Jl. Pajajaran, Bogor',
                    'Jl. Raya Bogor, Cibinong',
                    'Tol Jagorawi KM 35',
                    'SPBU Sentul City',
                ]
            ],
            'Bandung' => [
                'lat' => fake()->randomFloat(8, -6.95, -6.85),
                'lng' => fake()->randomFloat(8, 107.5, 107.7),
                'addresses' => [
                    'Jl. Asia Afrika, Bandung',
                    'Jl. Dago, Bandung',
                    'Tol Cipularang KM 95',
                    'Rest Area Padalarang',
                ]
            ],
        ];

        $location = fake()->randomElement($indonesianLocations);

        return [
            'delivery_order_id' => DeliveryOrder::factory(),
            'driver_id' => User::factory()->driver(),
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
            'accuracy' => fake()->randomFloat(2, 3, 15), // GPS accuracy in meters
            'altitude' => fake()->randomFloat(2, 50, 500), // Altitude in meters
            'speed' => fake()->randomFloat(2, 0, 80), // Speed in km/h
            'heading' => fake()->numberBetween(0, 359), // Compass direction
            'address' => fake()->randomElement($location['addresses']),
            'city' => fake()->randomElement(['Jakarta', 'Bogor', 'Bandung', 'Depok', 'Bekasi']),
            'district' => fake()->randomElement(['Menteng', 'Kemang', 'Senayan', 'Kelapa Gading', 'PIK']),
            'province' => fake()->randomElement(['DKI Jakarta', 'Jawa Barat', 'Banten']),
            'location_type' => TrackingLocation::TYPE_WAYPOINT,
            'is_milestone' => fake()->boolean(20), // 20% chance of milestone
            'notes' => fake()->optional(0.3)->sentence(),
            'distance_from_start' => null, // Will be calculated
            'distance_to_destination' => null, // Will be calculated
            'device_info' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'source' => fake()->randomElement([
                TrackingLocation::SOURCE_WEB_GPS,
                TrackingLocation::SOURCE_MANUAL,
                TrackingLocation::SOURCE_API,
            ]),
            'battery_level' => fake()->numberBetween(10, 100),
            'signal_strength' => fake()->numberBetween(1, 5),
            'recorded_at' => fake()->dateTimeBetween('-2 hours', 'now'),
        ];
    }

    /**
     * Start point location
     */
    public function startPoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => TrackingLocation::TYPE_START,
            'is_milestone' => true,
            'notes' => 'Mulai perjalanan dari gudang',
            'speed' => 0,
        ]);
    }

    /**
     * Destination point location
     */
    public function destination(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => TrackingLocation::TYPE_DESTINATION,
            'is_milestone' => true,
            'notes' => 'Sampai di tujuan',
            'speed' => 0,
        ]);
    }

    /**
     * Checkpoint location
     */
    public function checkpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => TrackingLocation::TYPE_CHECKPOINT,
            'is_milestone' => true,
            'notes' => fake()->randomElement([
                'Checkpoint Rest Area',
                'Checkpoint SPBU',
                'Checkpoint Gerbang Tol',
                'Checkpoint Terminal',
            ]),
            'speed' => fake()->randomFloat(2, 0, 20),
        ]);
    }

    /**
     * Stop location
     */
    public function stop(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => TrackingLocation::TYPE_STOP,
            'is_milestone' => true,
            'notes' => fake()->randomElement([
                'Berhenti makan siang',
                'Berhenti istirahat',
                'Berhenti isi bensin',
                'Berhenti sholat',
                'Berhenti perbaikan kendaraan',
            ]),
            'speed' => 0,
        ]);
    }

    /**
     * Moving vehicle
     */
    public function moving(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => TrackingLocation::TYPE_WAYPOINT,
            'speed' => fake()->randomFloat(2, 30, 70),
            'heading' => fake()->numberBetween(0, 359),
            'is_milestone' => false,
        ]);
    }

    /**
     * Highway location
     */
    public function highway(): static
    {
        return $this->state(function (array $attributes) {
            $highways = [
                'Tol Jakarta-Cikampek KM 25',
                'Tol Jagorawi KM 15',
                'Tol Cipularang KM 85',
                'Tol Jakarta Outer Ring Road',
                'Tol Tangerang-Merak KM 45',
            ];

            return [
                'address' => fake()->randomElement($highways),
                'speed' => fake()->randomFloat(2, 60, 90),
                'location_type' => TrackingLocation::TYPE_WAYPOINT,
            ];
        });
    }

    /**
     * City traffic location
     */
    public function cityTraffic(): static
    {
        return $this->state(fn (array $attributes) => [
            'speed' => fake()->randomFloat(2, 5, 25),
            'notes' => fake()->randomElement([
                'Macet ringan',
                'Lampu merah',
                'Jalan protokol',
                'Area padat penduduk',
            ]),
        ]);
    }

    /**
     * Recent location (within last hour)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * High accuracy GPS
     */
    public function highAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy' => fake()->randomFloat(2, 1, 5),
            'signal_strength' => fake()->numberBetween(4, 5),
            'source' => TrackingLocation::SOURCE_WEB_GPS,
        ]);
    }

    /**
     * Low battery device
     */
    public function lowBattery(): static
    {
        return $this->state(fn (array $attributes) => [
            'battery_level' => fake()->numberBetween(5, 20),
            'signal_strength' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Manual input location
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => TrackingLocation::SOURCE_MANUAL,
            'accuracy' => null,
            'battery_level' => null,
            'signal_strength' => null,
            'notes' => 'Input manual oleh driver',
        ]);
    }

    /**
     * Jakarta route points
     */
    public function jakartaRoute(): static
    {
        return $this->state(function (array $attributes) {
            $jakartaPoints = [
                ['lat' => -6.2088, 'lng' => 106.8456, 'address' => 'Monas, Jakarta Pusat'],
                ['lat' => -6.1944, 'lng' => 106.8229, 'address' => 'Bundaran HI, Jakarta Pusat'],
                ['lat' => -6.2297, 'lng' => 106.8291, 'address' => 'Blok M, Jakarta Selatan'],
                ['lat' => -6.1754, 'lng' => 106.8272, 'address' => 'Menteng, Jakarta Pusat'],
                ['lat' => -6.2615, 'lng' => 106.7837, 'address' => 'Senayan, Jakarta Selatan'],
            ];

            $point = fake()->randomElement($jakartaPoints);

            return [
                'latitude' => $point['lat'] + fake()->randomFloat(6, -0.01, 0.01),
                'longitude' => $point['lng'] + fake()->randomFloat(6, -0.01, 0.01),
                'address' => $point['address'],
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
            ];
        });
    }

    /**
     * Generate tracking route for a delivery order
     */
    public function createRoute(DeliveryOrder $deliveryOrder, User $driver, int $pointCount = 5): \Illuminate\Support\Collection
    {
        $locations = collect();
        $startTime = $deliveryOrder->dispatched_at ?? now()->subHours(2);

        for ($i = 0; $i < $pointCount; $i++) {
            $recordedAt = $startTime->copy()->addMinutes($i * 15); // Every 15 minutes

            $locationType = match($i) {
                0 => TrackingLocation::TYPE_START,
                $pointCount - 1 => TrackingLocation::TYPE_DESTINATION,
                default => fake()->randomElement([
                    TrackingLocation::TYPE_WAYPOINT,
                    TrackingLocation::TYPE_WAYPOINT,
                    TrackingLocation::TYPE_WAYPOINT,
                    TrackingLocation::TYPE_CHECKPOINT,
                    TrackingLocation::TYPE_STOP,
                ])
            };

            $location = TrackingLocation::factory()->state([
                'delivery_order_id' => $deliveryOrder->id,
                'driver_id' => $driver->id,
                'location_type' => $locationType,
                'is_milestone' => in_array($locationType, [
                    TrackingLocation::TYPE_START,
                    TrackingLocation::TYPE_DESTINATION,
                    TrackingLocation::TYPE_CHECKPOINT,
                    TrackingLocation::TYPE_STOP,
                ]),
                'recorded_at' => $recordedAt,
                'speed' => $locationType === TrackingLocation::TYPE_STOP ? 0 : fake()->randomFloat(2, 20, 70),
            ])->create();

            $locations->push($location);
        }

        return $locations;
    }
}
