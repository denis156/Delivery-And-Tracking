<div class="mx-auto max-w-4xl">
    <!-- Background Capsule Effect -->
    <div class="absolute top-0 inset-x-0 h-90 bg-primary/80 rounded-b-3xl"></div>

    <!-- Container wrapper -->
    <div class="p-4 space-y-4 relative z-10">
        <!-- Welcome Section -->
        <div class="space-y-4">
            <x-card title="Selamat Datang Kembali, {{ Auth::user()->name }}!"
                    subtitle="Berikut ringkasan aktivitas hari ini"
                    class="bg-base-300 backdrop-blur-sm border-0 shadow-lg">

                <!-- Weather & Time Info -->
                <div class="grid grid-cols-2 gap-1" wire:poll.60s>
                    <x-card title="Cuaca Hari Ini" class="bg-base-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-4xl font-bold text-info">{{ $this->weatherInfo['temperature'] }}°C</p>
                                <p class="text-sm text-info/80">{{ $this->weatherInfo['condition'] }}</p>
                                <p class="text-xs text-info/60">{{ $this->weatherInfo['location'] }}</p>
                                <p class="text-xs text-info/50 mt-1">
                                    Update: {{ $this->weatherInfo['last_updated'] }}
                                    @if($this->weatherInfo['source'] ?? null)
                                        <span class="ml-1 opacity-50">({{ ucfirst($this->weatherInfo['source']) }})</span>
                                    @endif
                                </p>

                                {{-- Location Status Indicator using new geolocation service --}}
                                @php
                                    $locationAccuracy = $this->locationAccuracy;
                                @endphp

                                @if($geolocationStatus === 'success' && $currentLatitude && $currentLongitude)
                                    <div class="flex items-center gap-1 mt-1">
                                        <x-icon name="phosphor.map-pin-area" class="h-3 w-3 text-{{ $locationAccuracy['color'] }}" />
                                        <span class="text-xs text-{{ $locationAccuracy['color'] }}">{{ $locationAccuracy['label'] }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 mt-1">
                                        <x-icon name="phosphor.map-pin" class="h-3 w-3 text-base-content/40" />
                                        <span class="text-xs text-base-content/40">Lokasi default</span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <x-icon name="phosphor.{{ $this->weatherInfo['icon'] }}" class="h-12 text-info" />
                                <p class="text-xs text-info/60 mt-1">{{ $this->weatherInfo['humidity'] }}% RH</p>
                                <p class="text-xs text-info/60">{{ $this->weatherInfo['wind_speed'] }} km/h</p>
                            </div>
                        </div>
                    </x-card>

                    <x-card title="Status Kerja" class="bg-base-300">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-primary/70">Status:</span>
                                <span class="text-sm font-medium text-primary/90">{{ $this->workingHours['status'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-primary/70">Jam Kerja:</span>
                                <span class="text-sm font-medium text-primary/90">{{ $this->workingHours['start_time'] }} - {{ $this->workingHours['end_time'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-primary/70">Waktu Sekarang:</span>
                                <span class="text-sm font-medium text-primary/90">{{ $this->workingHours['current_time'] }} WIB</span>
                            </div>
                            <x-progress value="{{ $this->workingHours['progress_percentage'] }}" max="100" class="progress-success" />
                            <p class="text-xs text-primary/70">{{ $this->workingHours['progress_percentage'] }}% hari kerja selesai</p>
                        </div>
                    </x-card>
                </div>

                <!-- Statistics with background polling -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4" wire:poll.30s.keep-alive="pollStats">
                    <x-stat title="Surat Jalan"
                            description="Hari ini"
                            value="{{ $this->todayStats['active_orders'] }}"
                            icon="phosphor.file-text"
                            class="bg-primary/20 border border-primary text-warp"
                            color="text-primary" />

                    <x-stat title="Perjalanan"
                            description="Di kirim"
                            value="{{ $this->todayStats['in_transit'] }}"
                            icon="phosphor.truck"
                            class="bg-warning/20 border border-warning"
                            color="text-warning" />

                    <x-stat title="Selesai"
                            description="Hari ini"
                            value="{{ $this->todayStats['completed'] }}"
                            icon="phosphor.check-circle"
                            class="bg-success/20 border border-success"
                            color="text-success" />

                    <x-stat title="Menunggu"
                            description="Konfirmasi"
                            value="{{ $this->todayStats['pending'] }}"
                            icon="phosphor.clock"
                            class="bg-error/20 border border-error"
                            color="text-error" />
                </div>

                <!-- Distance to Destination (if available) -->
                @if($this->getDistanceToDestination())
                    <div class="mt-4">
                        <x-alert icon="phosphor.map-pin-area" class="alert-info">
                            <x-slot:title>Jarak ke Tujuan</x-slot:title>
                            <x-slot:description>
                                Anda berjarak {{ number_format($this->getDistanceToDestination(), 1) }} km dari lokasi tujuan pengiriman.
                            </x-slot:description>
                        </x-alert>
                    </div>
                @endif

                <x-slot:actions>
                    {{-- Manual Refresh Button --}}
                    <x-button
                        wire:click="refreshData"
                        class="btn-sm btn-ghost"
                        icon="phosphor.arrow-clockwise"
                        spinner
                        tooltip="Refresh Data"
                    />
                </x-slot:actions>
            </x-card>
        </div>

        <!-- Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Quick Actions -->
            <x-card title="Aksi Cepat" class="bg-base-300 backdrop-blur-sm border-0 shadow-lg">
                <div class="space-y-3">
                    <x-button wire:click="refreshData"
                              label="Refresh Data"
                              icon="phosphor.arrow-clockwise"
                              class="btn-primary btn-block"
                              spinner />
                    <x-button label="Lihat Rute Aktif"
                              icon="phosphor.map-pin"
                              class="btn-outline btn-block" />
                    @if($currentLatitude && $currentLongitude)
                        <x-button onclick="window.open('https://www.google.com/maps?q={{ $currentLatitude }},{{ $currentLongitude }}', '_blank')"
                                  label="Buka Lokasi Saya"
                                  icon="phosphor.map-pin-area"
                                  class="btn-outline btn-block" />
                    @endif
                </div>
            </x-card>

            <!-- Recent Activity with polling -->
            <x-card title="Aktivitas Terbaru" class="bg-base-300 backdrop-blur-sm border-0 shadow-lg" wire:poll.20s.keep-alive="pollActivities">
                <div class="space-y-3">
                    @forelse($this->recentActivities as $activity)
                        <div class="flex items-center gap-3 p-2
                            {{ str_contains($activity['color_class'], 'green') ? 'bg-success/20' :
                               (str_contains($activity['color_class'], 'yellow') ? 'bg-warning/20' :
                               (str_contains($activity['color_class'], 'red') ? 'bg-error/20' : 'bg-info/20')) }} rounded-lg">
                            <div aria-label="status" class="status
                                {{ str_contains($activity['color_class'], 'green') ? 'status-success' :
                                   (str_contains($activity['color_class'], 'yellow') ? 'status-warning' :
                                   (str_contains($activity['color_class'], 'red') ? 'status-error' : 'status-info')) }} animate-pulse"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ $activity['order_number'] }}</p>
                                <p class="text-xs text-base-content/60">{{ $activity['description'] }}</p>
                                <p class="text-xs text-base-content/60">{{ $activity['time_ago'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <x-icon name="phosphor.clock" class="h-8 w-8 text-base-content/30 mx-auto mb-2" />
                            <p class="text-sm text-base-content/60">Belum ada aktivitas</p>
                        </div>
                    @endforelse
                </div>

                <x-slot:actions>
                    <x-button label="Lihat Semua" class="btn-sm btn-outline" />
                </x-slot:actions>
            </x-card>
        </div>

        <!-- Current Tasks with polling -->
        <x-card title="Tugas Hari Ini" class="bg-base-300 backdrop-blur-sm border-0 shadow-lg" wire:poll.15s.keep-alive>
            <div class="space-y-4">
                @forelse($this->todayTasks as $task)
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg
                        {{ $task['completed'] ? 'opacity-60' : '' }}">
                        <div class="flex items-center gap-3">
                            <x-checkbox
                                {{ $task['completed'] ? 'checked disabled' : '' }}
                                class="{{ $task['priority'] === 'urgent' ? 'checkbox-error' :
                                         ($task['priority'] === 'completed' ? 'checkbox-success' : 'checkbox-primary') }}" />
                            <div>
                                <p class="font-medium {{ $task['completed'] ? 'line-through' : '' }}">
                                    {{ $task['title'] }}
                                </p>
                                <p class="text-sm text-base-content/60">
                                    {{ $task['completed'] ? 'Selesai: ' : 'Deadline: ' }}{{ $task['deadline'] }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-badge value="{{ ucfirst($task['priority']) }}"
                                     class="{{ $task['priority'] === 'urgent' ? 'badge-error' :
                                              ($task['priority'] === 'completed' ? 'badge-success' : 'badge-info') }}" />
                            @if($task['can_complete'] && !$task['completed'])
                                <x-button wire:click="markTaskComplete({{ $task['order_id'] }}, '{{ $task['type'] }}')"
                                          icon="phosphor.play"
                                          class="btn-xs {{ $task['priority'] === 'urgent' ? 'btn-error' : 'btn-primary' }}"
                                          spinner />
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <x-icon name="phosphor.clipboard-text" class="h-12 w-12 text-base-content/30 mx-auto mb-4" />
                        <p class="text-base-content/60">Tidak ada tugas untuk hari ini</p>
                    </div>
                @endforelse
            </div>

            <x-slot:actions>
                <x-button label="Lihat Semua" class="btn-sm btn-outline" />
            </x-slot:actions>
        </x-card>

        <!-- Performance Chart -->
        <x-card title="Statistik Kinerja" class="bg-base-300 backdrop-blur-sm border-0 shadow-lg">
            <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="radial-progress text-primary bg-primary/20"
                         style="--value:{{ $this->performanceStats['on_time_percentage'] }};">
                        {{ $this->performanceStats['on_time_percentage'] }}%
                    </div>
                    <p class="text-sm mt-2 font-medium">Ketepatan Waktu</p>
                    <p class="text-xs text-base-content/60">{{ $this->performanceStats['total_deliveries'] }} pengiriman</p>
                </div>
                <div class="text-center">
                    <div class="radial-progress text-success bg-success/20"
                         style="--value:{{ $this->performanceStats['delivery_success'] }};">
                        {{ $this->performanceStats['delivery_success'] }}%
                    </div>
                    <p class="text-sm mt-2 font-medium">Success Rate</p>
                    <p class="text-xs text-base-content/60">Rata-rata {{ $this->performanceStats['avg_distance'] }} km</p>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Lihat Detail" class="btn-sm btn-primary" />
            </x-slot:actions>
        </x-card>

        <!-- Location Status Card (jika ada data lokasi) -->
        @if($currentLatitude && $currentLongitude)
            <x-card title="Status Lokasi" class="bg-base-300 backdrop-blur-sm border-0 shadow-lg">
                @php
                    $locationAccuracy = $this->locationAccuracy;
                @endphp

                <div class="flex items-center justify-between p-3 bg-{{ $locationAccuracy['color'] }}/10 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.map-pin-area" class="h-6 w-6 text-{{ $locationAccuracy['color'] }}" />
                        <div>
                            <p class="font-medium">{{ $locationAccuracy['label'] }}</p>
                            <p class="text-sm text-base-content/60">{{ $locationAddress ?: 'Alamat tidak tersedia' }}</p>
                            <p class="text-xs text-base-content/50">
                                {{ number_format($currentLatitude, 6) }}, {{ number_format($currentLongitude, 6) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-button onclick="navigator.clipboard.writeText('{{ $currentLatitude }}, {{ $currentLongitude }}')"
                                  icon="phosphor.copy"
                                  class="btn-xs btn-outline"
                                  tooltip="Salin koordinat" />
                        <x-button onclick="window.open('https://www.google.com/maps?q={{ $currentLatitude }},{{ $currentLongitude }}', '_blank')"
                                  icon="phosphor.map-pin-area"
                                  class="btn-xs btn-primary"
                                  tooltip="Buka di Google Maps" />
                    </div>
                </div>
            </x-card>
        @endif
    </div>

    {{-- Show location selection message when order is selected --}}
    @if($selectedOrderId)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="$set('selectedOrderId', null)">
            <x-card title="Memperbarui Lokasi" class="max-w-sm mx-4">
                <div class="text-center py-4">
                    <x-icon name="phosphor.map-pin" class="h-12 w-12 text-primary mx-auto mb-3 animate-bounce" />
                    <p class="text-base-content/80 mb-3">
                        Sistem sedang mengambil lokasi Anda untuk memperbarui posisi pengiriman.
                    </p>
                    <p class="text-sm text-base-content/60">
                        Pastikan Anda mengizinkan akses lokasi di browser.
                    </p>
                </div>
                <x-slot:actions>
                    <x-button wire:click="$set('selectedOrderId', null)" label="Batal" class="btn-ghost btn-block" />
                </x-slot:actions>
            </x-card>
        </div>
    @endif
</div>
