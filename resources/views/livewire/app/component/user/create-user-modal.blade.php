{{-- Create User Modal - Mary UI + DaisyUI Standards --}}
<div>
    <x-modal
        wire:model="showModal"
        title="Tambah Pengguna Baru"
        subtitle="Buat akun pengguna baru dalam sistem"
        persistent
        separator
        class="backdrop-blur"
        box-class="border-2 border-success max-w-4xl"
    >
        @if($processing)
            {{-- Loading State --}}
            <div class="flex items-center justify-center py-12">
                <x-loading class="loading-lg loading-spinner text-primary" />
                <span class="ml-3 text-lg">Membuat pengguna...</span>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Form Section --}}
                <div class="lg:col-span-2">
                    <x-form wire:submit="save">
                        <div class="space-y-6">
                            {{-- Personal Information --}}
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                    <x-icon name="phosphor.identification-card" class="w-5 h-5 inline mr-2" />
                                    Informasi Pribadi
                                </h3>

                                {{-- Avatar Upload --}}
                                <div class="flex items-center gap-4">
                                    <x-avatar
                                        :image="$avatar ? $avatar->temporaryUrl() : null"
                                        :placeholder="$name ? strtoupper(substr($name, 0, 2)) : 'U'"
                                        class="w-20 h-20 ring ring-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} ring-offset-base-100 ring-offset-2"
                                    />
                                    <div class="flex-1">
                                        <x-file
                                            label="Foto Profil"
                                            wire:model="avatar"
                                            accept="image/*"
                                            hint="Maksimal 2MB. Format: JPG, PNG, GIF"
                                        />
                                    </div>
                                </div>

                                {{-- Name --}}
                                <x-input
                                    label="Nama Lengkap"
                                    wire:model.live="name"
                                    placeholder="Masukkan nama lengkap"
                                    icon="phosphor.user"
                                    clearable
                                    required
                                />

                                {{-- Email --}}
                                <x-input
                                    label="Alamat Email"
                                    wire:model.blur="email"
                                    placeholder="user@example.com"
                                    type="email"
                                    icon="phosphor.envelope"
                                    clearable
                                    required
                                />
                            </div>

                            {{-- Role & Status --}}
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                    <x-icon name="phosphor.user-circle" class="w-5 h-5 inline mr-2" />
                                    Peran & Status
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Role --}}
                                    <x-select
                                        label="Peran Pengguna"
                                        wire:model.live="role"
                                        :options="collect($this->roles)->map(fn($label, $value) => ['id' => $value, 'name' => $label])->values()->toArray()"
                                        option-value="id"
                                        option-label="name"
                                        icon="phosphor.identification-badge"
                                        required
                                    />

                                    {{-- Status --}}
                                    <div>
                                        <x-checkbox
                                            label="Pengguna Aktif"
                                            wire:model.live="is_active"
                                            hint="Pengguna dapat login dan mengakses sistem"
                                        />
                                        <div class="mt-2">
                                            <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-sm">
                                                {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Role Preview --}}
                                @if($role)
                                    <div class="bg-base-200 rounded-lg p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="badge badge-{{ \App\Models\User::getRoleColorByKey($role) }} badge-lg">
                                                {{ \App\Models\User::getRoleLabelByKey($role) }}
                                            </div>
                                            <span class="text-sm text-base-content/70">
                                                Peran yang dipilih untuk pengguna ini
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Security --}}
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                    <x-icon name="phosphor.lock" class="w-5 h-5 inline mr-2" />
                                    Keamanan
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Password --}}
                                    <x-password
                                        label="Kata Sandi"
                                        wire:model.blur="password"
                                        placeholder="Minimal 8 karakter"
                                        hint="Kombinasi huruf besar, kecil, angka, dan simbol"
                                        required
                                    />

                                    {{-- Password Confirmation --}}
                                    <x-password
                                        label="Konfirmasi Kata Sandi"
                                        wire:model.blur="password_confirmation"
                                        placeholder="Ulangi kata sandi"
                                        required
                                    />
                                </div>

                                {{-- Password Strength Indicator --}}
                                @if($password)
                                    <div class="bg-base-200 rounded-lg p-3">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-sm font-medium">Kekuatan Password:</span>
                                            <div class="badge badge-{{ $passwordStrength['color'] }} badge-sm">
                                                {{ $passwordStrength['text'] }}
                                            </div>
                                        </div>
                                        <div class="w-full bg-base-300 rounded-full h-2">
                                            <div class="bg-{{ $passwordStrength['color'] }} h-2 rounded-full transition-all duration-300"
                                                 style="width: {{ $passwordStrength['strength'] }}%">
                                            </div>
                                        </div>
                                        @if(!empty($passwordStrength['feedback']))
                                            <div class="mt-2">
                                                <p class="text-xs text-base-content/70">Masih dibutuhkan:</p>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($passwordStrength['feedback'] as $feedback)
                                                        <div class="badge badge-warning badge-xs">{{ $feedback }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Password Requirements --}}
                                <div class="bg-info/10 border border-info/30 rounded-lg p-3">
                                    <p class="text-sm font-medium text-info mb-2">Persyaratan kata sandi:</p>
                                    <ul class="text-xs text-info/80 space-y-1 list-disc list-inside">
                                        <li>Minimal 8 karakter</li>
                                        <li>Mengandung huruf besar dan kecil</li>
                                        <li>Mengandung angka</li>
                                        <li>Mengandung simbol (!@#$%^&*)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </x-form>
                </div>

                {{-- Preview Section --}}
                <div class="lg:col-span-1">
                    <x-card title="Preview User" separator>
                        <x-slot:menu>
                            <x-icon name="phosphor.eye" class="w-5 h-5 text-secondary" />
                        </x-slot:menu>

                        <div class="space-y-4">
                            {{-- Avatar Preview --}}
                            <div class="flex justify-center">
                                <div class="avatar">
                                    <div class="w-24 h-24 rounded-full ring ring-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} ring-offset-base-100 ring-offset-2">
                                        @if($avatar)
                                            <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full bg-neutral text-neutral-content rounded-full flex items-center justify-center text-2xl font-bold">
                                                {{ $name ? strtoupper(substr($name, 0, 2)) : 'U' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- User Info --}}
                            <div class="text-center space-y-2">
                                <h4 class="font-semibold text-lg text-base-content">
                                    {{ $name ?: 'Nama User' }}
                                </h4>
                                <p class="text-sm text-base-content/70">
                                    {{ $email ?: 'email@example.com' }}
                                </p>

                                {{-- Badges --}}
                                <div class="flex flex-wrap justify-center gap-2 mt-3">
                                    <div class="badge badge-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} badge-md">
                                        {{ $role ? \App\Models\User::getRoleLabelByKey($role) : 'Pilih Role' }}
                                    </div>
                                    <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-md">
                                        {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Info List --}}
                            <div class="space-y-3 pt-4 border-t border-base-300">
                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.identification-badge" class="w-4 h-4 text-primary" />
                                    <span class="text-sm">
                                        <span class="font-medium">Role:</span>
                                        {{ $role ? \App\Models\User::getRoleLabelByKey($role) : 'Belum dipilih' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.check-circle" class="w-4 h-4 {{ $is_active ? 'text-success' : 'text-warning' }}" />
                                    <span class="text-sm">
                                        <span class="font-medium">Status:</span>
                                        {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <x-icon name="phosphor.lock" class="w-4 h-4 text-info" />
                                    <span class="text-sm">
                                        <span class="font-medium">Password:</span>
                                        {{ $password ? '••••••••' : 'Belum diisi' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Form Validation Status --}}
                            @if($isFormValid)
                                <div class="bg-success/10 border border-success/30 rounded-lg p-3 mt-4">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="phosphor.check-circle" class="w-4 h-4 text-success" />
                                        <span class="text-sm text-success font-medium">Siap untuk disimpan!</span>
                                    </div>
                                </div>
                            @else
                                <div class="bg-warning/10 border border-warning/30 rounded-lg p-3 mt-4">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="phosphor.warning" class="w-4 h-4 text-warning" />
                                        <span class="text-sm text-warning font-medium">Lengkapi semua field wajib</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>
        @endif

        {{-- Modal Actions --}}
        <x-slot:actions>
            <x-button
                label="Batal"
                @click="$wire.closeModal()"
                :disabled="$processing"
                class="btn-ghost"
                icon="phosphor.x"
            />

            <x-button
                label="Simpan User"
                wire:click="save"
                class="btn-success"
                icon="phosphor.check"
                :disabled="!$isFormValid || $processing"
                spinner="save"
            />
        </x-slot:actions>
    </x-modal>
</div>
