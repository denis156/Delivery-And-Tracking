// resources/js/MapsRoute/MapsRouteCore.js
// Core functionality untuk Maps Route component

import { mapsRouteRotation } from './MapsRouteRotation.js';

export class MapsRouteCore {
    constructor() {
        this.instances = {};
        this.isInitialized = false;
    }

    /**
     * Initialize all route maps on page
     */
    initializeAll() {
        if (this.isInitialized) return;

        document.querySelectorAll('[id^="map-route-"]:not([data-initialized])').forEach(container => {
            if (!container || container._leaflet_id) return;

            const mapData = this.extractMapData(container);
            if (!mapData) return;

            try {
                const map = this.createMap(container, mapData);
                const { originMarker, destinationMarker } = this.createMarkers(map, mapData);

                // Store instance
                this.instances[mapData.mapId] = {
                    map: map,
                    originMarker: originMarker,
                    destinationMarker: destinationMarker,
                    startLocationMarker: null,
                    staticRoute: null,
                    routeInitialized: false,
                    startLocation: null,
                    container: container
                };

                this.setupZoomControls(map, mapData.mapId);
                container.setAttribute('data-initialized', 'true');

                console.log(`✅ Route Map initialized: ${mapData.mapId} (${mapData.isActual ? 'Actual' : 'Default'} Location)`);
            } catch (error) {
                console.error('Route Map initialization error:', error);
                this.logError('initializeAll', error, mapData.mapId);
            }
        });

        this.isInitialized = true;
    }

    /**
     * Extract map data from container element
     */
    extractMapData(container) {
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

    /**
     * Create Leaflet map instance
     */
    createMap(container, mapData) {
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

    /**
     * Create map markers
     */
    createMarkers(map, mapData) {
        // Origin marker (User location) - marker yang akan bergerak dan berotasi
        const userLocationIcon = L.icon({
            iconUrl: '/images/map-pin/location-driver.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
            className: 'rotating-marker' // Add class for identification
        });

        // Create RotatedMarker for driver location
        const originMarker = L.marker([mapData.lat, mapData.lng], {
            icon: userLocationIcon,
            rotationAngle: 0, // Initial rotation angle
            rotationOrigin: 'center center' // Rotation around center
        }).addTo(map);

        // Destination marker - marker statis (regular marker)
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
        this.updateOriginMarkerPopup(originMarker, mapData);
        this.updateDestinationMarkerPopup(destinationMarker, mapData);

        return { originMarker, destinationMarker };
    }

    /**
     * Setup zoom controls for map
     */
    setupZoomControls(map, mapId) {
        const zoomInBtn = document.getElementById(`zoom-in-${mapId}`);
        const zoomOutBtn = document.getElementById(`zoom-out-${mapId}`);

        if (!zoomInBtn || !zoomOutBtn) {
            console.warn('Zoom control buttons not found for map:', mapId);
            return;
        }

        const updateButtonStates = () => {
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
        };

        // Event listeners
        zoomInBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!zoomInBtn.disabled) {
                map.zoomIn();
            }
        });

        zoomOutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!zoomOutBtn.disabled) {
                map.zoomOut();
            }
        });

        // Listen to zoom events
        map.on('zoom zoomend', updateButtonStates);
        updateButtonStates();
    }

    /**
     * Get instance by map ID
     */
    getInstance(mapId) {
        return this.instances[mapId] || null;
    }

    /**
     * Get all instances
     */
    getAllInstances() {
        return this.instances;
    }

    /**
     * Update origin marker popup content
     */
    updateOriginMarkerPopup(marker, mapData) {
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
    updateDestinationMarkerPopup(marker, mapData) {
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

    /**
     * Log JavaScript errors to server
     */
    logError(functionName, error, mapId = null) {
        if (window.Livewire) {
            window.Livewire.dispatch('log-js-error', {
                component: 'MapsRoute',
                function: functionName,
                error: error.message,
                mapId: mapId,
                user_agent: navigator.userAgent
            });
        }
    }

    /**
     * Cleanup all instances
     */
    cleanup() {
        Object.keys(this.instances).forEach(mapId => {
            const instance = this.instances[mapId];
            if (instance && instance.map) {
                try {
                    // Clean up routing control
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
            delete this.instances[mapId];
        });

        this.isInitialized = false;
    }
}

// Export singleton instance
export const mapsRouteCore = new MapsRouteCore();
