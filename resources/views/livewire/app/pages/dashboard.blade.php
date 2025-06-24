<div>
    <!-- HEADER -->
    <x-header title="Dashboard" icon="phosphor.gauge-duotone" icon-classes="text-primary h-10" separator>
        <x-slot:subtitle>
            Sistem {{ config('app.name') }}
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari Surat Jalan, client, driver..." wire:model.live.debounce="search" clearable icon="phosphor.magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filter" @click="$wire.drawer = true" responsive icon="phosphor.funnel" class="btn-primary" />
            <x-button label="Buat Surat Jalan Baru" icon="phosphor.plus-circle" class="btn-success" responsive />
            <x-button label="Live Tracking" icon="phosphor.map-pin-simple-area" class="btn-neutral" responsive />
        </x-slot:actions>
    </x-header>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($stats as $stat)
        <x-stat
            title="{{ $stat['title'] }}"
            value="{{ number_format($stat['value']) }}"
            icon="{{ $stat['icon'] }}"
            color="{{ $stat['color'] }}"
            class="{{ $stat['bg'] }} shadow-md"
        />
        @endforeach
    </div>

    <!-- CHART SECTION WITH WORKING CHART -->
    <div class="my-8">
        <x-card title="Statistik Surat Jalan Mingguan" shadow separator>
            <x-slot:menu>
                <x-button icon="phosphor.dots-three-vertical" class="btn-circle btn-ghost btn-sm" />
            </x-slot:menu>

            <div class="h-80 p-4">
                <canvas id="deliveryChart" class="w-full h-full"></canvas>
            </div>
        </x-card>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- RECENT Surat Jalan -->
        <div class="lg:col-span-2">
            <x-card title="Surat Jalan Terbaru" shadow separator>
                <x-slot:menu>
                    <x-button icon="phosphor.dots-three-vertical" class="btn-circle btn-ghost btn-sm" />
                </x-slot:menu>

                <div class="space-y-4">
                    @forelse($recentDeliveries->take(6) as $delivery)
                    <div class="flex items-center justify-between p-4 bg-base-300 rounded-md shadow-md">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <x-badge value="{{ $delivery['order_number'] }}" class="badge-outline font-mono text-xs" />
                                <x-badge
                                    value="{{ $delivery['status_label'] }}"
                                    class="badge-{{ $delivery['status_color'] }} text-xs"
                                />
                                @if($delivery['has_discrepancy'])
                                    <x-badge value="Discrepancy" class="badge-error badge-outline text-xs" />
                                @endif
                            </div>
                            <h4 class="font-semibold text-base mb-1">{{ $delivery['client_name'] }}</h4>
                            <p class="text-sm text-gray-600 mb-1">
                                <x-icon name="phosphor.user" class="w-4 h-4 inline mr-1" />
                                Driver: {{ $delivery['driver_name'] }}
                                <x-icon name="phosphor.package" class="w-4 h-4 inline ml-3 mr-1" />
                                {{ $delivery['total_items'] }} items
                            </p>
                            <p class="text-sm text-gray-600 mb-1">
                                <x-icon name="phosphor.map-pin" class="w-4 h-4 inline mr-1" />
                                {{ Str::limit($delivery['delivery_address'], 60) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                PIC: {{ $delivery['recipient_pic'] }}
                            </p>
                        </div>
                        <div class="text-right text-sm ml-4">
                            <div class="text-gray-500 mb-1">
                                Dibuat: {{ \Carbon\Carbon::parse($delivery['created_at'])->format('H:i') }}
                            </div>
                            @if(isset($delivery['dispatched_at']))
                                <div class="text-blue-600 mb-1">
                                    Berangkat: {{ \Carbon\Carbon::parse($delivery['dispatched_at'])->format('H:i') }}
                                </div>
                            @endif
                            @if(isset($delivery['completed_at']))
                                <div class="text-green-600 mb-1">
                                    Selesai: {{ \Carbon\Carbon::parse($delivery['completed_at'])->format('H:i') }}
                                </div>
                            @endif
                            @if(isset($delivery['arrived_at']))
                                <div class="text-purple-600 mb-1">
                                    Tiba: {{ \Carbon\Carbon::parse($delivery['arrived_at'])->format('H:i') }}
                                </div>
                            @endif
                            <div class="mt-2 space-x-1">
                                <x-button icon="phosphor.eye" class="btn-ghost btn-xs" tooltip="Lihat Detail" />
                                <x-button icon="phosphor.printer" class="btn-ghost btn-xs" tooltip="Print Surat Jalan" />
                                @if($delivery['status'] === 'dispatched')
                                    <x-button icon="phosphor.map-pin" class="btn-ghost btn-xs text-primary" tooltip="Track" />
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <x-icon name="phosphor.package" class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500">Tidak ada Surat Jalan yang ditemukan</p>
                    </div>
                    @endforelse
                </div>

                <x-slot:actions>
                    <x-button label="Lihat Semua Surat Jalan" link="###" class="btn-primary btn-sm" />
                </x-slot:actions>
            </x-card>
        </div>

        <!-- ACTIVE TRUCKS -->
        <div>
            <x-card title="Truck Aktif" shadow separator>
                <x-slot:menu>
                    <x-button icon="phosphor.dots-three-vertical" class="btn-circle btn-ghost btn-sm" />
                </x-slot:menu>

                <div class="space-y-4">
                    @foreach($activeTrucks as $truck)
                    <div class="p-3 bg-base-300 rounded-md shadow-md">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold font-mono">{{ $truck['plate'] }}</span>
                            <x-badge
                                value="{{ $truck['status'] }}"
                                class="{{ $truck['status'] === 'Aktif' ? 'badge-success' : ($truck['status'] === 'Parkir' ? 'badge-warning' : 'badge-info') }} text-xs"
                            />
                        </div>
                        <p class="text-sm text-gray-600 mb-2">
                            <x-icon name="phosphor.user" class="w-4 h-4 inline mr-1" />
                            {{ $truck['driver'] }}
                        </p>
                        <p class="text-xs text-gray-500 mb-2">
                            <x-icon name="phosphor.map-pin" class="w-4 h-4 inline mr-1" />
                            {{ $truck['location'] }}
                        </p>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="flex items-center">
                                <x-icon name="phosphor.gauge" class="w-3 h-3 mr-1" />
                                {{ $truck['speed'] }}
                            </span>
                            <span class="flex items-center">
                                <x-icon name="phosphor.drop" class="w-3 h-3 mr-1" />
                                BBM: {{ $truck['fuel'] }}%
                            </span>
                        </div>

                        <!-- Fuel Progress Bar -->
                        <div class="mb-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full transition-all duration-300 {{ $truck['fuel'] > 50 ? 'bg-success' : ($truck['fuel'] > 25 ? 'bg-warning' : 'bg-error') }}"
                                    style="width: {{ $truck['fuel'] }}%"
                                ></div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-xs text-gray-400">
                            <span>{{ $truck['last_update'] }}</span>
                            <x-button icon="phosphor.eye" class="btn-ghost btn-xs" tooltip="Detail Truck" />
                        </div>
                    </div>
                    @endforeach
                </div>

                <x-slot:actions>
                    <x-button label="Live Tracking" link="###" class="btn-outline btn-sm w-full" />
                </x-slot:actions>
            </x-card>
        </div>
    </div>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Surat Jalan" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input
                label="Cari Surat Jalan"
                placeholder="Nomor Surat Jalan, client, driver, PIC..."
                wire:model.live.debounce="search"
                icon="phosphor.magnifying-glass"
                @keydown.enter="$wire.drawer = false"
            />

            <x-select
                label="Status"
                wire:model.live="statusFilter"
                :options="$statusOptions"
                icon="phosphor.funnel"
                placeholder="Pilih status..."
            />

            @if($search || $statusFilter)
            <div class="bg-base-200 p-3 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Filter Aktif:</p>
                @if($search)
                    <x-badge value="Pencarian: {{ $search }}" class="badge-primary badge-outline mr-2 mb-2" />
                @endif
                @if($statusFilter)
                    <x-badge value="Status: {{ collect($statusOptions)->firstWhere('id', $statusFilter)['name'] ?? $statusFilter }}" class="badge-secondary badge-outline" />
                @endif
            </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="phosphor.x-circle" wire:click="clear" spinner />
            <x-button label="Tutup" icon="phosphor.check-circle" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('deliveryChart').getContext('2d');

    // Data chart dari backend
    const chartData = @js($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Surat Jalan Dibuat',
                    data: [12, 19, 15, 25, 22, 18, 10],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                },
                {
                    label: 'Surat Jalan Selesai',
                    data: [10, 15, 13, 20, 18, 15, 8],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                },
                {
                    label: 'Surat Jalan Dengan Discrepancy',
                    data: [2, 4, 2, 5, 4, 3, 2],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(239, 68, 68)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Trend Surat Jalan 7 Hari Terakhir',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: 20
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        stepSize: 5
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            }
        }
    });
});
</script>
@endassets
