{{-- User Index Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Manajemen Pengguna" subtitle="Kelola data pengguna internal sistem di sini"
        icon="phosphor.users-four-duotone" icon-classes="text-primary h-10" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari nama atau email..." wire:model.live.debounce="search" clearable
                icon="phosphor.magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filter" @click="$wire.drawer = true" responsive icon="phosphor.funnel" class="btn-primary" />
            <x-button label="Tambah Pengguna" link="{{ route('app.user.create') }}" responsive
                icon="phosphor.plus-circle" class="btn-success" />
        </x-slot:actions>
    </x-header>

    {{-- STATISTICS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 mb-6">
        <x-stat title="Total Pengguna" :value="$this->userStats['totalUsers']" icon="phosphor.users" color="text-primary"
            class="bg-primary/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="'Total ' .
                $this->userStats['totalUsers'] .
                ' pengguna terdaftar (' .
                $this->userStats['activeUsers'] .
                ' aktif, ' .
                $this->userStats['inactiveUsers'] .
                ' nonaktif)'" />
        <x-stat title="Pengguna Aktif" :value="$this->userStats['activeUsers']" icon="phosphor.check-circle" color="text-success"
            class="bg-success/20 hover:shadow-xl hover:shadow-primary transition-all duration-300" :tooltip="($this->userStats['totalUsers'] > 0
                ? number_format(($this->userStats['activeUsers'] / $this->userStats['totalUsers']) * 100, 1)
                : 0) . '% dari total pengguna dalam status aktif'" />
        <x-stat title="Pengguna Nonaktif" :value="$this->userStats['inactiveUsers']" icon="phosphor.x-circle" color="text-warning"
            class="text-warning bg-warning/20 hover:shadow-xl hover:shadow-primary transition-all duration-300"
            :tooltip="($this->userStats['totalUsers'] > 0
                ? number_format(($this->userStats['inactiveUsers'] / $this->userStats['totalUsers']) * 100, 1)
                : 0) . '% dari total pengguna dalam status nonaktif'" />
        <x-stat title="Pengguna Terhapus" :value="$this->userStats['deletedUsers']" icon="phosphor.trash" color="text-error"
            class="text-error bg-error/20 hover:shadow-xl hover:shadow-primary transition-all duration-300"
            :tooltip="$this->userStats['deletedUsers'] . ' pengguna yang telah dihapus dari sistem'" />
    </div>

    {{-- MAIN CONTENT --}}
    @if ($this->users->count() === 0)
        {{-- EMPTY STATE --}}
        <x-card class="p-12">
            <div class="text-center">
                <x-icon name="phosphor.users" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                <h3 class="text-lg font-semibold text-base-content/60 mb-2">Tidak ada pengguna ditemukan</h3>
                <p class="text-base-content/40 mb-6">
                    @if ($search)
                        Tidak ada pengguna yang cocok dengan pencarian "<strong>{{ $search }}</strong>"
                    @else
                        Belum ada pengguna yang terdaftar dalam sistem
                    @endif
                </p>
                @if (!$search)
                    <x-button label="Tambah Pengguna Pertama" link="{{ route('app.user.create') }}" icon="phosphor.plus"
                        class="btn-primary" />
                @else
                    <x-button label="Reset Pencarian" wire:click="$set('search', '')" icon="phosphor.x"
                        class="btn-ghost" />
                @endif
            </div>
        </x-card>
    @else
        {{-- USER CARDS --}}
        <x-card class="p-6 shadow-md">
            {{-- Grid Layout menggunakan DaisyUI responsive grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                @foreach ($this->users as $user)
                    {{-- User Card menggunakan Model Accessors --}}
                    <x-card title="{{ $user->name }}"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300"
                        wire:click="viewUser({{ $user->id }})" no-separator>
                        {{-- Status Badge di Menu menggunakan Model Accessor --}}
                        <x-slot:menu>
                            <div aria-label="{{ $user->status_color }}"
                                class="status status-{{ $user->status_color }} animate-pulse"></div>
                            <span class="text-md text-{{ $user->status_color }} font-mono">
                                {{ $user->status_label }}
                            </span>
                        </x-slot:menu>

                        {{-- Card Content --}}
                        <div class="flex w-full">
                            {{-- Avatar menggunakan Model Accessor --}}
                            <div class="grid place-items-center">
                                <x-avatar :placeholder="$user->avatar_placeholder" :image="$user->avatar"
                                    class="w-10 md:w-20 ring-{{ $user->status_color }} ring-offset-base-100 rounded-full ring-2 ring-offset-2" />
                            </div>

                            {{-- Divider --}}
                            <div class="divider divider-horizontal"></div>

                            {{-- User Information --}}
                            <div class="card bg-base-300 rounded-box h-30 grow p-3 shadow-md">
                                <div class="flex flex-col justify-between h-full overflow-hidden">
                                    {{-- Role Badge menggunakan Model Accessor --}}
                                    <div class="flex justify-center mb-2">
                                        <div
                                            class="badge badge-{{ $user->role_color }} badge-xs md:badge-sm lg:badge-md">
                                            <x-icon name="{{ $user->role_icon }}" class="h-2 md:h-4" />
                                            {{ $user->role_label }}
                                        </div>
                                    </div>

                                    {{-- Contact Information --}}
                                    <div class="space-y-1 text-xs flex-1 overflow-hidden">
                                        {{-- Email --}}
                                        <div class="flex items-center gap-1 overflow-hidden">
                                            <x-icon name="phosphor.envelope-simple" class="h-[10px] md:h-[19px]" />
                                            <span class="truncate text-[10px] md:text-[14px] text-base-content/80"
                                                title="{{ $user->email }}">
                                                {{ $user->email }}
                                            </span>
                                        </div>

                                        {{-- Created Date --}}
                                        <div class="flex items-center gap-1 overflow-hidden">
                                            <x-icon name="phosphor.calendar" class="h-[10px] md:h-[19px]" />
                                            <span class="truncate text-[10px] md:text-[14px] text-base-content/80"
                                                title="Tanggal bergabung">
                                                {{ $user->created_at->format('d M Y') }}
                                            </span>
                                        </div>

                                        {{-- Last Updated --}}
                                        <div class="flex items-center gap-1 overflow-hidden">
                                            <x-icon name="phosphor.clock" class="h-[10px] md:h-[19px]" />
                                            <span class="truncate text-[10px] md:text-[14px] text-base-content/80"
                                                title="Terakhir diperbarui">
                                                {{ $user->updated_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <x-slot:actions separator>
                            {{-- View Button --}}
                            <x-button label="Lihat" wire:click.stop="viewUser({{ $user->id }})"
                                class="btn-info btn-md" icon="phosphor.eye" responsive
                                tooltip="Lihat detail {{ $user->name }}" />

                            {{-- Change Status Button menggunakan Model Accessor --}}
                            <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click.stop="openChangeStatusModal({{ $user->id }})"
                                class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-md" :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'"
                                responsive :tooltip="$user->is_active ? 'Nonaktifkan akun pengguna' : 'Aktifkan akun pengguna'" />

                            {{-- Delete Button --}}
                            <x-button label="Hapus" wire:click.stop="openDeleteModal({{ $user->id }})"
                                class="btn-error btn-md" icon="phosphor.trash" responsive
                                tooltip="Hapus {{ $user->name }}" />
                        </x-slot:actions>
                    </x-card>
                @endforeach
            </div>

            {{-- Pagination sesuai Mary UI + DaisyUI Standards --}}
            @if ($this->users->hasPages())
                <div class="mt-8 border-t border-base-300 pt-6">
                    {{-- Results Info & Per Page Selector --}}
                    <div
                        class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0 mb-6">
                        {{-- Results Info --}}
                        <div class="text-sm text-base-content/70 text-center md:text-left">
                            Menampilkan <span class="font-semibold">{{ $this->users->firstItem() ?? 0 }}</span> -
                            <span class="font-semibold">{{ $this->users->lastItem() ?? 0 }}</span>
                            dari <span class="font-semibold">{{ $this->users->total() }}</span> pengguna
                            @if ($search)
                                <br class="md:hidden">
                                <span class="block md:inline mt-1 md:mt-0">
                                    untuk pencarian "<span
                                        class="font-semibold text-primary">{{ $search }}</span>"
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
                            @if ($this->users->onFirstPage())
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
                            @php
                                $maxPages = 4;
                                $start = max($this->users->currentPage() - intval($maxPages / 2), 1);
                                $end = min($start + $maxPages, $this->users->lastPage());
                                $start = max($end - $maxPages, 1);
                            @endphp

                            @if ($start > 1)
                                <button wire:click="gotoPage(1)"
                                    class="join-item btn btn-sm hover:btn-primary hidden md:flex">1</button>
                                @if ($start > 2)
                                    <span
                                        class="join-item btn btn-sm btn-disabled cursor-default hidden md:flex">...</span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $this->users->currentPage())
                                    <button
                                        class="join-item btn btn-sm btn-primary min-w-[2.5rem]">{{ $i }}</button>
                                @else
                                    <button wire:click="gotoPage({{ $i }})"
                                        class="join-item btn btn-sm hover:btn-primary min-w-[2.5rem]">{{ $i }}</button>
                                @endif
                            @endfor

                            @if ($end < $this->users->lastPage())
                                @if ($end < $this->users->lastPage() - 1)
                                    <span
                                        class="join-item btn btn-sm btn-disabled cursor-default hidden md:flex">...</span>
                                @endif
                                <button wire:click="gotoPage({{ $this->users->lastPage() }})"
                                    class="join-item btn btn-sm hover:btn-primary hidden md:flex">{{ $this->users->lastPage() }}</button>
                            @endif

                            {{-- Next Button --}}
                            @if ($this->users->hasMorePages())
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
                        Halaman {{ $this->users->currentPage() }} dari {{ $this->users->lastPage() }}
                    </div>
                </div>
            @else
                {{-- Show results info even when no pagination --}}
                @if ($this->users->count() > 0)
                    <div class="mt-6 pt-4 border-t border-base-300">
                        <div
                            class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                            <div class="text-sm text-base-content/70 text-center md:text-left">
                                Menampilkan <span class="font-semibold">{{ $this->users->count() }}</span> pengguna
                                @if ($search)
                                    <br class="md:hidden">
                                    <span class="block md:inline mt-1 md:mt-0">
                                        untuk pencarian "<span
                                            class="font-semibold text-primary">{{ $search }}</span>"
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
    <x-drawer wire:model="drawer" title="Filter Pengguna" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            {{-- Search --}}
            <x-input placeholder="Cari nama atau email..." wire:model.live.debounce="search"
                icon="phosphor.magnifying-glass" @keydown.enter="$wire.drawer = false" clearable />

            {{-- Status Filter --}}
            <x-select label="Filter Status" wire:model.live="statusFilter" :options="$this->statusFilterOptions" option-value="id"
                option-label="name" icon="phosphor.check-circle" />

            {{-- Role Filter --}}
            <x-select label="Filter Role" wire:model.live="roleFilter" :options="collect($this->roles)
                ->map(fn($label, $value) => ['id' => $value, 'name' => $label])
                ->values()
                ->toArray()" option-value="id"
                option-label="name" icon="phosphor.user-circle" />

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
                        @if ($search)
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.magnifying-glass" class="w-4 h-4" />
                                <span>Pencarian: <strong>{{ $search }}</strong></span>
                            </div>
                        @endif
                        @if ($statusFilter !== 'all')
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-4 h-4" />
                                <span>Status:
                                    <strong>{{ $statusFilter === 'active' ? 'Aktif' : 'Nonaktif' }}</strong></span>
                            </div>
                        @endif
                        @if ($roleFilter !== 'all')
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.user-circle" class="w-4 h-4" />
                                <span>Role: <strong>{{ $this->roles[$roleFilter] }}</strong></span>
                            </div>
                        @endif
                        @if ($sortBy['column'] !== 'created_at' || $sortBy['direction'] !== 'desc')
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.sort-ascending" class="w-4 h-4" />
                                <span>Urutan:
                                    <strong>
                                        @php
                                            $sortLabel =
                                                collect($this->sortOptions)->where('id', $sortBy['column'])->first()[
                                                    'name'
                                                ] ?? 'Unknown';
                                        @endphp
                                        {{ $sortLabel }}
                                        ({{ $sortBy['direction'] === 'desc' ? 'Terbaru ke Lama' : 'Lama ke Terbaru' }})
                                    </strong>
                                </span>
                            </div>
                        @endif
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

    {{-- MODAL COMPONENTS - Hanya yang diperlukan untuk Index --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
