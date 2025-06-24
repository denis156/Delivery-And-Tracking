{{-- View Permission Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Detail Permission - {{ $permission->name }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.dashboard') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('app.permission.index') }}" wire:navigate>Permission</a></li>
                    <li>{{ $permission->name }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- PERMISSION PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Permission Profile" separator sticky>
                <x-slot:menu>
                    <x-icon name="{{ $permissionIcon }}" class="w-5 h-5 text-{{ $permissionColor }}" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Permission Icon & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Icon --}}
                        <div class="flex justify-center">
                            <div class="w-32 h-32 rounded-full bg-{{ $permissionColor }}/20 flex items-center justify-center">
                                <x-icon name="{{ $permissionIcon }}" class="w-16 h-16 text-{{ $permissionColor }}" />
                            </div>
                        </div>

                        {{-- Name & Details --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">{{ $permission->name }}</h3>
                            <p class="text-base-content/70">{{ $permission->guard_name }} guard</p>

                            {{-- Category & Guard Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-{{ $permissionColor }} badge-lg">
                                    <x-icon name="{{ $permissionIcon }}" class="w-4 h-4 mr-1" />
                                    {{ ucfirst($permissionCategory) }}
                                </div>
                                <div class="badge badge-info badge-lg">
                                    <x-icon name="phosphor.shield" class="w-4 h-4 mr-1" />
                                    {{ ucfirst($permission->guard_name) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-{{ $permissionColor }}/10 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-{{ $permissionColor }}">{{ $permissionStats['roles_count'] }}</div>
                            <div class="text-xs text-base-content/70">Roles</div>
                        </div>
                        <div class="bg-{{ $permissionColor }}/10 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-{{ $permissionColor }}">{{ $permissionStats['total_users_count'] }}</div>
                            <div class="text-xs text-base-content/70">Users</div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button
                            label="Kembali ke Daftar"
                            wire:click="backToIndex"
                            class="btn-primary btn-block"
                            icon="phosphor.list"
                        />

                        <x-button
                            label="Edit Permission"
                            wire:click="editPermission"
                            class="btn-warning btn-block"
                            icon="phosphor.pencil"
                        />

                        <x-button
                            label="Assign ke Roles"
                            wire:click="openAssignModal"
                            class="btn-success btn-outline btn-block"
                            icon="phosphor.users"
                        />

                        <x-button
                            label="Hapus Permission"
                            wire:click="deletePermission"
                            class="btn-error btn-outline btn-block"
                            icon="phosphor.trash"
                            :disabled="!$canBeDeleted"
                        />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Tabs Navigation --}}
            <x-card class="p-0">
                <div class="tabs tabs-lifted w-full">
                    @foreach($tabs as $tab)
                        <button
                            wire:click="$set('activeTab', '{{ $tab['id'] }}')"
                            class="tab {{ $activeTab === $tab['id'] ? 'tab-active' : '' }}"
                        >
                            <x-icon name="{{ $tab['icon'] }}" class="w-4 h-4 mr-2" />
                            {{ $tab['label'] }}
                            @if($tab['badge'])
                                <div class="badge badge-neutral badge-sm ml-2">{{ $tab['badge'] }}</div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </x-card>

            {{-- Tab Content --}}
            @if($activeTab === 'info')
                {{-- Permission Information --}}
                <x-card title="Informasi Permission" separator>
                    <x-slot:menu>
                        <x-icon name="phosphor.info" class="w-5 h-5 text-info" />
                    </x-slot:menu>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Basic Details --}}
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base-content border-b border-base-300 pb-2">Detail Dasar</h4>

                            <div class="space-y-3">
                                {{-- Name --}}
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.key" class="w-5 h-5 text-primary flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Nama Permission</p>
                                        <p class="font-semibold">{{ $permission->name }}</p>
                                    </div>
                                </div>

                                {{-- Guard --}}
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.shield" class="w-5 h-5 text-info flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Guard</p>
                                        <p class="font-semibold">{{ $permission->guard_name }}</p>
                                    </div>
                                </div>

                                {{-- Category --}}
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="{{ $permissionIcon }}" class="w-5 h-5 text-{{ $permissionColor }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Kategori</p>
                                        <p class="font-semibold">{{ ucfirst($permissionCategory) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Usage Statistics --}}
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base-content border-b border-base-300 pb-2">Statistik Penggunaan</h4>

                            <div class="space-y-3">
                                {{-- Roles Count --}}
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.user-circle" class="w-5 h-5 text-secondary flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Total Roles</p>
                                        <p class="font-semibold">{{ $permissionStats['roles_count'] }} role(s)</p>
                                    </div>
                                </div>

                                {{-- Direct Users --}}
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.user" class="w-5 h-5 text-accent flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Direct Users</p>
                                        <p class="font-semibold">{{ $permissionStats['direct_users_count'] }} user(s)</p>
                                    </div>
                                </div>

                                {{-- Users via Roles --}}
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.users" class="w-5 h-5 text-success flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-base-content/70">Users via Roles</p>
                                        <p class="font-semibold">{{ $permissionStats['via_roles_users_count'] }} user(s)</p>
                                    </div>
                                </div>

                                {{-- Total Users --}}
                                <div class="flex items-center gap-3 p-3 bg-{{ $permissionColor }}/10 border border-{{ $permissionColor }}/30 rounded-lg">
                                    <x-icon name="phosphor.users-three" class="w-5 h-5 text-{{ $permissionColor }} flex-shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-{{ $permissionColor }}">Total Users</p>
                                        <p class="font-bold text-{{ $permissionColor }}">{{ $permissionStats['total_users_count'] }} user(s)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Timestamps --}}
                    <div class="mt-6 pt-4 border-t border-base-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.calendar-plus" class="w-4 h-4 text-primary" />
                                <span>Dibuat: {{ $permission->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.clock-clockwise" class="w-4 h-4 text-secondary" />
                                <span>Diperbarui: {{ $permission->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </x-card>

            @elseif($activeTab === 'roles')
                {{-- Roles Tab --}}
                <x-card title="Roles yang Memiliki Permission Ini" separator>
                    <x-slot:menu>
                        <x-icon name="phosphor.user-circle" class="w-5 h-5 text-secondary" />
                        <div class="badge badge-secondary badge-sm">{{ $permissionStats['roles_count'] }}</div>
                    </x-slot:menu>

                    {{-- Search --}}
                    <div class="mb-4">
                        <x-input
                            placeholder="Cari roles..."
                            wire:model.live.debounce="rolesSearch"
                            icon="phosphor.magnifying-glass"
                            clearable
                        />
                    </div>

                    @if($rolesWithPermission->count() > 0)
                        <div class="space-y-3">
                            @foreach($rolesWithPermission as $role)
                                <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center">
                                            <x-icon name="phosphor.user-circle" class="w-5 h-5 text-secondary" />
                                        </div>
                                        <div>
                                            <h5 class="font-semibold">{{ $role->name }}</h5>
                                            <p class="text-sm text-base-content/70">{{ $role->guard_name }} guard</p>
                                        </div>
                                    </div>
                                    <x-button
                                        label="Cabut"
                                        wire:click="revokeFromRole({{ $role->id }})"
                                        class="btn-error btn-sm"
                                        icon="phosphor.x"
                                        confirm="Yakin ingin mencabut permission dari role {{ $role->name }}?"
                                    />
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($rolesWithPermission->hasPages())
                            <div class="mt-4">
                                {{ $rolesWithPermission->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <x-icon name="phosphor.user-circle-minus" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                            <h3 class="text-lg font-semibold text-base-content/60 mb-2">Belum ada roles</h3>
                            <p class="text-base-content/40 mb-4">
                                @if($rolesSearch)
                                    Tidak ada roles yang cocok dengan pencarian "{{ $rolesSearch }}"
                                @else
                                    Permission ini belum di-assign ke role manapun
                                @endif
                            </p>
                            <x-button
                                label="Assign ke Roles"
                                wire:click="openAssignModal"
                                class="btn-primary"
                                icon="phosphor.plus"
                            />
                        </div>
                    @endif
                </x-card>

            @elseif($activeTab === 'users')
                {{-- Users Tab --}}
                <x-card title="Users yang Memiliki Permission Ini" separator>
                    <x-slot:menu>
                        <x-icon name="phosphor.users" class="w-5 h-5 text-success" />
                        <div class="badge badge-success badge-sm">{{ $permissionStats['total_users_count'] }}</div>
                    </x-slot:menu>

                    {{-- Search --}}
                    <div class="mb-4">
                        <x-input
                            placeholder="Cari users..."
                            wire:model.live.debounce="usersSearch"
                            icon="phosphor.magnifying-glass"
                            clearable
                        />
                    </div>

                    @if($usersWithPermission->count() > 0)
                        <div class="space-y-3">
                            @foreach($usersWithPermission as $user)
                                <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <x-avatar
                                            :placeholder="strtoupper(substr($user->name, 0, 2))"
                                            :image="$user->avatar_url ? asset('storage/' . $user->avatar_url) : null"
                                            class="w-10 h-10"
                                        />
                                        <div>
                                            <h5 class="font-semibold">{{ $user->name }}</h5>
                                            <p class="text-sm text-base-content/70">{{ $user->email }}</p>
                                            <div class="flex items-center gap-1 mt-1">
                                                @foreach($user->roles as $userRole)
                                                    <div class="badge badge-primary badge-xs">{{ $userRole->name }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($user->hasDirectPermission($permission->name))
                                            <div class="badge badge-info badge-sm">Direct</div>
                                        @endif
                                        @if($user->hasPermissionTo($permission->name))
                                            <div class="badge badge-success badge-sm">Via Role</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($usersWithPermission->hasPages())
                            <div class="mt-4">
                                {{ $usersWithPermission->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <x-icon name="phosphor.users-x" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                            <h3 class="text-lg font-semibold text-base-content/60 mb-2">Belum ada users</h3>
                            <p class="text-base-content/40">
                                @if($usersSearch)
                                    Tidak ada users yang cocok dengan pencarian "{{ $usersSearch }}"
                                @else
                                    Belum ada users yang memiliki permission ini
                                @endif
                            </p>
                        </div>
                    @endif
                </x-card>
            @endif
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Assign Permission Modal --}}
    <livewire:app.component.permission.assign-permission-modal />

    {{-- Delete Permission Modal --}}
    <livewire:app.component.permission.delete-permission-modal />
</div>
