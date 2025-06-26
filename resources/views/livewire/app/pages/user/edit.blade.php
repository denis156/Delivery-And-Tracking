{{-- Edit User Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Edit Pengguna - {{ $user->name }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.dashboard') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('app.user.index') }}" wire:navigate>Pengguna</a></li>
                    <li><a href="{{ route('app.user.view', $user) }}" wire:navigate>{{ $user->name }}</a></li>
                    <li>Edit</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ACTIONS & PREVIEW SECTION --}}
        <div class="lg:col-span-1">
            <x-card title="Actions & Preview" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.gear" class="w-5 h-5 text-warning" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info Preview (seperti di View) --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Preview --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }} ring-offset-base-100 ring-offset-4">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="Preview"
                                            class="w-full h-full object-cover" />
                                    @elseif($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="Current"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }} text-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $name ? strtoupper(substr($name, 0, 2)) : $user->avatar_placeholder }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email Preview --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: $user->name }}
                            </h3>
                            <p class="text-base-content/70 break-all">
                                {{ $email ?: $user->email }}
                            </p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div
                                    class="badge badge-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }} badge-lg">
                                    <x-icon name="phosphor.identification-badge" class="w-4 h-4 mr-1" />
                                    {{ $role ? \App\Class\Helper\UserHelper::getRoleLabel($role) : $user->role_label }}
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="phosphor.{{ $is_active ? 'check-circle' : 'pause-circle' }}"
                                        class="w-4 h-4 mr-1" />
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Save Status Alert --}}
                    @if ($hasChanges)
                        <div class="bg-warning/10 border border-warning/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.warning" class="w-4 h-4 text-warning" />
                                <span class="text-sm text-warning font-medium">Ada perubahan yang belum disimpan</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-4 h-4 text-success" />
                                <span class="text-sm text-success font-medium">Semua tersimpan</span>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Actions (seperti di View) --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Kembali ke Daftar" wire:click="backToList" class="btn-primary btn-block"
                            icon="phosphor.list" />

                        <x-button label="Kembali ke Detail" wire:click="cancel" class="btn-info btn-block"
                            icon="phosphor.arrow-left" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="changeUserStatus"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'" />

                        <x-button label="Hapus Pengguna" wire:click="deleteUser" class="btn-error btn-outline btn-block"
                            icon="phosphor.trash" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Edit Informasi Pengguna" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.pencil" class="w-5 h-5 text-warning" />
                </x-slot:menu>

                <x-form wire:submit="update">
                    <div class="space-y-6">
                        {{-- Personal Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.identification-card" class="w-5 h-5 inline mr-2" />
                                Informasi Pribadi
                            </h3>

                            {{-- Avatar Upload with Preview --}}
                            <div class="space-y-4">
                                <label class="text-sm font-medium text-base-content">Foto Profil</label>

                                {{-- Current and New Avatar Preview --}}
                                <div class="flex items-center gap-6">
                                    {{-- Current Avatar --}}
                                    <div class="text-center">
                                        <div class="avatar">
                                            <div
                                                class="w-20 h-20 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                                @if ($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="Current" />
                                                @else
                                                    <div
                                                        class="w-full h-full bg-{{ $user->role_color }} text-{{ $user->role_color }}-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $user->avatar_placeholder }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Saat ini</p>
                                    </div>

                                    {{-- Arrow --}}
                                    @if ($avatar)
                                        <x-icon name="phosphor.arrow-right" class="w-6 h-6 text-base-content/30" />

                                        {{-- New Avatar Preview --}}
                                        <div class="text-center">
                                            <div class="avatar">
                                                <div
                                                    class="w-20 h-20 rounded-full ring ring-success ring-offset-base-100 ring-offset-2">
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="New" />
                                                </div>
                                            </div>
                                            <p class="text-xs text-success mt-1">Baru</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- File Upload --}}
                                <x-file label="Ubah Foto Profil" wire:model="avatar" accept="image/*"
                                    hint="Kosongkan jika tidak ingin mengubah. Maksimal 2MB." />
                            </div>

                            {{-- Name --}}
                            <x-input label="Nama Lengkap" wire:model.live="name" placeholder="Masukkan nama lengkap"
                                icon="phosphor.user" clearable required />

                            {{-- Email --}}
                            <x-input label="Alamat Email" wire:model.blur="email" placeholder="user@example.com"
                                type="email" icon="phosphor.envelope" clearable required />
                        </div>

                        {{-- Role & Status --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.user-circle" class="w-5 h-5 inline mr-2" />
                                Peran & Status
                            </h3>

                            {{-- Role --}}
                            <x-select label="Peran Pengguna" wire:model.live="role" :options="collect($this->roles)
                                ->map(fn($label, $value) => ['id' => $value, 'name' => $label])
                                ->values()
                                ->toArray()"
                                option-value="id" option-label="name" icon="phosphor.identification-badge"
                                required />

                            {{-- Changes Alert --}}
                            @if ($hasChanges)
                                <div class="alert alert-warning">
                                    <x-icon name="phosphor.warning" class="w-5 h-5" />
                                    <div>
                                        <h4 class="font-semibold">Ada Perubahan yang Belum Disimpan</h4>
                                        <p class="text-sm">Pastikan untuk menyimpan perubahan sebelum meninggalkan
                                            halaman.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Security (Optional Password Change) --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.lock" class="w-5 h-5 inline mr-2" />
                                Ubah Kata Sandi (Opsional)
                            </h3>

                            <div class="alert alert-info">
                                <x-icon name="phosphor.info" class="w-5 h-5" />
                                <div>
                                    <h4 class="font-semibold">Ubah Kata Sandi</h4>
                                    <p class="text-sm">Kosongkan field kata sandi jika tidak ingin mengubah password
                                        pengguna.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi Baru" wire:model.blur="password"
                                    placeholder="Kosongkan jika tidak diubah" hint="Minimal 8 karakter jika diisi" />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.blur="password_confirmation"
                                    placeholder="Ulangi kata sandi baru" />
                            </div>

                            {{-- Password Strength Indicator --}}
                            @if ($password)
                                <div class="bg-base-200 rounded-lg p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-sm font-medium">Kekuatan Password Baru:</span>
                                        <div class="badge badge-{{ $passwordStrength['color'] }} badge-sm">
                                            {{ $passwordStrength['text'] }}
                                        </div>
                                    </div>
                                    <div class="w-full bg-base-300 rounded-full h-3">
                                        <div class="bg-{{ $passwordStrength['color'] }} h-3 rounded-full transition-all duration-300"
                                            style="width: {{ $passwordStrength['strength'] }}%">
                                        </div>
                                    </div>
                                    @if (!empty($passwordStrength['feedback']))
                                        <div class="mt-3">
                                            <p class="text-xs text-base-content/70 mb-1">Masih dibutuhkan:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($passwordStrength['feedback'] as $feedback)
                                                    <div class="badge badge-warning badge-xs">{{ $feedback }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button label="Simpan Perubahan" type="submit" class="btn-warning" icon="phosphor.check"
                            :disabled="!$hasChanges" spinner="update" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS yang dibutuhkan untuk actions --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
