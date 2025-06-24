// resources/js/app.js
import '../css/app.css';
import './bootstrap';

// Import Leaflet from node_modules
import L from 'leaflet';

// Import Leaflet Routing Machine - untuk route functionality
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';

// Fix Leaflet default markers icons with Vite
// This is important because Vite changes asset paths
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Fix the default icon paths
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

// Make Leaflet globally available for Alpine components
window.L = L;

// Import Maps Route functionality
import './MapsRoute/MapsRouteMain.js';
