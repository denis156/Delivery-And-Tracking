<?php

namespace Database\Factories;

use App\Models\StatusHistory;
use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusHistory>
 */
class StatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actionType = fake()->randomElement([
            StatusHistory::ACTION_CREATED,
            StatusHistory::ACTION_UPDATED,
            StatusHistory::ACTION_VERIFIED,
            StatusHistory::ACTION_ASSIGNED,
            StatusHistory::ACTION_DISPATCHED,
            StatusHistory::ACTION_LOCATION_UPDATED,
            StatusHistory::ACTION_MILESTONE_REACHED,
            StatusHistory::ACTION_ARRIVED,
            StatusHistory::ACTION_COMPLETED,
            StatusHistory::ACTION_CANCELLED,
            StatusHistory::ACTION_NOTE_ADDED,
        ]);

        return [
            'delivery_order_id' => DeliveryOrder::factory(),
            'user_id' => User::factory(),
            'user_role' => fake()->randomElement([
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_DRIVER,
                User::ROLE_PETUGAS_LAPANGAN,
                User::ROLE_PETUGAS_RUANGAN,
                User::ROLE_PETUGAS_GUDANG,
            ]),
            'status_from' => fake()->optional()->randomElement([
                DeliveryOrder::STATUS_DRAFT,
                DeliveryOrder::STATUS_VERIFIED,
                DeliveryOrder::STATUS_DISPATCHED,
                DeliveryOrder::STATUS_IN_TRANSIT,
                DeliveryOrder::STATUS_ARRIVED,
            ]),
            'status_to' => fake()->randomElement([
                DeliveryOrder::STATUS_VERIFIED,
                DeliveryOrder::STATUS_DISPATCHED,
                DeliveryOrder::STATUS_IN_TRANSIT,
                DeliveryOrder::STATUS_ARRIVED,
                DeliveryOrder::STATUS_COMPLETED,
                DeliveryOrder::STATUS_CANCELLED,
            ]),
            'action_type' => $actionType,
            'description' => $this->getDescriptionForAction($actionType),
            'changes' => fake()->optional(0.3)->randomElement([
                ['driver_id' => ['old' => null, 'new' => '5']],
                ['status' => ['old' => 'draft', 'new' => 'verified']],
                ['notes' => ['old' => null, 'new' => 'Catatan tambahan']],
            ]),
            'notes' => fake()->optional(0.4)->sentence(),
            'latitude' => fake()->optional(0.3)->randomFloat(8, -6.3, -6.1),
            'longitude' => fake()->optional(0.3)->randomFloat(8, 106.7, 106.9),
            'location_address' => fake()->optional(0.3)->address(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'device_type' => fake()->randomElement([
                StatusHistory::DEVICE_DESKTOP,
                StatusHistory::DEVICE_MOBILE,
                StatusHistory::DEVICE_TABLET,
                StatusHistory::DEVICE_UNKNOWN,
            ]),
            'reference_id' => fake()->optional(0.2)->randomNumber(5),
            'reference_type' => fake()->optional(0.2)->randomElement(['item', 'document', 'location']),
            'requires_notification' => fake()->boolean(30),
            'is_critical' => fake()->boolean(20),
            'is_visible_to_client' => fake()->boolean(70),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Get appropriate description for action type
     */
    private function getDescriptionForAction(string $actionType): string
    {
        return match($actionType) {
            StatusHistory::ACTION_CREATED => fake()->randomElement([
                'Order delivery dibuat oleh petugas lapangan',
                'Delivery order baru ditambahkan ke sistem',
                'Order baru dari klien telah diregistrasi',
            ]),
            StatusHistory::ACTION_UPDATED => fake()->randomElement([
                'Data delivery order diperbarui',
                'Informasi pengiriman telah direvisi',
                'Detail order mengalami perubahan',
            ]),
            StatusHistory::ACTION_VERIFIED => fake()->randomElement([
                'Order diverifikasi oleh petugas ruangan',
                'Verifikasi dokumen dan data telah selesai',
                'Order telah lolos tahap verifikasi',
            ]),
            StatusHistory::ACTION_ASSIGNED => fake()->randomElement([
                'Driver telah ditugaskan untuk pengiriman',
                'Penugasan driver ke delivery order',
                'Driver assignment berhasil dilakukan',
            ]),
            StatusHistory::ACTION_DISPATCHED => fake()->randomElement([
                'Driver memulai perjalanan pengiriman',
                'Order telah di-dispatch dari gudang',
                'Pengiriman dimulai oleh driver',
            ]),
            StatusHistory::ACTION_LOCATION_UPDATED => fake()->randomElement([
                'Lokasi GPS driver telah diperbarui',
                'Update posisi kendaraan pengiriman',
                'Tracking lokasi real-time aktif',
            ]),
            StatusHistory::ACTION_MILESTONE_REACHED => fake()->randomElement([
                'Driver mencapai checkpoint perjalanan',
                'Milestone penting dalam perjalanan tercapai',
                'Checkpoint tracking berhasil dilalui',
            ]),
            StatusHistory::ACTION_ARRIVED => fake()->randomElement([
                'Driver telah sampai di lokasi tujuan',
                'Kendaraan tiba di alamat pengiriman',
                'Arrival confirmation berhasil',
            ]),
            StatusHistory::ACTION_COMPLETED => fake()->randomElement([
                'Delivery order telah diselesaikan',
                'Pengiriman berhasil dikonfirmasi selesai',
                'Order completion oleh petugas gudang',
            ]),
            StatusHistory::ACTION_CANCELLED => fake()->randomElement([
                'Order dibatalkan karena permintaan klien',
                'Pembatalan delivery order',
                'Order cancelled due to technical issue',
            ]),
            StatusHistory::ACTION_NOTE_ADDED => fake()->randomElement([
                'Catatan tambahan ditambahkan ke order',
                'Note penting untuk pengiriman',
                'Keterangan khusus dari petugas',
            ]),
            default => 'Aktivitas sistem pada delivery order',
        };
    }

    /**
     * Order created history
     */
    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_CREATED,
            'status_from' => null,
            'status_to' => DeliveryOrder::STATUS_DRAFT,
            'description' => 'Order delivery dibuat oleh petugas lapangan',
            'user_role' => User::ROLE_PETUGAS_LAPANGAN,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first()?->id,
            'is_critical' => false,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Order verified history
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_VERIFIED,
            'status_from' => DeliveryOrder::STATUS_DRAFT,
            'status_to' => DeliveryOrder::STATUS_VERIFIED,
            'description' => 'Order diverifikasi dan siap untuk dispatch',
            'user_role' => User::ROLE_PETUGAS_RUANGAN,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_PETUGAS_RUANGAN)->first()?->id,
            'is_critical' => true,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Driver assigned history
     */
    public function driverAssigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_ASSIGNED,
            'description' => 'Driver telah ditugaskan untuk pengiriman',
            'user_role' => fake()->randomElement([
                User::ROLE_MANAGER,
                User::ROLE_PETUGAS_RUANGAN,
            ]),
            'user_id' => $attributes['user_id'] ?? User::whereIn('role', [User::ROLE_MANAGER, User::ROLE_PETUGAS_RUANGAN])->first()?->id,
            'changes' => ['driver_id' => ['old' => null, 'new' => fake()->randomNumber(2)]],
            'is_critical' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Order dispatched history
     */
    public function dispatched(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_DISPATCHED,
            'status_from' => DeliveryOrder::STATUS_VERIFIED,
            'status_to' => DeliveryOrder::STATUS_DISPATCHED,
            'description' => 'Driver memulai perjalanan pengiriman',
            'user_role' => User::ROLE_DRIVER,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_DRIVER)->first()?->id,
            'is_critical' => true,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Location update history
     */
    public function locationUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_LOCATION_UPDATED,
            'description' => 'Lokasi GPS driver telah diperbarui',
            'user_role' => User::ROLE_DRIVER,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_DRIVER)->first()?->id,
            'latitude' => fake()->randomFloat(8, -6.3, -6.1),
            'longitude' => fake()->randomFloat(8, 106.7, 106.9),
            'location_address' => fake()->address(),
            'is_critical' => false,
            'is_visible_to_client' => true,
            'requires_notification' => false,
        ]);
    }

    /**
     * Milestone reached history
     */
    public function milestoneReached(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_MILESTONE_REACHED,
            'description' => fake()->randomElement([
                'Driver mencapai checkpoint Rest Area',
                'Driver melewati Gerbang Tol',
                'Driver berhenti di SPBU',
                'Driver mencapai batas kota',
            ]),
            'user_role' => User::ROLE_DRIVER,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_DRIVER)->first()?->id,
            'latitude' => fake()->randomFloat(8, -6.3, -6.1),
            'longitude' => fake()->randomFloat(8, 106.7, 106.9),
            'location_address' => fake()->address(),
            'is_critical' => false,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Order arrived history
     */
    public function arrived(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_ARRIVED,
            'status_from' => DeliveryOrder::STATUS_IN_TRANSIT,
            'status_to' => DeliveryOrder::STATUS_ARRIVED,
            'description' => 'Driver telah sampai di lokasi tujuan',
            'user_role' => User::ROLE_DRIVER,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_DRIVER)->first()?->id,
            'latitude' => fake()->randomFloat(8, -6.3, -6.1),
            'longitude' => fake()->randomFloat(8, 106.7, 106.9),
            'location_address' => fake()->address(),
            'is_critical' => true,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Order completed history
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_COMPLETED,
            'status_from' => DeliveryOrder::STATUS_ARRIVED,
            'status_to' => DeliveryOrder::STATUS_COMPLETED,
            'description' => 'Delivery order telah diselesaikan',
            'user_role' => User::ROLE_PETUGAS_GUDANG,
            'user_id' => $attributes['user_id'] ?? User::where('role', User::ROLE_PETUGAS_GUDANG)->first()?->id,
            'is_critical' => true,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Order cancelled history
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => StatusHistory::ACTION_CANCELLED,
            'status_to' => DeliveryOrder::STATUS_CANCELLED,
            'description' => fake()->randomElement([
                'Order dibatalkan karena permintaan klien',
                'Pembatalan karena alamat tidak ditemukan',
                'Order cancelled - barang tidak tersedia',
                'Dibatalkan karena kondisi cuaca buruk',
            ]),
            'user_role' => fake()->randomElement([
                User::ROLE_MANAGER,
                User::ROLE_PETUGAS_RUANGAN,
                User::ROLE_CLIENT,
            ]),
            'user_id' => $attributes['user_id'] ?? User::whereIn('role', [User::ROLE_MANAGER, User::ROLE_PETUGAS_RUANGAN])->first()?->id,
            'is_critical' => true,
            'is_visible_to_client' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Critical action
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_critical' => true,
            'requires_notification' => true,
        ]);
    }

    /**
     * Client visible action
     */
    public function clientVisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible_to_client' => true,
        ]);
    }

    /**
     * Recent activity (last 24 hours)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'occurred_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Mobile device action
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => StatusHistory::DEVICE_MOBILE,
            'user_agent' => fake()->randomElement([
                'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)',
                'Mozilla/5.0 (Android 11; Mobile; rv:68.0)',
                'Mozilla/5.0 (Linux; Android 10; SM-G973F)',
            ]),
        ]);
    }

    /**
     * Desktop device action
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => StatusHistory::DEVICE_DESKTOP,
            'user_agent' => fake()->randomElement([
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            ]),
        ]);
    }

    /**
     * System generated action
     */
    public function systemGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'user_role' => 'system',
            'device_type' => StatusHistory::DEVICE_UNKNOWN,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System/1.0 (Automated)',
        ]);
    }

    /**
     * Emergency/urgent action
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_critical' => true,
            'requires_notification' => true,
            'description' => 'URGENT: ' . $attributes['description'],
            'notes' => 'Tindakan mendesak diperlukan',
        ]);
    }

    /**
     * Create complete history sequence for an order
     */
    public function createSequenceFor(DeliveryOrder $order): \Illuminate\Support\Collection
    {
        $histories = collect();
        $currentTime = $order->created_at;

        // 1. Created
        $histories->push(StatusHistory::factory()->created()->state([
            'delivery_order_id' => $order->id,
            'user_id' => $order->created_by,
            'occurred_at' => $currentTime,
        ])->create());

        // 2. Verified (if order is verified or beyond)
        if (in_array($order->status, [
            DeliveryOrder::STATUS_VERIFIED,
            DeliveryOrder::STATUS_DISPATCHED,
            DeliveryOrder::STATUS_IN_TRANSIT,
            DeliveryOrder::STATUS_ARRIVED,
            DeliveryOrder::STATUS_COMPLETED
        ])) {
            $currentTime = $currentTime->addMinutes(fake()->numberBetween(30, 180));
            $histories->push(StatusHistory::factory()->verified()->state([
                'delivery_order_id' => $order->id,
                'user_id' => $order->verified_by,
                'occurred_at' => $currentTime,
            ])->create());
        }

        // 3. Driver Assigned (if has driver)
        if ($order->driver_id) {
            $currentTime = $currentTime->addMinutes(fake()->numberBetween(15, 60));
            $histories->push(StatusHistory::factory()->driverAssigned()->state([
                'delivery_order_id' => $order->id,
                'occurred_at' => $currentTime,
            ])->create());
        }

        // 4. Dispatched (if dispatched or beyond)
        if (in_array($order->status, [
            DeliveryOrder::STATUS_DISPATCHED,
            DeliveryOrder::STATUS_IN_TRANSIT,
            DeliveryOrder::STATUS_ARRIVED,
            DeliveryOrder::STATUS_COMPLETED
        ])) {
            $currentTime = $currentTime->addMinutes(fake()->numberBetween(30, 120));
            $histories->push(StatusHistory::factory()->dispatched()->state([
                'delivery_order_id' => $order->id,
                'user_id' => $order->driver_id,
                'occurred_at' => $currentTime,
            ])->create());
        }

        // 5. Multiple location updates (if in transit or beyond)
        if (in_array($order->status, [
            DeliveryOrder::STATUS_IN_TRANSIT,
            DeliveryOrder::STATUS_ARRIVED,
            DeliveryOrder::STATUS_COMPLETED
        ])) {
            for ($i = 0; $i < fake()->numberBetween(2, 5); $i++) {
                $currentTime = $currentTime->addMinutes(fake()->numberBetween(15, 45));
                $histories->push(StatusHistory::factory()->locationUpdate()->state([
                    'delivery_order_id' => $order->id,
                    'user_id' => $order->driver_id,
                    'occurred_at' => $currentTime,
                ])->create());
            }
        }

        // 6. Milestone reached (random checkpoints)
        if (in_array($order->status, [
            DeliveryOrder::STATUS_IN_TRANSIT,
            DeliveryOrder::STATUS_ARRIVED,
            DeliveryOrder::STATUS_COMPLETED
        ])) {
            for ($i = 0; $i < fake()->numberBetween(1, 3); $i++) {
                $currentTime = $currentTime->addMinutes(fake()->numberBetween(30, 90));
                $histories->push(StatusHistory::factory()->milestoneReached()->state([
                    'delivery_order_id' => $order->id,
                    'user_id' => $order->driver_id,
                    'occurred_at' => $currentTime,
                ])->create());
            }
        }

        // 7. Arrived (if arrived or completed)
        if (in_array($order->status, [
            DeliveryOrder::STATUS_ARRIVED,
            DeliveryOrder::STATUS_COMPLETED
        ])) {
            $currentTime = $currentTime->addMinutes(fake()->numberBetween(60, 180));
            $histories->push(StatusHistory::factory()->arrived()->state([
                'delivery_order_id' => $order->id,
                'user_id' => $order->driver_id,
                'occurred_at' => $currentTime,
            ])->create());
        }

        // 8. Completed (if completed)
        if ($order->status === DeliveryOrder::STATUS_COMPLETED) {
            $currentTime = $currentTime->addMinutes(fake()->numberBetween(15, 60));
            $histories->push(StatusHistory::factory()->completed()->state([
                'delivery_order_id' => $order->id,
                'user_id' => $order->completed_by,
                'occurred_at' => $currentTime,
            ])->create());
        }

        // 9. Cancelled (if cancelled)
        if ($order->status === DeliveryOrder::STATUS_CANCELLED) {
            $currentTime = $currentTime->addMinutes(fake()->numberBetween(30, 300));
            $histories->push(StatusHistory::factory()->cancelled()->state([
                'delivery_order_id' => $order->id,
                'occurred_at' => $currentTime,
            ])->create());
        }

        return $histories;
    }
}
