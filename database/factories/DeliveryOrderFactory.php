<?php

namespace Database\Factories;

use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryOrder>
 */
class DeliveryOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plannedDate = fake()->dateTimeBetween('-7 days', '+14 days');
        $plannedTime = fake()->time('H:i');

        // Realistic Indonesian addresses
        $indonesianCities = [
            'Jakarta' => [
                'addresses' => [
                    'Jl. Sudirman No. 123, Menteng, Jakarta Pusat',
                    'Jl. Thamrin No. 456, Tanah Abang, Jakarta Pusat',
                    'Jl. Kuningan Raya No. 789, Setiabudi, Jakarta Selatan',
                    'Jl. Kemang Raya No. 321, Mampang Prapatan, Jakarta Selatan',
                    'Jl. Puri Indah No. 654, Kembangan, Jakarta Barat',
                ],
                'coordinates' => [
                    'lat' => fake()->randomFloat(8, -6.3, -6.1),
                    'lng' => fake()->randomFloat(8, 106.7, 106.9)
                ]
            ],
            'Surabaya' => [
                'addresses' => [
                    'Jl. Diponegoro No. 567, Gubeng, Surabaya',
                    'Jl. Basuki Rahmat No. 890, Genteng, Surabaya',
                    'Jl. Ahmad Yani No. 234, Wonokromo, Surabaya',
                    'Jl. Raya Darmo No. 678, Wonokromo, Surabaya',
                ],
                'coordinates' => [
                    'lat' => fake()->randomFloat(8, -7.4, -7.1),
                    'lng' => fake()->randomFloat(8, 112.6, 112.9)
                ]
            ],
            'Bandung' => [
                'addresses' => [
                    'Jl. Asia Afrika No. 345, Bandung Wetan, Bandung',
                    'Jl. Dago No. 678, Coblong, Bandung',
                    'Jl. Setiabudi No. 912, Sukasari, Bandung',
                ],
                'coordinates' => [
                    'lat' => fake()->randomFloat(8, -6.95, -6.85),
                    'lng' => fake()->randomFloat(8, 107.5, 107.7)
                ]
            ]
        ];

        $senderCity = fake()->randomElement(array_keys($indonesianCities));
        $recipientCity = fake()->randomElement(array_keys($indonesianCities));

        $senderData = $indonesianCities[$senderCity];
        $recipientData = $indonesianCities[$recipientCity];

        return [
            'order_number' => $this->generateOrderNumber(),
            'status' => fake()->randomElement([
                DeliveryOrder::STATUS_DRAFT,
                DeliveryOrder::STATUS_VERIFIED,
                DeliveryOrder::STATUS_DISPATCHED,
                DeliveryOrder::STATUS_IN_TRANSIT,
                DeliveryOrder::STATUS_ARRIVED,
                DeliveryOrder::STATUS_COMPLETED,
                DeliveryOrder::STATUS_CANCELLED,
            ]),

            // Sender data
            'sender_name' => fake()->randomElement([
                'PT. ArteliaDev Indonesia',
                'CV. Teknologi Maju',
                'UD. Sumber Barokah',
                'PT. Global Logistics',
                'CV. Mandiri Sejahtera',
            ]),
            'sender_address' => fake()->randomElement($senderData['addresses']),
            'sender_phone' => fake()->randomElement([
                '021-' . fake()->randomNumber(8, true),
                '031-' . fake()->randomNumber(8, true),
                '022-' . fake()->randomNumber(8, true),
            ]),

            // Recipient data
            'recipient_name' => fake()->randomElement([
                'PT. Mitra Sejahtera',
                'CV. Berkah Jaya',
                'UD. Sukses Mandiri',
                'PT. Indo Makmur',
                'CV. Bintang Terang',
                'Toko Elektronik Jaya',
                'Supermarket Sumber Rezeki',
                'Restaurant Padang Sederhana',
            ]),
            'recipient_address' => fake()->randomElement($recipientData['addresses']),
            'recipient_phone' => fake()->randomElement([
                '08' . fake()->randomNumber(9, true),
                '08' . fake()->randomNumber(9, true),
                '021-' . fake()->randomNumber(8, true),
            ]),
            'recipient_pic' => fake()->name(),

            // Destination coordinates
            'destination_latitude' => $recipientData['coordinates']['lat'],
            'destination_longitude' => $recipientData['coordinates']['lng'],

            // User assignments (will be set by specific states)
            'created_by' => null,
            'verified_by' => null,
            'driver_id' => null,
            'completed_by' => null,

            // Timestamps
            'verified_at' => null,
            'dispatched_at' => null,
            'arrived_at' => null,
            'completed_at' => null,

            // Planning
            'planned_delivery_date' => $plannedDate,
            'planned_delivery_time' => $plannedTime,
            'estimated_distance' => fake()->randomFloat(2, 5, 500), // 5-500 km
            'actual_distance' => null,

            // Notes
            'notes' => fake()->optional(0.7)->sentence(),
            'delivery_notes' => null,
            'completion_notes' => null,

            // Discrepancy
            'has_discrepancy' => fake()->boolean(10), // 10% chance
            'discrepancy_notes' => null,

            // Document
            'physical_document_number' => null,
            'document_printed_at' => null,
        ];
    }

    /**
     * Generate realistic order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'DO';
        $date = fake()->dateTimeBetween('-30 days', 'now')->format('ymd');
        $random = strtoupper(fake()->lexify('????'));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Draft status
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeliveryOrder::STATUS_DRAFT,
            'verified_by' => null,
            'driver_id' => null,
            'verified_at' => null,
            'dispatched_at' => null,
            'arrived_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Verified status
     */
    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            $baseDate = Carbon::now()->subDays(rand(2, 7));

            return [
                'status' => DeliveryOrder::STATUS_VERIFIED,
                'verified_by' => User::factory()->petugasRuangan(),
                'verified_at' => $baseDate,
                'dispatched_at' => null,
                'arrived_at' => null,
                'completed_at' => null,
            ];
        });
    }

    /**
     * Dispatched status
     */
    public function dispatched(): static
    {
        return $this->state(function (array $attributes) {
            $baseDate = Carbon::now()->subDays(rand(1, 5));
            $verifiedAt = $baseDate->copy()->subHours(rand(2, 12));
            $dispatchedAt = $baseDate;

            return [
                'status' => DeliveryOrder::STATUS_DISPATCHED,
                'verified_by' => User::factory()->petugasRuangan(),
                'driver_id' => User::factory()->driver(),
                'verified_at' => $verifiedAt,
                'dispatched_at' => $dispatchedAt,
                'arrived_at' => null,
                'completed_at' => null,
            ];
        });
    }

    /**
     * In transit status
     */
    public function inTransit(): static
    {
        return $this->state(function (array $attributes) {
            $baseDate = Carbon::now()->subDays(rand(1, 3));
            $verifiedAt = $baseDate->copy()->subHours(rand(6, 24));
            $dispatchedAt = $baseDate->copy()->subHours(rand(2, 6));

            return [
                'status' => DeliveryOrder::STATUS_IN_TRANSIT,
                'verified_by' => User::factory()->petugasRuangan(),
                'driver_id' => User::factory()->driver(),
                'verified_at' => $verifiedAt,
                'dispatched_at' => $dispatchedAt,
                'arrived_at' => null,
                'completed_at' => null,
                'delivery_notes' => fake()->optional(0.5)->sentence(),
            ];
        });
    }

    /**
     * Arrived status
     */
    public function arrived(): static
    {
        return $this->state(function (array $attributes) {
            $baseDate = Carbon::now()->subHours(rand(1, 12));
            $dispatchedAt = $baseDate->copy()->subHours(rand(4, 12));
            $verifiedAt = $dispatchedAt->copy()->subHours(rand(2, 8));
            $arrivedAt = $baseDate;

            return [
                'status' => DeliveryOrder::STATUS_ARRIVED,
                'verified_by' => User::factory()->petugasRuangan(),
                'driver_id' => User::factory()->driver(),
                'verified_at' => $verifiedAt,
                'dispatched_at' => $dispatchedAt,
                'arrived_at' => $arrivedAt,
                'completed_at' => null,
                'delivery_notes' => fake()->sentence(),
                'actual_distance' => fake()->randomFloat(2, 5, 500),
            ];
        });
    }

    /**
     * Completed status
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $completedAt = Carbon::now()->subDays(rand(1, 7));
            $arrivedAt = $completedAt->copy()->subHours(rand(1, 6));
            $dispatchedAt = $arrivedAt->copy()->subHours(rand(4, 12));
            $verifiedAt = $dispatchedAt->copy()->subHours(rand(2, 8));
            $printedAt = $dispatchedAt->copy()->addHours(rand(1, 6));

            return [
                'status' => DeliveryOrder::STATUS_COMPLETED,
                'verified_by' => User::factory()->petugasRuangan(),
                'driver_id' => User::factory()->driver(),
                'completed_by' => User::factory()->petugasGudang(),
                'verified_at' => $verifiedAt,
                'dispatched_at' => $dispatchedAt,
                'arrived_at' => $arrivedAt,
                'completed_at' => $completedAt,
                'delivery_notes' => fake()->sentence(),
                'completion_notes' => fake()->optional(0.8)->sentence(),
                'actual_distance' => fake()->randomFloat(2, 5, 500),
                'physical_document_number' => 'SJ' . $completedAt->format('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'document_printed_at' => $printedAt,
            ];
        });
    }

    /**
     * Cancelled status
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $cancelledAt = Carbon::now()->subDays(rand(1, 10));
            $verifiedAt = null;

            // 50% chance order was verified before being cancelled
            if (fake()->boolean(50)) {
                $verifiedAt = $cancelledAt->copy()->subHours(rand(2, 24));
            }

            return [
                'status' => DeliveryOrder::STATUS_CANCELLED,
                'verified_by' => $verifiedAt ? User::factory()->petugasRuangan() : null,
                'verified_at' => $verifiedAt,
                'completion_notes' => 'Dibatalkan: ' . fake()->randomElement([
                    'Permintaan klien',
                    'Alamat tidak ditemukan',
                    'Barang tidak tersedia',
                    'Kondisi cuaca buruk',
                    'Kendala teknis',
                ]),
            ];
        });
    }

    /**
     * With discrepancy
     */
    public function withDiscrepancy(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_discrepancy' => true,
            'discrepancy_notes' => fake()->randomElement([
                'Kekurangan 2 unit barang',
                'Barang mengalami kerusakan ringan',
                'Kemasan rusak saat pengiriman',
                'Ukuran tidak sesuai pesanan',
                'Warna barang berbeda',
            ]),
        ]);
    }

    /**
     * Today's delivery
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'planned_delivery_date' => now()->toDateString(),
        ]);
    }

    /**
     * Overdue delivery
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'planned_delivery_date' => Carbon::now()->subDays(rand(1, 7))->toDateString(),
        ]);
    }

    /**
     * With specific creator
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
