{{-- View User Page - Mary UI + DaisyUI Standards (Simplified) --}}
<div>
    {{-- HEADER --}}
    <x-header title="Detail {{ $user->name }}" icon="phosphor.eye-duotone" icon-classes="text-info h-10" separator
        progress-indicator>
        <x-slot:subtitle>
            <div>Lihat detail pengguna internal <span class="text-info/60">{{ $user->name }}</span> di sini</div>
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.user.index') }}" wire:navigate>Data Pengguna</a></li>
                    <li>Detail - {{ $user->name }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- USER PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Profil {{ $user->name }}" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.user-focus" class="h-5 text-info" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $user->status_color }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-{{ $user->role_color }} text-{{ $user->role_color }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $user->avatar_placeholder }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">{{ $user->name }}</h3>
                            <p class="text-base-content/70 break-all">{{ $user->email }}</p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-{{ $user->role_color }} badge-lg">
                                    <x-icon name="{{ $user->role_icon }}" class="h-4" />
                                    {{ $user->role_label }}
                                </div>
                                <div class="badge badge-{{ $user->status_color }} badge-lg">
                                    <x-icon name="phosphor.{{ $user->is_active ? 'check-circle' : 'pause-circle' }}"
                                        class="h-4" />
                                    {{ $user->status_label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Pengguna" wire:click="backToList"
                            class="btn-primary btn-outline btn-block" icon="phosphor.users-four" />

                        <x-button label="Edit {{ $user->name }}" wire:click="editUser"
                            class="btn-warning btn-outline btn-block" icon="phosphor.pencil" />

                        <x-button label="Hapus {{ $user->name }}" wire:click="deleteUser"
                            class="btn-error btn-outline btn-block" icon="phosphor.trash" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="changeUserStatus"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'" />

                    </div>
                </div>
            </x-card>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Account Information --}}
            <x-card title="Informasi {{ $user->name }}" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.eye" class="h-5 text-info" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Personal Details --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-info pb-2">Detail Pribadi</h4>

                        <div class="space-y-3">
                            {{-- Name --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.user" class="h-5 text-primary flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Nama Lengkap</p>
                                    <p class="font-semibold truncate">{{ $user->name }}</p>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.envelope" class="h-5 text-info flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Email</p>
                                    <p class="font-semibold break-all">{{ $user->email }}</p>
                                </div>
                            </div>

                            {{-- Role --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="{{ $user->role_icon }}"
                                    class="h-5 text-{{ $user->role_color }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Peran {{ $user->name }}</p>
                                    <p class="font-semibold">{{ $user->role_label }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Account Status --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-info pb-2">Status Akun</h4>

                        <div class="space-y-3">
                            {{-- Active Status --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.{{ $user->is_active ? 'check-circle' : 'pause-circle' }}"
                                    class="h-5 text-{{ $user->status_color }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Status Akun</p>
                                    <p class="font-semibold">{{ $user->status_label }}</p>
                                </div>
                            </div>

                            {{-- Email Verification --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon
                                    name="phosphor.{{ $user->email_verified_at ? 'shield-check' : 'shield-warning' }}"
                                    class="h-5 text-{{ $user->email_verified_at ? 'success' : 'warning' }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Verifikasi Email</p>
                                    <p class="font-semibold">
                                        {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                                    </p>
                                    @if ($user->email_verified_at)
                                        <p class="text-xs text-base-content/50">
                                            {{ $user->email_verified_at->format('d M Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Join Date --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.calendar-plus" class="h-5 text-secondary flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Bergabung</p>
                                    <p class="font-semibold">{{ $user->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-base-content/50">
                                        {{ $user->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            {{-- Last Update --}}
                            <div
                                class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                <x-icon name="phosphor.clock-clockwise" class="h-5 text-accent flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Terakhir Diperbarui</p>
                                    <p class="font-semibold">{{ $user->updated_at->format('d M Y') }}</p>
                                    <p class="text-xs text-base-content/50">
                                        {{ $user->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </x-card>

            {{-- Activity Summary --}}
            <x-card title="Ringkasan Aktivitas" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.person-simple-run" class="h-5 text-info" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    {{-- Account Age --}}
                    <x-stat title="Usia Akun" :value="$this->userActivity['joinedDays']" description="{{ $this->userActivity['accountAge'] }}"
                        icon="phosphor.calendar-check" color="text-primary"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />

                    {{-- Last Activity --}}
                    <x-stat title="Aktivitas Terakhir" :value="$this->userActivity['lastUpdateDays']"
                        description="{{ $this->userActivity['lastUpdate'] }}" icon="phosphor.clock-clockwise"
                        color="text-secondary"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />


                    {{-- Email Status --}}
                    <x-stat title="Status Email" :value="$this->userActivity['isEmailVerified'] ? 'Terverifikasi' : 'Belum Verifikasi'"
                        description="{{ $this->userActivity['isEmailVerified'] ? 'Email sudah dikonfirmasi' : 'Perlu verifikasi email' }}"
                        icon="phosphor.{{ $this->userActivity['isEmailVerified'] ? 'check-circle' : 'warning-circle' }}"
                        color="text-{{ $this->userActivity['isEmailVerified'] ? 'success' : 'warning' }}"
                        class="bg-base-200 hover:shadow-xl hover:shadow-primary transition-all duration-300" />
                </div>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS yang dibutuhkan untuk actions --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
