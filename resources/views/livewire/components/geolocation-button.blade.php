<div x-data="geolocationHandler()" wire:poll.1500ms="updateLocation" class="relative">
    <!-- Geolocation Dropdown - MaryUI Pattern -->
    <x-dropdown class="z-50" no-x-anchor>
        <x-slot:trigger>
            @php
                $buttonClasses = $this->getButtonClasses();
                $iconName = $this->getIconName();
                $statusBadge = $this->getStatusBadge();
                $tooltipText = 'Klik untuk membuka menu lokasi';
            @endphp

            <x-button :icon="$iconName" :class="$buttonClasses" :title="$tooltipText" class="{{ $buttonClasses }} indicator">
                {{-- Badge hanya muncul saat live tracking aktif --}}
                @if ($showBadge && $statusBadge['text'] && $isTracking && $status === 'success')
                    <x-badge :value="$statusBadge['text']" :class="$statusBadge['class'] . ' indicator-item'" />
                @endif
            </x-button>
        </x-slot:trigger>

        <!-- Header Info -->
        <div class="px-4 py-3 border-b border-base-300">
            <h3 class="font-semibold text-lg flex items-center gap-2">
                <x-icon name="phosphor.broadcast" class="h-5 w-5" />
                Real-Time GPS Tracking
            </h3>
            @php
                $trackingStatusColor = $this->getTrackingStatusColor();
                $trackingStatusText = $this->getTrackingStatusText();
            @endphp
            <div class="flex items-center gap-2 mt-1">
                <div class="w-2 h-2 rounded-full {{ $isTracking ? 'bg-success animate-pulse' : 'bg-base-300' }}"></div>
                <span class="text-xs {{ $trackingStatusColor }}">{{ $trackingStatusText }}</span>
            </div>
        </div>

        <!-- Status Info (jika ada lokasi) -->
        @if ($status === 'success' && $latitude && $longitude)
            <div class="px-4 py-2 bg-base-100 border-b border-base-300">
                <div class="text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Lokasi:</span>
                        <span
                            class="font-medium truncate">{{ Str::words($address ?: 'Tidak diketahui', 3, '...') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Koordinat:</span>
                        <span class="font-mono text-xs">{{ number_format($latitude, 6) }},
                            {{ number_format($longitude, 6) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Update:</span>
                        <span class="font-mono text-xs">{{ $lastUpdated ?? '-' }} WITA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Status:</span>
                        <span class="{{ $this->isLocationRecent() ? 'text-success' : 'text-warning' }}">
                            {{ $this->isLocationRecent() ? 'Terbaru' : 'Perlu refresh' }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Menu Items - MaryUI Pattern -->
        @if (!$isTracking)
            <!-- Tracking Non-aktif -->
            <x-menu-item title="Mulai Live Tracking" icon="phosphor.play" wire:click="startTracking"
                wire:loading.attr="disabled" wire:target="startTracking" />
            <x-menu-item title="Ambil Lokasi Sekali" icon="phosphor.crosshair" wire:click="requestLocation"
                wire:loading.attr="disabled" wire:target="requestLocation" />
        @else
            <!-- Tracking Aktif -->
            <x-menu-item title="Stop Live Tracking" icon="phosphor.stop" wire:click="stopTracking"
                wire:loading.attr="disabled" wire:target="stopTracking" class="text-warning" />
            <x-menu-item title="Refresh Manual" icon="phosphor.arrow-clockwise" wire:click="refreshLocation"
                wire:loading.attr="disabled" wire:target="refreshLocation" />
        @endif

        <!-- Menu Items Lainnya -->
        @if ($latitude && $longitude)
            <hr class="border-base-300">
            <x-menu-item title="Salin Koordinat" icon="phosphor.copy"
                onclick="navigator.clipboard.writeText('{{ $latitude }}, {{ $longitude }}'); $dispatch('notify', {type: 'success', message: 'Koordinat disalin'})" />
            <x-menu-item title="Buka Google Maps" icon="phosphor.map-pin-area"
                onclick="window.open('https://www.google.com/maps?q={{ $latitude }},{{ $longitude }}', '_blank')" />
            <hr class="border-base-300">
            <x-menu-item title="Hapus Data Lokasi" icon="phosphor.trash" wire:click="clearLocation"
                wire:loading.attr="disabled" wire:target="clearLocation" class="text-error"
                onclick="confirm('Yakin ingin menghapus data lokasi?') || event.stopImmediatePropagation()" />
        @endif

        <!-- Info Footer -->
        <div class="px-4 py-2 border-t border-base-300 bg-base-100">
            <div class="text-[10px] text-base-content/50 text-center">
                @if ($isTracking)
                    <div class="flex items-center justify-center gap-1">
                        <x-icon name="phosphor.broadcast" class="h-3 w-3 text-success animate-pulse" />
                        <span>Live tracking setiap 5 detik</span>
                    </div>
                @else
                    <div class="flex items-center justify-center gap-1">
                        <x-icon name="phosphor.map-pin" class="h-3 w-3 text-base-content/40" />
                        <span>GPS tracking nonaktif</span>
                    </div>
                @endif
                <div class="text-base-content/40 mt-1">
                    Powered by BMKG API
                </div>
            </div>
        </div>
    </x-dropdown>
</div>

@assets
<!-- Optimized Alpine.js Handler -->
<script>
    function geolocationHandler() {
        return {
            isRequestingLocation: false,

            init() {
                // Listen for geolocation requests
                this.$wire.on('request-geolocation', () => {
                    this.getCurrentPosition(true); // with loading
                });

                // Listen for silent geolocation requests (for real-time polling)
                this.$wire.on('request-geolocation-silent', () => {
                    this.getCurrentPosition(false); // no loading
                });
            },

            getCurrentPosition(showLoading = true) {
                // Prevent multiple simultaneous requests
                if (this.isRequestingLocation) {
                    return;
                }

                if (!navigator.geolocation) {
                    this.$wire.handleLocationError('Browser tidak mendukung geolocation');
                    return;
                }

                this.isRequestingLocation = true;

                const options = {
                    enableHighAccuracy: true,
                    timeout: 10000, // Reduced for real-time
                    maximumAge: 0 // Always get fresh location for real-time
                };

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const {
                            latitude,
                            longitude,
                            accuracy
                        } = position.coords;
                        this.$wire.handleLocationSuccess(latitude, longitude, accuracy);
                        this.isRequestingLocation = false;
                    },
                    (error) => {
                        const errorMessages = {
                            1: "Akses lokasi ditolak",
                            2: "Lokasi tidak tersedia",
                            3: "Request timeout"
                        };

                        const message = errorMessages[error.code] || "Error mengambil lokasi";

                        // Only show error for manual requests
                        if (showLoading) {
                            this.$wire.handleLocationError(message);
                        }

                        this.isRequestingLocation = false;
                    },
                    options
                );
            }
        }
    }
</script>
@endassets
