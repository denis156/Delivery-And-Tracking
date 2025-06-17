<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-dvh antialiased bg-base-200">
    <div class="flex flex-col h-dvh">
        <header class="sticky top-0 z-50 bg-transparent">
            <nav class="navbar bg-gradient-to-tr from-base-200 to-primary/20 shadow-xl border-b-4 border-primary/80">
                <div class="navbar-start ml-2">
                    {{-- Geolocation Button Component - Click to open only dengan polling setiap 30 detik --}}
                    <livewire:components.geolocation-button :auto-update="true" :poll-interval="30"
                        button-class="btn-circle btn-md border-primary border-2" icon-name="phosphor.map-pin"
                        :show-toast="true" :show-badge="true" :click-to-open-only="true" />
                </div>
                <div class="navbar-center flex flex-col justify-center items-center">
                    <span
                        class="font-bold text-md bg-gradient-to-r from-primary to-info bg-clip-text text-transparent">{{ config('app.name') }}</span>
                    <span class="font-semibold text-md text-base-content/78">{{ $title ?? '' }}</span>
                </div>
                <div class="navbar-end mr-2">
                    {{-- User Avatar with Dropdown --}}
                    <x-dropdown class="z-50" no-x-anchor right>
                        <x-slot:trigger>
                            <x-avatar placeholder="{{ Auth::user()->avatar_placeholder }}"
                                class="w-10 border-2 border-primary cursor-pointer" />
                        </x-slot:trigger>

                        <div class="w-64 p-3 space-y-3">
                            <!-- User Info -->
                            <div class="pb-2 border-b border-base-300">
                                <h3 class="font-semibold text-lg">{{ Auth::user()->name }}</h3>
                                <p class="text-xs text-base-content/60">{{ Auth::user()->role_label }}</p>

                                {{-- Location Status in Dropdown --}}
                                @php
                                    $userLocation = app('geolocation')->getUserLocation(Auth::id());
                                    $locationAccuracy = app('geolocation')->getLocationAccuracyStatus(Auth::id());
                                @endphp

                                @if ($userLocation['latitude'] && $userLocation['longitude'])
                                    <div class="flex items-center gap-1 mt-1">
                                        <x-icon name="phosphor.map-pin-area"
                                            class="h-3 w-3 text-{{ $locationAccuracy['color'] }}" />
                                        <span
                                            class="text-xs text-{{ $locationAccuracy['color'] }}">{{ $locationAccuracy['label'] }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 mt-1">
                                        <x-icon name="phosphor.map-pin" class="h-3 w-3 text-base-content/40" />
                                        <span class="text-xs text-base-content/40">Lokasi belum diaktifkan</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Stats -->
                            @php
                                $todayStats = app('dashboard.stats')->getTodayStats(Auth::id());
                            @endphp

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="text-center p-2 bg-primary/10 rounded">
                                    <div class="font-bold text-primary">{{ $todayStats['active_orders'] ?? 0 }}</div>
                                    <div class="text-base-content/60">Aktif</div>
                                </div>
                                <div class="text-center p-2 bg-success/10 rounded">
                                    <div class="font-bold text-success">{{ $todayStats['completed'] ?? 0 }}</div>
                                    <div class="text-base-content/60">Selesai</div>
                                </div>
                            </div>

                            <!-- Current Location Info (if available) -->
                            @if ($userLocation['latitude'] && $userLocation['longitude'])
                                <div class="p-2 bg-info/10 rounded">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-base-content/70">Lokasi Saat Ini:</span>
                                        <x-button
                                            onclick="window.open('https://www.google.com/maps?q={{ $userLocation['latitude'] }},{{ $userLocation['longitude'] }}', '_blank')"
                                            icon="phosphor.map-pin-area" class="btn-xs btn-ghost"
                                            tooltip="Buka di Google Maps" />
                                    </div>
                                    <p class="text-xs font-medium text-info">
                                        {{ $userLocation['city'] ?? 'Alamat tidak tersedia' }}</p>
                                    @if ($userLocation['last_updated'])
                                        <p class="text-xs text-base-content/50">
                                            Update:
                                            {{ \Carbon\Carbon::parse($userLocation['last_updated'])->format('H:i') }}
                                            WIB
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <!-- Menu Actions -->
                            <div class="space-y-1">
                                <x-button wire:navigate {{-- href="{{ route('driver.profile') }}" --}} label="Profil Saya"
                                    icon="phosphor.user-circle" class="btn-sm btn-ghost btn-block justify-start" />
                                <x-button wire:navigate {{-- href="{{ route('driver.settings') }}" --}} label="Pengaturan" icon="phosphor.gear"
                                    class="btn-sm btn-ghost btn-block justify-start" />
                                <x-button wire:navigate href="{{ route('logout') }}" label="Keluar"
                                    icon="phosphor.sign-out"
                                    class="btn-sm btn-ghost btn-block justify-start text-error" />
                            </div>
                        </div>
                    </x-dropdown>
                </div>
            </nav>
        </header>

        <main class="flex-1 overflow-auto relative bg-base-200">
            {{ $slot }}
        </main>

        <!-- Custom Bottom Navigation dengan x-button -->
        <nav
            class="bg-gradient-to-bl from-base-200 to-primary/10 border-t-4 border-primary/80 px-4 py-3 shadow-xl rounded-t-2xl">
            <div class="flex justify-around items-center max-w-md mx-auto">

                <!-- Beranda - Active -->
                <div class="flex flex-col items-center mt-2">
                    <x-button link="{{ route('driver.dashboard') }}" icon="phosphor.speedometer-fill"
                        class="btn-sm btn-primary bg-info/10 text-primary btn-circle" />
                    <span class="text-xs font-medium text-primary">Beranda</span>
                </div>

                <!-- Surat Jalan -->
                <div class="flex flex-col items-center mt-2">
                    <x-button link="{{ route('driver.delivery-orders') }}" icon="phosphor.files-fill"
                        class="btn-sm btn-secondary bg-secondary/10 text-secondary btn-circle" />
                    <span class="text-xs text-secondary">Surat Jalan</span>
                </div>

                <!-- Navigasi - Center Button (Larger) -->
                <div class="flex flex-col items-center">
                    <x-button link="{{ route('driver.navigate') }}" class="btn-xl btn-primary bg-info/20 btn-circle">
                        <x-icon name="phosphor.map-trifold-fill" class="h-8 text-primary" />
                    </x-button>
                </div>

                <!-- Profil -->
                <div class="flex flex-col items-center mt-2">
                    <x-button link="{{ route('driver.profile') }}" icon="phosphor.user-circle-fill"
                        class="btn-sm btn-secondary bg-secondary/10 text-secondary btn-circle" />
                    <span class="text-xs text-secondary">Profil</span>
                </div>

                <!-- Logout -->
                <div class="flex flex-col items-center mt-2">
                    <x-button link="{{ route('logout') }}" icon="phosphor.sign-out-fill"
                        class="btn-sm btn-warning bg-warning/10 text-warning btn-circle" />
                    <span class="text-xs text-warning">Keluar</span>
                </div>

            </div>
        </nav>
    </div>

    <!-- TOAST area -->
    <x-toast />
    @livewireScripts

    <script>
        document.addEventListener('livewire:init', () => {
            // Listen for global location updates
            Livewire.on('location-updated', (data) => {
                console.log('🌍 Global location updated:', data);

                // Dispatch to all components that need location data
                window.dispatchEvent(new CustomEvent('user-location-updated', {
                    detail: data
                }));

                // Find and refresh dashboard component specifically
                refreshDashboardComponent();
            });

            // Listen for location cleared events
            Livewire.on('location-cleared', () => {
                console.log('🗑️ Location data cleared');

                // Dispatch to all components
                window.dispatchEvent(new CustomEvent('user-location-cleared'));

                // Refresh dashboard if available
                refreshDashboardComponent();
            });

            // Proximity alerts with improved component detection
            let lastProximityCheck = 0;
            Livewire.on('location-updated', (data) => {
                const now = Date.now();
                // Check proximity every 30 seconds max
                if (now - lastProximityCheck > 30000) {
                    lastProximityCheck = now;
                    console.log('🎯 Checking proximity to destinations...');

                    // Only call proximity check on dashboard components
                    checkProximityOnDashboard();
                }
            });
        });

        /**
         * Find and refresh dashboard component
         */
        function refreshDashboardComponent() {
            // Look for dashboard-specific components
            const dashboardSelectors = [
                '[wire\\:id*="dashboard"]',
                '[wire\\:id*="driver-dashboard"]',
                '.dashboard-component',
                '[data-component="dashboard"]'
            ];

            for (const selector of dashboardSelectors) {
                const dashboardElement = document.querySelector(selector);
                if (dashboardElement) {
                    const componentId = dashboardElement.getAttribute('wire:id');
                    if (componentId) {
                        const component = window.Livewire.find(componentId);
                        if (component) {
                            console.log('🌤️ Refreshing dashboard component:', componentId);
                            try {
                                component.call('$refresh');
                                return; // Exit after successful refresh
                            } catch (error) {
                                console.warn('Failed to refresh dashboard component:', error);
                            }
                        }
                    }
                }
            }

            console.log('ℹ️ Dashboard component not found for refresh');
        }

        /**
         * Check proximity alerts only on dashboard components
         */
        function checkProximityOnDashboard() {
            // Look for components that have proximity check method
            const dashboardSelectors = [
                '[wire\\:id*="dashboard"]',
                '[wire\\:id*="driver-dashboard"]',
                '.dashboard-component',
                '[data-component="dashboard"]'
            ];

            let proximityChecked = false;

            for (const selector of dashboardSelectors) {
                const dashboardElement = document.querySelector(selector);
                if (dashboardElement) {
                    const componentId = dashboardElement.getAttribute('wire:id');
                    if (componentId) {
                        const component = window.Livewire.find(componentId);
                        if (component) {
                            try {
                                // Check if the method exists before calling it
                                if (typeof component.call === 'function') {
                                    component.call('checkProximityAlerts');
                                    proximityChecked = true;
                                    console.log('✅ Proximity alerts checked on:', componentId);
                                    break; // Exit after successful check
                                }
                            } catch (error) {
                                // If method doesn't exist, just log and continue
                                if (error.message.includes('not found')) {
                                    console.log('ℹ️ Component does not have proximity check method:', componentId);
                                } else {
                                    console.warn('Error calling proximity check:', error);
                                }
                            }
                        }
                    }
                }
            }

            if (!proximityChecked) {
                console.log('ℹ️ No dashboard component with proximity check method found');
            }
        }

        // Enhanced error handling for geolocation
        window.addEventListener('error', (event) => {
            if (event.message && (event.message.includes('geolocation') || event.message.includes('location'))) {
                console.error('🚨 Geolocation error:', event.error);
                // Dispatch custom event for error handling
                window.dispatchEvent(new CustomEvent('geolocation-error', {
                    detail: {
                        error: event.error,
                        message: event.message
                    }
                }));
            }
        });

        // Handle unhandled promise rejections (common with geolocation)
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.toString().includes('geolocation')) {
                console.warn('🚨 Unhandled geolocation promise rejection:', event.reason);
                event.preventDefault(); // Prevent the error from being logged to console
            }
        });

        // Debug: Log all Livewire events for troubleshooting
        @if (config('app.debug'))
            document.addEventListener('livewire:init', () => {
                console.log('🔧 Livewire initialized for geolocation debugging');

                // Log all Livewire events
                ['location-updated', 'location-cleared', 'task-completed', 'delivery-order-updated'].forEach(
                    eventName => {
                        Livewire.on(eventName, (data) => {
                            console.log(`📡 Livewire event: ${eventName}`, data);
                        });
                    });

                // Log all available Livewire components
                console.log('🔍 Available Livewire components:', Object.keys(window.Livewire.all()));
            });
        @endif
    </script>

</body>

</html>
