{{-- Create User Page - Mary UI + DaisyUI Standards (Simplified) --}}
<div>
    {{-- HEADER --}}
    <x-header title="{{ \App\Class\Helper\UserHelper::PAGE_TITLE_CREATE }}" subtitle="{{ \App\Class\Helper\UserHelper::PAGE_SUBTITLE_CREATE }}"
        icon="{{ $this->userUIConfig['icons']['add'] }}" icon-classes="text-success h-10" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.user.index') }}" wire:navigate>{{ \App\Class\Helper\UserHelper::PAGE_TITLE_INDEX }}</a></li>
                    <li>{{ \App\Class\Helper\UserHelper::PAGE_TITLE_CREATE }}</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- USER PROFILE CARD --}}
        <div class="lg:col-span-1">
            <x-card title="Profil Pengguna" separator sticky class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->userUIConfig['icons']['user'] }}" class="h-5 text-success" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Display --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div
                                    class="w-32 h-32 rounded-full ring ring-{{ \App\Class\Helper\UserHelper::getStatusColor($is_active) ?? 'neutral' }} ring-offset-base-100 ring-offset-4 hover:shadow-xl hover:shadow-primary transition-all duration-300">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar"
                                            class="w-full h-full object-cover" />
                                    @else
                                        <div
                                            class="w-full h-full bg-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : 'neutral' }} text-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : 'neutral' }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $name ? strtoupper(substr($name, 0, 2)) : 'U' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email Display --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: 'Nama Pengguna' }}
                            </h3>
                            <p class="text-base-content/70 break-all">
                                {{ $email ?: 'email@example.com' }}
                            </p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div
                                    class="badge badge-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : 'neutral' }} badge-lg">
                                    <x-icon
                                        name="{{ $role ? \App\Class\Helper\UserHelper::getRoleIcon($role) : 'phosphor.question' }}"
                                        class="h-5" />
                                    {{ $role ? \App\Class\Helper\UserHelper::getRoleLabel($role) : 'Pilih Role' }}
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="{{ $is_active ? \App\Class\Helper\UserHelper::getStatusIcon('active') : \App\Class\Helper\UserHelper::getStatusIcon('inactive') }}"
                                        class="h-5" />
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Status Alert --}}
                    @if ($this->isFormValid)
                        <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $this->userUIConfig['icons']['success'] }}" class="w-4 h-4 text-success" />
                                <span class="text-sm text-success font-medium">Siap untuk disimpan!</span>
                            </div>
                        </div>
                    @elseif($this->hasData)
                        <div class="bg-warning/10 border border-warning/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $this->userUIConfig['icons']['warning'] }}" class="w-4 h-4 text-warning" />
                                <span class="text-sm text-warning font-medium">Lengkapi semua field wajib</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-info/10 border border-info/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $this->userUIConfig['icons']['info'] }}" class="w-4 h-4 text-info" />
                                <span class="text-sm text-info font-medium">Mulai mengisi form untuk preview</span>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Pengguna" wire:click="cancel" class="btn-primary btn-block"
                            icon="{{ $this->userUIConfig['icons']['back'] }}" />

                        <x-button label="Reset Form" wire:click="resetForm" class="btn-secondary btn-block"
                            icon="{{ $this->userUIConfig['icons']['reset'] }}" :disabled="!$this->hasData" />

                        <x-button :label="$is_active ? 'Set: Nonaktifkan' : 'Set: Aktifkan'" wire:click="toggleUserStatus"
                            class="btn-{{ $is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$is_active ? \App\Class\Helper\UserHelper::getStatusIcon('inactive') : \App\Class\Helper\UserHelper::getStatusIcon('active')" :disabled="empty($name) || empty($email)" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Informasi Pengguna Baru" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->userUIConfig['icons']['add'] }}" class="h-5 text-success" />
                </x-slot:menu>

                <x-form wire:submit="save">
                    <div class="space-y-6">
                        {{-- User Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
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
                                                @if ($avatar)
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar" />
                                                @else
                                                    <div
                                                        class="w-full h-full bg-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : 'neutral' }} text-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : 'neutral' }}-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $name ? strtoupper(substr($name, 0, 2)) : 'U' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Tampilan</p>
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <x-file label="Upload Foto Profil" wire:model="avatar" accept="image/*"
                                    hint="Opsional. Maksimal 2MB. Format: JPEG, JPG, PNG, WebP" />
                            </div>

                            {{-- Name --}}
                            <x-input label="Nama Lengkap" wire:model.live="name" placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_NAME_USER }}"
                                icon="{{ $this->userUIConfig['icons']['user'] }}" clearable required />

                            {{-- Email --}}
                            <x-input label="Alamat Email" wire:model.blur="email" placeholder="{{ \App\Class\Helper\FormatHelper::PLACEHOLDER_EMAIL }}"
                                type="email" icon="{{ $this->userUIConfig['icons']['email'] }}" clearable required />

                            {{-- Role --}}
                            <x-select label="Peran Pengguna" wire:model.live="role" :options="collect($this->roles)
                                ->map(fn($label, $value) => ['id' => $value, 'name' => $label])
                                ->values()
                                ->toArray()" option-value="id"
                                option-label="name"
                                icon="{{ $role ? \App\Class\Helper\UserHelper::getRoleIcon($role) : 'phosphor.question' }}"
                                required />
                        </div>

                        {{-- Security --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-success pb-2">
                                Keamanan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi" wire:model.blur="password"
                                    placeholder="Minimal 8 karakter"
                                    hint="Kombinasi huruf besar, kecil, angka, dan simbol" required />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.blur="password_confirmation"
                                    placeholder="Ulangi kata sandi" required />
                            </div>


                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_SAVE }} Pengguna" type="submit" class="btn-primary" icon="phosphor.check"
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
