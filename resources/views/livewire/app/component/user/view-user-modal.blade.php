{{-- View User Detail Modal - Mary UI + DaisyUI Standards --}}
<div>
    <x-modal
        wire:model="showModal"
        :title="$user ? 'Detail Pengguna - ' . $user->name : 'Detail Pengguna'"
        subtitle="Informasi lengkap pengguna"
        persistent
        separator
        class="backdrop-blur"
        box-class="border-2 border-info max-w-5xl"
    >
        @if($loading)
            {{-- Loading State --}}
            <div class="flex items-center justify-center py-12">
                <x-loading class="loading-lg loading-spinner text-info" />
                <span class="ml-3 text-lg">Memuat data pengguna...</span>
            </div>
        @elseif($user)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- User Profile Section --}}
                <div class="lg:col-span-1">
                    <x-card title="Profil Pengguna" separator>
                        <x-slot:menu>
                            <x-icon name="phosphor.user-circle" class="w-5 h-5 text-{{ $user->role_color }}" />
                        </x-slot:menu>

                        <div class="space-y-4">
                            {{-- Avatar --}}
                            <div class="flex justify-center">
                                <div class="avatar">
                                    <div class="w-32 h-32 rounded-full ring ring-{{ $user->role_color }} ring-offset-base-100 ring-offset-4">
                                        @if($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full bg-{{ $user->role_color }} text-{{ $user->role_color }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                                {{ $user->avatar_placeholder }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Basic Info --}}
                            <div class="text-center space-y-2">
                                <h3 class="text-xl font-bold text-base-content">{{ $user->name }}</h3>
                                <p class="text-base-content/70">{{ $user->email }}</p>

                                {{-- Status & Role Badges --}}
                                <div class="flex flex-wrap justify-center gap-2 mt-3">
                                    <div class="badge badge-{{ $user->role_color }} badge-lg">
                                        <x-icon name="phosphor.identification-badge" class="w-4 h-4 mr-1" />
                                        {{ $user->role_label }}
                                    </div>
                                    <div class="badge badge-{{ $user->status_color }} badge-lg">
                                        <x-icon name="phosphor.{{ $user->is_active ? 'check-circle' : 'pause-circle' }}" class="w-4 h-4 mr-1" />
                                        {{ $user->status_label }}
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Actions --}}
                            <div class="flex flex-col gap-2 pt-4 border-t border-base-300">
                                <x-button
                                    label="Edit Pengguna"
                                    wire:click="editUser"
                                    class="btn-primary btn-block"
                                    icon="phosphor.pencil"
                                />

                                <x-button
                                    :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                    wire:click="toggleUserStatus"
                                    class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-block"
                                    :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'"
                                />

                                <x-button
                                    label="Hapus Pengguna"
                                    wire:click="deleteUser"
                                    class="btn-error btn-block"
                                    icon="phosphor.trash"
                                />
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- Detailed Information Section --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Account Information --}}
                    <x-card title="Informasi Akun" separator>
                        <x-slot:menu>
                            <x-icon name="phosphor.info" class="w-5 h-5 text-info" />
                        </x-slot:menu>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Account Details --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.envelope" class="w-5 h-5 text-info" />
                                    <div>
                                        <p class="text-sm font-medium text-base-content/70">Email</p>
                                        <p class="font-semibold">{{ $user->email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.identification-badge" class="w-5 h-5 text-{{ $user->role_color }}" />
                                    <div>
                                        <p class="text-sm font-medium text-base-content/70">Peran</p>
                                        <p class="font-semibold">{{ $user->role_label }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.{{ $user->is_active ? 'check-circle' : 'pause-circle' }}" class="w-5 h-5 text-{{ $user->status_color }}" />
                                    <div>
                                        <p class="text-sm font-medium text-base-content/70">Status</p>
                                        <p class="font-semibold">{{ $user->status_label }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Email Verification --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.{{ $user->email_verified_at ? 'check-circle' : 'warning' }}" class="w-5 h-5 text-{{ $user->email_verified_at ? 'success' : 'warning' }}" />
                                    <div>
                                        <p class="text-sm font-medium text-base-content/70">Verifikasi Email</p>
                                        <p class="font-semibold">
                                            {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                                        </p>
                                        @if($user->email_verified_at)
                                            <p class="text-xs text-base-content/50">
                                                {{ $user->email_verified_at->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.calendar" class="w-5 h-5 text-primary" />
                                    <div>
                                        <p class="text-sm font-medium text-base-content/70">Bergabung</p>
                                        <p class="font-semibold">{{ $user->created_at->format('d M Y') }}</p>
                                        <p class="text-xs text-base-content/50">
                                            {{ $user->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    <x-icon name="phosphor.clock" class="w-5 h-5 text-secondary" />
                                    <div>
                                        <p class="text-sm font-medium text-base-content/70">Terakhir Diperbarui</p>
                                        <p class="font-semibold">{{ $user->updated_at->format('d M Y') }}</p>
                                        <p class="text-xs text-base-content/50">
                                            {{ $user->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    {{-- Activity Summary --}}
                    <x-card title="Ringkasan Aktivitas" separator>
                        <x-slot:menu>
                            <x-icon name="phosphor.chart-line" class="w-5 h-5 text-success" />
                        </x-slot:menu>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Account Age --}}
                            <x-stat
                                title="Usia Akun"
                                :value="($this->userActivity['joinedDays'] ?? 0) . ' hari'"
                                description="{{ $this->userActivity['accountAge'] ?? '-' }}"
                                icon="phosphor.calendar-check"
                                color="text-primary"
                            />

                            {{-- Last Activity --}}
                            <x-stat
                                title="Aktivitas Terakhir"
                                :value="($this->userActivity['lastUpdateDays'] ?? 0) . ' hari lalu'"
                                description="{{ $this->userActivity['lastUpdate'] ?? '-' }}"
                                icon="phosphor.clock-clockwise"
                                color="text-secondary"
                            />

                            {{-- Email Status --}}
                            <x-stat
                                title="Status Email"
                                :value="($this->userActivity['isEmailVerified'] ?? false) ? 'Terverifikasi' : 'Belum Verifikasi'"
                                description="{{ ($this->userActivity['isEmailVerified'] ?? false) ? 'Email sudah dikonfirmasi' : 'Perlu verifikasi email' }}"
                                icon="phosphor.{{ ($this->userActivity['isEmailVerified'] ?? false) ? 'check-circle' : 'warning-circle' }}"
                                color="text-{{ ($this->userActivity['isEmailVerified'] ?? false) ? 'success' : 'warning' }}"
                            />
                        </div>
                    </x-card>

                    {{-- User Capabilities --}}
                    <x-card title="Hak Akses & Kemampuan" separator>
                        <x-slot:menu>
                            <x-icon name="phosphor.key" class="w-5 h-5 text-warning" />
                        </x-slot:menu>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Login Capabilities --}}
                            <div class="space-y-2">
                                <h4 class="font-semibold text-base-content border-b border-base-300 pb-1">Akses Sistem</h4>

                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.{{ ($this->userCapabilities['canLogin'] ?? false) ? 'check' : 'x' }}" class="w-4 h-4 text-{{ ($this->userCapabilities['canLogin'] ?? false) ? 'success' : 'error' }}" />
                                    <span class="text-sm">{{ ($this->userCapabilities['canLogin'] ?? false) ? 'Dapat login' : 'Tidak dapat login' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.{{ ($this->userCapabilities['hasEmailVerified'] ?? false) ? 'check' : 'x' }}" class="w-4 h-4 text-{{ ($this->userCapabilities['hasEmailVerified'] ?? false) ? 'success' : 'error' }}" />
                                    <span class="text-sm">{{ ($this->userCapabilities['hasEmailVerified'] ?? false) ? 'Email terverifikasi' : 'Email belum verifikasi' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.{{ ($this->userCapabilities['isManageable'] ?? false) ? 'check' : 'x' }}" class="w-4 h-4 text-{{ ($this->userCapabilities['isManageable'] ?? false) ? 'success' : 'warning' }}" />
                                    <span class="text-sm">{{ ($this->userCapabilities['isManageable'] ?? false) ? 'Dapat dikelola' : 'Kelola terpisah (driver)' }}</span>
                                </div>
                            </div>

                            {{-- Role-based Capabilities --}}
                            <div class="space-y-2">
                                <h4 class="font-semibold text-base-content border-b border-base-300 pb-1">Hak Berdasarkan Peran</h4>

                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.{{ ($this->userCapabilities['canManageUsers'] ?? false) ? 'check' : 'x' }}" class="w-4 h-4 text-{{ ($this->userCapabilities['canManageUsers'] ?? false) ? 'success' : 'error' }}" />
                                    <span class="text-sm">{{ ($this->userCapabilities['canManageUsers'] ?? false) ? 'Kelola pengguna' : 'Tidak dapat kelola pengguna' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.{{ ($this->userCapabilities['canViewReports'] ?? false) ? 'check' : 'x' }}" class="w-4 h-4 text-{{ ($this->userCapabilities['canViewReports'] ?? false) ? 'success' : 'error' }}" />
                                    <span class="text-sm">{{ ($this->userCapabilities['canViewReports'] ?? false) ? 'Lihat laporan' : 'Tidak dapat lihat laporan' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.{{ ($this->userCapabilities['canManageSystem'] ?? false) ? 'check' : 'x' }}" class="w-4 h-4 text-{{ ($this->userCapabilities['canManageSystem'] ?? false) ? 'success' : 'error' }}" />
                                    <span class="text-sm">{{ ($this->userCapabilities['canManageSystem'] ?? false) ? 'Kelola sistem' : 'Tidak dapat kelola sistem' }}</span>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        @else
            {{-- Error State --}}
            <div class="flex flex-col items-center justify-center py-12">
                <x-icon name="phosphor.warning" class="w-16 h-16 text-error mb-4" />
                <h3 class="text-lg font-semibold text-error mb-2">Data Tidak Ditemukan</h3>
                <p class="text-base-content/70">Pengguna yang diminta tidak dapat ditemukan.</p>
            </div>
        @endif

        {{-- Modal Actions --}}
        <x-slot:actions>
            <x-button
                label="Tutup"
                @click="$wire.closeModal()"
                class="btn-ghost"
                icon="phosphor.x"
            />

            @if($user && !$loading)
                <x-button
                    label="Edit Pengguna"
                    wire:click="editUser"
                    class="btn-primary"
                    icon="phosphor.pencil"
                />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
