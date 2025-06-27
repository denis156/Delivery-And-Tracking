{{-- Edit Driver Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Edit {{ $user->name }}" icon="phosphor.pencil-duotone" icon-classes="text-warning h-10" separator
        progress-indicator>
        <x-slot:subtitle>
            <div>Perbarui data sopir <span class="text-warning/60">{{ $user->name }}</span> dan informasi SIM di sini</div>
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.driver.index') }}" wire:navigate>Data Sopir</a></li>
                    <li><a href="{{ route('app.driver.view', $user) }}" wire:navigate>Detail</a></li>
                    <li>Edit - {{ $user->name }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- DRIVER PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Profil {{ $user->name }}" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.truck" class="h-5 text-warning" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Display --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $user->status_color }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar"
                                            class="w-full h-full object-cover" />
                                    @elseif($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="Current"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-error text-error-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $name ? strtoupper(substr($name, 0, 2)) : $user->avatar_placeholder }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email Display --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: $user->name }}
                            </h3>
                            <p class="text-base-content/70 break-all">
                                {{ $email ?: $user->email }}
                            </p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-error badge-lg">
                                    <x-icon name="phosphor.truck" class="h-4" />
                                    Sopir
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="phosphor.{{ $is_active ? 'check-circle' : 'pause-circle' }}"
                                        class="w-4 h-4 mr-1" />
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Status Alert --}}
                    @if ($this->hasChanges)
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

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Sopir" wire:click="backToList"
                            class="btn-primary btn-outline btn-block" icon="phosphor.truck" />

                        <x-button label="Detail {{ $user->name }}" wire:click="cancel"
                            class="btn-info btn-outline btn-block" icon="phosphor.eye" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="changeUserStatus"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? 'phosphor.pause' : 'phosphor.play'" />

                        <x-button label="Hapus {{ $user->name }}" wire:click="deleteUser"
                            class="btn-error btn-outline btn-block" icon="phosphor.trash" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Edit Informasi Sopir {{ $user->name }}" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.pencil" class="h-5 text-warning" />
                </x-slot:menu>

                <x-form wire:submit="update">
                    <div class="space-y-6">
                        {{-- User Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi Pengguna
                            </h3>

                            {{-- Avatar Upload with Preview --}}
                            <div class="space-y-4">
                                <label class="text-sm font-medium text-base-content">Foto Profil</label>

                                {{-- Avatar Display --}}
                                <div class="flex justify-center items-center gap-6">
                                    {{-- Current Avatar --}}
                                    <div class="text-center">
                                        <div class="avatar">
                                            <div
                                                class="w-20 h-20 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                                @if ($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="Current" />
                                                @else
                                                    <div
                                                        class="w-full h-full bg-error text-error-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $user->avatar_placeholder }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Saat ini</p>
                                    </div>

                                    {{-- Arrow & New Avatar --}}
                                    @if ($avatar)
                                        <x-icon name="phosphor.arrow-right" class="w-6 h-6 text-base-content/30" />
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
                            <x-input label="Nama Lengkap" wire:model.live.debounce.500ms="name" placeholder="Masukkan nama lengkap"
                                icon="phosphor.user" clearable required />

                            {{-- Email --}}
                            <x-input label="Alamat Email" wire:model.live.debounce.500ms="email" placeholder="driver@example.com"
                                type="email" icon="phosphor.envelope" clearable required />

                            {{-- Phone --}}
                            <x-input label="Nomor Telepon" wire:model.live.debounce.500ms="phone" placeholder="081234567890"
                                icon="phosphor.phone" clearable required />

                            {{-- Address --}}
                            <x-textarea label="Alamat Lengkap" wire:model.live.debounce.500ms="address"
                                placeholder="Masukkan alamat lengkap sopir"
                                rows="3" />
                        </div>

                        {{-- Driver License Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi SIM
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- License Number --}}
                                <x-input label="Nomor SIM" wire:model.live.debounce.500ms="license_number"
                                    placeholder="12345678901234" icon="phosphor.identification-card"
                                    clearable required />

                                {{-- License Type --}}
                                <x-select label="Jenis SIM" wire:model.live="license_type"
                                    :options="collect($this->licenseTypes)
                                        ->map(fn($label, $value) => ['id' => $value, 'name' => $label])
                                        ->values()
                                        ->toArray()"
                                    option-value="id" option-label="name"
                                    icon="phosphor.identification-badge" required />
                            </div>

                            {{-- License Expiry --}}
                            <x-datetime label="Tanggal Kadaluarsa SIM" wire:model.live="license_expiry"
                                icon="phosphor.calendar-x" type="date" required
                                hint="Pilih tanggal kadaluarsa SIM" />
                        </div>

                        {{-- Vehicle Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi Kendaraan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Vehicle Type --}}
                                <x-input label="Jenis Kendaraan" wire:model.live.debounce.500ms="vehicle_type"
                                    placeholder="Contoh: Truk Engkel, Motor, Mobil"
                                    icon="phosphor.truck-trailer" clearable />

                                {{-- Vehicle Plate --}}
                                <x-input label="Plat Nomor" wire:model.live.debounce.500ms="vehicle_plate"
                                    placeholder="Contoh: B1234ABC"
                                    icon="phosphor.textbox" clearable />
                            </div>
                        </div>

                        {{-- Security --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Keamanan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi Baru" wire:model.live.debounce.500ms="password"
                                    placeholder="Kosongkan jika tidak diubah"
                                    hint="Kombinasi huruf besar, kecil, angka, dan simbol" />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.live.debounce.500ms="password_confirmation"
                                    placeholder="Ulangi kata sandi baru" />
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button label="Simpan Perubahan" type="submit" class="btn-warning" icon="phosphor.check"
                            :disabled="!$this->hasChanges" spinner="update" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Change Status Modal --}}
    <livewire:app.component.user.change-status-modal />

    {{-- Delete User Modal --}}
    <livewire:app.component.user.delete-user-modal />
</div>
