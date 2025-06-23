// resources/js/app.js
import '../css/app.css';
import './bootstrap';

// Lazy load Leaflet only when needed
async function loadLeaflet() {
    const [L, routing] = await Promise.all([
        import('leaflet'),
        import('leaflet-routing-machine')
    ]);

    // Import Leaflet CSS dynamically
    await import('leaflet/dist/leaflet.css');
    await import('leaflet-routing-machine/dist/leaflet-routing-machine.css');

    // Fix Leaflet default markers icons with Vite
    const [markerIcon, markerIcon2x, markerShadow] = await Promise.all([
        import('leaflet/dist/images/marker-icon.png'),
        import('leaflet/dist/images/marker-icon-2x.png'),
        import('leaflet/dist/images/marker-shadow.png')
    ]);

    // Fix the default icon paths
    delete L.default.Icon.Default.prototype._getIconUrl;
    L.default.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x.default,
        iconUrl: markerIcon.default,
        shadowUrl: markerShadow.default,
    });

    return L.default;
}

// Initialize Leaflet only when maps are present
function initializeLeafletOnDemand() {
    const mapContainers = document.querySelectorAll('[id^="map-"]');

    if (mapContainers.length > 0) {
        loadLeaflet().then(L => {
            window.L = L;
            window.dispatchEvent(new CustomEvent('leaflet-loaded'));
            console.log('✅ Leaflet loaded dynamically');
        }).catch(error => {
            console.error('❌ Failed to load Leaflet:', error);
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initializeLeafletOnDemand);

// Also initialize on Livewire navigation
document.addEventListener('livewire:navigated', () => {
    setTimeout(initializeLeafletOnDemand, 100);
});
