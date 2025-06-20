{{-- resources/views/livewire/driver/pages/navigate/index.blade.php --}}
<div class="mx-auto max-w-4xl">
    <div class="space-y-4 relative z-10">
        <!-- Maps Component - akan handle sendiri jika lat/lng null -->
        <livewire:components.maps
            class="h-[50dvh] w-full z-5 rounded-b-3xl"
            :lat="$latitude"
            :lng="$longitude"
            :badge-top-left="$badgeTopLeft"
            :badge-top-right="$badgeTopRight"
            badge-bottom-left="SJ-2024-001"
            badge-bottom-right="Makassar → Jakarta"
            :address="$address"
            zoom="15"
        />

        <!-- Information Cards Grid -->
        <div class="grid grid-cols-2 gap-2 mt-2">
            <!-- Surat Jalan Card -->
            <x-card title="Surat Jalan" subtitle="Lihat informasi surat jalan disini" class="bg-base-300">
                <x-alert title="tidak ada surat jalan" icon="phosphor.files" class="alert-info alert-soft text-xs" />
                <x-slot:actions separator>
                    <x-button label="Lihat Detail" icon="phosphor.file-text"
                        class="btn-primary btn-dash btn-block btn-sm bg-primary/20 text-primary" />
                </x-slot:actions>
            </x-card>

            <!-- Route Card -->
            <x-card title="Rute Anda" subtitle="Lihat informasi rute aktif disini" class="bg-base-300">
                <x-alert title="tidak ada rute aktif" icon="phosphor.map-pin" class="alert-info alert-soft text-xs" />
                <x-slot:actions separator>
                    <x-button label="Lihat Rute" icon="phosphor.map-pin-area"
                        class="btn-primary btn-dash btn-block btn-sm bg-primary/20 text-primary" />
                </x-slot:actions>
            </x-card>
        </div>

        <!-- Location Details -->
        @if($hasLocation)
            <div class="mt-4">
                <x-card title="Detail Lokasi Saat Ini" class="bg-base-200">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Alamat:</span>
                            <span class="font-medium text-right flex-1 ml-2">{{ $address }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Koordinat:</span>
                            <span class="font-mono text-xs">{{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}</span>
                        </div>
                        @if($lastUpdated)
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Terakhir update:</span>
                                <span class="font-medium">{{ $lastUpdated }} WITA</span>
                            </div>
                        @endif
                        @if(!empty($weatherInfo))
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Cuaca:</span>
                                <span class="font-medium">{{ $weatherInfo['condition'] ?? 'Tidak diketahui' }} ({{ $weatherInfo['temperature'] ?? '-' }}°C)</span>
                            </div>
                            @if(isset($weatherInfo['humidity']))
                                <div class="flex justify-between">
                                    <span class="text-base-content/70">Kelembaban:</span>
                                    <span class="font-medium">{{ $weatherInfo['humidity'] }}%</span>
                                </div>
                            @endif
                            @if(isset($weatherInfo['wind_speed']))
                                <div class="flex justify-between">
                                    <span class="text-base-content/70">Angin:</span>
                                    <span class="font-medium">{{ $weatherInfo['wind_speed'] }} km/h</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <x-slot:actions separator>
                        <div class="flex gap-2 w-full">
                            <x-button
                                onclick="navigator.clipboard.writeText('{{ $latitude }}, {{ $longitude }}');
                                         alert('Koordinat disalin ke clipboard')"
                                label="Salin Koordinat"
                                icon="phosphor.copy"
                                class="btn-outline btn-sm flex-1"
                            />
                            <x-button
                                onclick="window.open('https://www.google.com/maps?q={{ $latitude }},{{ $longitude }}', '_blank')"
                                label="Google Maps"
                                icon="phosphor.map-pin-area"
                                class="btn-outline btn-sm flex-1"
                            />
                        </div>
                    </x-slot:actions>
                </x-card>
            </div>
        @endif
    </div>
</div>
