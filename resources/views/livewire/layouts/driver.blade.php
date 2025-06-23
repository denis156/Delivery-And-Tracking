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

<body class="min-h-dvh antialiased bg-base-200">
    <div class="flex flex-col h-dvh">
        <header class="sticky top-0 z-50 bg-transparent">
            <nav class="navbar bg-gradient-to-tr from-base-200 to-primary/20 shadow-xl border-b-4 border-primary/80">
                <div class="navbar-start ml-2">
                    {{-- Real-Time Geolocation Button --}}
                    <livewire:components.geolocation-button
                        button-class="btn-circle btn-md border-primary border-2"
                        icon-name="phosphor.broadcast"
                        :show-toast="true"
                        :show-badge="true"
                    />
                </div>
                <div class="navbar-center flex flex-col justify-center items-center">
                    <span
                        class="font-bold text-md bg-gradient-to-r from-primary to-info bg-clip-text text-transparent">{{ config('app.name') }}</span>
                    <span class="font-semibold text-md text-base-content/78">{{ $title ?? '' }}</span>
                </div>
                <div class="navbar-end mr-2">
                    {{-- User Avatar with Simple Dropdown --}}
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
                            </div>

                            <!-- Menu Actions -->
                            <div class="space-y-1">
                                <x-button wire:navigate href="{{ route('driver.profile') }}" label="Profil Saya"
                                    icon="phosphor.user-circle" class="btn-sm btn-ghost btn-block justify-start" />
                                <x-button wire:navigate href="#" label="Pengaturan" icon="phosphor.gear"
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

        <main class="flex-1 overflow-auto relative">
            {{ $slot }}
        </main>

        <aside class="bg-gradient-to-bl from-base-200 to-primary/10 border-t-4 border-primary/80 py-1 shadow-xl">
            <div class="grid grid-cols-5 items-baseline justify-items-baseline max-w-md mx-auto gap-4">

                <!-- Beranda -->
                <div class="flex flex-col items-center">
                    <x-button link="{{ route('driver.dashboard') }}"
                        class="{{ request()->routeIs('driver.dashboard') ? 'btn-xl btn-primary bg-info/20 btn-circle' : 'btn-md btn-secondary bg-secondary/10 text-secondary btn-circle' }}">
                        <x-icon name="phosphor.speedometer-fill"
                            class="{{ request()->routeIs('driver.dashboard') ? 'h-8 text-primary' : 'h-6 text-secondary' }}" />
                    </x-button>
                    <span
                        class="{{ request()->routeIs('driver.dashboard') ? 'text-md text-primary font-bold' : 'text-xs text-secondary font-semibold' }} mt-1">Beranda</span>
                </div>

                <!-- Surat Jalan -->
                <div class="flex flex-col items-center">
                    <x-button link="{{ route('driver.delivery-orders') }}"
                        class="{{ request()->routeIs('driver.delivery-orders') ? 'btn-xl btn-primary bg-info/20 btn-circle' : 'btn-md btn-secondary bg-secondary/10 text-secondary btn-circle' }}">
                        <x-icon name="phosphor.files-fill"
                            class="{{ request()->routeIs('driver.delivery-orders') ? 'h-8 text-primary' : 'h-6 text-secondary' }}" />
                    </x-button>
                    <span
                        class="{{ request()->routeIs('driver.delivery-orders') ? 'text-md text-primary font-bold' : 'text-xs text-secondary font-semibold' }} mt-1">Dokumen</span>
                </div>

                <!-- Navigasi -->
                <div class="flex flex-col items-center">
                    <x-button link="{{ route('driver.navigate.index') }}"
                        class="{{ request()->routeIs('driver.navigate.*') ? 'btn-xl btn-primary bg-info/20 btn-circle' : 'btn-md btn-secondary bg-secondary/10 text-secondary btn-circle' }}">
                        <x-icon name="phosphor.map-pin-area-fill"
                            class="{{ request()->routeIs('driver.navigate.*') ? 'h-8 text-primary' : 'h-6 text-secondary' }}" />
                    </x-button>
                    <span
                        class="{{ request()->routeIs('driver.navigate.*') ? 'text-md text-primary font-bold' : 'text-xs text-secondary font-semibold' }} mt-1">Navigasi</span>
                </div>

                <!-- Profil -->
                <div class="flex flex-col items-center">
                    <x-button link="{{ route('driver.profile') }}"
                        class="{{ request()->routeIs('driver.profile') ? 'btn-xl btn-primary bg-info/20 btn-circle' : 'btn-md btn-secondary bg-secondary/10 text-secondary btn-circle' }}">
                        <x-icon name="phosphor.user-circle-fill"
                            class="{{ request()->routeIs('driver.profile') ? 'h-8 text-primary' : 'h-6 text-secondary' }}" />
                    </x-button>
                    <span
                        class="{{ request()->routeIs('driver.profile') ? 'text-md text-primary font-bold' : 'text-xs text-secondary font-semibold' }} mt-1">Profil</span>
                </div>

                <!-- Logout -->
                <div class="flex flex-col items-center">
                    <x-button link="{{ route('logout') }}"
                        class="btn-md btn-secondary bg-secondary/10 text-secondary btn-circle">
                        <x-icon name="phosphor.sign-out-fill" class="h-6 text-secondary" />
                    </x-button>
                    <span class="text-xs text-secondary font-semibold mt-1">Keluar</span>
                </div>

            </div>
        </aside>
    </div>

    <!-- TOAST area -->
    <x-toast />
    @livewireScripts

</body>

</html>
