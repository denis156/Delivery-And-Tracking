{{-- View User Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Detail Pengguna - {{ $user->name }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.dashboard') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('app.user') }}" wire:navigate>Pengguna</a></li>
                    <li>{{ $user->name }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- USER PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Profil Pengguna" separator sticky>
                <x-slot:menu>
                    <x-icon name="phosphor.user-circle" class="w-5 h-5 text-{{ $user->role_color }}" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $user->role_color }} ring-offset-base-100 ring-offset-4">
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
                                    <x-icon name="phosphor.identification-badge" class="w-4 h-4 mr-1" />
                                    {{ $user->role_label }}
                                </div>
                                <div class="badge badge-{{ $user->status_color }} badge-lg">
                                    <x-icon name="phosphor.{{ $user->is_active ? 'check-circle' : 'pause-circle' }}"
                                        class="w-4 h-4 mr-1" />
                                    {{ $user->status_label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Kembali ke Daftar" wire:click="backToList" class="btn-primary btn-block"
                            icon="phosphor.list" />

                        <x-button label="Edit Pengguna" wire:click="editUser" class="btn-warning btn-block"
                            icon="phosphor.pencil" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="toggleUserStatus"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'" />

                        <x-button label="Hapus Pengguna" wire:click="deleteUser" class="btn-error btn-outline btn-block"
                            icon="phosphor.trash" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Account Information --}}
            <x-card title="Informasi Akun" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.info" class="w-5 h-5 text-info" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Personal Details --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-base-300 pb-2">Detail Pribadi</h4>

                        <div class="space-y-3">
                            {{-- Name --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="phosphor.user" class="w-5 h-5 text-primary flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Nama Lengkap</p>
                                    <p class="font-semibold truncate">{{ $user->name }}</p>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="phosphor.envelope" class="w-5 h-5 text-info flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Email</p>
                                    <p class="font-semibold break-all">{{ $user->email }}</p>
                                </div>
                            </div>

                            {{-- Role --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="phosphor.identification-badge"
                                    class="w-5 h-5 text-{{ $user->role_color }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Peran</p>
                                    <p class="font-semibold">{{ $user->role_label }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Account Status --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-base-300 pb-2">Status Akun</h4>

                        <div class="space-y-3">
                            {{-- Active Status --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="phosphor.{{ $user->is_active ? 'check-circle' : 'pause-circle' }}"
                                    class="w-5 h-5 text-{{ $user->status_color }} flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Status Akun</p>
                                    <p class="font-semibold">{{ $user->status_label }}</p>
                                </div>
                            </div>

                            {{-- Email Verification --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon
                                    name="phosphor.{{ $user->email_verified_at ? 'shield-check' : 'shield-warning' }}"
                                    class="w-5 h-5 text-{{ $user->email_verified_at ? 'success' : 'warning' }} flex-shrink-0" />
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
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="phosphor.calendar-plus" class="w-5 h-5 text-secondary flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content/70">Bergabung</p>
                                    <p class="font-semibold">{{ $user->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-base-content/50">
                                        {{ $user->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            {{-- Last Update --}}
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="phosphor.clock-clockwise" class="w-5 h-5 text-accent flex-shrink-0" />
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
            <x-card title="Ringkasan Aktivitas" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.chart-line" class="w-5 h-5 text-success" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Account Age --}}
                    <x-stat title="Usia Akun" :value="$userActivity['joinedDays'] . ' hari'" description="{{ $userActivity['accountAge'] }}"
                        icon="phosphor.calendar-check" color="text-primary" />

                    {{-- Last Activity --}}
                    <x-stat title="Aktivitas Terakhir" :value="$userActivity['lastUpdateDays'] . ' hari lalu'"
                        description="{{ $userActivity['lastUpdate'] }}" icon="phosphor.clock-clockwise"
                        color="text-secondary" />

                    {{-- Email Status --}}
                    <x-stat title="Status Email" :value="$userActivity['isEmailVerified'] ? 'Terverifikasi' : 'Belum Verifikasi'"
                        description="{{ $userActivity['isEmailVerified'] ? 'Email sudah dikonfirmasi' : 'Perlu verifikasi email' }}"
                        icon="phosphor.{{ $userActivity['isEmailVerified'] ? 'check-circle' : 'warning-circle' }}"
                        color="text-{{ $userActivity['isEmailVerified'] ? 'success' : 'warning' }}" />
                </div>
            </x-card>

            {{-- User Capabilities & Permissions --}}
            <x-card title="Hak Akses & Kemampuan" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.key" class="w-5 h-5 text-warning" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- System Access --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-base-300 pb-2">Akses Sistem</h4>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.{{ $userCapabilities['canLogin'] ? 'check' : 'x' }}"
                                        class="w-5 h-5 text-{{ $userCapabilities['canLogin'] ? 'success' : 'error' }}" />
                                    <span class="text-sm font-medium">Login ke Sistem</span>
                                </div>
                                <div
                                    class="badge badge-{{ $userCapabilities['canLogin'] ? 'success' : 'error' }} badge-sm">
                                    {{ $userCapabilities['canLogin'] ? 'Bisa' : 'Tidak Bisa' }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-icon
                                        name="phosphor.{{ $userCapabilities['hasEmailVerified'] ? 'check' : 'x' }}"
                                        class="w-5 h-5 text-{{ $userCapabilities['hasEmailVerified'] ? 'success' : 'error' }}" />
                                    <span class="text-sm font-medium">Email Terverifikasi</span>
                                </div>
                                <div
                                    class="badge badge-{{ $userCapabilities['hasEmailVerified'] ? 'success' : 'error' }} badge-sm">
                                    {{ $userCapabilities['hasEmailVerified'] ? 'Ya' : 'Tidak' }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.{{ $userCapabilities['isManageable'] ? 'check' : 'x' }}"
                                        class="w-5 h-5 text-{{ $userCapabilities['isManageable'] ? 'success' : 'warning' }}" />
                                    <span class="text-sm font-medium">Dapat Dikelola</span>
                                </div>
                                <div
                                    class="badge badge-{{ $userCapabilities['isManageable'] ? 'success' : 'warning' }} badge-sm">
                                    {{ $userCapabilities['isManageable'] ? 'Ya' : 'Driver' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Role-based Permissions --}}
                    <div class="space-y-4">
                        <h4 class="font-semibold text-base-content border-b border-base-300 pb-2">Hak Berdasarkan Peran
                        </h4>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.{{ $userCapabilities['canManageUsers'] ? 'check' : 'x' }}"
                                        class="w-5 h-5 text-{{ $userCapabilities['canManageUsers'] ? 'success' : 'error' }}" />
                                    <span class="text-sm font-medium">Kelola Pengguna</span>
                                </div>
                                <div
                                    class="badge badge-{{ $userCapabilities['canManageUsers'] ? 'success' : 'error' }} badge-sm">
                                    {{ $userCapabilities['canManageUsers'] ? 'Bisa' : 'Tidak Bisa' }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.{{ $userCapabilities['canViewReports'] ? 'check' : 'x' }}"
                                        class="w-5 h-5 text-{{ $userCapabilities['canViewReports'] ? 'success' : 'error' }}" />
                                    <span class="text-sm font-medium">Lihat Laporan</span>
                                </div>
                                <div
                                    class="badge badge-{{ $userCapabilities['canViewReports'] ? 'success' : 'error' }} badge-sm">
                                    {{ $userCapabilities['canViewReports'] ? 'Bisa' : 'Tidak Bisa' }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.{{ $userCapabilities['canManageSystem'] ? 'check' : 'x' }}"
                                        class="w-5 h-5 text-{{ $userCapabilities['canManageSystem'] ? 'success' : 'error' }}" />
                                    <span class="text-sm font-medium">Kelola Sistem</span>
                                </div>
                                <div
                                    class="badge badge-{{ $userCapabilities['canManageSystem'] ? 'success' : 'error' }} badge-sm">
                                    {{ $userCapabilities['canManageSystem'] ? 'Bisa' : 'Tidak Bisa' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Role Description --}}
                <div
                    class="mt-6 p-4 bg-{{ $user->role_color }}/10 border border-{{ $user->role_color }}/30 rounded-lg">
                    <h5 class="font-semibold text-{{ $user->role_color }} mb-2">Deskripsi Peran:
                        {{ $user->role_label }}</h5>
                    <p class="text-sm text-base-content/70">
                        @switch($user->role)
                            @case('admin')
                                Administrator memiliki akses penuh ke sistem, dapat mengelola pengguna, melihat semua laporan,
                                dan mengonfigurasi sistem.
                            @break

                            @case('manager')
                                Manajer dapat mengelola pengguna dan melihat laporan, tetapi tidak memiliki akses ke konfigurasi
                                sistem.
                            @break

                            @case('client')
                                Klien memiliki akses terbatas hanya untuk fitur yang berkaitan dengan layanan mereka.
                            @break

                            @case('petugas-lapangan')
                                Petugas lapangan bertanggung jawab untuk operasional di lapangan dan memiliki akses sesuai
                                kebutuhan tugasnya.
                            @break

                            @case('petugas-ruangan')
                                Petugas ruangan mengelola operasional internal dan memiliki akses untuk mengelola data dalam
                                ruangan.
                            @break

                            @case('petugas-gudang')
                                Petugas gudang bertanggung jawab untuk mengelola inventori dan logistik gudang.
                            @break

                            @default
                                Pengguna dengan peran khusus dalam sistem.
                        @endswitch
                    </p>
                </div>
            </x-card>

            {{-- Timeline or Additional Info (Optional) --}}
            <x-card title="Riwayat Akun" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.clock-counter-clockwise" class="w-5 h-5 text-neutral" />
                </x-slot:menu>

                <div class="space-y-4">
                    {{-- Timeline --}}
                    <div class="timeline timeline-vertical">
                        {{-- Account Created --}}
                        <div class="timeline-start timeline-box bg-primary/10 border-primary/30">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.user-plus" class="w-4 h-4 text-primary" />
                                <div>
                                    <p class="font-semibold text-sm">Akun Dibuat</p>
                                    <p class="text-xs text-base-content/70">
                                        {{ $user->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-middle">
                            <div class="timeline-marker bg-primary"></div>
                        </div>
                        <div class="timeline-end"></div>

                        {{-- Email Verified --}}
                        @if ($user->email_verified_at)
                            <div class="timeline-start"></div>
                            <div class="timeline-middle">
                                <div class="timeline-marker bg-success"></div>
                            </div>
                            <div class="timeline-end timeline-box bg-success/10 border-success/30">
                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.shield-check" class="w-4 h-4 text-success" />
                                    <div>
                                        <p class="font-semibold text-sm">Email Diverifikasi</p>
                                        <p class="text-xs text-base-content/70">
                                            {{ $user->email_verified_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Last Update --}}
                        @if ($user->updated_at->diffInDays($user->created_at) > 0)
                            <div class="timeline-start timeline-box bg-info/10 border-info/30">
                                <div class="flex items-center gap-2">
                                    <x-icon name="phosphor.pencil" class="w-4 h-4 text-info" />
                                    <div>
                                        <p class="font-semibold text-sm">Terakhir Diperbarui</p>
                                        <p class="text-xs text-base-content/70">
                                            {{ $user->updated_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="timeline-middle">
                                <div class="timeline-marker bg-info"></div>
                            </div>
                            <div class="timeline-end"></div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS yang dibutuhkan untuk actions --}}
    {{-- Toggle Status Modal --}}
    <livewire:app.component.user.toggle-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
