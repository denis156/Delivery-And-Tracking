{{-- resources/views/livewire/components/maps.blade.php --}}
@php
    // Cek apakah user sedang tracking untuk conditional polling
    $trackingCacheKey = 'user_tracking_state_' . auth()->id();
    $isUserTracking = \Illuminate\Support\Facades\Cache::get($trackingCacheKey, false);
@endphp

<div class="relative" @if ($isUserTracking) wire:poll.visible.1500ms="updateMapLocation" @endif>

    <!-- Offline Indicator -->
    <div wire:offline class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-15">
        <x-card class="text-center p-6 bg-base-100/95 backdrop-blur-sm border-2 border-error/20">
            <div class="flex flex-col items-center gap-3">
                <x-icon name="phosphor.cell-signal-slash-duotone" class="w-12 h-12 text-error animate-pulse" />
                <div>
                    <h3 class="font-bold text-error text-lg">Tidak Ada Koneksi Internet</h3>
                    <p class="text-base-content/70 text-sm mt-1">
                        Peta mungkin tidak dapat dimuat dengan sempurna
                    </p>
                </div>
                <x-badge value="Mode Offline" class="badge-error badge-outline" />
            </div>
        </x-card>
    </div>

    <!-- Corner Divs - Positioned Absolutely -->

    <!-- Top Right - Informasi Cuaca & Waktu (Dynamic dari Geolocation) -->
    @if ($currentTime || $weatherData)
        <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 items-end">
            <!-- Badge Cuaca -->
            @if ($weatherData)
                @php
                    $weatherCondition = $weatherData['condition'] ?? ($weatherData['description'] ?? 'Cuaca');
                    $weatherTemp = round($weatherData['temperature'] ?? 0);
                    $weatherText = $weatherCondition . ' ' . $weatherTemp . '°C';
                @endphp
                <x-badge value="Cuaca: {{ $weatherText }}" class="badge-info badge-soft badge-xs" />
            @endif

            <!-- Badge Waktu Update -->
            @if ($currentTime)
                <x-badge value="Waktu: {{ $currentTime }}" class="badge-success badge-soft badge-xs" />
            @endif
        </div>
    @endif

    <!-- Custom Zoom Controls - Top Left -->
    <div class="absolute top-4 left-4 z-10 flex flex-col gap-1">
        <button id="zoom-in-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-plus-duotone" class="w-4 h-4" />
        </button>
        <button id="zoom-out-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-minus-duotone" class="w-4 h-4" />
        </button>

        <!-- Go to My Location Button - Hanya muncul jika ada lokasi aktual -->
        @if ($isActualLocation)
            <button id="go-to-location-{{ $mapId }}" wire:click="goToMyLocation"
                class="btn btn-sm btn-circle btn-success btn-soft" title="Ke Lokasi Saya (Zoom Max)">
                <x-icon name="phosphor.crosshair-duotone" class="w-4 h-4" />
            </button>
        @endif
    </div>

    <!-- Badge Bottom Left - No Surat Jalan -->
    @if ($badgeBottomLeft)
        <x-badge value="{{ $badgeBottomLeft }}" class="absolute bottom-4 left-4 z-10 badge-primary badge-md" />
    @endif

    <!-- Badge Bottom Right - Lokasi Tujuan -->
    @if ($badgeBottomRight)
        <x-badge value="{{ $badgeBottomRight }}" class="absolute bottom-4 right-4 z-10 badge-primary badge-md" />
    @endif

    <!-- Flexible Map Container with inline style -->
    <div id="{{ $mapId }}" wire:ignore class="{{ $class ?? '' }}" style="{{ $style }}"
        data-lat="{{ $lat }}" data-lng="{{ $lng }}" data-zoom="{{ $zoom }}"
        data-address="{{ $address }}" data-is-actual="{{ $isActualLocation ? 'true' : 'false' }}"
        data-status-text="{{ $this->getLocationStatusText() }}"
        data-status-class="{{ $this->getLocationStatusClass() }}"
        data-text-class="{{ $this->getLocationTextClass() }}">
    </div>
</div>

@assets
    <script>
        // Global object untuk menyimpan map instances dan markers
        window.mapInstances = window.mapInstances || {};

        /**
         * Center map to specific location with max zoom
         */
        function centerMapToLocation(eventData) {
            // Extract data dari eventData
            let mapId, lat, lng, zoom;

            if (Array.isArray(eventData) && eventData.length > 0) {
                const data = eventData[0];
                mapId = data.mapId;
                lat = data.lat;
                lng = data.lng;
                zoom = data.zoom;
            } else if (typeof eventData === 'object') {
                mapId = eventData.mapId;
                lat = eventData.lat;
                lng = eventData.lng;
                zoom = eventData.zoom;
            } else {
                return;
            }

            // Ambil map instance
            const mapInstance = window.mapInstances[mapId];
            if (!mapInstance) {
                return;
            }

            const { map } = mapInstance;

            try {
                // Animate to location dengan zoom maksimal
                map.flyTo([lat, lng], zoom, {
                    duration: 1.5,
                    easeLinearity: 0.25
                });
            } catch (error) {
                // Send error to Laravel via Livewire
                if (window.Livewire) {
                    window.Livewire.dispatch('log-js-error', {
                        component: 'Maps',
                        function: 'centerMapToLocation',
                        error: error.message,
                        mapId: mapId,
                        user_agent: navigator.userAgent
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeMaps();
        });

        // Handle Livewire navigation
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeMaps, 50);
        });

        // Listen untuk update marker position events
        document.addEventListener('livewire:init', function() {
            Livewire.on('update-marker-position', (event) => {
                updateMarkerPosition(event);
            });

            // Listen untuk center map events
            Livewire.on('center-map-to-location', (event) => {
                centerMapToLocation(event);
            });
        });

        function initializeMaps() {
            document.querySelectorAll('[id^="map-"]:not([data-initialized])').forEach(container => {
                if (!container || container._leaflet_id) return;

                const lat = parseFloat(container.dataset.lat);
                const lng = parseFloat(container.dataset.lng);
                const zoom = parseInt(container.dataset.zoom);
                const address = container.dataset.address || null;
                const mapId = container.id;

                // Data untuk status lokasi
                const isActual = container.dataset.isActual === 'true';
                const statusText = container.dataset.statusText;
                const statusClass = container.dataset.statusClass;
                const textClass = container.dataset.textClass;

                // Validate data
                if (isNaN(lat) || isNaN(lng) || isNaN(zoom)) {
                    return;
                }

                try {
                    // Create map
                    const map = L.map(mapId, {
                        attributionControl: false,
                        zoomControl: false
                    }).setView([lat, lng], zoom);

                    // Add tiles
                    L.tileLayer('https://mt0.google.com/vt/lyrs=m@221097413,traffic&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        minZoom: 1,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                    }).addTo(map);

                    // Create custom user location icon
                    const userLocationIcon = L.icon({
                        iconUrl: '/images/map-pin/user-location.png',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                    });

                    // Add marker dengan custom icon
                    const marker = L.marker([lat, lng], {
                        icon: userLocationIcon
                    }).addTo(map);

                    // Create initial popup
                    updateMarkerPopup(marker, lat, lng, address, isActual, statusText, statusClass, textClass);

                    // Setup zoom controls
                    setupZoomControls(map, mapId);

                    // Store map dan marker dalam global object untuk real-time updates
                    window.mapInstances[mapId] = {
                        map: map,
                        marker: marker,
                        userLocationIcon: userLocationIcon
                    };

                    // Mark as initialized
                    container.setAttribute('data-initialized', 'true');

                } catch (error) {
                    // Send error to Laravel via Livewire
                    if (window.Livewire) {
                        window.Livewire.dispatch('log-js-error', {
                            component: 'Maps',
                            function: 'initializeMaps',
                            error: error.message,
                            mapId: mapId,
                            user_agent: navigator.userAgent
                        });
                    }
                }
            });
        }

        /**
         * Update marker position - Core functionality untuk real-time updates
         */
        function updateMarkerPosition(eventData) {
            // Extract data dari eventData (bisa berupa array atau object)
            let mapId, lat, lng, address, isActual;

            if (Array.isArray(eventData) && eventData.length > 0) {
                // Jika data dalam format array (Livewire v3)
                const data = eventData[0];
                mapId = data.mapId;
                lat = parseFloat(data.lat); // PENTING: Pastikan lat adalah number
                lng = parseFloat(data.lng); // PENTING: Pastikan lng adalah number
                address = data.address;
                isActual = data.isActual;
            } else if (typeof eventData === 'object') {
                // Jika data dalam format object langsung
                mapId = eventData.mapId;
                lat = parseFloat(eventData.lat); // PENTING: Pastikan lat adalah number
                lng = parseFloat(eventData.lng); // PENTING: Pastikan lng adalah number
                address = eventData.address;
                isActual = eventData.isActual;
            } else {
                return;
            }

            // Validate coordinates
            if (isNaN(lat) || isNaN(lng)) {
                return;
            }

            // Ambil map instance dari global object
            const mapInstance = window.mapInstances[mapId];

            if (!mapInstance) {
                return;
            }

            const { map, marker } = mapInstance;

            try {
                // Cek apakah popup sedang terbuka
                const wasPopupOpen = marker.isPopupOpen();

                // Update marker position menggunakan setLatLng
                const newLatLng = L.latLng(lat, lng);
                marker.setLatLng(newLatLng);

                // Update popup content dengan koordinat yang fresh
                updateMarkerPopup(
                    marker,
                    lat, // Koordinat fresh dari server
                    lng, // Koordinat fresh dari server
                    address,
                    isActual,
                    isActual ? 'Lokasi Aktual' : 'Lokasi Default',
                    isActual ? 'status-success' : 'status-warning',
                    isActual ? 'text-success' : 'text-warning'
                );

                // HANYA buka popup jika sebelumnya sudah terbuka (preserve user choice)
                if (wasPopupOpen) {
                    marker.openPopup();
                }

            } catch (error) {
                // Send error to Laravel via Livewire
                if (window.Livewire) {
                    window.Livewire.dispatch('log-js-error', {
                        component: 'Maps',
                        function: 'updateMarkerPosition',
                        error: error.message,
                        mapId: mapId,
                        user_agent: navigator.userAgent
                    });
                }
            }
        }

        /**
         * Update popup content berdasarkan data lokasi
         */
        function updateMarkerPopup(marker, lat, lng, address, isActual, statusText, statusClass, textClass) {
            const popupContent = `
                <div class="min-w-28 max-w-40 text-xs">
                    <!-- Status Section - Compact -->
                    <div class="flex items-center gap-1 mb-1">
                        <div aria-label="${isActual ? 'success' : 'warning'}" class="status status-md ${statusClass} ${isActual ? '' : 'animate-pulse'}"></div>
                        <span class="${textClass} font-medium text-xs">${statusText}</span>
                    </div>

                    <!-- Address Section - Truncated -->
                    <div class="text-xs text-gray-600 font-medium mb-1 line-clamp-2 leading-tight">
                        ${address ? address.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : 'Lokasi tidak diketahui'}
                    </div>

                    <!-- Coordinates Section - Two Columns -->
                    <div class="flex gap-1">
                        <span class="badge badge-xs badge-soft badge-info">Lat: ${lat.toFixed(3)}°</span>
                        <span class="badge badge-xs badge-soft badge-info">Lng: ${lng.toFixed(3)}°</span>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);

            // PENTING: Jangan auto-open popup setiap update
            // Biarkan user yang kontrol kapan popup terbuka/tertutup
        }

        function setupZoomControls(map, mapId) {
            const zoomInBtn = document.getElementById(`zoom-in-${mapId}`);
            const zoomOutBtn = document.getElementById(`zoom-out-${mapId}`);

            if (!zoomInBtn || !zoomOutBtn) {
                return;
            }

            function updateButtonStates() {
                const currentZoom = map.getZoom();
                const maxZoom = map.getMaxZoom();
                const minZoom = map.getMinZoom();

                // Update zoom in button - menggunakan style inline untuk stabilitas
                if (currentZoom >= maxZoom) {
                    zoomInBtn.disabled = true;
                    zoomInBtn.style.opacity = '0.5';
                    zoomInBtn.style.cursor = 'not-allowed';
                    zoomInBtn.style.pointerEvents = 'none';
                } else {
                    zoomInBtn.disabled = false;
                    zoomInBtn.style.opacity = '';
                    zoomInBtn.style.cursor = '';
                    zoomInBtn.style.pointerEvents = '';
                }

                // Update zoom out button - menggunakan style inline untuk stabilitas
                if (currentZoom <= minZoom) {
                    zoomOutBtn.disabled = true;
                    zoomOutBtn.style.opacity = '0.5';
                    zoomOutBtn.style.cursor = 'not-allowed';
                    zoomOutBtn.style.pointerEvents = 'none';
                } else {
                    zoomOutBtn.disabled = false;
                    zoomOutBtn.style.opacity = '';
                    zoomOutBtn.style.cursor = '';
                    zoomOutBtn.style.pointerEvents = '';
                }
            }

            // Event listeners dengan validasi
            zoomInBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentZoom = map.getZoom();
                const maxZoom = map.getMaxZoom();

                if (!zoomInBtn.disabled && currentZoom < maxZoom) {
                    try {
                        map.zoomIn();
                    } catch (error) {
                        // Send error to Laravel via Livewire
                        if (window.Livewire) {
                            window.Livewire.dispatch('log-js-error', {
                                component: 'Maps',
                                function: 'zoomIn',
                                error: error.message,
                                mapId: mapId,
                                user_agent: navigator.userAgent
                            });
                        }
                    }
                }
            });

            zoomOutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentZoom = map.getZoom();
                const minZoom = map.getMinZoom();

                if (!zoomOutBtn.disabled && currentZoom > minZoom) {
                    try {
                        map.zoomOut();
                    } catch (error) {
                        // Send error to Laravel via Livewire
                        if (window.Livewire) {
                            window.Livewire.dispatch('log-js-error', {
                                component: 'Maps',
                                function: 'zoomOut',
                                error: error.message,
                                mapId: mapId,
                                user_agent: navigator.userAgent
                            });
                        }
                    }
                }
            });

            // Listen to zoom events dengan error handling
            try {
                map.on('zoom', updateButtonStates);
                map.on('zoomend', updateButtonStates);

                // Initial state
                updateButtonStates();
            } catch (error) {
                // Send error to Laravel via Livewire
                if (window.Livewire) {
                    window.Livewire.dispatch('log-js-error', {
                        component: 'Maps',
                        function: 'setupZoomControls',
                        error: error.message,
                        mapId: mapId,
                        user_agent: navigator.userAgent
                    });
                }
            }
        }
    </script>
@endassets
