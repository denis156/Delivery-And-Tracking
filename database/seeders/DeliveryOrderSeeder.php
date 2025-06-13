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

class DeliveryOrderSeeder extends Seeder
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
        $this->createDeliveryOrders();
        $this->showSummary();
    }

    private function showHeader(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=blue>📦 Creating Delivery Orders...</fg=blue>');
    }

    private function createDeliveryOrders(): void
    {
        $statusDistribution = [
            DeliveryOrder::STATUS_DRAFT => ['count' => 8, 'emoji' => '📝'],
            DeliveryOrder::STATUS_VERIFIED => ['count' => 6, 'emoji' => '✅'],
            DeliveryOrder::STATUS_DISPATCHED => ['count' => 5, 'emoji' => '🚚'],
            DeliveryOrder::STATUS_IN_TRANSIT => ['count' => 7, 'emoji' => '🛣️'],
            DeliveryOrder::STATUS_ARRIVED => ['count' => 4, 'emoji' => '📍'],
            DeliveryOrder::STATUS_COMPLETED => ['count' => 15, 'emoji' => '🎉'],
            DeliveryOrder::STATUS_CANCELLED => ['count' => 3, 'emoji' => '❌'],
        ];

        $totalOrders = array_sum(array_column($statusDistribution, 'count'));
        $this->progressBar = new ProgressBar($this->output, $totalOrders);
        $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $this->progressBar->start();

        // Get users for assignments
        $creators = User::whereIn('role', [User::ROLE_PETUGAS_LAPANGAN, User::ROLE_ADMIN])->get();
        $verifiers = User::where('role', User::ROLE_PETUGAS_RUANGAN)->get();
        $drivers = User::where('role', User::ROLE_DRIVER)->get();
        $completers = User::where('role', User::ROLE_PETUGAS_GUDANG)->get();

        foreach ($statusDistribution as $status => $config) {
            $statusLabel = DeliveryOrder::STATUSES[$status];

            for ($i = 0; $i < $config['count']; $i++) {
                $this->progressBar->setMessage("Creating {$config['emoji']} {$statusLabel} order #" . ($i + 1));

                // Create delivery order with appropriate status
                $order = $this->createOrderWithStatus($status, $creators, $verifiers, $drivers, $completers);

                // Add items to the order
                $this->createItemsForOrder($order);

                // Create status history
                StatusHistory::factory()->createSequenceFor($order);

                // Create tracking locations for active orders
                if (in_array($status, [
                    DeliveryOrder::STATUS_DISPATCHED,
                    DeliveryOrder::STATUS_IN_TRANSIT,
                    DeliveryOrder::STATUS_ARRIVED,
                    DeliveryOrder::STATUS_COMPLETED
                ]) && $order->driver) {
                    TrackingLocation::factory()->createRoute($order, $order->driver, rand(3, 8));
                }

                usleep(150000); // 0.15 second delay
                $this->progressBar->advance();
            }
        }

        $this->progressBar->finish();
        $this->output->writeln('');
    }

    private function createOrderWithStatus(
        string $status,
        $creators,
        $verifiers,
        $drivers,
        $completers
    ): DeliveryOrder {
        $factory = DeliveryOrder::factory();

        // Apply status-specific configuration
        $factory = match($status) {
            DeliveryOrder::STATUS_DRAFT => $factory->draft(),
            DeliveryOrder::STATUS_VERIFIED => $factory->verified(),
            DeliveryOrder::STATUS_DISPATCHED => $factory->dispatched(),
            DeliveryOrder::STATUS_IN_TRANSIT => $factory->inTransit(),
            DeliveryOrder::STATUS_ARRIVED => $factory->arrived(),
            DeliveryOrder::STATUS_COMPLETED => $factory->completed(),
            DeliveryOrder::STATUS_CANCELLED => $factory->cancelled(),
            default => $factory,
        };

        // Add some special scenarios
        if (rand(1, 10) <= 2) { // 20% chance
            $factory = $factory->withDiscrepancy();
        }

        if (rand(1, 10) <= 3) { // 30% chance
            $factory = rand(1, 2) === 1 ? $factory->today() : $factory->overdue();
        }

        return $factory->create([
            'created_by' => $creators->random()->id,
        ]);
    }

    private function createItemsForOrder(DeliveryOrder $order): void
    {
        $itemCount = rand(1, 6); // 1-6 items per order

        for ($i = 0; $i < $itemCount; $i++) {
            $factory = Item::factory();

            // Vary item types
            $factory = match(rand(1, 6)) {
                1 => $factory->electronics(),
                2 => $factory->food(),
                3 => $factory->furniture(),
                4 => $factory->automotive(),
                5 => $factory->officeSupplies(),
                default => $factory,
            };

            // Add special characteristics
            if (rand(1, 10) <= 2) { // 20% chance
                $factory = $factory->fragile();
            }

            if (rand(1, 10) <= 1) { // 10% chance
                $factory = $factory->highValue();
            }

            if (rand(1, 10) <= 3) { // 30% chance
                $factory = $factory->withSerial();
            }

            // Set status based on order status
            if ($order->status === DeliveryOrder::STATUS_COMPLETED) {
                if (rand(1, 10) <= 2) { // 20% chance of discrepancy
                    $factory = rand(1, 2) === 1 ? $factory->withDiscrepancy() : $factory->damaged();
                } else {
                    $factory = $factory->delivered();
                }
            }

            $factory->create(['delivery_order_id' => $order->id]);
        }
    }

    private function showSummary(): void
    {
        $totalOrders = DeliveryOrder::count();
        $totalItems = Item::count();
        $totalHistories = StatusHistory::count();
        $totalLocations = TrackingLocation::count();

        $this->output->writeln('');
        $this->output->writeln('<fg=green>✅ Delivery Orders created successfully!</fg=green>');
        $this->output->writeln("   📦 Total orders: <fg=yellow>{$totalOrders}</fg=yellow>");
        $this->output->writeln("   📋 Total items: <fg=yellow>{$totalItems}</fg=yellow>");
        $this->output->writeln("   📚 Status histories: <fg=yellow>{$totalHistories}</fg=yellow>");
        $this->output->writeln("   📍 Tracking points: <fg=yellow>{$totalLocations}</fg=yellow>");

        // Show breakdown by status
        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>   📊 Orders by status:</fg=cyan>');

        foreach (DeliveryOrder::STATUSES as $status => $label) {
            $count = DeliveryOrder::where('status', $status)->count();
            $emoji = match($status) {
                DeliveryOrder::STATUS_DRAFT => '📝',
                DeliveryOrder::STATUS_VERIFIED => '✅',
                DeliveryOrder::STATUS_DISPATCHED => '🚚',
                DeliveryOrder::STATUS_IN_TRANSIT => '🛣️',
                DeliveryOrder::STATUS_ARRIVED => '📍',
                DeliveryOrder::STATUS_COMPLETED => '🎉',
                DeliveryOrder::STATUS_CANCELLED => '❌',
                default => '📦',
            };

            if ($count > 0) {
                $this->output->writeln("   {$emoji} {$label}: <fg=yellow>{$count}</fg=yellow>");
            }
        }

        // Show special statistics
        $overdueCount = DeliveryOrder::overdue()->count();
        $todayCount = DeliveryOrder::today()->count();
        $discrepancyCount = DeliveryOrder::where('has_discrepancy', true)->count();
        $activeCount = DeliveryOrder::activeOrders()->count();

        $this->output->writeln('');
        $this->output->writeln('<fg=cyan>   🎯 Special metrics:</fg=cyan>');
        $this->output->writeln("   ⏰ Today's deliveries: <fg=yellow>{$todayCount}</fg=yellow>");
        $this->output->writeln("   🔄 Active orders: <fg=yellow>{$activeCount}</fg=yellow>");
        $this->output->writeln("   ⚠️ Overdue orders: <fg=red>{$overdueCount}</fg=red>");
        $this->output->writeln("   🔍 With discrepancy: <fg=yellow>{$discrepancyCount}</fg=yellow>");

        $this->output->writeln('');
        $this->output->writeln('<fg=magenta>📊 Complete delivery workflow data ready!</fg=magenta>');
    }
}
