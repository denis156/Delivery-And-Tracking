{{-- resources/views/livewire/components/maps.blade.php --}}
<div class="relative">
    <!-- Offline Indicator -->
    <div wire:offline class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[9999]">
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

    <!-- Top Right - Informasi Cuaca & Waktu -->
    @if ($badgeTopLeft || $badgeTopRight)
        <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 items-center">
            <!-- Badge Kiri -->
            @if ($badgeTopLeft)
                <x-badge value="{{ $badgeTopLeft }}" class="badge-success badge-soft badge-xs" />
            @endif

            <!-- Badge Kanan -->
            @if ($badgeTopRight)
                <x-badge value="{{ $badgeTopRight }}" class="badge-info badge-soft badge-xs" />
            @endif
        </div>
    @endif

    <!-- Custom Zoom Controls - Top Left -->
    <div class="absolute top-4 left-4 z-[1000] flex flex-col gap-1">
        <button id="zoom-in-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-plus-duotone" class="w-4 h-4" />
        </button>
        <button id="zoom-out-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-minus-duotone" class="w-4 h-4" />
        </button>
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
        document.addEventListener('DOMContentLoaded', function() {
            initializeMaps();
        });

        // Handle Livewire navigation
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeMaps, 50);
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
                    console.error('Invalid map data for:', mapId);
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

                    // Add marker dengan popup yang dinamis berdasarkan status lokasi
                    const marker = L.marker([lat, lng]).addTo(map);

                    // Buat popup content berdasarkan status lokasi
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
                                <span class="badge badge-xs badge-soft badge-info">lng: ${lng.toFixed(3)}°</span>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent).openPopup();

                    // Setup zoom controls
                    setupZoomControls(map, mapId);

                    // Mark as initialized
                    container.setAttribute('data-initialized', 'true');
                    console.log(`✅ Map initialized: ${mapId} (${isActual ? 'Actual' : 'Default'} Location)`);

                } catch (error) {
                    console.error('Map initialization error:', error);
                }
            });
        }

        function setupZoomControls(map, mapId) {
            const zoomInBtn = document.getElementById(`zoom-in-${mapId}`);
            const zoomOutBtn = document.getElementById(`zoom-out-${mapId}`);

            if (!zoomInBtn || !zoomOutBtn) {
                console.warn('Zoom buttons not found for:', mapId);
                return;
            }

            function updateButtonStates() {
                const currentZoom = map.getZoom();
                const maxZoom = map.getMaxZoom();
                const minZoom = map.getMinZoom();

                // Update zoom in button
                if (currentZoom >= maxZoom) {
                    zoomInBtn.disabled = true;
                    zoomInBtn.classList.add('btn-disabled');
                } else {
                    zoomInBtn.disabled = false;
                    zoomInBtn.classList.remove('btn-disabled');
                }

                // Update zoom out button
                if (currentZoom <= minZoom) {
                    zoomOutBtn.disabled = true;
                    zoomOutBtn.classList.add('btn-disabled');
                } else {
                    zoomOutBtn.disabled = false;
                    zoomOutBtn.classList.remove('btn-disabled');
                }
            }

            // Event listeners
            zoomInBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!zoomInBtn.disabled) {
                    map.zoomIn();
                }
            });

            zoomOutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!zoomOutBtn.disabled) {
                    map.zoomOut();
                }
            });

            // Listen to zoom events
            map.on('zoom', updateButtonStates);
            map.on('zoomend', updateButtonStates);

            // Initial state
            updateButtonStates();
        }
    </script>
@endassets
