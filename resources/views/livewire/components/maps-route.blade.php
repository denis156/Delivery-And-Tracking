{{-- resources/views/livewire/components/maps-route.blade.php --}}
@php
    // Cek apakah user sedang tracking untuk conditional polling - SAMA seperti Maps component
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
                        Peta dan route mungkin tidak dapat dimuat dengan sempurna
                    </p>
                </div>
                <x-badge value="Mode Offline" class="badge-error badge-outline" />
            </div>
        </x-card>
    </div>

    <!-- Corner Badges - Top -->
    @if ($badgeTopLeft || $badgeTopRight)
        <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 items-end">
            @if ($badgeTopLeft)
                <x-badge value="{{ $badgeTopLeft }}" class="badge-success badge-soft badge-xs" />
            @endif
            @if ($badgeTopRight)
                <x-badge value="{{ $badgeTopRight }}" class="badge-info badge-soft badge-xs" />
            @endif
        </div>
    @endif

    <!-- Custom Controls - Top Left -->
    <div class="absolute top-4 left-4 z-10 flex flex-col gap-1">
        <!-- Zoom Controls -->
        <button id="zoom-in-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-plus-duotone" class="w-4 h-4" />
        </button>
        <button id="zoom-out-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft">
            <x-icon name="phosphor.magnifying-glass-minus-duotone" class="w-4 h-4" />
        </button>

        <!-- Navigation Controls -->
        @if ($isActualLocation)
            <button id="go-to-location-{{ $mapId }}" wire:click="goToMyLocation"
                class="btn btn-sm btn-circle btn-success btn-soft" title="Ke Lokasi Saya">
                <x-icon name="phosphor.crosshair-duotone" class="w-4 h-4" />
            </button>
        @endif

        <button id="go-to-destination-{{ $mapId }}" wire:click="goToDestination"
            class="btn btn-sm btn-circle btn-error btn-soft" title="Ke Tujuan">
            <x-icon name="phosphor.map-pin-area-duotone" class="w-4 h-4" />
        </button>

        <!-- Center Route Button -->
        <button id="center-route-{{ $mapId }}" onclick="centerRouteView('{{ $mapId }}')"
            class="btn btn-sm btn-circle btn-warning btn-soft" title="Lihat Route">
            <x-icon name="phosphor.path-duotone" class="w-4 h-4" />
        </button>
    </div>

    <!-- Distance Info - Top Center -->
    @if($this->getDistanceText())
        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10">
            <div class="bg-base-100/90 backdrop-blur-sm rounded-lg px-3 py-1 border border-primary/20">
                <div class="flex items-center gap-2 text-sm">
                    <x-icon name="phosphor.ruler" class="w-4 h-4 text-primary" />
                    <span class="font-medium">{{ $this->getDistanceText() }}</span>
                    @if($this->isNearDestination())
                        <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                    @endif
                </div>
            </div>
        </div>
    @endif

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
        data-destination-lat="{{ $destinationLat }}"
        data-destination-lng="{{ $destinationLng }}"
        data-destination-address="{{ $destinationAddress }}"
        data-show-route="{{ $showRoute ? 'true' : 'false' }}"
        data-route-color="{{ $routeColor }}"
        data-route-weight="{{ $routeWeight }}">
    </div>
</div>

@assets
    <script>
        // Global object untuk menyimpan map instances dan markers dengan route support
        window.mapRouteInstances = window.mapRouteInstances || {};

        /**
         * Center map to show entire route
         */
        function centerRouteView(mapId) {
            const mapInstance = window.mapRouteInstances[mapId];
            if (!mapInstance) {
                return;
            }

            try {
                const { map, originMarker, destinationMarker, routePolyline } = mapInstance;

                // Jika menggunakan routing control
                if (routePolyline && routePolyline.getWaypoints) {
                    const waypoints = routePolyline.getWaypoints();
                    if (waypoints.length >= 2) {
                        const group = new L.featureGroup([
                            L.marker(waypoints[0].latLng),
                            L.marker(waypoints[waypoints.length - 1].latLng)
                        ]);
                        map.fitBounds(group.getBounds().pad(0.1));
                        return;
                    }
                }

                // Fallback untuk simple polyline atau markers
                const group = new L.featureGroup([originMarker, destinationMarker]);
                map.fitBounds(group.getBounds().pad(0.1));
            } catch (error) {
                console.error('Error centering route view:', error);
            }
        }

        /**
         * Center map to specific location with max zoom
         */
        function centerMapToLocation(eventData) {
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

            const mapInstance = window.mapRouteInstances[mapId];
            if (!mapInstance) {
                return;
            }

            const { map } = mapInstance;

            try {
                map.flyTo([lat, lng], zoom, {
                    duration: 1.5,
                    easeLinearity: 0.25
                });
            } catch (error) {
                if (window.Livewire) {
                    window.Livewire.dispatch('log-js-error', {
                        component: 'MapsRoute',
                        function: 'centerMapToLocation',
                        error: error.message,
                        mapId: mapId,
                        user_agent: navigator.userAgent
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeRouteMaps();
        });

        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeRouteMaps, 50);
        });

        // Listen untuk update marker position events dengan route support
        document.addEventListener('livewire:init', function() {
            Livewire.on('update-route-marker-position', (event) => {
                updateRouteMarkerPosition(event);
            });

            Livewire.on('center-map-to-location', (event) => {
                centerMapToLocation(event);
            });
        });

        function initializeRouteMaps() {
            document.querySelectorAll('[id^="map-route-"]:not([data-initialized])').forEach(container => {
                if (!container || container._leaflet_id) return;

                const mapData = extractRouteMapData(container);
                if (!mapData) return;

                try {
                    const map = createRouteMap(container, mapData);
                    const { originMarker, destinationMarker } = createRouteMarkers(map, mapData);
                    const routePolyline = createSimpleRoute(map, mapData);

                    // Store map dan markers dalam global object untuk real-time updates
                    window.mapRouteInstances[mapData.mapId] = {
                        map: map,
                        originMarker: originMarker,
                        destinationMarker: destinationMarker,
                        routePolyline: routePolyline,
                        container: container
                    };

                    setupZoomControls(map, mapData.mapId);
                    container.setAttribute('data-initialized', 'true');

                    console.log(`✅ Route Map initialized: ${mapData.mapId} (${mapData.isActual ? 'Actual' : 'Default'} Location)`);
                } catch (error) {
                    console.error('Route Map initialization error:', error);
                }
            });
        }

        function extractRouteMapData(container) {
            const lat = parseFloat(container.dataset.lat);
            const lng = parseFloat(container.dataset.lng);
            const zoom = parseInt(container.dataset.zoom);
            const destinationLat = parseFloat(container.dataset.destinationLat);
            const destinationLng = parseFloat(container.dataset.destinationLng);

            if (isNaN(lat) || isNaN(lng) || isNaN(zoom) || isNaN(destinationLat) || isNaN(destinationLng)) {
                console.error('Invalid route map data for:', container.id);
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
                destinationLat: destinationLat,
                destinationLng: destinationLng,
                destinationAddress: container.dataset.destinationAddress || 'Tujuan',
                showRoute: container.dataset.showRoute === 'true',
                routeColor: container.dataset.routeColor || '#DC2626',
                routeWeight: parseInt(container.dataset.routeWeight) || 6
            };
        }

        function createRouteMap(container, mapData) {
            const map = L.map(mapData.mapId, {
                attributionControl: false,
                zoomControl: false
            });

            // Add tile layer
            L.tileLayer('https://mt0.google.com/vt/lyrs=m@221097413,traffic&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                minZoom: 1,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(map);

            // Set initial view to show both origin and destination
            const bounds = L.latLngBounds([
                [mapData.lat, mapData.lng],
                [mapData.destinationLat, mapData.destinationLng]
            ]);
            map.fitBounds(bounds.pad(0.1));

            return map;
        }

        function createRouteMarkers(map, mapData) {
            // Origin marker (User location) - menggunakan icon yang sama seperti Maps component
            const userLocationIcon = L.icon({
                iconUrl: '/images/map-pin/location-driver.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            const originMarker = L.marker([mapData.lat, mapData.lng], {
                icon: userLocationIcon
            }).addTo(map);

            // Destination marker - menggunakan custom destination icon
            const destinationIcon = L.icon({
                iconUrl: '/images/map-pin/location-destination.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            const destinationMarker = L.marker([mapData.destinationLat, mapData.destinationLng], {
                icon: destinationIcon
            }).addTo(map);

            // Update popups
            updateOriginMarkerPopup(originMarker, mapData);
            updateDestinationMarkerPopup(destinationMarker, mapData);

            return { originMarker, destinationMarker };
        }

        function createSimpleRoute(map, mapData) {
            if (!mapData.showRoute) return null;

            try {
                // Create routing control using OSRM untuk route yang mengikuti jalan
                const routeControl = L.Routing.control({
                    waypoints: [
                        L.latLng(mapData.lat, mapData.lng),
                        L.latLng(mapData.destinationLat, mapData.destinationLng)
                    ],
                    routeWhileDragging: false,
                    addWaypoints: false,
                    createMarker: function() { return null; }, // Don't create default markers
                    lineOptions: {
                        styles: [{
                            color: mapData.routeColor,
                            weight: mapData.routeWeight,
                            opacity: 0.8
                        }]
                    },
                    router: L.Routing.osrmv1({
                        serviceUrl: 'https://router.project-osrm.org/route/v1',
                        profile: 'driving',
                        timeout: 5000 // 5 second timeout
                    }),
                    show: false, // Hide direction panel
                    collapsible: false
                }).addTo(map);

                // Hide the routing control interface
                const routingContainer = routeControl.getContainer();
                if (routingContainer) {
                    routingContainer.style.display = 'none';
                }

                // Suppress console warning (opsional)
                const originalConsoleWarn = console.warn;
                console.warn = function(msg) {
                    if (msg && msg.includes && msg.includes('OSRM')) {
                        return; // Skip OSRM warnings
                    }
                    originalConsoleWarn.apply(console, arguments);
                };

                return routeControl;
            } catch (error) {
                console.error('Error creating route control:', error);
                // Fallback ke simple polyline jika routing gagal
                const routePolyline = L.polyline([
                    [mapData.lat, mapData.lng],
                    [mapData.destinationLat, mapData.destinationLng]
                ], {
                    color: mapData.routeColor,
                    weight: mapData.routeWeight,
                    opacity: 0.8
                }).addTo(map);
                return routePolyline;
            }
        }

        /**
         * Update route marker position - Core functionality untuk real-time updates
         */
        function updateRouteMarkerPosition(eventData) {
            let mapId, lat, lng, address, isActual, destinationLat, destinationLng, destinationAddress;

            if (Array.isArray(eventData) && eventData.length > 0) {
                const data = eventData[0];
                mapId = data.mapId;
                lat = parseFloat(data.lat);
                lng = parseFloat(data.lng);
                address = data.address;
                isActual = data.isActual;
                destinationLat = parseFloat(data.destinationLat);
                destinationLng = parseFloat(data.destinationLng);
                destinationAddress = data.destinationAddress;
            } else if (typeof eventData === 'object') {
                mapId = eventData.mapId;
                lat = parseFloat(eventData.lat);
                lng = parseFloat(eventData.lng);
                address = eventData.address;
                isActual = eventData.isActual;
                destinationLat = parseFloat(eventData.destinationLat);
                destinationLng = parseFloat(eventData.destinationLng);
                destinationAddress = eventData.destinationAddress;
            } else {
                return;
            }

            // Validate coordinates
            if (isNaN(lat) || isNaN(lng)) {
                return;
            }

            const mapInstance = window.mapRouteInstances[mapId];
            if (!mapInstance) {
                return;
            }

            const { map, originMarker, destinationMarker, routePolyline } = mapInstance;

            try {
                // Update origin marker position (smooth movement)
                const newLatLng = L.latLng(lat, lng);
                originMarker.setLatLng(newLatLng);

                // Update origin marker popup
                const mapData = {
                    lat: lat,
                    lng: lng,
                    address: address,
                    isActual: isActual,
                    statusText: isActual ? 'Lokasi Aktual' : 'Lokasi Default',
                    statusClass: isActual ? 'status-success' : 'status-warning',
                    textClass: isActual ? 'text-success' : 'text-warning'
                };
                updateOriginMarkerPopup(originMarker, mapData);

                // Update route jika ada
                if (routePolyline) {
                    // Jika menggunakan routing control
                    if (routePolyline.setWaypoints) {
                        routePolyline.setWaypoints([
                            L.latLng(lat, lng),
                            L.latLng(destinationLat, destinationLng)
                        ]);
                    }
                    // Jika menggunakan simple polyline
                    else if (routePolyline.setLatLngs) {
                        routePolyline.setLatLngs([
                            [lat, lng],
                            [destinationLat, destinationLng]
                        ]);
                    }
                }

                console.log(`📍 Route marker updated: ${mapId} (${lat.toFixed(6)}, ${lng.toFixed(6)})`);

            } catch (error) {
                if (window.Livewire) {
                    window.Livewire.dispatch('log-js-error', {
                        component: 'MapsRoute',
                        function: 'updateRouteMarkerPosition',
                        error: error.message,
                        mapId: mapId,
                        user_agent: navigator.userAgent
                    });
                }
            }
        }

        /**
         * Update origin marker popup content
         */
        function updateOriginMarkerPopup(marker, mapData) {
            const popupContent = `
                <div class="min-w-32 max-w-44 text-xs">
                    <div class="flex items-center gap-1 mb-2">
                        <div aria-label="${mapData.isActual ? 'success' : 'warning'}"
                             class="status status-md ${mapData.statusClass} ${mapData.isActual ? '' : 'animate-pulse'}"></div>
                        <span class="${mapData.textClass} font-medium text-xs">${mapData.statusText}</span>
                        <span class="badge badge-xs badge-info">ORIGIN</span>
                    </div>
                    <div class="text-xs text-gray-600 font-medium mb-2 line-clamp-2 leading-tight">
                        ${mapData.address ? mapData.address.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : 'Lokasi tidak diketahui'}
                    </div>
                    <div class="flex gap-1">
                        <span class="badge badge-xs badge-soft badge-success">Lat: ${mapData.lat.toFixed(3)}°</span>
                        <span class="badge badge-xs badge-soft badge-success">Lng: ${mapData.lng.toFixed(3)}°</span>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
        }

        /**
         * Update destination marker popup content
         */
        function updateDestinationMarkerPopup(marker, mapData) {
            const popupContent = `
                <div class="min-w-32 max-w-44 text-xs">
                    <div class="flex items-center gap-1 mb-2">
                        <div aria-label="error" class="status status-md status-error"></div>
                        <span class="text-error font-medium text-xs">Tujuan</span>
                        <span class="badge badge-xs badge-error">DESTINATION</span>
                    </div>
                    <div class="text-xs text-gray-600 font-medium mb-2 line-clamp-2 leading-tight">
                        ${mapData.destinationAddress ? mapData.destinationAddress.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : 'Alamat tujuan'}
                    </div>
                    <div class="flex gap-1">
                        <span class="badge badge-xs badge-soft badge-error">Lat: ${mapData.destinationLat.toFixed(3)}°</span>
                        <span class="badge badge-xs badge-soft badge-error">Lng: ${mapData.destinationLng.toFixed(3)}°</span>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
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

                // Update zoom in button
                if (currentZoom >= maxZoom) {
                    zoomInBtn.disabled = true;
                    zoomInBtn.style.opacity = '0.5';
                } else {
                    zoomInBtn.disabled = false;
                    zoomInBtn.style.opacity = '';
                }

                // Update zoom out button
                if (currentZoom <= minZoom) {
                    zoomOutBtn.disabled = true;
                    zoomOutBtn.style.opacity = '0.5';
                } else {
                    zoomOutBtn.disabled = false;
                    zoomOutBtn.style.opacity = '';
                }
            }

            // Event listeners
            zoomInBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!zoomInBtn.disabled) map.zoomIn();
            });

            zoomOutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!zoomOutBtn.disabled) map.zoomOut();
            });

            // Listen to zoom events
            map.on('zoom zoomend', updateButtonStates);
            updateButtonStates();
        }

        // Cleanup on page navigation
        document.addEventListener('livewire:navigating', function() {
            Object.keys(window.mapRouteInstances).forEach(mapId => {
                const instance = window.mapRouteInstances[mapId];
                if (instance && instance.map) {
                    // Clean up routing control jika ada
                    if (instance.routePolyline && instance.routePolyline.remove) {
                        try {
                            instance.map.removeControl(instance.routePolyline);
                        } catch (e) {
                            // Ignore cleanup errors
                        }
                    }
                    instance.map.remove();
                }
                delete window.mapRouteInstances[mapId];
            });
        });
    </script>
@endassets
