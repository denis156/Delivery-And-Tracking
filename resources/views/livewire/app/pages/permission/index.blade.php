{{-- Permission Index Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Daftar Permission" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input
                placeholder="Cari permission..."
                wire:model.live.debounce="search"
                clearable
                icon="phosphor.magnifying-glass"
            />
        </x-slot:middle>
        <x-slot:actions>
            <x-button
                label="Filter"
                @click="$wire.drawer = true"
                responsive
                icon="phosphor.funnel"
                class="btn-primary"
            />
            <x-button
                label="Tambah Permission"
                link="{{ route('app.permission.create') }}"
                responsive
                icon="phosphor.plus"
                class="btn-success"
            />
        </x-slot:actions>
    </x-header>

    {{-- STATISTICS CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat
            title="Total Permission"
            :value="$permissionStats['total']"
            icon="phosphor.key"
            color="text-primary-content"
            class="text-primary-content bg-primary"
            :tooltip="'Total ' . $permissionStats['total'] . ' permission terdaftar'"
        />
        <x-stat
            title="Sudah Di-assign"
            :value="$permissionStats['with_roles']"
            icon="phosphor.check-circle"
            color="text-success-content"
            class="text-success-content bg-success"
            :tooltip="$permissionStats['with_roles'] . ' permission sudah di-assign ke roles'"
        />
        <x-stat
            title="Belum Di-assign"
            :value="$permissionStats['without_roles']"
            icon="phosphor.x-circle"
            color="text-warning-content"
            class="text-warning-content bg-warning"
            :tooltip="$permissionStats['without_roles'] . ' permission belum di-assign ke roles'"
        />
        <x-stat
            title="Guard Web"
            :value="$permissionStats['by_guard']['web'] ?? 0"
            icon="phosphor.globe"
            color="text-info-content"
            class="text-info-content bg-info"
            :tooltip="($permissionStats['by_guard']['web'] ?? 0) . ' permission untuk Web Guard'"
        />
    </div>

    {{-- MAIN CONTENT --}}
    @if($permissions->count() === 0)
        {{-- EMPTY STATE --}}
        <x-card class="p-12">
            <div class="text-center">
                <x-icon name="phosphor.key" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                <h3 class="text-lg font-semibold text-base-content/60 mb-2">Tidak ada permission ditemukan</h3>
                <p class="text-base-content/40 mb-6">
                    @if($search)
                        Tidak ada permission yang cocok dengan pencarian "<strong>{{ $search }}</strong>"
                    @else
                        Belum ada permission yang terdaftar dalam sistem
                    @endif
                </p>
                @if(!$search)
                    <x-button
                        label="Tambah Permission Pertama"
                        link="{{ route('app.permission.create') }}"
                        icon="phosphor.plus"
                        class="btn-primary"
                    />
                @else
                    <x-button
                        label="Reset Pencarian"
                        wire:click="$set('search', '')"
                        icon="phosphor.x"
                        class="btn-ghost"
                    />
                @endif
            </div>
        </x-card>
    @else
        {{-- PERMISSION CARDS --}}
        <x-card class="p-6">
            {{-- Grid Layout menggunakan DaisyUI responsive grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($permissions as $permission)
                    @php
                        $category = $this->getPermissionCategory($permission->name);
                        $color = $this->getPermissionColor($category);
                        $icon = $this->getPermissionIcon($category);
                        $displayName = ucwords(str_replace(['-', '_', '.'], ' ', $permission->name));
                        $canBeDeleted = $this->canPermissionBeDeleted($permission);
                    @endphp

                    {{-- Permission Card --}}
                    <x-card
                        title="{{ $displayName }}"
                        class="bg-base-200 hover:shadow-lg transition-all duration-100 cursor-pointer"
                        wire:click="viewPermission({{ $permission->id }})"
                        no-separator
                    >
                        {{-- Category Badge di Menu --}}
                        <x-slot:menu>
                            <div class="badge badge-{{ $color }} badge-sm">
                                <x-icon name="{{ $icon }}" class="w-3 h-3 mr-1" />
                                {{ ucfirst($category) }}
                            </div>
                        </x-slot:menu>

                        {{-- Card Content --}}
                        <div class="space-y-4">
                            {{-- Permission Name & Details --}}
                            <div class="text-center">
                                <div class="flex justify-center mb-3">
                                    <div class="w-16 h-16 rounded-full bg-{{ $color }}/20 flex items-center justify-center">
                                        <x-icon name="{{ $icon }}" class="w-8 h-8 text-{{ $color }}" />
                                    </div>
                                </div>

                                <h4 class="font-semibold text-base-content mb-1">{{ $permission->name }}</h4>
                                <p class="text-xs text-base-content/60">{{ $permission->guard_name }} guard</p>
                            </div>

                            {{-- Statistics --}}
                            <div class="grid grid-cols-2 gap-3 text-center">
                                <div class="bg-base-300 rounded-lg p-2">
                                    <div class="text-lg font-bold text-{{ $color }}">{{ $permission->roles()->count() }}</div>
                                    <div class="text-xs text-base-content/70">Roles</div>
                                </div>
                                <div class="bg-base-300 rounded-lg p-2">
                                    <div class="text-lg font-bold text-{{ $color }}">{{ $permission->users()->count() }}</div>
                                    <div class="text-xs text-base-content/70">Users</div>
                                </div>
                            </div>

                            {{-- Usage Status --}}
                            <div class="text-center">
                                @if($permission->roles()->count() > 0)
                                    <div class="badge badge-success badge-sm">
                                        <x-icon name="phosphor.check" class="w-3 h-3 mr-1" />
                                        Sudah Di-assign
                                    </div>
                                @else
                                    <div class="badge badge-warning badge-sm">
                                        <x-icon name="phosphor.warning" class="w-3 h-3 mr-1" />
                                        Belum Di-assign
                                    </div>
                                @endif
                            </div>

                            {{-- Timestamps --}}
                            <div class="text-xs text-base-content/50 space-y-1">
                                <div class="flex items-center gap-1">
                                    <x-icon name="phosphor.calendar" class="w-3 h-3" />
                                    <span>{{ $permission->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-icon name="phosphor.clock" class="w-3 h-3" />
                                    <span>{{ $permission->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <x-slot:actions separator>
                            {{-- View Button --}}
                            <x-button
                                label="Lihat"
                                wire:click.stop="viewPermission({{ $permission->id }})"
                                class="btn-info btn-sm"
                                icon="phosphor.eye"
                                responsive
                                tooltip="Lihat detail {{ $permission->name }}"
                            />

                            {{-- Assign Button --}}
                            <x-button
                                label="Assign"
                                wire:click.stop="openAssignModal({{ $permission->id }})"
                                class="btn-primary btn-sm"
                                icon="phosphor.users"
                                responsive
                                tooltip="Assign ke roles"
                            />

                            {{-- Delete Button --}}
                            <x-button
                                label="Hapus"
                                wire:click.stop="openDeleteModal({{ $permission->id }})"
                                class="btn-error btn-sm"
                                icon="phosphor.trash"
                                responsive
                                tooltip="Hapus {{ $permission->name }}"
                                :disabled="!$canBeDeleted"
                            />
                        </x-slot:actions>
                    </x-card>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($permissions->hasPages())
                <div class="mt-8 border-t border-base-300 pt-6">
                    {{-- Results Info & Per Page Selector --}}
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0 mb-6">
                        {{-- Results Info --}}
                        <div class="text-sm text-base-content/70 text-center md:text-left">
                            Menampilkan <span class="font-semibold">{{ $permissions->firstItem() ?? 0 }}</span> -
                            <span class="font-semibold">{{ $permissions->lastItem() ?? 0 }}</span>
                            dari <span class="font-semibold">{{ $permissions->total() }}</span> permission
                            @if($search)
                                <br class="md:hidden">
                                <span class="block md:inline mt-1 md:mt-0">
                                    untuk pencarian "<span class="font-semibold text-primary">{{ $search }}</span>"
                                </span>
                            @endif
                        </div>

                        {{-- Per Page Selector --}}
                        <div class="flex items-center justify-center md:justify-end gap-3">
                            <label class="text-sm text-base-content/70 font-medium">Per halaman:</label>
                            <x-select
                                wire:model.live="perPage"
                                :options="$perPageOptions"
                                option-value="value"
                                option-label="label"
                                class="select-sm w-20"
                            />
                        </div>
                    </div>

                    {{-- Pagination Links --}}
                    {{ $permissions->links() }}
                </div>
            @endif
        </x-card>
    @endif

    {{-- FILTER DRAWER --}}
    <x-drawer wire:model="drawer" title="Filter Permission" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            {{-- Search --}}
            <x-input
                placeholder="Cari permission..."
                wire:model.live.debounce="search"
                icon="phosphor.magnifying-glass"
                @keydown.enter="$wire.drawer = false"
                clearable
            />

            {{-- Category Filter --}}
            <x-select
                label="Filter Kategori"
                wire:model.live="categoryFilter"
                :options="collect($this->categories)->map(fn($label, $value) => ['id' => $value, 'name' => $label])->values()->toArray()"
                option-value="id"
                option-label="name"
                icon="phosphor.tag"
            />

            {{-- Guard Filter --}}
            <x-select
                label="Filter Guard"
                wire:model.live="guardFilter"
                :options="collect($this->guards)->map(fn($label, $value) => ['id' => $value, 'name' => $label])->values()->toArray()"
                option-value="id"
                option-label="name"
                icon="phosphor.shield"
            />

            {{-- Assignment Filter --}}
            <x-select
                label="Status Assignment"
                wire:model.live="assignmentFilter"
                :options="$assignmentFilterOptions"
                option-value="id"
                option-label="name"
                icon="phosphor.check-circle"
            />

            {{-- Sort Options --}}
            <x-select
                label="Urutkan Berdasarkan"
                wire:model.live="sortBy.column"
                :options="$sortOptions"
                option-value="id"
                option-label="name"
                icon="phosphor.sort-ascending"
            />

            {{-- Sort Direction --}}
            <x-select
                label="Urutan"
                wire:model.live="sortBy.direction"
                :options="$sortDirectionOptions"
                option-value="id"
                option-label="name"
                icon="phosphor.arrows-down-up"
            />

            {{-- Filter Summary --}}
            @if($hasActiveFilters)
                <x-card class="bg-base-200" title="Filter Aktif" no-separator>
                    <div class="space-y-2 text-sm">
                        @if($search)
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.magnifying-glass" class="w-4 h-4" />
                                <span>Pencarian: <strong>{{ $search }}</strong></span>
                            </div>
                        @endif
                        @if($categoryFilter !== 'all')
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.tag" class="w-4 h-4" />
                                <span>Kategori: <strong>{{ $this->categories[$categoryFilter] }}</strong></span>
                            </div>
                        @endif
                        @if($guardFilter !== 'all')
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.shield" class="w-4 h-4" />
                                <span>Guard: <strong>{{ $this->guards[$guardFilter] }}</strong></span>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>

        <x-slot:actions>
            <x-button
                label="Reset Filter"
                icon="phosphor.x-circle"
                wire:click="clear"
                spinner
                class="btn-ghost"
            />
            <x-button
                label="Tutup"
                icon="phosphor.check-circle"
                class="btn-primary"
                @click="$wire.drawer = false"
            />
        </x-slot:actions>
    </x-drawer>

    {{-- MODAL COMPONENTS --}}
    {{-- Assign Permission Modal --}}
    <livewire:app.component.permission.assign-permission-modal />

    {{-- Delete Permission Modal --}}
    <livewire:app.component.permission.delete-permission-modal />
</div>
