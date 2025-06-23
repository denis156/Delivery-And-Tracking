{{-- Single root element untuk Livewire component --}}
<div x-data="geolocationHandler()" @if($isTracking) wire:poll.1500ms="updateLocation" @endif class="relative">
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
                $startLocationStatus = $this->getStartLocationStatus();
            @endphp
            <div class="flex items-center gap-2 mt-1">
                <div class="w-2 h-2 rounded-full {{ $isTracking ? 'bg-success animate-pulse' : 'bg-base-300' }}"></div>
                <span class="text-xs {{ $trackingStatusColor }}">{{ $trackingStatusText }}</span>
            </div>
            <!-- Start Location Status -->
            <div class="flex items-center gap-2 mt-2">
                <x-icon name="{{ $startLocationStatus['icon'] }}" class="h-3 w-3 {{ $startLocationStatus['color'] }}" />
                <span class="text-xs {{ $startLocationStatus['color'] }}">{{ $startLocationStatus['text'] }}</span>
            </div>
        </div>

        <!-- Tracking Session Info (jika tracking aktif) -->
        @if ($isTracking)
            @php
                $sessionInfo = $this->getTrackingSessionInfo();
                $distanceFromStart = $this->getDistanceFromStart();
            @endphp

            @if ($sessionInfo['has_start_location'])
                <!-- Ada Start Location - Session Aktif -->
                <div class="px-4 py-2 bg-success/5 border-b border-success/20">
                    <div class="text-xs space-y-1">
                        <div class="flex items-center gap-2 text-success font-medium">
                            <x-icon name="phosphor.flag" class="h-3 w-3" />
                            <span>Start Point Terkunci</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Session ID:</span>
                            <span class="font-mono text-xs">{{ Str::limit($sessionInfo['session_id'], 8, '') }}</span>
                        </div>
                        @if ($distanceFromStart)
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Jarak:</span>
                                <span class="font-medium text-success">{{ $distanceFromStart }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Start:</span>
                            <span class="font-mono text-xs">{{ $sessionInfo['start_location']['timestamp_wita'] ?? '-' }} WITA</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Belum Ada Start Location - Waiting for GPS -->
                <div class="px-4 py-2 bg-warning/5 border-b border-warning/20">
                    <div class="text-xs space-y-1">
                        <div class="flex items-center gap-2 text-warning font-medium">
                            <x-icon name="{{ $startLocationStatus['icon'] }}" class="h-3 w-3 {{ $startLocationStatus['status'] === 'waiting' ? 'animate-spin' : '' }}" />
                            <span>{{ $startLocationStatus['text'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Status:</span>
                            <span class="font-medium text-warning">Menunggu koordinat pertama</span>
                        </div>
                        @if ($sessionInfo['tracking_start_time'])
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Tracking mulai:</span>
                                <span class="font-mono text-xs">{{ \Carbon\Carbon::parse($sessionInfo['tracking_start_time'])->setTimezone('Asia/Makassar')->format('H:i:s') }} WITA</span>
                            </div>
                        @endif
                        <div class="text-warning/70 text-[10px] mt-1">
                            💡 Start point akan otomatis di-set saat GPS pertama kali didapat
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Status Info (jika ada lokasi) -->
        @if ($status === 'success' && $latitude && $longitude)
            <div class="px-4 py-2 bg-base-100 border-b border-base-300">
                <div class="text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Lokasi:</span>
                        <span class="font-medium truncate">{{ Str::words($address ?: 'Tidak diketahui', 3, '...') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Koordinat:</span>
                        <span class="font-mono text-xs">{{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}</span>
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

            {{-- Menu untuk copy start location jika ada --}}
            @if ($isTracking && isset($sessionInfo) && $sessionInfo['has_start_location'])
                @php
                    $startLat = $sessionInfo['start_location']['latitude'];
                    $startLng = $sessionInfo['start_location']['longitude'];
                @endphp
                <x-menu-item title="Salin Start Point" icon="phosphor.flag"
                    onclick="navigator.clipboard.writeText('{{ $startLat }}, {{ $startLng }}'); $dispatch('notify', {type: 'success', message: 'Start point disalin'})" />
            @endif

            <hr class="border-base-300">
            <x-menu-item title="Hapus Data Lokasi" icon="phosphor.trash" wire:click="clearLocation"
                wire:loading.attr="disabled" wire:target="clearLocation" class="text-error"
                onclick="confirm('Yakin ingin menghapus data lokasi dan tracking session?') || event.stopImmediatePropagation()" />
        @endif

        <!-- Info Footer -->
        <div class="px-4 py-2 border-t border-base-300 bg-base-100">
            <div class="text-[10px] text-base-content/50 text-center">
                @if ($isTracking)
                    @php
                        $sessionInfo = $this->getTrackingSessionInfo();
                    @endphp
                    <div class="flex items-center justify-center gap-1">
                        <x-icon name="phosphor.broadcast" class="h-3 w-3 text-success animate-pulse" />
                        <span>Live tracking setiap 1.5 detik</span>
                    </div>
                    @if ($sessionInfo['has_start_location'])
                        <div class="flex items-center justify-center gap-1 mt-1">
                            <x-icon name="phosphor.flag" class="h-3 w-3 text-success" />
                            <span>Route dari start point terkunci</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center gap-1 mt-1 text-warning">
                            <x-icon name="phosphor.crosshair" class="h-3 w-3 animate-pulse" />
                            <span>Siap set start point...</span>
                        </div>
                    @endif
                @else
                    <div class="flex items-center justify-center gap-1">
                        <x-icon name="phosphor.map-pin" class="h-3 w-3 text-base-content/40" />
                        <span>GPS tracking nonaktif</span>
                    </div>
                    <div class="text-base-content/40 mt-1">
                        Klik "Mulai Live Tracking" untuk set start point
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
                        try {
                            const { latitude, longitude, accuracy } = position.coords;
                            this.$wire.handleLocationSuccess(latitude, longitude, accuracy);
                            this.isRequestingLocation = false;
                        } catch (error) {
                            // Log error to server (if logging endpoint available)
                            if (window.fetch) {
                                fetch('/api/log-error', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        component: 'GeolocationButton',
                                        function: 'getCurrentPosition_success',
                                        error: error.message,
                                        user_agent: navigator.userAgent
                                    })
                                }).catch(() => {}); // Silent fail jika logging gagal
                            }
                            this.isRequestingLocation = false;
                        }
                    },
                    (error) => {
                        try {
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
                        } catch (handlingError) {
                            // Log error to server (if logging endpoint available)
                            if (window.fetch) {
                                fetch('/api/log-error', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        component: 'GeolocationButton',
                                        function: 'getCurrentPosition_error',
                                        error: handlingError.message,
                                        original_error: error.message,
                                        user_agent: navigator.userAgent
                                    })
                                }).catch(() => {}); // Silent fail jika logging gagal
                            }
                            this.isRequestingLocation = false;
                        }
                    },
                    options
                );
            }
        }
    }
</script>
@endassets
