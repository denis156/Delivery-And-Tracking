{{-- Driver Index Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Manajemen Sopir" subtitle="Kelola data sopir dan informasi SIM di sini" icon="phosphor.truck-duotone"
        icon-classes="text-primary h-10" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari nama, email, SIM, atau plat..." wire:model.live.debounce="search" clearable
                icon="phosphor.magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filter" @click="$wire.drawer = true" responsive icon="phosphor.funnel" class="btn-primary" />
            <x-button label="Tambah Sopir" link="{{ route('app.driver.create') }}" responsive
                icon="phosphor.plus-circle" class="btn-success" />
        </x-slot:actions>
    </x-header>

    {{-- STATISTICS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 mb-6">
        <x-stat title="Total Driver" :value="$this->driverStats['totalDrivers']" icon="{{ $this->driverUIConfig['icons']['user'] }}" color="text-primary"
            class="bg-primary/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->driverStats['totalDriversTooltip']" />
        <x-stat title="Driver Aktif" :value="$this->driverStats['activeDrivers']" icon="phosphor.check-circle" color="text-success"
            class="bg-success/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->driverStats['activeDriversTooltip']" />
        <x-stat title="Driver Nonaktif" :value="$this->driverStats['inactiveDrivers']" icon="phosphor.x-circle" color="text-warning"
            class="text-warning bg-warning/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->driverStats['inactiveDriversTooltip']" />
        <x-stat title="SIM Kadaluarsa" :value="$this->driverStats['expiredLicenses']" icon="phosphor.warning" color="text-error"
            class="text-error bg-error/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="$this->driverStats['expiredLicensesTooltip']" />
    </div>

    {{-- MAIN CONTENT --}}
    @if ($this->drivers->count() === 0)
        {{-- EMPTY STATE --}}
        <x-card class="p-12">
            <div class="text-center">
                <x-icon name="{{ $this->driverUIConfig['icons']['user'] }}" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                <h3 class="text-lg font-semibold text-base-content/60 mb-2">Tidak ada driver ditemukan</h3>
                <p class="text-base-content/40 mb-6">
                    @if ($search)
                        Tidak ada driver yang cocok dengan pencarian "<strong>{{ $search }}</strong>"
                    @else
                        Belum ada driver yang terdaftar dalam sistem
                    @endif
                </p>
                @if (!$search)
                    <x-button label="Tambah Driver Pertama" link="{{ route('app.driver.create') }}" icon="phosphor.plus"
                        class="btn-primary" />
                @else
                    <x-button label="Reset Pencarian" wire:click="$set('search', '')" icon="phosphor.x"
                        class="btn-ghost" />
                @endif
            </div>
        </x-card>
    @else
        {{-- DRIVER CARDS --}}
        <x-card class="p-6 shadow-md">
            {{-- Grid Layout menggunakan DaisyUI responsive grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                @foreach ($this->drivers as $driver)
                    {{-- Driver Card menggunakan Model Accessors --}}
                    <x-card title="{{ $driver->name }}"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300"
                        wire:click="viewDriver({{ $driver->id }})" no-separator>
                        {{-- DYNAMIC: Status Badge di Menu menggunakan Model Accessor --}}
                        <x-slot:menu>
                            <div aria-label="{{ $driver->status_color }}"
                                class="status status-{{ $driver->status_color }} animate-pulse"></div>
                            <span class="text-md text-{{ $driver->status_color }} font-mono">
                                {{ $driver->status_label }}
                            </span>
                        </x-slot:menu>

                        {{-- Card Content --}}
                        <div class="flex w-full">
                            {{-- Avatar menggunakan Model Accessor --}}
                            <div class="grid place-items-center">
                                <x-avatar :placeholder="$driver->avatar_placeholder" :image="$driver->avatar"
                                    class="w-10 md:w-20 ring-{{ $driver->status_color }} ring-offset-base-100 rounded-full ring-2 ring-offset-2" />
                            </div>

                            {{-- Divider --}}
                            <div class="divider divider-horizontal"></div>

                            {{-- Driver Information - TWO COLUMN LAYOUT --}}
                            <div class="card bg-base-300 rounded-box grow p-3 shadow-md overflow-visible">
                                <div class="flex flex-col space-y-2">
                                    {{-- DYNAMIC: Driver Badge & License Status --}}
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-2 mb-2">
                                        {{-- Driver Role Badge dengan helper --}}
                                        <div class="badge badge-{{ $this->driverUIConfig['colors']['driver_role'] }} badge-xs md:badge-sm lg:badge-md relative">
                                            <x-icon name="{{ $this->driverUIConfig['icons']['user'] }}" class="h-2 md:h-4" />
                                            {{ $this->driverUIConfig['labels']['driver_role'] }}
                                        </div>

                                        {{-- DYNAMIC: License Status Badge dengan icon dari helper --}}
                                        @if ($driver->driver && isset($driver->driver->license_status))
                                            <div class="badge badge-{{ $driver->driver->license_status['color'] }} badge-xs md:badge-sm lg:badge-md relative">
                                                <x-icon name="{{ $driver->driver->license_status['icon'] }}" class="h-2 md:h-3" />
                                                {{ $driver->driver->license_status['label'] }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Contact & License Information - Two Column Layout --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                        {{-- Left Column --}}
                                        <div class="space-y-2">
                                            {{-- DYNAMIC: Email dengan icon dari UI config --}}
                                            <div class="flex items-center gap-1">
                                                <x-icon name="{{ $this->driverUIConfig['icons']['email'] }}"
                                                    class="h-2 md:h-4 flex-shrink-0" />
                                                <span class="truncate text-xs md:text-sm text-base-content/80"
                                                    title="{{ $driver->email }}">
                                                    {{ $driver->email }}
                                                </span>
                                            </div>

                                            {{-- DYNAMIC: Phone dengan icon dari UI config --}}
                                            @if ($driver->driver && $driver->driver->phone)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->driverUIConfig['icons']['phone'] }}" class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80"
                                                        title="{{ $driver->driver->formatted_phone }}">
                                                        {{ $driver->driver->formatted_phone }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Right Column --}}
                                        <div class="space-y-2">
                                            {{-- DYNAMIC: License Info dengan icon dari UI config --}}
                                            @if ($driver->driver)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->driverUIConfig['icons']['license'] }}"
                                                        class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80"
                                                        title="{{ $driver->driver->license_label }} - {{ $driver->driver->formatted_license_number }}">
                                                        {{ $driver->driver->license_label }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- DYNAMIC: Vehicle Info dengan icon dari UI config --}}
                                            @if ($driver->driver && $driver->driver->vehicle_type)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->driverUIConfig['icons']['vehicle'] }}" class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80"
                                                        title="{{ $driver->driver->vehicle_type }} - {{ $driver->driver->formatted_vehicle_plate }}">
                                                        {{ $driver->driver->vehicle_type }}
                                                        @if ($driver->driver->vehicle_plate)
                                                            - {{ $driver->driver->formatted_vehicle_plate }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Full Width Address --}}
                                        <div class="col-span-full">
                                            @if ($driver->driver && $driver->driver->address)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="{{ $this->driverUIConfig['icons']['location'] }}"
                                                        class="h-2 md:h-4 flex-shrink-0" />
                                                    <span class="truncate text-xs md:text-sm text-base-content/80">
                                                        {{ $driver->driver->address }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <x-slot:actions separator>
                            {{-- View Button --}}
                            <x-button label="Lihat" wire:click.stop="viewDriver({{ $driver->id }})"
                                class="btn-info btn-md" icon="phosphor.eye" responsive
                                tooltip="Lihat detail {{ $driver->name }}" />

                            {{-- Change Status Button menggunakan Model Accessor --}}
                            <x-button :label="$driver->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click.stop="openChangeStatusModal({{ $driver->id }})"
                                class="btn-{{ $driver->is_active ? 'warning' : 'success' }} btn-md" :icon="$driver->is_active ? 'phosphor.pause' : 'phosphor.play'"
                                responsive :tooltip="$driver->is_active ? 'Nonaktifkan akun driver' : 'Aktifkan akun driver'" />

                            {{-- Delete Button --}}
                            <x-button label="Hapus" wire:click.stop="openDeleteModal({{ $driver->id }})"
                                class="btn-error btn-md" icon="phosphor.trash" responsive
                                tooltip="Hapus {{ $driver->name }}" />
                        </x-slot:actions>
                    </x-card>
                @endforeach
            </div>

            {{-- Pagination sesuai Mary UI + DaisyUI Standards --}}
            @if ($this->drivers->hasPages())
                <div class="mt-8 border-t border-base-300 pt-6">
                    {{-- Results Info & Per Page Selector --}}
                    <div
                        class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0 mb-6">
                        {{-- Results Info --}}
                        <div class="text-sm text-base-content/70 text-center md:text-left">
                            {{ $this->paginationInfo['current'] }}
                            @if ($search)
                                <br class="md:hidden">
                                <span class="block md:inline mt-1 md:mt-0">
                                    {{ $this->paginationInfo['search'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Per Page Selector --}}
                        <div class="flex items-center justify-center md:justify-end gap-3">
                            <label class="text-sm text-base-content/70 font-medium">Per halaman:</label>
                            <x-select wire:model.live="perPage" :options="$this->perPageOptions" option-value="value"
                                option-label="label" class="select-sm w-20" />
                        </div>
                    </div>

                    {{-- Custom Pagination dengan DaisyUI join components --}}
                    <div class="flex justify-center">
                        <div class="join shadow-sm">
                            {{-- Previous Button --}}
                            @if ($this->drivers->onFirstPage())
                                <button class="join-item btn btn-sm btn-disabled">
                                    <x-icon name="phosphor.caret-left" class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="hidden sm:inline ml-1">Prev</span>
                                </button>
                            @else
                                <button wire:click="previousPage" class="join-item btn btn-sm hover:btn-primary">
                                    <x-icon name="phosphor.caret-left" class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="hidden sm:inline ml-1">Prev</span>
                                </button>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach ($this->paginationPages as $page)
                                @if ($page['type'] === 'page')
                                    @if ($page['current'])
                                        <button class="join-item btn btn-sm btn-primary min-w-[2.5rem]">{{ $page['page'] }}</button>
                                    @else
                                        <button wire:click="gotoPage({{ $page['page'] }})"
                                            class="join-item btn btn-sm hover:btn-primary min-w-[2.5rem]">{{ $page['page'] }}</button>
                                    @endif
                                @elseif ($page['type'] === 'dots')
                                    <span class="join-item btn btn-sm btn-disabled cursor-default hidden md:flex">...</span>
                                @endif
                            @endforeach

                            {{-- Next Button --}}
                            @if ($this->drivers->hasMorePages())
                                <button wire:click="nextPage" class="join-item btn btn-sm hover:btn-primary">
                                    <span class="hidden sm:inline mr-1">Next</span>
                                    <x-icon name="phosphor.caret-right" class="w-3 h-3 md:w-4 md:h-4" />
                                </button>
                            @else
                                <button class="join-item btn btn-sm btn-disabled">
                                    <span class="hidden sm:inline mr-1">Next</span>
                                    <x-icon name="phosphor.caret-right" class="w-3 h-3 md:w-4 md:h-4" />
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Mobile Page Info --}}
                    <div class="mt-4 text-center text-xs text-base-content/60 md:hidden">
                        {{ $this->paginationInfo['mobile'] }}
                    </div>
                </div>
            @else
                {{-- Show results info even when no pagination --}}
                @if ($this->drivers->count() > 0)
                    <div class="mt-6 pt-4 border-t border-base-300">
                        <div
                            class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                            <div class="text-sm text-base-content/70 text-center md:text-left">
                                {{ $this->paginationInfo['simple'] }}
                                @if ($search)
                                    <br class="md:hidden">
                                    <span class="block md:inline mt-1 md:mt-0">
                                        {{ $this->paginationInfo['search'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-center md:justify-end gap-3">
                                <label class="text-sm text-base-content/70 font-medium">Per halaman:</label>
                                <x-select wire:model.live="perPage" :options="$this->perPageOptions" option-value="value"
                                    option-label="label" class="select-sm w-20" />
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </x-card>
    @endif

    {{-- FILTER DRAWER menggunakan Mary UI Drawer --}}
    <x-drawer wire:model="drawer" title="Filter Driver" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            {{-- Search --}}
            <x-input placeholder="Cari nama, email, SIM, atau plat..." wire:model.live.debounce="search"
                icon="phosphor.magnifying-glass" @keydown.enter="$wire.drawer = false" clearable />

            {{-- Status Filter --}}
            <x-select label="Filter Status" wire:model.live="statusFilter" :options="$this->statusFilterOptions" option-value="id"
                option-label="name" icon="phosphor.check-circle" />

            {{-- License Type Filter --}}
            <x-select label="Filter Jenis SIM" wire:model.live="licenseFilter" :options="$this->licenseFilterOptions"
                option-value="id" option-label="name" icon="phosphor.identification-card" />

            {{-- License Status Filter --}}
            <x-select label="Filter Status SIM" wire:model.live="licenseStatusFilter" :options="$this->licenseStatusFilterOptions"
                option-value="id" option-label="name" icon="phosphor.warning" />

            {{-- Sort Options --}}
            <x-select label="Urutkan Berdasarkan" wire:model.live="sortBy.column" :options="$this->sortOptions"
                option-value="id" option-label="name" icon="phosphor.sort-ascending" />

            {{-- Sort Direction --}}
            <x-select label="Urutan" wire:model.live="sortBy.direction" :options="$this->sortDirectionOptions" option-value="id"
                option-label="name" icon="phosphor.arrows-down-up" />

            {{-- Filter Summary --}}
            @if ($this->hasActiveFilters)
                <x-card class="bg-base-200" title="Filter & Pengurutan Aktif" no-separator>
                    <div class="space-y-2 text-sm">
                        @foreach ($this->activeFiltersInfo as $filter)
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $filter['icon'] }}" class="w-4 h-4" />
                                <span>{{ $filter['label'] }}: <strong>{{ $filter['value'] }}</strong></span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Reset Filter" icon="phosphor.x-circle" wire:click="clear" spinner class="btn-ghost" />
            <x-button label="Tutup" icon="phosphor.check-circle" class="btn-primary"
                @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>

    {{-- MODAL COMPONENTS --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
