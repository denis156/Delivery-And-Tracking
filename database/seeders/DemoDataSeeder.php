<?php

namespace Database\Seeders;

use App\Models\DeliveryOrder;
use App\Models\Item;
use App\Models\User;
use App\Models\StatusHistory;
use App\Models\TrackingLocation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    private ConsoleOutput $output;
    private ProgressBar $progressBar;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->showHeader();
        $this->createDemoScenarios();
        $this->showSummary();
    }

    private function showHeader(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=blue>🎭 Creating Demo Scenarios...</fg=blue>');
    }

    private function createDemoScenarios(): void
    {
        $scenarios = [
            'createUrgentDelivery' => '🚨 Urgent electronics delivery',
            'createBulkFoodDelivery' => '🍱 Bulk food shipment with cold storage',
            'createFurnitureDelivery' => '🪑 Large furniture delivery',
            'createDelayedDelivery' => '⏰ Delayed delivery scenario',
            'createDamagedItemsScenario' => '💔 Damaged items scenario',
            'createMultiStopDelivery' => '🗺️ Multi-stop delivery route',
            'createHighValueDelivery' => '💎 High-value insured items',
            'createClientReturns' => '↩️ Client return scenario',
        ];

        $this->progressBar = new ProgressBar($this->output, count($scenarios));
        $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $this->progressBar->start();

        foreach ($scenarios as $method => $description) {
            $this->progressBar->setMessage($description);
            $this->$method();
            usleep(400000); // 0.4 second delay for each scenario
            $this->progressBar->advance();
        }

        $this->progressBar->finish();
        $this->output->writeln('');
    }

    private function createUrgentDelivery(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $driver = User::where('role', User::ROLE_DRIVER)->first();
        $verifier = User::where('role', User::ROLE_PETUGAS_RUANGAN)->first();

        $order = DeliveryOrder::factory()->inTransit()->create([
            'order_number' => 'DO' . date('ymd') . 'URGENT',
            'created_by' => $admin->id,
            'verified_by' => $verifier->id,
            'driver_id' => $driver->id,
            'sender_name' => 'ArteliaDev Tech Solutions',
            'recipient_name' => 'Hospital Siloam Kebon Jeruk',
            'recipient_address' => 'Jl. Perjuangan No.8, Kebon Jeruk, Jakarta Barat',
            'planned_delivery_date' => now()->toDateString(),
            'planned_delivery_time' => now()->addHours(2)->format('H:i'),
            'notes' => 'URGENT: Medical equipment delivery - Hospital priority',
        ]);

        // Add critical medical items
        Item::factory()->electronics()->create([
            'delivery_order_id' => $order->id,
            'name' => 'Ventilator Portable Medis',
            'description' => 'Ventilator darurat untuk ICU',
            'unit_value' => 85000000,
            'planned_quantity' => 2,
            'is_fragile' => true,
            'is_insured' => true,
            'status' => Item::STATUS_IN_TRANSIT,
            'notes' => 'CRITICAL - Handle with extreme care',
            'sort_order' => 1,
        ]);

        Item::factory()->electronics()->create([
            'delivery_order_id' => $order->id,
            'name' => 'Monitor Pasien Portable',
            'description' => 'Monitor vital signs pasien',
            'unit_value' => 45000000,
            'planned_quantity' => 3,
            'is_fragile' => true,
            'is_insured' => true,
            'status' => Item::STATUS_IN_TRANSIT,
            'sort_order' => 2,
        ]);

        // Create real-time tracking
        TrackingLocation::factory()->createRoute($order, $driver, 6);

        // Add urgent status history using factory approach
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        StatusHistory::factory()->create([
            'delivery_order_id' => $order->id,
            'user_id' => $admin->id,
            'user_role' => $admin->role,
            'action_type' => 'note_added',
            'status_from' => null,
            'status_to' => $order->status,
            'description' => 'PRIORITY URGENT: Medical emergency delivery approved',
            'is_critical' => true,
            'requires_notification' => true,
            'is_visible_to_client' => true,
            'occurred_at' => now()->subMinutes(30),
        ]);
    }

    private function createBulkFoodDelivery(): void
    {
        $creator = User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first();
        $driver = User::where('role', User::ROLE_DRIVER)->skip(1)->first();

        $order = DeliveryOrder::factory()->dispatched()->create([
            'order_number' => 'DO' . date('ymd') . 'FOOD',
            'created_by' => $creator->id,
            'driver_id' => $driver->id,
            'sender_name' => 'PT. Sumber Pangan Nusantara',
            'recipient_name' => 'Supermarket Ranch Market',
            'recipient_address' => 'Jl. Kemang Raya No.3, Jakarta Selatan',
            'planned_delivery_date' => now()->addDay()->toDateString(),
            'notes' => 'Cold storage delivery - maintain temperature below 4°C',
        ]);

        // Add various food items
        $foodItems = [
            ['name' => 'Daging Sapi Import Premium', 'quantity' => 150, 'value' => 180000],
            ['name' => 'Ikan Salmon Segar Norway', 'quantity' => 75, 'value' => 320000],
            ['name' => 'Sayuran Organik Mix', 'quantity' => 200, 'value' => 45000],
            ['name' => 'Susu UHT Premium 1L', 'quantity' => 500, 'value' => 25000],
            ['name' => 'Frozen Food Siap Saji', 'quantity' => 300, 'value' => 65000],
        ];

        foreach ($foodItems as $index => $itemData) {
            Item::factory()->food()->create([
                'delivery_order_id' => $order->id,
                'name' => $itemData['name'],
                'unit' => 'kg',
                'planned_quantity' => $itemData['quantity'],
                'unit_value' => $itemData['value'],
                'total_value' => $itemData['quantity'] * $itemData['value'],
                'requires_cold_storage' => true,
                'status' => Item::STATUS_LOADED,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createFurnitureDelivery(): void
    {
        $creator = User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first();
        $driver = User::where('role', User::ROLE_DRIVER)->skip(2)->first();
        $completer = User::where('role', User::ROLE_PETUGAS_GUDANG)->first();

        $order = DeliveryOrder::factory()->completed()->create([
            'order_number' => 'DO' . date('ymd') . 'FURN',
            'created_by' => $creator->id,
            'driver_id' => $driver->id,
            'completed_by' => $completer->id,
            'sender_name' => 'IKEA Indonesia',
            'recipient_name' => 'Kantor ArteliaDev',
            'recipient_address' => 'Jl. Sudirman No.123, Jakarta Pusat',
            'planned_delivery_date' => now()->subDays(2)->toDateString(),
            'completed_at' => now()->subDay(),
            'completion_notes' => 'Furniture assembly completed by recipient',
        ]);

        // Add furniture items
        $furnitureItems = [
            'Meja Kantor Standing Desk L-Shape',
            'Kursi Ergonomis Executive Premium',
            'Lemari Arsip 4 Laci dengan Kunci',
            'Sofa Kantor 3 Dudukan',
            'Rak Buku Industrial 5 Tingkat',
        ];

        foreach ($furnitureItems as $index => $itemName) {
            Item::factory()->furniture()->delivered()->create([
                'delivery_order_id' => $order->id,
                'name' => $itemName,
                'status' => Item::STATUS_DELIVERED,
                'condition_sent' => Item::CONDITION_GOOD,
                'condition_received' => Item::CONDITION_GOOD,
                'actual_quantity' => fake()->randomFloat(2, 1, 2),
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createDelayedDelivery(): void
    {
        $creator = User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first();

        $order = DeliveryOrder::factory()->overdue()->inTransit()->create([
            'order_number' => 'DO' . date('ymd') . 'LATE',
            'created_by' => $creator->id,
            'sender_name' => 'PT. Express Logistics',
            'recipient_name' => 'Toko Elektronik Megah',
            'recipient_address' => 'Jl. Mangga Besar No.45, Jakarta Pusat',
            'planned_delivery_date' => now()->subDays(3)->toDateString(),
            'planned_delivery_time' => '14:00',
            'notes' => 'Delayed due to traffic conditions and weather',
        ]);

        // Add delay status history using factory approach
        StatusHistory::factory()->create([
            'delivery_order_id' => $order->id,
            'user_id' => $order->driver_id ?? User::where('role', User::ROLE_DRIVER)->first()->id,
            'user_role' => User::ROLE_DRIVER,
            'action_type' => 'note_added',
            'status_from' => $order->status,
            'status_to' => $order->status,
            'description' => 'Delay notification: Terjebak macet akibat banjir di Jl. Gatot Subroto',
            'notes' => 'ETA diperpanjang 3 jam karena kondisi jalan',
            'is_critical' => true,
            'requires_notification' => true,
            'occurred_at' => now()->subHours(4),
        ]);

        Item::factory()->electronics()->count(3)->create([
            'delivery_order_id' => $order->id,
            'status' => Item::STATUS_IN_TRANSIT,
        ]);
    }

    private function createDamagedItemsScenario(): void
    {
        $creator = User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first();

        $order = DeliveryOrder::factory()->completed()->withDiscrepancy()->create([
            'order_number' => 'DO' . date('ymd') . 'DMG',
            'created_by' => $creator->id,
            'sender_name' => 'CV. Elektronik Prima',
            'recipient_name' => 'Kantor Cabang Surabaya',
            'recipient_address' => 'Jl. Basuki Rahmat No.67, Surabaya',
            'planned_delivery_date' => now()->subDays(1)->toDateString(),
            'has_discrepancy' => true,
            'discrepancy_notes' => 'Beberapa item mengalami kerusakan ringan akibat benturan',
            'completion_notes' => 'Delivery completed with noted damages',
        ]);

        // Create items with various damage levels
        Item::factory()->electronics()->damaged()->create([
            'delivery_order_id' => $order->id,
            'name' => 'Monitor LED 24" Samsung',
            'planned_quantity' => 5,
            'actual_quantity' => 5,
            'status' => Item::STATUS_DAMAGED,
            'condition_sent' => Item::CONDITION_GOOD,
            'condition_received' => Item::CONDITION_MINOR_DAMAGE,
            'damage_notes' => 'Goresan pada frame, layar masih berfungsi normal',
            'sort_order' => 1,
        ]);

        Item::factory()->electronics()->create([
            'delivery_order_id' => $order->id,
            'name' => 'Printer Laser HP LaserJet',
            'planned_quantity' => 2,
            'actual_quantity' => 1,
            'status' => Item::STATUS_DAMAGED,
            'condition_sent' => Item::CONDITION_GOOD,
            'condition_received' => Item::CONDITION_MAJOR_DAMAGE,
            'damage_notes' => '1 unit rusak total akibat jatuh, 1 unit dalam kondisi baik',
            'sort_order' => 2,
        ]);

        // Good condition items
        Item::factory()->electronics()->delivered()->count(3)->create([
            'delivery_order_id' => $order->id,
            'status' => Item::STATUS_DELIVERED,
            'condition_received' => Item::CONDITION_GOOD,
        ]);
    }

    private function createMultiStopDelivery(): void
    {
        $creator = User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first();
        $driver = User::where('role', User::ROLE_DRIVER)->skip(3)->first();

        $order = DeliveryOrder::factory()->inTransit()->create([
            'order_number' => 'DO' . date('ymd') . 'MULTI',
            'created_by' => $creator->id,
            'driver_id' => $driver->id,
            'sender_name' => 'Hub Distribution Center',
            'recipient_name' => 'Multiple Drop Points',
            'recipient_address' => 'Jakarta - Bogor - Depok Route',
            'planned_delivery_date' => now()->toDateString(),
            'notes' => 'Multi-stop delivery: 5 locations in Greater Jakarta area',
        ]);

        // Create detailed tracking route with multiple stops
        $stops = [
            ['type' => 'start', 'address' => 'Gudang Utama Cakung', 'lat' => -6.1950, 'lng' => 106.9650],
            ['type' => 'stop', 'address' => 'Toko A - Kelapa Gading', 'lat' => -6.1617, 'lng' => 106.9017],
            ['type' => 'stop', 'address' => 'Kantor B - Kuningan', 'lat' => -6.2297, 'lng' => 106.8291],
            ['type' => 'checkpoint', 'address' => 'Rest Area Cibubur', 'lat' => -6.3660, 'lng' => 106.8747],
            ['type' => 'stop', 'address' => 'Rumah C - Bogor', 'lat' => -6.5950, 'lng' => 106.8160],
            ['type' => 'destination', 'address' => 'Final Stop - Depok', 'lat' => -6.4025, 'lng' => 106.7942],
        ];

        $baseTime = now()->subHours(3);
        foreach ($stops as $index => $stop) {
            TrackingLocation::create([
                'delivery_order_id' => $order->id,
                'driver_id' => $driver->id,
                'latitude' => $stop['lat'],
                'longitude' => $stop['lng'],
                'address' => $stop['address'],
                'location_type' => $stop['type'],
                'is_milestone' => true,
                'notes' => "Stop #" . ($index + 1) . ": {$stop['address']}",
                'recorded_at' => $baseTime->copy()->addMinutes($index * 45),
                'speed' => $stop['type'] === 'stop' ? 0 : fake()->numberBetween(30, 60),
                'source' => TrackingLocation::SOURCE_WEB_GPS,
            ]);
        }

        Item::factory()->count(8)->create([
            'delivery_order_id' => $order->id,
            'status' => Item::STATUS_IN_TRANSIT,
        ]);
    }

    private function createHighValueDelivery(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $manager = User::where('role', User::ROLE_MANAGER)->first();

        $order = DeliveryOrder::factory()->verified()->create([
            'order_number' => 'DO' . date('ymd') . 'LUXURY',
            'created_by' => $admin->id,
            'verified_by' => $manager->id,
            'sender_name' => 'Jewelry & Watch Premium Store',
            'recipient_name' => 'VIP Customer - Mr. Budi Santoso',
            'recipient_address' => 'PIK Avenue Mall, Pantai Indah Kapuk',
            'planned_delivery_date' => now()->addDays(2)->toDateString(),
            'notes' => 'HIGH VALUE delivery - requires signature and ID verification',
        ]);

        // Add luxury items
        Item::factory()->highValue()->create([
            'delivery_order_id' => $order->id,
            'name' => 'Rolex Submariner Date 41mm',
            'description' => 'Swiss luxury watch with ceramic bezel',
            'category' => 'Luxury Goods',
            'unit' => 'pcs',
            'planned_quantity' => 1,
            'unit_value' => 180000000,
            'total_value' => 180000000,
            'is_insured' => true,
            'is_fragile' => true,
            'serial_number' => 'RLX2024001',
            'barcode' => '1234567890123',
            'notes' => 'Certificate of authenticity included',
            'sort_order' => 1,
        ]);

        Item::factory()->highValue()->create([
            'delivery_order_id' => $order->id,
            'name' => 'Diamond Ring 2.5 Carat',
            'description' => 'Solitaire diamond ring with platinum setting',
            'category' => 'Jewelry',
            'unit' => 'pcs',
            'planned_quantity' => 1,
            'unit_value' => 350000000,
            'total_value' => 350000000,
            'is_insured' => true,
            'is_fragile' => true,
            'serial_number' => 'DMD2024002',
            'notes' => 'GIA certificate included - handle with extreme care',
            'sort_order' => 2,
        ]);
    }

    private function createClientReturns(): void
    {
        $creator = User::where('role', User::ROLE_PETUGAS_LAPANGAN)->first();

        $order = DeliveryOrder::factory()->cancelled()->create([
            'order_number' => 'DO' . date('ymd') . 'RET',
            'created_by' => $creator->id,
            'sender_name' => 'PT. Fashion Central',
            'recipient_name' => 'Toko Pakaian Modern',
            'recipient_address' => 'Mall Taman Anggrek, Jakarta Barat',
            'planned_delivery_date' => now()->subDays(1)->toDateString(),
            'completion_notes' => 'Dibatalkan: Alamat tidak ditemukan setelah 3x percobaan',
        ]);

        // Add fashion items that were returned
        $fashionItems = [
            'Jaket Kulit Premium Import',
            'Dress Formal Wanita',
            'Sepatu High Heels Designer',
            'Tas Handbag Branded',
            'Celana Jeans Premium',
        ];

        foreach ($fashionItems as $index => $itemName) {
            Item::factory()->create([
                'delivery_order_id' => $order->id,
                'name' => $itemName,
                'category' => 'Fashion',
                'status' => Item::STATUS_RETURNED,
                'notes' => 'Returned to sender - delivery unsuccessful',
                'sort_order' => $index + 1,
            ]);
        }

        // Add return status history using factory approach
        StatusHistory::factory()->create([
            'delivery_order_id' => $order->id,
            'user_id' => $order->driver_id ?? User::where('role', User::ROLE_DRIVER)->first()->id,
            'user_role' => User::ROLE_DRIVER,
            'action_type' => 'returned',
            'status_from' => DeliveryOrder::STATUS_IN_TRANSIT,
            'status_to' => DeliveryOrder::STATUS_CANCELLED,
            'description' => 'Items returned to sender - recipient address not found',
            'notes' => 'Attempted delivery 3 times, building demolished',
            'is_critical' => true,
            'requires_notification' => true,
            'occurred_at' => now()->subHours(6),
        ]);
    }

    private function showSummary(): void
    {
        // Count demo scenarios created
        $urgentOrders = DeliveryOrder::where('notes', 'like', '%URGENT%')->count();
        $coldStorageOrders = DeliveryOrder::where('notes', 'like', '%Cold storage%')->count();
        $delayedOrders = DeliveryOrder::where('notes', 'like', '%Delayed%')->count();
        $highValueOrders = DeliveryOrder::where('notes', 'like', '%HIGH VALUE%')->count();
        $multiStopOrders = DeliveryOrder::where('notes', 'like', '%Multi-stop%')->count();

        $this->output->writeln('');
        $this->output->writeln('<fg=green>✅ Demo scenarios created successfully!</fg=green>');
        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>   🎭 Demo scenarios breakdown:</fg=cyan>');
        $this->output->writeln("   🚨 Urgent deliveries: <fg=red>{$urgentOrders}</fg=red>");
        $this->output->writeln("   🧊 Cold storage shipments: <fg=blue>{$coldStorageOrders}</fg=blue>");
        $this->output->writeln("   ⏰ Delayed deliveries: <fg=yellow>{$delayedOrders}</fg=yellow>");
        $this->output->writeln("   💎 High-value items: <fg=magenta>{$highValueOrders}</fg=magenta>");
        $this->output->writeln("   🗺️ Multi-stop routes: <fg=cyan>{$multiStopOrders}</fg=cyan>");

        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>   📊 Special data created:</fg=cyan>');
        $this->output->writeln('   • Medical equipment urgent delivery');
        $this->output->writeln('   • Bulk food shipment with temperature control');
        $this->output->writeln('   • Complete furniture office setup');
        $this->output->writeln('   • Weather-delayed shipment scenario');
        $this->output->writeln('   • Damaged items with insurance claim');
        $this->output->writeln('   • Multi-location delivery route');
        $this->output->writeln('   • Luxury goods with high security');
        $this->output->writeln('   • Client return and cancellation flow');

        $this->output->writeln('');
        $this->output->writeln('<fg=green>🎯 All demo scenarios ready for UI testing!</fg=green>');
        $this->output->writeln('<fg=magenta>Perfect for showcasing real-world delivery workflows!</fg=magenta>');
    }
}
