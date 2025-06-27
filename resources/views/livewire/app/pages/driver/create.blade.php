{{-- Create Driver Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Tambah Sopir" subtitle="Tambahkan data sopir baru beserta informasi SIM di sini"
        icon="{{ $this->driverUIConfig['icons']['add'] }}" icon-classes="text-success h-10" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.driver.index') }}" wire:navigate>Data Sopir</a></li>
                    <li>Tambah Sopir</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- DRIVER PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Preview Sopir" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->driverUIConfig['icons']['user'] }}" class="h-5 text-success" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Display --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ $is_active ? 'success' : 'warning' }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-error text-error-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $name ? strtoupper(substr($name, 0, 2)) : 'S' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email Display --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: 'Nama Sopir' }}
                            </h3>
                            <p class="text-base-content/70 break-all">
                                {{ $email ?: 'email@example.com' }}
                            </p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-error badge-lg">
                                    <x-icon name="phosphor.truck" class="h-4" />
                                    Sopir
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="{{ $is_active ? \App\Class\Helper\UserHelper::getStatusIcon('active') : \App\Class\Helper\UserHelper::getStatusIcon('inactive') }}"
                                        class="h-4" />
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Status Alert --}}
                    @if ($this->isFormValid)
                        <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-4 h-4 text-success" />
                                <span class="text-sm text-success font-medium">Siap untuk disimpan!</span>
                            </div>
                        </div>
                    @elseif($this->hasData)
                        <div class="bg-warning/10 border border-warning/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.warning" class="w-4 h-4 text-warning" />
                                <span class="text-sm text-warning font-medium">Lengkapi semua field wajib</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-info/10 border border-info/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.info" class="w-4 h-4 text-info" />
                                <span class="text-sm text-info font-medium">Mulai mengisi form untuk preview</span>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Sopir" wire:click="cancel" class="btn-primary btn-block"
                            icon="phosphor.truck" />

                        <x-button label="Reset Form" wire:click="resetForm" class="btn-secondary btn-block"
                            icon="phosphor.arrow-counter-clockwise" :disabled="!$this->hasData" />

                        <x-button :label="$is_active ? 'Set: Nonaktifkan' : 'Set: Aktifkan'"
                            wire:click="$dispatch('openChangeStatusPreview', $this->previewData)"
                            class="btn-{{ $is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$is_active ? \App\Class\Helper\UserHelper::getStatusIcon('inactive') : \App\Class\Helper\UserHelper::getStatusIcon('active')" :disabled="empty($name) || empty($email)" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Informasi Sopir Baru" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="phosphor.plus-circle" class="h-5 text-success" />
                </x-slot:menu>

                <x-form wire:submit="save">
                    <div class="space-y-6">
                        {{-- User Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
                                Informasi Akun Sopir
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
                                                @if ($avatar)
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar" />
                                                @else
                                                    <div
                                                        class="w-full h-full bg-error text-error-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $name ? strtoupper(substr($name, 0, 2)) : 'S' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Preview</p>
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <x-file label="Upload Foto Profil" wire:model="avatar" accept="image/*"
                                    hint="Opsional. Maksimal 2MB. Format: JPEG, JPG, PNG, WebP" />
                            </div>

                            {{-- Name --}}
                            <x-input label="Nama Lengkap" wire:model.live.debounce.500ms="name" placeholder="Masukkan nama lengkap sopir"
                                icon="{{ $this->driverUIConfig['icons']['name'] }}" clearable required />

                            {{-- Email --}}
                            <x-input label="Alamat Email" wire:model.live.debounce.500ms="email" placeholder="driver@example.com"
                                type="email" icon="{{ $this->driverUIConfig['icons']['email'] }}" clearable required />

                            {{-- Security --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi" wire:model.live.debounce.500ms="password"
                                    placeholder="Minimal 8 karakter"
                                    hint="Kombinasi huruf besar, kecil, angka, dan simbol" required />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.live.debounce.500ms="password_confirmation"
                                    placeholder="Ulangi kata sandi" required />
                            </div>
                        </div>

                        {{-- Driver Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
                                Informasi SIM Sopir
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- License Number --}}
                                <x-input label="Nomor SIM" wire:model.live.debounce.500ms="license_number"
                                    placeholder="Contoh: 1234567890123456"
                                    icon="{{ $this->driverUIConfig['icons']['license_number'] }}" clearable required />

                                {{-- License Type --}}
                                <x-select label="Jenis SIM" wire:model.live="license_type"
                                    :options="collect($this->licenseTypes)
                                        ->map(fn($label, $value) => ['id' => $value, 'name' => $label])
                                        ->values()
                                        ->toArray()"
                                    option-value="id" option-label="name"
                                    icon="{{ $this->driverUIConfig['icons']['license_type'] }}" required />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- License Expiry --}}
                                <x-datetime label="Tanggal Kadaluarsa SIM" wire:model.live="license_expiry"
                                    icon="{{ $this->driverUIConfig['icons']['license_expiry'] }}" type="date" required />

                                {{-- Phone --}}
                                <x-input label="Nomor Telepon" wire:model.live.debounce.500ms="phone"
                                    placeholder="Contoh: 08123456789"
                                    icon="{{ $this->driverUIConfig['icons']['phone'] }}" clearable required />
                            </div>

                            {{-- Address --}}
                            <x-textarea label="Alamat" wire:model.live.debounce.500ms="address"
                                placeholder="Alamat lengkap sopir (opsional)"
                                rows="3" />
                        </div>

                        {{-- Vehicle Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
                                Informasi Kendaraan (Opsional)
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Vehicle Type --}}
                                <x-input label="Jenis Kendaraan" wire:model.live.debounce.500ms="vehicle_type"
                                    placeholder="Contoh: Truk, Mobil Box, Motor"
                                    icon="{{ $this->driverUIConfig['icons']['vehicle_type'] }}" clearable />

                                {{-- Vehicle Plate --}}
                                <x-input label="Plat Nomor" wire:model.live.debounce.500ms="vehicle_plate"
                                    placeholder="Contoh: B 1234 ABC"
                                    icon="{{ $this->driverUIConfig['icons']['vehicle_plate'] }}" clearable />
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button label="Simpan Sopir" type="submit" class="btn-primary" icon="{{ $this->formStatus['icon'] }}"
                            :disabled="!$this->isFormValid" spinner="save" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Change Status Modal (untuk preview) --}}
    <livewire:app.component.user.change-status-modal />
</div>
