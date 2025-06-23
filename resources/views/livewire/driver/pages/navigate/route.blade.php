{{-- resources/views/livewire/driver/pages/navigate/route.blade.php --}}
<div class="w-full">
    <livewire:components.maps-route
        class="w-full z-5"
        style="height: calc(100dvh - 160px);"
        :zoom="$zoom"
        :destination-lat="$destination['lat']"
        :destination-lng="$destination['lng']"
        :destination-address="$destination['address']"
        :show-route="true"
        route-color="#DC2626"
        badge-bottom-left="📋 SJ-2025-001"
        badge-bottom-right="🎯 Tujuan: Wawoone"
    />
</div>
