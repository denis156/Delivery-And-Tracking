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

    <!-- Real-time Status Indicator (only if real-time enabled) -->
    @if($enableRealTimeUpdates)
        <div class="absolute top-1 left-1/2 transform -translate-x-1/2 z-[1001]">
            <div class="flex items-center gap-1 bg-base-100/90 backdrop-blur-sm rounded-full px-2 py-1 border border-primary/20">
                <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                <span class="text-xs font-medium text-success">Live</span>
            </div>
        </div>
    @endif

    <!-- Corner Badges -->
    @if ($badgeTopLeft || $badgeTopRight)
        <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 items-center">
            @if ($badgeTopLeft)
                <x-badge value="{{ $badgeTopLeft }}" class="badge-success badge-soft badge-xs" />
            @endif
            @if ($badgeTopRight)
                <x-badge value="{{ $badgeTopRight }}" class="badge-info badge-soft badge-xs" />
            @endif
        </div>
    @endif

    <!-- Custom Zoom Controls -->
    <div class="absolute top-4 left-4 z-[1000] flex flex-col gap-1">
        <button id="zoom-in-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-plus-duotone" class="w-4 h-4" />
        </button>
        <button id="zoom-out-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-minus-duotone" class="w-4 h-4" />
        </button>
    </div>

    <!-- Bottom Badges -->
    @if ($badgeBottomLeft)
        <x-badge value="{{ $badgeBottomLeft }}" class="absolute bottom-4 left-4 z-10 badge-primary badge-md" />
    @endif
    @if ($badgeBottomRight)
        <x-badge value="{{ $badgeBottomRight }}" class="absolute bottom-4 right-4 z-10 badge-primary badge-md" />
    @endif

    <!-- Map Container -->
    <div id="{{ $mapId }}" wire:ignore class="{{ $class ?? '' }}" style="{{ $style }}"
        data-lat="{{ $lat }}" data-lng="{{ $lng }}" data-zoom="{{ $zoom }}"
        data-address="{{ $address }}" data-is-actual="{{ $isActualLocation ? 'true' : 'false' }}"
        data-status-text="{{ $this->getLocationStatusText() }}"
        data-status-class="{{ $this->getLocationStatusClass() }}"
        data-text-class="{{ $this->getLocationTextClass() }}"
        data-real-time="{{ $enableRealTimeUpdates ? 'true' : 'false' }}"
        data-user-id="{{ $userId }}">
    </div>
</div>

@assets
    <script>
        // Store map instances globally for real-time updates
        window.mapInstances = window.mapInstances || {};

        document.addEventListener('DOMContentLoaded', function() {
            initializeMaps();
        });

        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeMaps, 50);
        });

        function initializeMaps() {
            document.querySelectorAll('[id^="map-"]:not([data-initialized])').forEach(container => {
                if (!container || container._leaflet_id) return;

                const mapData = extractMapData(container);
                if (!mapData) return;

                try {
                    const map = createMap(container, mapData);
                    const marker = createMarker(map, mapData);

                    // Store instances for real-time updates
                    window.mapInstances[mapData.mapId] = {
                        map: map,
                        marker: marker,
                        container: container
                    };

                    setupZoomControls(map, mapData.mapId);
                    container.setAttribute('data-initialized', 'true');

                    console.log(`✅ Map initialized: ${mapData.mapId} (${mapData.isActual ? 'Actual' : 'Default'} Location, Real-time: ${mapData.realTime})`);
                } catch (error) {
                    console.error('Map initialization error:', error);
                }
            });
        }

        function extractMapData(container) {
            const lat = parseFloat(container.dataset.lat);
            const lng = parseFloat(container.dataset.lng);
            const zoom = parseInt(container.dataset.zoom);

            if (isNaN(lat) || isNaN(lng) || isNaN(zoom)) {
                console.error('Invalid map data for:', container.id);
                return null;
            }

            return {
                lat: lat,
                lng: lng,
                zoom: zoom,
                address: container.dataset.address || null,
                mapId: container.id,
                isActual: container.dataset.isActual === 'true',
                statusText: container.dataset.statusText,
                statusClass: container.dataset.statusClass,
                textClass: container.dataset.textClass,
                realTime: container.dataset.realTime === 'true',
                userId: container.dataset.userId
            };
        }

        function createMap(container, mapData) {
            const map = L.map(mapData.mapId, {
                attributionControl: false,
                zoomControl: false
            }).setView([mapData.lat, mapData.lng], mapData.zoom);

            L.tileLayer('https://mt0.google.com/vt/lyrs=m@221097413,traffic&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                minZoom: 1,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(map);

            return map;
        }

        function createMarker(map, mapData) {
            const userLocationIcon = L.icon({
                iconUrl: '/images/map-pin/user-location.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            const marker = L.marker([mapData.lat, mapData.lng], {
                icon: userLocationIcon
            }).addTo(map);

            updateMarkerPopup(marker, mapData);
            marker.openPopup();

            return marker;
        }

        function updateMarkerPopup(marker, mapData) {
            const popupContent = `
                <div class="min-w-28 max-w-40 text-xs">
                    <div class="flex items-center gap-1 mb-1">
                        <div aria-label="${mapData.isActual ? 'success' : 'warning'}"
                             class="status status-md ${mapData.statusClass} ${mapData.isActual ? '' : 'animate-pulse'}"></div>
                        <span class="${mapData.textClass} font-medium text-xs">${mapData.statusText}</span>
                    </div>
                    <div class="text-xs text-gray-600 font-medium mb-1 line-clamp-2 leading-tight">
                        ${mapData.address ? mapData.address.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : 'Lokasi tidak diketahui'}
                    </div>
                    <div class="flex gap-1">
                        <span class="badge badge-xs badge-soft badge-info">Lat: ${mapData.lat.toFixed(3)}°</span>
                        <span class="badge badge-xs badge-soft badge-info">Lng: ${mapData.lng.toFixed(3)}°</span>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
        }

        // Listen for real-time location updates
        document.addEventListener('livewire:init', function() {
            Livewire.on('update-map-location', function(data) {
                updateMapLocation(data);
            });
        });

        function updateMapLocation(data) {
            const { mapId, lat, lng, address, isActual, statusText, statusClass, textClass } = data;
            const instance = window.mapInstances[mapId];

            if (!instance) {
                console.warn('Map instance not found for real-time update:', mapId);
                return;
            }

            const { map, marker } = instance;

            // Smooth animation to new position
            const newLatLng = L.latLng(lat, lng);

            // Animate marker to new position
            marker.setLatLng(newLatLng);

            // Smoothly pan map to new center (optional - only if significantly different)
            const currentCenter = map.getCenter();
            const distance = currentCenter.distanceTo(newLatLng);

            if (distance > 100) { // Only pan if moved more than 100 meters
                map.panTo(newLatLng, {
                    animate: true,
                    duration: 1.0 // 1 second smooth animation
                });
            }

            // Update popup content
            const updatedMapData = {
                lat: lat,
                lng: lng,
                address: address,
                isActual: isActual,
                statusText: statusText,
                statusClass: statusClass,
                textClass: textClass
            };

            updateMarkerPopup(marker, updatedMapData);

            console.log(`📍 Map location updated: ${mapId} (${lat.toFixed(6)}, ${lng.toFixed(6)})`);
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

                zoomInBtn.disabled = currentZoom >= maxZoom;
                zoomOutBtn.disabled = currentZoom <= minZoom;

                zoomInBtn.classList.toggle('btn-disabled', currentZoom >= maxZoom);
                zoomOutBtn.classList.toggle('btn-disabled', currentZoom <= minZoom);
            }

            zoomInBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!zoomInBtn.disabled) map.zoomIn();
            });

            zoomOutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!zoomOutBtn.disabled) map.zoomOut();
            });

            map.on('zoom zoomend', updateButtonStates);
            updateButtonStates();
        }

        // Cleanup on page navigation
        document.addEventListener('livewire:navigating', function() {
            // Clean up map instances to prevent memory leaks
            Object.keys(window.mapInstances).forEach(mapId => {
                const instance = window.mapInstances[mapId];
                if (instance && instance.map) {
                    instance.map.remove();
                }
                delete window.mapInstances[mapId];
            });
        });
    </script>
@endassets
