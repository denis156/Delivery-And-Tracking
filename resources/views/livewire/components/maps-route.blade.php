{{-- resources/views/livewire/components/maps-route.blade.php (Refactored) --}}
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
                <x-badge value="{{ $badgeTopRight }}" class="badge-success badge-soft badge-xs" />
            @endif
            @if ($badgeTopLeft)
                <x-badge value="{{ $badgeTopLeft }}" class="badge-info badge-soft badge-xs" />
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
            <x-badge value="Jarak: {{ $this->getDistanceText() }}" class="badge-success badge-soft badge-sm" />
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
