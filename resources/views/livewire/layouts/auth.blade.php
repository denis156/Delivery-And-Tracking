<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-dvh font-sans antialiased bg-base-200 p-0 m-0 overflow-x-hidden">

    <!--MAIN -->
    <div class="min-h-dvh w-full">
        <!--The `$slot` goes here -->
        <div class="flex flex-col md:grid md:grid-cols-2 md:grid-rows-1 md:gap-[1px] min-h-dvh w-full">
            <!-- Card Utama - Full background dengan centered content -->
            <section class="flex items-center justify-center order-1 bg-base-200 min-h-dvh w-full">
                <div class="w-full max-w-lg px-4">
                    <x-card class="bg-base-200">
                        {{ $slot }}
                    </x-card>
                </div>
            </section>

            <!-- Card Kedua - Enhanced dengan visual elements -->
            <section class="hidden md:flex md:items-center md:justify-center order-2 bg-gradient-to-br from-primary via-primary/90 to-secondary min-h-dvh w-full relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-full h-full bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="1"%3E%3Ccircle cx="7" cy="7" r="2"/%3E%3Ccircle cx="37" cy="37" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
                </div>

                <!-- Main Content -->
                <div class="w-full max-w-lg px-4 z-10 relative">
                    <!-- Brand/Logo Section -->
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 mx-auto mb-4 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                            <x-icon name="phosphor.shipping-container" class="w-10 h-10 text-white" />
                        </div>
                        <h1 class="text-3xl font-bold text-white mb-2">{{ config('app.name') }}</h1>
                        <p class="text-white/80 text-lg">Sistem Manajemen Pengiriman</p>
                    </div>

                    <!-- Feature Highlights -->
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center space-x-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <x-icon name="phosphor.shield-check" class="w-4 h-4" />
                            </div>
                            <span class="text-sm">Keamanan data {{ config('landingpage.stats.data_accuracy', '99%') }}</span>
                        </div>
                        <div class="flex items-center space-x-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <x-icon name="phosphor.clock" class="w-4 h-4" />
                            </div>
                            <span class="text-sm">Monitoring {{ config('landingpage.stats.uptime', '24/7') }}</span>
                        </div>
                        <div class="flex items-center space-x-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <x-icon name="phosphor.chart-line" class="w-4 h-4" />
                            </div>
                            <span class="text-sm">Efisiensi hingga {{ config('landingpage.stats.efficiency_improvement', '75%') }}</span>
                        </div>
                        <div class="flex items-center space-x-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <x-icon name="phosphor.map-pin" class="w-4 h-4" />
                            </div>
                            <span class="text-sm">Melayani {{ config('landingpage.stats.sultra_coverage', '17') }} Kab/Kota Sultra</span>
                        </div>
                    </div>

                    <!-- Quote Section -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                        @php
                            [$message, $author] = str(\App\Foundation\Inspiring::quotes()->random())->explode(' - ');
                        @endphp
                        <blockquote class="text-white/90 italic mb-3 text-sm leading-relaxed">
                            "{{ trim($message) }}"
                        </blockquote>
                        <cite class="text-white/70 text-xs font-medium">
                            - {{ trim($author) }}
                        </cite>
                    </div>

                    <!-- Stats Section -->
                    <div class="grid grid-cols-3 gap-4 mt-8">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ config('landingpage.stats.documents_processed', '500+') }}</div>
                            <div class="text-white/70 text-xs">Dokumen</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ config('landingpage.stats.fleet_managed', '15+') }}</div>
                            <div class="text-white/70 text-xs">Armada</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ config('landingpage.stats.clients_served', '25+') }}</div>
                            <div class="text-white/70 text-xs">Klien</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- TOAST area -->
    <x-toast />
    @livewireScripts

</body>

</html>
