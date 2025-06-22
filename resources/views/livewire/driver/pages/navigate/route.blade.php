{{-- resources/views/livewire/driver/pages/navigate/route.blade.php --}}
<div class="w-full">
    <livewire:components.maps-route
        class="w-full z-5"
        style="height: calc(100dvh - 160px);"
        :lat="$currentLocation['lat']"
        :lng="$currentLocation['lng']"
        :zoom="$zoom"
        :address="$currentLocation['address']"
        :destination-lat="$destination['lat']"
        :destination-lng="$destination['lng']"
        :destination-address="$destination['address']"
        :show-route="true"
        :route-color="$routeColor"
        :route-weight="$routeWeight"
        badge-top-left="{{ $trackingBadge }}"
        badge-top-right="📡 GPS {{ $gpsStatus }}"
        badge-bottom-left="📋 SJ-2025-001"
        badge-bottom-right="🎯 Tujuan: Wawoone"
        :use-real-time-tracking="true"
    />
</div>
