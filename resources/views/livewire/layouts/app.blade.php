<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">

    <!--NAVBAR mobile only -->
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="phosphor.list" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    <!--MAIN -->
    <x-main full-width>
        <!--SIDEBAR -->
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 border-r-1 border-primary rounded-r-2xl lg:bg-inherit"
            collapse-text="Sembunyikan">

            <!--BRAND -->
            <x-app-brand class="px-5 pt-4" />

            <!--MENU -->
            <x-menu activate-by-route active-bg-color="bg-primary text-primary-content">

                <!--User -->
                @if ($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                        class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>

                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                {{-- Dashboard Menu untuk Sistem Management Surat Jalan & Tracking Truck --}}
                <x-menu-item title="Dashboard" icon="phosphor.gauge-light" link="{{ route('app.dashboard') }}" exact />
                {{-- Master Data --}}
                <x-menu-sub title="Master Data" icon="phosphor.database-light">
                    <x-menu-item title="Data Sopir" icon="phosphor.user-circle-light" link="###" exact />
                    <x-menu-item title="Data Klien" icon="phosphor.buildings-light" link="###" exact />
                    <x-menu-item title="Data Barang" icon="phosphor.package-light" link="###" exact />
                </x-menu-sub>
                {{-- Operasional --}}
                <x-menu-sub title="Operasional" icon="phosphor.truck-light">
                    <x-menu-item title="Surat Jalan" icon="phosphor.receipt-light" link="{{ route('app.delivery-order.index') }}" exact />
                    <x-menu-item title="Status Pengiriman" icon="phosphor.clock-clockwise-light" link="###" exact />
                </x-menu-sub>
                {{-- Tracking --}}
                <x-menu-sub title="Tracking & Monitoring" icon="phosphor.monitor-light">
                    <x-menu-item title="Live Tracking" icon="phosphor.map-pin-simple-area-light" link="###" exact />
                    <x-menu-item title="Riwayat Perjalanan" icon="phosphor.clock-counter-clockwise-light" link="###" exact />
                </x-menu-sub>
                {{-- Laporan --}}
                <x-menu-sub title="Laporan" icon="phosphor.chart-line-light">
                    <x-menu-item title="Laporan Pengiriman" icon="phosphor.file-text-light" link="###" exact />
                    <x-menu-item title="Laporan Keuangan" icon="phosphor.chart-pie-light" link="###" exact />
                </x-menu-sub>
                {{-- Management Pengguna --}}
                <x-menu-sub title="Management Pengguna" icon="phosphor.user-gear-light">
                    <x-menu-item title="Pengguna" icon="phosphor.users-four-light" link="{{ route('app.user.index') }}" exact />
                    <x-menu-item title="Permission" icon="phosphor.key-light" link="{{ route('app.permission.index') }}" exact />
                </x-menu-sub>
                {{-- Logout --}}
                <x-menu-item title="Keluar" icon="phosphor.sign-out-light" link="{{ route('logout') }}" exact />
                <x-menu-separator />
            </x-menu>
        </x-slot:sidebar>

        <!--The `$slot` goes here -->
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <!-- TOAST area -->
    <x-toast />
</body>

</html>
