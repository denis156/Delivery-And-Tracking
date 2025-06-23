{{-- resources/views/livewire/components/maps-route.blade.php --}}
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
                        Peta dan route mungkin tidak dapat dimuat dengan sempurna
                    </p>
                </div>
                <x-badge value="Mode Offline" class="badge-error badge-outline" />
            </div>
        </x-card>
    </div>

    <!-- Corner Badges - Top Right -->
    @if ($badgeTopLeft || $badgeTopRight)
        <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 items-end">
            @if ($badgeTopRight)
                <x-badge value="{{ $badgeTopRight }}" class="badge-info badge-soft badge-xs" />
            @endif
            @if ($badgeTopLeft)
                <x-badge value="{{ $badgeTopLeft }}" class="badge-success badge-soft badge-xs" />
            @endif
        </div>
    @endif

    <!-- Custom Controls - Top Left -->
    <div class="absolute top-4 left-4 z-10 flex flex-col gap-1">
        <!-- Zoom Controls -->
        <button id="zoom-in-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft" title="Zoom In">
            <x-icon name="phosphor.magnifying-glass-plus-duotone" class="w-4 h-4" />
        </button>
        <button id="zoom-out-{{ $mapId }}" class="btn btn-sm btn-circle btn-primary btn-soft" title="Zoom Out">
            <x-icon name="phosphor.magnifying-glass-minus-duotone" class="w-4 h-4" />
        </button>

        <!-- Navigation Controls -->
        @if ($isActualLocation)
            <button id="go-to-location-{{ $mapId }}" wire:click="goToMyLocation"
                class="btn btn-sm btn-circle btn-success btn-soft" title="Ke Lokasi Saya">
                <x-icon name="phosphor.crosshair-duotone" class="w-4 h-4" />
            </button>
        @endif

        <!-- Start Location Button -->
        @if ($hasStartLocation)
            <button id="go-to-start-{{ $mapId }}" wire:click="goToStartLocation"
                class="btn btn-sm btn-circle btn-info btn-soft" title="Ke Start Point">
                <x-icon name="phosphor.flag-duotone" class="w-4 h-4" />
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
        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10 flex flex-col gap-2 items-center">
            <x-badge value="{{ $this->getDistanceText() }}" class="badge-primary badge-soft badge-sm" />
            @if($this->getDistanceFromStartText())
                <x-badge value="{{ $this->getDistanceFromStartText() }}" class="badge-info badge-soft badge-xs" />
            @endif
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
        data-lat="{{ $lat }}"
        data-lng="{{ $lng }}"
        data-zoom="{{ $zoom }}"
        data-address="{{ $address }}"
        data-is-actual="{{ $isActualLocation ? 'true' : 'false' }}"
        data-status-text="{{ $this->getLocationStatusText() }}"
        data-status-class="{{ $this->getLocationStatusClass() }}"
        data-text-class="{{ $this->getLocationTextClass() }}"
        data-destination-lat="{{ $destinationLat }}"
        data-destination-lng="{{ $destinationLng }}"
        data-destination-address="{{ $destinationAddress }}"
        data-show-route="{{ $showRoute ? 'true' : 'false' }}"
        data-route-color="{{ $routeColor }}"
        data-route-weight="{{ $routeWeight }}"
        data-is-tracking="{{ $isTracking ? 'true' : 'false' }}"
        data-has-start-location="{{ $hasStartLocation ? 'true' : 'false' }}"
        @if($hasStartLocation && $startLocationData)
        data-start-lat="{{ $startLocationData['latitude'] ?? '' }}"
        data-start-lng="{{ $startLocationData['longitude'] ?? '' }}"
        data-start-session-id="{{ $trackingSessionId ?? '' }}"
        @endif
        >
    </div>
</div>

@assets
    <script>
        // Global object untuk menyimpan map instances dengan route tracking
        window.mapRouteInstances = window.mapRouteInstances || {};

        /**
         * Center map to show entire route
         */
        function centerRouteView(mapId) {
            const mapInstance = window.mapRouteInstances[mapId];
            if (!mapInstance) {
                console.warn('Map instance not found:', mapId);
                return;
            }

            try {
                const { map, originMarker, destinationMarker, startLocationMarker } = mapInstance;

                // Fit bounds ke start point (jika ada), current location, dan destination
                const markersToInclude = [destinationMarker];

                if (startLocationMarker) {
                    markersToInclude.push(startLocationMarker);
                } else {
                    markersToInclude.push(originMarker);
                }

                const group = L.featureGroup(markersToInclude);
                map.fitBounds(group.getBounds().pad(0.1));

                console.log('✅ Route view centered for map:', mapId);
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
                console.warn('Invalid event data for centerMapToLocation');
                return;
            }

            const mapInstance = window.mapRouteInstances[mapId];
            if (!mapInstance) {
                console.warn('Map instance not found for centerMapToLocation:', mapId);
                return;
            }

            const { map } = mapInstance;

            try {
                map.flyTo([lat, lng], zoom, {
                    duration: 1.5,
                    easeLinearity: 0.25
                });

                console.log(`📍 Map centered to: ${lat.toFixed(3)}, ${lng.toFixed(3)}`);
            } catch (error) {
                console.error('Error centering map:', error);
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

                    // Store map dan markers dalam global object untuk real-time updates
                    window.mapRouteInstances[mapData.mapId] = {
                        map: map,
                        originMarker: originMarker,
                        destinationMarker: destinationMarker,
                        startLocationMarker: null, // Akan diset saat tracking dimulai
                        staticRoute: null, // Route yang statis
                        routeInitialized: false,
                        startLocation: null, // Koordinat start point
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
                routeWeight: parseInt(container.dataset.routeWeight) || 6,
                // Start location data
                isTracking: container.dataset.isTracking === 'true',
                hasStartLocation: container.dataset.hasStartLocation === 'true',
                startLat: parseFloat(container.dataset.startLat) || null,
                startLng: parseFloat(container.dataset.startLng) || null,
                startSessionId: container.dataset.startSessionId || null
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
            // Origin marker (User location) - marker yang akan bergerak
            const userLocationIcon = L.icon({
                iconUrl: '/images/map-pin/location-driver.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            const originMarker = L.marker([mapData.lat, mapData.lng], {
                icon: userLocationIcon
            }).addTo(map);

            // Destination marker - marker statis
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

        /**
         * Initialize static route SEKALI SAJA ketika tracking dimulai
         */
        function initializeStaticRoute(mapInstance, mapData, startLat, startLng) {
            const { map } = mapInstance;

            // Jika route sudah diinisialisasi, jangan buat lagi
            if (mapInstance.routeInitialized) {
                console.log('Route already initialized, skipping...');
                return;
            }

            try {
                // Set start location (titik awal yang tetap)
                mapInstance.startLocation = { lat: startLat, lng: startLng };

                // Buat start location marker (titik hijau untuk start)
                const startIcon = L.icon({
                    iconUrl: '/images/map-pin/location-start.png',
                    iconSize: [24, 24],
                    iconAnchor: [12, 24],
                    popupAnchor: [0, -24]
                });

                const startLocationMarker = L.marker([startLat, startLng], {
                    icon: startIcon
                }).addTo(map);

                startLocationMarker.bindPopup(`
                <div class="min-w-32 max-w-44 text-xs">
                    <div class="flex items-center gap-1 mb-2">
                        <div aria-label="success" class="status status-md status-success"></div>
                        <span class="text-success font-medium text-xs">Titik Start</span>
                        <span class="badge badge-xs badge-success">START</span>
                    </div>
                    <div class="text-xs text-gray-600 font-medium mb-2">
                        Lokasi awal perjalanan
                    </div>
                    <div class="flex gap-1">
                        <span class="badge badge-xs badge-soft badge-success">Lat: ${startLat.toFixed(3)}°</span>
                        <span class="badge badge-xs badge-soft badge-success">Lng: ${startLng.toFixed(3)}°</span>
                    </div>
                </div>
            `);

                mapInstance.startLocationMarker = startLocationMarker;

                // Buat route statis SEKALI SAJA dari start ke destination
                if (mapData.showRoute) {
                    const routeControl = L.Routing.control({
                        waypoints: [
                            L.latLng(startLat, startLng), // Start point (TETAP)
                            L.latLng(mapData.destinationLat, mapData.destinationLng) // Destination (TETAP)
                        ],
                        routeWhileDragging: false,
                        addWaypoints: false,
                        createMarker: function() { return null; }, // Jangan buat marker default
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
                            timeout: 10000
                        }),
                        show: false, // Hide direction panel
                        collapsible: false
                    }).addTo(map);

                    // Hide routing control interface
                    const routingContainer = routeControl.getContainer();
                    if (routingContainer) {
                        routingContainer.style.display = 'none';
                    }

                    mapInstance.staticRoute = routeControl;
                }

                // Mark route sebagai sudah diinisialisasi
                mapInstance.routeInitialized = true;

                console.log(`🛣️ Static route initialized from start point (${startLat.toFixed(3)}, ${startLng.toFixed(3)})`);

            } catch (error) {
                console.error('Error initializing static route:', error);

                // Fallback ke simple polyline jika routing gagal
                try {
                    const routePolyline = L.polyline([
                        [startLat, startLng],
                        [mapData.destinationLat, mapData.destinationLng]
                    ], {
                        color: mapData.routeColor,
                        weight: mapData.routeWeight,
                        opacity: 0.8
                    }).addTo(map);

                    mapInstance.staticRoute = routePolyline;
                    mapInstance.routeInitialized = true;

                    console.log('🛣️ Fallback route created with simple polyline');
                } catch (fallbackError) {
                    console.error('Fallback route creation failed:', fallbackError);
                }
            }
        }

        /**
         * Update route marker position dengan start location support
         */
        function updateRouteMarkerPosition(eventData) {
            let mapId, lat, lng, address, isActual, destinationLat, destinationLng, destinationAddress;
            let hasStartLocation, startLocationData, trackingSessionId;

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
                hasStartLocation = data.hasStartLocation;
                startLocationData = data.startLocationData;
                trackingSessionId = data.trackingSessionId;
            } else if (typeof eventData === 'object') {
                mapId = eventData.mapId;
                lat = parseFloat(eventData.lat);
                lng = parseFloat(eventData.lng);
                address = eventData.address;
                isActual = eventData.isActual;
                destinationLat = parseFloat(eventData.destinationLat);
                destinationLng = parseFloat(eventData.destinationLng);
                destinationAddress = eventData.destinationAddress;
                hasStartLocation = eventData.hasStartLocation;
                startLocationData = eventData.startLocationData;
                trackingSessionId = eventData.trackingSessionId;
            } else {
                console.warn('Invalid event data for updateRouteMarkerPosition');
                return;
            }

            // Validate coordinates
            if (isNaN(lat) || isNaN(lng)) {
                console.warn('Invalid coordinates received:', { lat, lng });
                return;
            }

            const mapInstance = window.mapRouteInstances[mapId];
            if (!mapInstance) {
                console.warn('Map instance not found for marker update:', mapId);
                return;
            }

            const { map, originMarker } = mapInstance;

            try {
                // Inisialisasi route SEKALI SAJA dengan start location data
                if (isActual && !mapInstance.routeInitialized && hasStartLocation && startLocationData) {
                    const mapData = extractRouteMapDataFromMarker(mapInstance.container);
                    if (mapData) {
                        // Gunakan start location dari session sebagai start point
                        initializeStaticRoute(mapInstance, mapData, startLocationData.latitude, startLocationData.longitude);
                    }
                }

                // Update HANYA posisi driver marker (smooth movement)
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

                // Route tetap statis dari start point ke destination
                console.log(`📍 Driver marker updated: ${mapId} (${lat.toFixed(3)}, ${lng.toFixed(3)}) - Route stays static`);

            } catch (error) {
                console.error('Error updating route marker position:', error);
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
         * Extract map data dari container untuk route initialization
         */
        function extractRouteMapDataFromMarker(container) {
            const destinationLat = parseFloat(container.dataset.destinationLat);
            const destinationLng = parseFloat(container.dataset.destinationLng);

            if (isNaN(destinationLat) || isNaN(destinationLng)) {
                console.error('Invalid destination coordinates in container');
                return null;
            }

            return {
                destinationLat: destinationLat,
                destinationLng: destinationLng,
                destinationAddress: container.dataset.destinationAddress || 'Tujuan',
                showRoute: container.dataset.showRoute === 'true',
                routeColor: container.dataset.routeColor || '#DC2626',
                routeWeight: parseInt(container.dataset.routeWeight) || 6
            };
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
                    <span class="badge badge-xs badge-info">DRIVER</span>
                </div>
                <div class="text-xs text-gray-600 font-medium mb-2 line-clamp-2 leading-tight">
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
                console.warn('Zoom control buttons not found for map:', mapId);
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
            map.on('zoom zoomend', updateButtonStates);
            updateButtonStates();
        }

        // Cleanup on page navigation
        document.addEventListener('livewire:navigating', function() {
            Object.keys(window.mapRouteInstances).forEach(mapId => {
                const instance = window.mapRouteInstances[mapId];
                if (instance && instance.map) {
                    try {
                        // Clean up routing control jika ada
                        if (instance.staticRoute && instance.staticRoute.remove) {
                            instance.map.removeControl(instance.staticRoute);
                        }

                        // Remove all markers
                        if (instance.originMarker) {
                            instance.map.removeLayer(instance.originMarker);
                        }
                        if (instance.destinationMarker) {
                            instance.map.removeLayer(instance.destinationMarker);
                        }
                        if (instance.startLocationMarker) {
                            instance.map.removeLayer(instance.startLocationMarker);
                        }

                        // Remove map
                        instance.map.remove();

                        console.log('✅ Map cleaned up:', mapId);
                    } catch (error) {
                        console.warn('Error cleaning up map:', mapId, error);
                    }
                }
                delete window.mapRouteInstances[mapId];
            });
        });

        // Debug helper functions
        window.debugMapsRoute = {
            instances: () => window.mapRouteInstances,
            instance: (mapId) => window.mapRouteInstances[mapId],
            centerRoute: (mapId) => centerRouteView(mapId),
            logs: true
        };
    </script>
@endassets
