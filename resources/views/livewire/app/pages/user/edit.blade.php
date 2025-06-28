{{-- Edit User Page - Mary UI + DaisyUI Standards (Simplified) --}}
<div>
    {{-- HEADER --}}
    <x-header title="Edit {{ $user->name }}" icon="{{ $this->userUIConfig['icons']['edit'] }}" icon-classes="text-warning h-10" separator
        progress-indicator>
        <x-slot:subtitle>
            <div>Perbarui data {{ $user->name }} internal <span class="text-warning/60">{{ $user->name }}</span> di
                sini</div>
        </x-slot:subtitle>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.user.index') }}" wire:navigate>{{ \App\Class\Helper\UserHelper::PAGE_TITLE_INDEX }}</a></li>
                    <li><a href="{{ route('app.user.view', $user) }}" wire:navigate>Detail</a></li>
                    <li>Edit - {{ $user->name }}</li>
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
                    <x-icon name="{{ $this->userUIConfig['icons']['user'] }}" class="h-5 text-warning" />
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
                                            class="w-full h-full bg-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }} text-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }}-content rounded-full flex items-center justify-center text-3xl font-bold">
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
                                <div
                                    class="badge badge-{{ $role ? \App\Class\Helper\UserHelper::getRoleColor($role) : $user->role_color }} badge-lg">
                                    <x-icon
                                        name="{{ $role ? \App\Class\Helper\UserHelper::getRoleIcon($role) : $user->role_icon }}"
                                        class="h-4" />
                                    {{ $role ? \App\Class\Helper\UserHelper::getRoleLabel($role) : $user->role_label }}
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="{{ $is_active ? \App\Class\Helper\UserHelper::getStatusIcon('active') : \App\Class\Helper\UserHelper::getStatusIcon('inactive') }}"
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
                                <x-icon name="{{ $this->userUIConfig['icons']['warning'] }}" class="w-4 h-4 text-warning" />
                                <span class="text-sm text-warning font-medium">Ada perubahan yang belum disimpan</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="{{ $this->userUIConfig['icons']['success'] }}" class="w-4 h-4 text-success" />
                                <span class="text-sm text-success font-medium">Semua tersimpan</span>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button label="Data Pengguna" wire:click="backToList"
                            class="btn-primary btn-outline btn-block" icon="{{ $this->userUIConfig['icons']['back'] }}" />

                        <x-button label="Detail {{ $user->name }}" wire:click="cancel"
                            class="btn-info btn-outline btn-block" icon="{{ $this->userUIConfig['icons']['view'] }}" />

                        <x-button :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" wire:click="changeUserStatus"
                            class="btn-{{ $user->is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$user->is_active ? \App\Class\Helper\UserHelper::getStatusIcon('inactive') : \App\Class\Helper\UserHelper::getStatusIcon('active')" />

                        <x-button label="Hapus {{ $user->name }}" wire:click="deleteUser"
                            class="btn-error btn-outline btn-block" icon="{{ $this->userUIConfig['icons']['delete'] }}" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Edit Informasi {{ $user->name }}" separator class="shadow-md">
                <x-slot:menu>
                    <x-icon name="{{ $this->userUIConfig['icons']['edit'] }}" class="h-5 text-warning" />
                </x-slot:menu>

                <x-form wire:submit="update">
                    <div class="space-y-6">
                        {{-- User Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Informasi {{ $user->name }}
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
                                                        class="w-full h-full bg-{{ $user->role_color }} text-{{ $user->role_color }}-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $user->avatar_placeholder }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Saat ini</p>
                                    </div>

                                    {{-- Arrow & New Avatar --}}
                                    @if ($avatar)
                                        <x-icon name="{{ $this->userUIConfig['icons']['arrow_right'] }}" class="w-6 h-6 text-base-content/30" />
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
                                icon="{{ $this->userUIConfig['icons']['user'] }}" clearable required />

                            {{-- Email --}}
                            <x-input label="Alamat Email" wire:model.blur="email" placeholder="user@example.com"
                                type="email" icon="{{ $this->userUIConfig['icons']['email'] }}" clearable required />

                            {{-- Role --}}
                            <x-select label="Peran {{ $user->name }}" wire:model.live="role" :options="collect($this->roles)
                                ->map(fn($label, $value) => ['id' => $value, 'name' => $label])
                                ->values()
                                ->toArray()"
                                option-value="id" option-label="name"
                                icon="{{ $role ? \App\Class\Helper\UserHelper::getRoleIcon($role) : 'phosphor.question' }}"
                                required />
                        </div>

                        {{-- Security --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-warning pb-2">
                                Keamanan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Password --}}
                                <x-password label="Kata Sandi Baru" wire:model.blur="password"
                                    placeholder="Kosongkan jika tidak diubah"
                                    hint="Kombinasi huruf besar, kecil, angka, dan simbol" />

                                {{-- Password Confirmation --}}
                                <x-password label="Konfirmasi Kata Sandi" wire:model.blur="password_confirmation"
                                    placeholder="Ulangi kata sandi baru" />
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button label="{{ \App\Class\Helper\FormatHelper::LABEL_SAVE }} Perubahan" type="submit" class="btn-warning" icon="{{ $this->userUIConfig['icons']['success'] }}"
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
