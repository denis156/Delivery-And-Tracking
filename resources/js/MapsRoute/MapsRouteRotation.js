// resources/js/MapsRoute/MapsRouteRotation.js
// Simplified marker rotation using Leaflet.RotatedMarker plugin

export class MapsRouteRotation {
    constructor() {
        this.compassSupported = false;
        this.compassPermissionGranted = false;
        this.lastPosition = null;
        this.lastHeading = 0;
        this.rotationEnabled = true;
        this.smoothRotation = true;
        this.rotationHistory = [];
        this.maxHistorySize = 5;

        this.init();
    }

    /**
     * Initialize rotation system
     */
    async init() {
        this.detectCompassSupport();

        // Auto-request compass permission if supported
        if (this.compassSupported) {
            await this.requestCompassPermission();
        } else {
            console.log('📍 Using movement-based rotation (compass not supported)');
        }
    }

    /**
     * Detect if device supports compass
     */
    detectCompassSupport() {
        if (typeof DeviceOrientationEvent !== 'undefined') {
            this.compassSupported = true;
            console.log('✅ Device orientation API supported');
        } else {
            this.compassSupported = false;
            console.log('❌ Device orientation API not supported - using movement bearing');
        }
    }

    /**
     * Request compass permission - dengan konfirmasi otomatis
     */
    async requestCompassPermission() {
        if (!this.compassSupported) return false;

        try {
            // For iOS 13+ devices, request permission
            if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                console.log('📱 Requesting device orientation permission (iOS)...');

                // Show confirmation dialog to user
                const userWantsCompass = confirm(
                    'Aplikasi ini memerlukan akses kompas untuk memutar marker sesuai arah gerak Anda.\n\n' +
                    'Klik OK untuk mengizinkan, atau Cancel jika tidak ingin menggunakan fitur rotasi marker.'
                );

                if (!userWantsCompass) {
                    console.log('👤 User declined compass permission');
                    this.compassPermissionGranted = false;
                    return false;
                }

                const permission = await DeviceOrientationEvent.requestPermission();
                this.compassPermissionGranted = permission === 'granted';

                if (this.compassPermissionGranted) {
                    console.log('✅ Device orientation permission granted');
                    this.setupCompassListener();
                    alert('Kompas aktif! Marker akan berotasi sesuai arah device Anda.');
                } else {
                    console.log('❌ Device orientation permission denied - marker akan tetap diam');
                    alert('Permission ditolak. Marker tidak akan berotasi.');
                }
            } else {
                // Android or older iOS - no permission needed
                this.compassPermissionGranted = true;
                this.setupCompassListener();
                console.log('✅ Device orientation ready (Android/older iOS)');
            }
        } catch (error) {
            console.error('Error requesting compass permission:', error);
            this.compassPermissionGranted = false;
            alert('Terjadi error saat meminta permission compass. Marker tidak akan berotasi.');
        }

        return this.compassPermissionGranted;
    }

    /**
     * Setup compass event listener
     */
    setupCompassListener() {
        if (!this.compassSupported) return;

        // Listen for absolute orientation (preferred)
        window.addEventListener('deviceorientationabsolute', (event) => {
            this.handleCompassEvent(event, true);
        });

        // Fallback to regular orientation
        window.addEventListener('deviceorientation', (event) => {
            this.handleCompassEvent(event, false);
        });

        console.log('🧭 Compass event listeners active');
    }

    /**
     * Handle compass orientation events - simplified
     */
    handleCompassEvent(event, isAbsolute) {
        if (!this.rotationEnabled) return;

        let heading = null;

        // Get heading from different sources
        if (isAbsolute && event.alpha !== null) {
            // Absolute orientation (preferred)
            heading = 360 - event.alpha; // Convert to compass heading
        } else if (event.webkitCompassHeading !== undefined) {
            // iOS WebKit compass
            heading = event.webkitCompassHeading;
        } else if (event.alpha !== null) {
            // Regular orientation fallback
            heading = 360 - event.alpha;
        }

        if (heading !== null) {
            // Adjust for device orientation
            heading = this.adjustForDeviceOrientation(heading);

            // Apply smoothing if enabled
            if (this.smoothRotation) {
                heading = this.smoothHeading(heading);
            }

            this.lastHeading = heading;

            // Dispatch update for any listening components
            this.dispatchRotationUpdate(heading, 'compass');
        }
    }

    /**
     * Adjust heading for device orientation
     */
    adjustForDeviceOrientation(heading) {
        const orientation = window.orientation || 0;

        switch (orientation) {
            case 0:   // Portrait
                return heading;
            case 90:  // Landscape left
                return (heading + 90) % 360;
            case -90: // Landscape right
                return (heading - 90 + 360) % 360;
            case 180: // Portrait upside down
                return (heading + 180) % 360;
            default:
                return heading;
        }
    }

    /**
     * Calculate movement bearing from GPS coordinates
     */
    calculateMovementBearing(currentLat, currentLng, previousLat, previousLng) {
        if (!previousLat || !previousLng || !currentLat || !currentLng) {
            return null;
        }

        // Minimum distance threshold to avoid noise (about 5 meters)
        const distance = this.calculateDistance(previousLat, previousLng, currentLat, currentLng);
        if (distance < 0.005) { // Less than 5 meters
            return null;
        }

        // Convert to radians
        const lat1 = this.toRadians(previousLat);
        const lat2 = this.toRadians(currentLat);
        const deltaLng = this.toRadians(currentLng - previousLng);

        // Calculate bearing using Haversine formula
        const y = Math.sin(deltaLng) * Math.cos(lat2);
        const x = Math.cos(lat1) * Math.sin(lat2) -
                  Math.sin(lat1) * Math.cos(lat2) * Math.cos(deltaLng);

        let bearing = Math.atan2(y, x);
        bearing = this.toDegrees(bearing);

        // Normalize to 0-360 degrees
        return (bearing + 360) % 360;
    }

    /**
     * Calculate distance between two points (simple)
     */
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in km
        const dLat = this.toRadians(lat2 - lat1);
        const dLng = this.toRadians(lng2 - lng1);

        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    /**
     * Update marker rotation - MAIN FUNCTION using RotatedMarker plugin
     */
    updateMarkerRotation(mapId, currentLat, currentLng, marker) {
        if (!this.rotationEnabled || !marker) return;

        let heading = null;

        // Priority 1: Use compass heading if available and permission granted
        if (this.compassPermissionGranted && this.lastHeading !== null) {
            heading = this.lastHeading;
        }
        // Priority 2: Calculate from movement if we have previous position
        else if (this.lastPosition) {
            heading = this.calculateMovementBearing(
                currentLat, currentLng,
                this.lastPosition.lat, this.lastPosition.lng
            );

            if (heading !== null) {
                this.dispatchRotationUpdate(heading, 'movement');
            }
        }

        // Apply rotation using RotatedMarker plugin
        if (heading !== null && marker.setRotationAngle) {
            try {
                // Convert heading to rotation angle
                // Truck icon faces right (0°) by default, so adjust accordingly
                const rotationAngle = heading - 112;

                marker.setRotationAngle(rotationAngle);

                console.log(`🧭 Marker rotated to ${heading.toFixed(1)}° (${rotationAngle.toFixed(1)}° rotation)`);

            } catch (error) {
                console.error('Error rotating marker:', error);
            }
        }

        // Store current position for next calculation
        this.lastPosition = {
            lat: currentLat,
            lng: currentLng,
            timestamp: Date.now()
        };
    }

    /**
     * Smooth heading using simple moving average
     */
    smoothHeading(newHeading) {
        if (!this.smoothRotation) return newHeading;

        this.rotationHistory.push(newHeading);

        if (this.rotationHistory.length > this.maxHistorySize) {
            this.rotationHistory.shift();
        }

        // Simple average with angle wrapping consideration
        let sum = 0;
        let count = this.rotationHistory.length;

        // Handle angle wrapping (e.g. averaging 350° and 10°)
        let reference = this.rotationHistory[0];

        for (let angle of this.rotationHistory) {
            let diff = angle - reference;
            if (diff > 180) {
                angle -= 360;
            } else if (diff < -180) {
                angle += 360;
            }
            sum += angle;
        }

        let smoothed = sum / count;
        return (smoothed + 360) % 360;
    }

    /**
     * Dispatch rotation update event
     */
    dispatchRotationUpdate(heading, source) {
        const event = new CustomEvent('marker-rotation-update', {
            detail: {
                heading: heading,
                source: source,
                timestamp: Date.now()
            }
        });

        document.dispatchEvent(event);

        // Also dispatch to console for debugging
        console.log(`🧭 Heading: ${heading.toFixed(1)}° from ${source}`);
    }

    /**
     * Manual permission request (dapat dipanggil dari luar)
     */
    async requestPermission() {
        return await this.requestCompassPermission();
    }

    /**
     * Toggle rotation on/off
     */
    setRotationEnabled(enabled) {
        this.rotationEnabled = enabled;
        console.log(`🧭 Marker rotation ${enabled ? 'enabled' : 'disabled'}`);
    }

    /**
     * Toggle smooth rotation
     */
    setSmoothRotation(enabled) {
        this.smoothRotation = enabled;
        if (!enabled) {
            this.rotationHistory = [];
        }
        console.log(`🧭 Smooth rotation ${enabled ? 'enabled' : 'disabled'}`);
    }

    /**
     * Get rotation status for debugging
     */
    getStatus() {
        return {
            compassSupported: this.compassSupported,
            compassPermissionGranted: this.compassPermissionGranted,
            rotationEnabled: this.rotationEnabled,
            smoothRotation: this.smoothRotation,
            lastHeading: this.lastHeading,
            lastPosition: this.lastPosition,
            source: this.compassPermissionGranted ? 'compass' : 'movement'
        };
    }

    /**
     * Reset system
     */
    reset() {
        this.lastPosition = null;
        this.lastHeading = 0;
        this.rotationHistory = [];
        console.log('🧭 Rotation system reset');
    }

    // Utility functions
    toRadians(degrees) {
        return degrees * Math.PI / 180;
    }

    toDegrees(radians) {
        return radians * 180 / Math.PI;
    }
}

// Export singleton instance
export const mapsRouteRotation = new MapsRouteRotation();
