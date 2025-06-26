<?php

namespace App\Livewire\App\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

#[Title('Dashboard')]
#[Layout('livewire.layouts.app')]
class Dashboard extends Component
{
    use Toast;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'order_number', 'direction' => 'desc'];
    public string $statusFilter = '';

    // Method untuk refresh line chart secara manual
    public function refreshChart(): void
    {
        $this->dispatch('refreshChart', chartData: $this->getChartData());
        $this->dispatch('refreshDoughnutChart', chartData: $this->getDoughnutChartData());
        $this->success('Line Chart berhasil direfresh.', position: 'toast-top toast-end');
    }

    // Method untuk refresh doughnut chart secara manual
    public function refreshDoughnutChart(): void
    {
        $this->dispatch('refreshDoughnutChart', chartData: $this->getDoughnutChartData());
        $this->success('Doughnut chart berhasil direfresh.', position: 'toast-top toast-end');
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->success('Filter berhasil dibersihkan.', position: 'toast-top toast-end');

        // Refresh both charts after filter change
        $this->dispatch('refreshChart', chartData: $this->getChartData());
        $this->dispatch('refreshDoughnutChart', chartData: $this->getDoughnutChartData());
    }

    // Dashboard stats - updated sesuai dengan status di migration
    public function getStats(): array
    {
        return [
            [
                'title' => 'Total Surat Jalan',
                'value' => 156,
                'icon' => 'phosphor.receipt',
                'color' => 'text-primary',
                'bg' => 'bg-primary/20'
            ],
            [
                'title' => 'Dalam Perjalanan',
                'value' => 23,
                'icon' => 'phosphor.truck',
                'color' => 'text-warning',
                'bg' => 'bg-warning/20'
            ],
            [
                'title' => 'Selesai Hari Ini',
                'value' => 8,
                'icon' => 'phosphor.check-circle',
                'color' => 'text-success',
                'bg' => 'bg-success/20'
            ],
            [
                'title' => 'Ada Discrepancy',
                'value' => 2,
                'icon' => 'phosphor.warning-circle',
                'color' => 'text-error',
                'bg' => 'bg-error/20'
            ]
        ];
    }

    // Status options untuk filter
    public function getStatusOptions(): array
    {
        return [
            ['id' => '', 'name' => 'Semua Status'],
            ['id' => 'draft', 'name' => 'Draft'],
            ['id' => 'loading', 'name' => 'Loading'],
            ['id' => 'verified', 'name' => 'Verified'],
            ['id' => 'dispatched', 'name' => 'Dispatched'],
            ['id' => 'arrived', 'name' => 'Arrived'],
            ['id' => 'completed', 'name' => 'Completed'],
            ['id' => 'cancelled', 'name' => 'Cancelled']
        ];
    }

    // Recent Surat Jalan - sesuai dengan migration
    public function getRecentDeliveries(): Collection
    {
        return collect([
            [
                'id' => 1,
                'order_number' => 'SJ-2025-001',
                'barcode_do' => 'BC001234567890',
                'client_name' => 'PT. Maju Jaya',
                'delivery_address' => 'Jl. Raya Surabaya No. 123, Surabaya',
                'recipient_pic' => 'Budi Santoso',
                'driver_name' => 'Ahmad Zulkarnain',
                'status' => 'dispatched',
                'status_label' => 'Dalam Perjalanan',
                'status_color' => 'warning',
                'created_at' => '2025-06-25 08:30:00',
                'dispatched_at' => '2025-06-25 09:15:00',
                'has_discrepancy' => false,
                'total_items' => 25
            ],
            [
                'id' => 2,
                'order_number' => 'SJ-2025-002',
                'barcode_do' => 'BC001234567891',
                'client_name' => 'CV. Berkah Makmur',
                'delivery_address' => 'Jl. Asia Afrika No. 45, Bandung',
                'recipient_pic' => 'Siti Nurhaliza',
                'driver_name' => 'Joko Susilo',
                'status' => 'completed',
                'status_label' => 'Selesai',
                'status_color' => 'success',
                'created_at' => '2025-06-25 06:00:00',
                'completed_at' => '2025-06-25 14:30:00',
                'has_discrepancy' => false,
                'total_items' => 18
            ],
            [
                'id' => 3,
                'order_number' => 'SJ-2025-003',
                'barcode_do' => 'BC001234567892',
                'client_name' => 'UD. Sumber Rejeki',
                'delivery_address' => 'Jl. Malioboro No. 67, Yogyakarta',
                'recipient_pic' => 'Andi Wijaya',
                'driver_name' => 'Rudi Hartono',
                'status' => 'arrived',
                'status_label' => 'Tiba di Tujuan',
                'status_color' => 'info',
                'created_at' => '2025-06-25 07:15:00',
                'arrived_at' => '2025-06-25 15:45:00',
                'has_discrepancy' => true,
                'total_items' => 12
            ],
            [
                'id' => 4,
                'order_number' => 'SJ-2025-004',
                'barcode_do' => 'BC001234567893',
                'client_name' => 'PT. Karya Mandiri',
                'delivery_address' => 'Jl. Pemuda No. 89, Semarang',
                'recipient_pic' => 'Dewi Sartika',
                'driver_name' => 'Agus Prasetyo',
                'status' => 'verified',
                'status_label' => 'Terverifikasi',
                'status_color' => 'primary',
                'created_at' => '2025-06-25 10:00:00',
                'verified_at' => '2025-06-25 11:30:00',
                'has_discrepancy' => false,
                'total_items' => 8
            ],
            [
                'id' => 5,
                'order_number' => 'SJ-2025-005',
                'barcode_do' => 'BC001234567894',
                'client_name' => 'CV. Mitra Sejati',
                'delivery_address' => 'Jl. Sudirman No. 12, Solo',
                'recipient_pic' => 'Bambang Suryadi',
                'driver_name' => 'Hendra Gunawan',
                'status' => 'loading',
                'status_label' => 'Loading',
                'status_color' => 'warning',
                'created_at' => '2025-06-25 11:45:00',
                'loading_started_at' => '2025-06-25 12:15:00',
                'has_discrepancy' => false,
                'total_items' => 30
            ],
            [
                'id' => 6,
                'order_number' => 'SJ-2025-006',
                'barcode_do' => 'BC001234567895',
                'client_name' => 'PT. Sukses Makmur',
                'delivery_address' => 'Jl. Gatot Subroto No. 88, Jakarta',
                'recipient_pic' => 'Rianti Kartika',
                'driver_name' => 'Indra Permana',
                'status' => 'draft',
                'status_label' => 'Draft',
                'status_color' => 'neutral',
                'created_at' => '2025-06-25 13:20:00',
                'has_discrepancy' => false,
                'total_items' => 15
            ]
        ])
            ->sortBy([[...array_values($this->sortBy)]])
            ->when($this->search, function (Collection $collection) {
                return $collection->filter(function ($item) {
                    return str($item['order_number'])->contains($this->search, true) ||
                        str($item['client_name'])->contains($this->search, true) ||
                        str($item['driver_name'])->contains($this->search, true);
                });
            })
            ->when($this->statusFilter, function (Collection $collection) {
                return $collection->filter(fn($item) => $item['status'] === $this->statusFilter);
            });
    }

    // Active trucks
    public function getActiveTrucks(): Collection
    {
        return collect([
            [
                'id' => 'TRK-001',
                'plate' => 'B 1234 AB',
                'driver' => 'Ahmad Zulkarnain',
                'location' => 'Jl. Tol Surabaya KM 45',
                'speed' => '65 km/h',
                'fuel' => 75,
                'status' => 'Aktif',
                'last_update' => '2 menit lalu'
            ],
            [
                'id' => 'TRK-002',
                'plate' => 'D 5678 CD',
                'driver' => 'Joko Susilo',
                'location' => 'Terminal Bandung',
                'speed' => '0 km/h',
                'fuel' => 90,
                'status' => 'Parkir',
                'last_update' => '5 menit lalu'
            ],
            [
                'id' => 'TRK-003',
                'plate' => 'AB 9012 EF',
                'driver' => 'Rudi Hartono',
                'location' => 'Jl. Bypass Yogya KM 12',
                'speed' => '45 km/h',
                'fuel' => 40,
                'status' => 'Aktif',
                'last_update' => '1 menit lalu'
            ],
            [
                'id' => 'TRK-004',
                'plate' => 'F 7890 GH',
                'driver' => 'Agus Prasetyo',
                'location' => 'Rest Area Tol Semarang',
                'speed' => '0 km/h',
                'fuel' => 60,
                'status' => 'Istirahat',
                'last_update' => '10 menit lalu'
            ],
            [
                'id' => 'TRK-005',
                'plate' => 'DT 7890 FA',
                'driver' => 'Rinto Bara',
                'location' => 'Lalonona Konawe',
                'speed' => '0 km/h',
                'fuel' => 60,
                'status' => 'Istirahat',
                'last_update' => '10 menit lalu'
            ]
        ]);
    }

    // Chart data for line chart
    public function getChartData(): array
    {
        return [
            'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
            'datasets' => [
                [
                    'label' => 'Surat Jalan Dibuat',
                    'data' => [12, 19, 15, 25, 22, 18, 10],
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Surat Jalan Selesai',
                    'data' => [10, 15, 13, 20, 18, 15, 8],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Surat Jalan Dengan Discrepancy',
                    'data' => [2, 4, 2, 5, 4, 3, 2],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];
    }

    // Data untuk doughnut chart - Status distribution
    public function getDoughnutChartData(): array
    {
        // Ambil data dari recent deliveries dan hitung distribusi status
        $deliveries = $this->getRecentDeliveries();
        $statusCounts = [];

        foreach ($deliveries as $delivery) {
            $status = $delivery['status_label'];
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        // Default colors untuk setiap status
        $statusColors = [
            'Draft' => '#6B7280',           // Gray
            'Loading' => '#F59E0B',         // Amber
            'Terverifikasi' => '#3B82F6',   // Blue
            'Dalam Perjalanan' => '#F59E0B', // Amber
            'Tiba di Tujuan' => '#8B5CF6',  // Purple
            'Selesai' => '#10B981',         // Green
            'Dibatalkan' => '#EF4444'       // Red
        ];

        $labels = array_keys($statusCounts);
        $data = array_values($statusCounts);
        $colors = array_map(fn($label) => $statusColors[$label] ?? '#6B7280', $labels);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverBorderWidth' => 3,
                    'hoverBorderColor' => '#ffffff'
                ]
            ]
        ];
    }

    // Update method updated untuk refresh kedua chart
    public function updated($propertyName): void
    {
        // When search or statusFilter changes, refresh both charts
        if (in_array($propertyName, ['search', 'statusFilter'])) {
            $this->dispatch('refreshChart', chartData: $this->getChartData());
            $this->dispatch('refreshDoughnutChart', chartData: $this->getDoughnutChartData());
        }
    }

    public function render()
    {
        return view('livewire.app.pages.dashboard', [
            'stats' => $this->getStats(),
            'recentDeliveries' => $this->getRecentDeliveries(),
            'activeTrucks' => $this->getActiveTrucks(),
            'chartData' => $this->getChartData(),
            'doughnutChartData' => $this->getDoughnutChartData(),
            'statusOptions' => $this->getStatusOptions(),
            'sortBy' => $this->sortBy
        ]);
    }
}
