<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

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
    <x-main>
        <!--SIDEBAR -->
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit" collapse-text="Sembunyikan">

            <!--BRAND -->
            <x-app-brand class="px-5 pt-4" />

            <!--MENU -->
            <x-menu activate-by-route active-bg-color="bg-primary text-primary-content" >

                <!--User -->
                @if($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-button icon="phosphor.power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Beranda" icon="phosphor.gauge" link="{{ route('app.dashboard') }}" exact />

                <x-menu-sub title="Management Pengguna" icon="phosphor.users-three">
                    <x-menu-item title="Pengguna" icon="phosphor.user-list" link="{{ route('app.user') }}" exact />
                    <x-menu-item title="Supir" icon="phosphor.archive" link="####" />
                </x-menu-sub>
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
