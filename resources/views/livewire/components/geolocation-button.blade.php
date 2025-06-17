<div
    x-data="geolocationHandler()"
    @if($autoUpdate)
        wire:poll.{{ $pollInterval }}s="autoRefreshLocation"
    @endif
    class="relative"
>
    <!-- Geolocation Button -->
    <x-dropdown class="z-50" no-x-anchor>
        <x-slot:trigger>
            @php
                $buttonClasses = $this->getButtonClasses();
                $iconName = $this->getIconName();
                $statusBadge = $this->getStatusBadge();
                $tooltipText = $status === 'success'
                    ? 'Lokasi: ' . $address . ' (Update: ' . $lastUpdated . ')'
                    : 'Klik untuk buka menu lokasi';
            @endphp

            <x-button
                :icon="$iconName"
                :class="$buttonClasses"
                wire:click="handleNavbarClick"
                :title="$tooltipText"
                class="{{ $buttonClasses }} indicator"
            >
                @if($showBadge)
                    <x-badge
                        :value="$statusBadge['text']"
                        :class="$statusBadge['class'] . ' indicator-item'"
                    />
                @endif
            </x-button>
        </x-slot:trigger>

        <!-- Dropdown Content -->
        <div class="w-80 max-h-96 overflow-y-auto p-4 space-y-4">
            <!-- Header -->
            <div class="pb-2 border-b border-base-300">
                <h3 class="font-semibold text-lg flex items-center gap-2">
                    <x-icon name="phosphor.map-pin" class="h-5 w-5" />
                    Status Lokasi
                </h3>
            </div>

            <!-- Status Info -->
            <div class="space-y-3">
                <!-- Status Card -->
                @php
                    $statusConfig = [
                        'waiting' => ['bg' => 'bg-warning/10', 'text' => 'text-warning', 'label' => 'Belum diambil'],
                        'getting' => ['bg' => 'bg-info/10', 'text' => 'text-info', 'label' => 'Mengambil lokasi...'],
                        'success' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'label' => 'Berhasil'],
                        'error' => ['bg' => 'bg-error/10', 'text' => 'text-error', 'label' => 'Gagal']
                    ];
                    $currentStatus = $statusConfig[$status];
                @endphp

                <div class="p-3 rounded-lg {{ $currentStatus['bg'] }}">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Status:</span>
                        <div class="flex items-center gap-2">
                            <span class="capitalize {{ $currentStatus['text'] }}">
                                {{ $currentStatus['label'] }}
                            </span>
                            <x-icon
                                name="{{ $status === 'getting' ? 'phosphor.spinner' : $iconName }}"
                                class="h-4 w-4 {{ $status === 'getting' ? 'animate-spin' : '' }}"
                            />
                        </div>
                    </div>
                </div>

                @if($status === 'success' && $latitude && $longitude)
                    <!-- Location Details -->
                    <div class="space-y-2 text-sm">
                        @php
                            $locationDetails = [
                                'Alamat' => $address ?: 'Tidak diketahui',
                                'Koordinat' => number_format($latitude, 6) . ', ' . number_format($longitude, 6),
                                'Update terakhir' => ($lastUpdated ?? '-') . ' WIB',
                            ];
                        @endphp

                        @foreach($locationDetails as $label => $value)
                            <div class="flex justify-between">
                                <span class="text-base-content/70">{{ $label }}:</span>
                                <span class="{{ $label === 'Koordinat' ? 'font-mono text-xs' : 'font-medium' }}">
                                    {{ $value }}
                                </span>
                            </div>
                        @endforeach

                        <div class="flex justify-between">
                            <span class="text-base-content/70">Akurasi:</span>
                            <span class="text-xs {{ $this->isLocationRecent() ? 'text-success' : 'text-warning' }}">
                                {{ $this->isLocationRecent() ? 'Terbaru' : 'Perlu diperbarui' }}
                            </span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex gap-2">
                        <x-button
                            wire:click="refreshLocation"
                            label="Perbarui"
                            icon="phosphor.arrow-clockwise"
                            class="btn-sm btn-primary flex-1"
                            spinner="refreshLocation"
                            wire:loading.attr="disabled"
                            wire:target="refreshLocation"
                        />
                        <x-button
                            wire:click="stopLocation"
                            label="Hentikan"
                            icon="phosphor.stop"
                            class="btn-sm btn-error btn-outline"
                            spinner="stopLocation"
                            wire:loading.attr="disabled"
                            wire:target="stopLocation"
                        />
                    </div>

                    <!-- Copy Coordinates -->
                    <x-button
                        onclick="navigator.clipboard.writeText('{{ $latitude }}, {{ $longitude }}');
                                 $dispatch('notify', {type: 'success', message: 'Koordinat disalin ke clipboard'})"
                        label="Salin Koordinat"
                        icon="phosphor.copy"
                        class="btn-sm btn-outline btn-block"
                    />

                    <!-- Map Link -->
                    <x-button
                        onclick="window.open('https://www.google.com/maps?q={{ $latitude }},{{ $longitude }}', '_blank')"
                        label="Buka di Google Maps"
                        icon="phosphor.map-pin-area"
                        class="btn-sm btn-outline btn-block"
                    />

                @elseif($status === 'error')
                    <!-- Error Help -->
                    <div class="text-sm space-y-2">
                        <p class="text-error">Gagal mengambil lokasi. Pastikan:</p>
                        <ul class="list-disc list-inside text-xs text-base-content/70 space-y-1">
                            <li>Browser mendukung geolocation</li>
                            <li>Izin lokasi sudah diberikan</li>
                            <li>GPS/Layanan lokasi aktif</li>
                            <li>Koneksi internet stabil</li>
                        </ul>
                    </div>

                    <x-button
                        wire:click="requestLocation"
                        label="Coba Lagi"
                        icon="phosphor.arrow-clockwise"
                        class="btn-sm btn-error btn-block"
                        spinner="requestLocation"
                        wire:loading.attr="disabled"
                        wire:target="requestLocation"
                    />

                @else
                    <!-- Initial State -->
                    <div class="text-center py-4">
                        <x-icon name="phosphor.map-pin" class="h-12 w-12 text-base-content/30 mx-auto mb-2" />
                        <p class="text-sm text-base-content/60 mb-3">Belum ada data lokasi</p>
                        <x-button
                            wire:click="requestLocation"
                            label="Ambil Lokasi Sekarang"
                            icon="phosphor.crosshair"
                            class="btn-sm btn-primary"
                            spinner="requestLocation"
                            wire:loading.attr="disabled"
                            wire:target="requestLocation"
                        />
                    </div>
                @endif
            </div>

            <!-- Settings Info -->
            @if($autoUpdate)
                <div class="pt-2 border-t border-base-300">
                    <div class="flex items-center justify-between text-xs text-base-content/60">
                        <span>Auto-update setiap {{ $pollInterval }}s</span>
                        <x-icon name="phosphor.timer" class="h-3 w-3" />
                    </div>
                </div>
            @endif
        </div>
    </x-dropdown>

    <!-- Alpine.js Geolocation Handler -->
    <script>
        function geolocationHandler() {
            return {
                init() {
                    this.$wire.on('request-geolocation', () => {
                        this.getCurrentPosition();
                    });
                },

                getCurrentPosition() {
                    if (!navigator.geolocation) {
                        this.$wire.call('handleLocationError', 'Browser tidak mendukung geolocation');
                        return;
                    }

                    const options = {
                        enableHighAccuracy: true,
                        timeout: 20000,
                        maximumAge: 300000 // 5 minutes cache
                    };

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const { latitude, longitude, accuracy } = position.coords;

                            console.log('📍 Location obtained:', {
                                latitude,
                                longitude,
                                accuracy: accuracy + 'm'
                            });

                            this.$wire.call('handleLocationSuccess', latitude, longitude, accuracy);
                        },
                        (error) => {
                            const errorMessage = this.getGeolocationErrorMessage(error);
                            console.error('❌ Geolocation error:', error);
                            this.$wire.call('handleLocationError', errorMessage);
                        },
                        options
                    );
                },

                getGeolocationErrorMessage(error) {
                    const errorMessages = {
                        [error.PERMISSION_DENIED]: "Akses lokasi ditolak. Silakan aktifkan di pengaturan browser.",
                        [error.POSITION_UNAVAILABLE]: "Informasi lokasi tidak tersedia. Periksa GPS/layanan lokasi.",
                        [error.TIMEOUT]: "Permintaan lokasi timeout. Silakan coba lagi."
                    };

                    return errorMessages[error.code] || "Terjadi kesalahan tidak diketahui saat mengambil lokasi.";
                }
            }
        }
    </script>
</div>
