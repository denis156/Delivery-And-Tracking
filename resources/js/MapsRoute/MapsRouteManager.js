// resources/js/MapsRoute/MapsRouteManager.js
// Route management dan real-time updates

import { mapsRouteCore } from './MapsRouteCore.js';

export class MapsRouteManager {
    constructor() {
        this.livewireEventsSetup = false;
    }

    /**
     * Initialize static route SEKALI SAJA ketika tracking dimulai
     */
    initializeStaticRoute(mapInstance, mapData, startLat, startLng) {
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
                mapsRouteCore.logError('initializeStaticRoute_fallback', fallbackError);
            }
        }
    }

    /**
     * Update route marker position dengan start location support
     */
    updateMarkerPosition(eventData) {
        let mapId, lat, lng, address, isActual, destinationLat, destinationLng, destinationAddress;
        let hasStartLocation, startLocationData, trackingSessionId;

        // Extract data from event
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
            console.warn('Invalid event data for updateMarkerPosition');
            return;
        }

        // Validate coordinates
        if (isNaN(lat) || isNaN(lng)) {
            console.warn('Invalid coordinates received:', { lat, lng });
            return;
        }

        const mapInstance = mapsRouteCore.getInstance(mapId);
        if (!mapInstance) {
            console.warn('Map instance not found for marker update:', mapId);
            return;
        }

        const { map, originMarker } = mapInstance;

        try {
            // Inisialisasi route SEKALI SAJA dengan start location data
            if (isActual && !mapInstance.routeInitialized && hasStartLocation && startLocationData) {
                const mapData = this.extractMapDataFromContainer(mapInstance.container);
                if (mapData) {
                    // Gunakan start location dari session sebagai start point
                    this.initializeStaticRoute(mapInstance, mapData, startLocationData.latitude, startLocationData.longitude);
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
            mapsRouteCore.updateOriginMarkerPopup(originMarker, mapData);

            // Route tetap statis dari start point ke destination
            console.log(`📍 Driver marker updated: ${mapId} (${lat.toFixed(3)}, ${lng.toFixed(3)}) - Route stays static`);

        } catch (error) {
            console.error('Error updating route marker position:', error);
            mapsRouteCore.logError('updateMarkerPosition', error, mapId);
        }
    }

    /**
     * Center map to show entire route
     */
    centerRouteView(mapId) {
        const mapInstance = mapsRouteCore.getInstance(mapId);
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
            mapsRouteCore.logError('centerRouteView', error, mapId);
        }
    }

    /**
     * Center map to specific location with max zoom
     */
    centerMapToLocation(eventData) {
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

        const mapInstance = mapsRouteCore.getInstance(mapId);
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
            mapsRouteCore.logError('centerMapToLocation', error, mapId);
        }
    }

    /**
     * Extract map data dari container untuk route initialization
     */
    extractMapDataFromContainer(container) {
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
     * Setup Livewire event listeners
     */
    setupLivewireEvents() {
        if (this.livewireEventsSetup || !window.Livewire) return;

        // Listen untuk update marker position events dengan route support
        window.Livewire.on('update-route-marker-position', (event) => {
            this.updateMarkerPosition(event);
        });

        window.Livewire.on('center-map-to-location', (event) => {
            this.centerMapToLocation(event);
        });

        this.livewireEventsSetup = true;
        console.log('✅ Livewire events setup for MapsRoute');
    }

    /**
     * Initialize everything
     */
    initialize() {
        // Setup Livewire events first
        this.setupLivewireEvents();

        // Initialize all maps
        mapsRouteCore.initializeAll();
    }
}

// Export singleton instance
export const mapsRouteManager = new MapsRouteManager();
