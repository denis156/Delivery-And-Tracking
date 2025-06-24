// resources/js/MapsRoute/MapsRouteMain.js
// Main entry point untuk Maps Route functionality

import { mapsRouteCore } from './MapsRouteCore.js';
import { mapsRouteManager } from './MapsRouteManager.js';

// Global functions yang dibutuhkan oleh component blade
window.centerRouteView = function(mapId) {
    mapsRouteManager.centerRouteView(mapId);
};

// Make instances accessible globally untuk debugging
window.mapRouteInstances = mapsRouteCore.getAllInstances();

// Debug helper functions
window.debugMapsRoute = {
    instances: () => mapsRouteCore.getAllInstances(),
    instance: (mapId) => mapsRouteCore.getInstance(mapId),
    centerRoute: (mapId) => mapsRouteManager.centerRouteView(mapId),
    core: mapsRouteCore,
    manager: mapsRouteManager,
    logs: true
};

/**
 * Initialize maps route system
 */
function initializeMapsRoute() {
    // Wait for Livewire to be ready
    if (typeof window.Livewire !== 'undefined') {
        mapsRouteManager.initialize();
    } else {
        // Fallback - initialize core functionality without Livewire events
        mapsRouteCore.initializeAll();
    }
}

/**
 * DOM Ready handlers
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeMapsRoute();
});

// Livewire navigation handlers
document.addEventListener('livewire:navigated', function() {
    setTimeout(() => {
        initializeMapsRoute();
    }, 50);
});

// Livewire init handlers (for events)
document.addEventListener('livewire:init', function() {
    mapsRouteManager.setupLivewireEvents();
});

// Cleanup on page navigation
document.addEventListener('livewire:navigating', function() {
    mapsRouteCore.cleanup();
});

// Export untuk penggunaan internal
export { mapsRouteCore, mapsRouteManager };
