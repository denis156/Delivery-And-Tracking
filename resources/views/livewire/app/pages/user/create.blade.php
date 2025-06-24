{{-- Create User Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Tambah Pengguna Baru" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.dashboard') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('app.user.index') }}" wire:navigate>Pengguna</a></li>
                    <li>Tambah Pengguna</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- PREVIEW & ACTIONS SECTION --}}
        <div class="lg:col-span-1">
            <x-card title="Preview & Actions" separator sticky>
                <x-slot:menu>
                    <x-icon name="phosphor.eye" class="w-5 h-5 text-primary" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Avatar & Basic Info Preview --}}
                    <div class="text-center space-y-4">
                        {{-- Avatar Preview --}}
                        <div class="flex justify-center">
                            <div class="avatar">
                                <div class="w-32 h-32 rounded-full ring ring-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} ring-offset-base-100 ring-offset-4">
                                    @if($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" />
                                    @else
                                        <div class="w-full h-full bg-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} text-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }}-content rounded-full flex items-center justify-center text-3xl font-bold">
                                            {{ $name ? strtoupper(substr($name, 0, 2)) : 'U' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Name & Email Preview --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: 'Nama Pengguna' }}
                            </h3>
                            <p class="text-base-content/70 break-all">
                                {{ $email ?: 'email@example.com' }}
                            </p>

                            {{-- Status & Role Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} badge-lg">
                                    <x-icon name="phosphor.identification-badge" class="w-4 h-4 mr-1" />
                                    {{ $role ? \App\Models\User::getRoleLabelByKey($role) : 'Pilih Role' }}
                                </div>
                                <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-lg">
                                    <x-icon name="phosphor.{{ $is_active ? 'check-circle' : 'pause-circle' }}" class="w-4 h-4 mr-1" />
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Status Alert --}}
                    @if($isFormValid)
                        <div class="bg-success/10 border border-success/30 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-4 h-4 text-success" />
                                <span class="text-sm text-success font-medium">Siap untuk disimpan!</span>
                            </div>
                        </div>
                    @elseif($hasData)
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
                        <x-button
                            label="Kembali ke Daftar"
                            wire:click="cancel"
                            class="btn-primary btn-block"
                            icon="phosphor.list"
                        />

                        <x-button
                            label="Reset Form"
                            wire:click="resetForm"
                            class="btn-secondary btn-block"
                            icon="phosphor.arrow-counter-clockwise"
                            :disabled="!$hasData"
                        />

                        <x-button
                            :label="$is_active ? 'Preview: Nonaktifkan' : 'Preview: Aktifkan'"
                            wire:click="toggleUserStatus"
                            class="btn-{{ $is_active ? 'warning' : 'success' }} btn-outline btn-block"
                            :icon="$is_active ? 'phosphor.pause' : 'phosphor.play'"
                            :disabled="empty($name) || empty($email)"
                        />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Informasi Pengguna Baru" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.user-plus" class="w-5 h-5 text-primary" />
                </x-slot:menu>

                <x-form wire:submit="save">
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

                                {{-- Avatar Preview --}}
                                <div class="flex items-center gap-6">
                                    {{-- Current Preview --}}
                                    <div class="text-center">
                                        <div class="avatar">
                                            <div class="w-20 h-20 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                                @if($avatar)
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" />
                                                @else
                                                    <div class="w-full h-full bg-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} text-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }}-content rounded-full flex items-center justify-center text-xl font-bold">
                                                        {{ $name ? strtoupper(substr($name, 0, 2)) : 'U' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Preview</p>
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <x-file
                                    label="Upload Foto Profil"
                                    wire:model="avatar"
                                    accept="image/*"
                                    hint="Opsional. Maksimal 2MB. Format: JPEG, JPG, PNG, WebP"
                                />
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

                            {{-- Role Description --}}
                            @if($role)
                                <div class="bg-{{ \App\Models\User::getRoleColorByKey($role) }}/10 border border-{{ \App\Models\User::getRoleColorByKey($role) }}/30 rounded-lg p-3">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="badge badge-{{ \App\Models\User::getRoleColorByKey($role) }} badge-lg">
                                            {{ \App\Models\User::getRoleLabelByKey($role) }}
                                        </div>
                                        <span class="text-sm font-medium text-{{ \App\Models\User::getRoleColorByKey($role) }}">
                                            Deskripsi Peran
                                        </span>
                                    </div>
                                    <p class="text-sm text-base-content/70">
                                        @switch($role)
                                            @case('admin')
                                                Administrator memiliki akses penuh ke sistem, dapat mengelola pengguna, melihat semua laporan, dan mengonfigurasi sistem.
                                            @break
                                            @case('manager')
                                                Manajer dapat mengelola pengguna dan melihat laporan, tetapi tidak memiliki akses ke konfigurasi sistem.
                                            @break
                                            @case('client')
                                                Klien memiliki akses terbatas hanya untuk fitur yang berkaitan dengan layanan mereka.
                                            @break
                                            @case('petugas-lapangan')
                                                Petugas lapangan bertanggung jawab untuk operasional di lapangan dan memiliki akses sesuai kebutuhan tugasnya.
                                            @break
                                            @case('petugas-ruangan')
                                                Petugas ruangan mengelola operasional internal dan memiliki akses untuk mengelola data dalam ruangan.
                                            @break
                                            @case('petugas-gudang')
                                                Petugas gudang bertanggung jawab untuk mengelola inventori dan logistik gudang.
                                            @break
                                            @default
                                                Pengguna dengan peran khusus dalam sistem.
                                        @endswitch
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Security --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.lock" class="w-5 h-5 inline mr-2" />
                                Keamanan
                            </h3>

                            <div class="alert alert-info">
                                <x-icon name="phosphor.info" class="w-5 h-5" />
                                <div>
                                    <h4 class="font-semibold">Kata Sandi Wajib</h4>
                                    <p class="text-sm">Pengguna baru harus memiliki kata sandi untuk dapat login ke sistem.</p>
                                </div>
                            </div>

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
                                <div class="bg-base-200 rounded-lg p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-sm font-medium">Kekuatan Password:</span>
                                        <div class="badge badge-{{ $passwordStrength['color'] }} badge-sm">
                                            {{ $passwordStrength['text'] }}
                                        </div>
                                    </div>
                                    <div class="w-full bg-base-300 rounded-full h-3">
                                        <div class="bg-{{ $passwordStrength['color'] }} h-3 rounded-full transition-all duration-300"
                                             style="width: {{ $passwordStrength['strength'] }}%">
                                        </div>
                                    </div>
                                    @if(!empty($passwordStrength['feedback']))
                                        <div class="mt-3">
                                            <p class="text-xs text-base-content/70 mb-1">Masih dibutuhkan:</p>
                                            <div class="flex flex-wrap gap-1">
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
                                    <li>Mengandung angka (0-9)</li>
                                    <li>Mengandung simbol (@$!%*?&)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button
                            label="Simpan Pengguna"
                            type="submit"
                            class="btn-primary"
                            icon="phosphor.check"
                            :disabled="!$isFormValid"
                            spinner="save"
                        />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Toggle Status Modal (untuk preview) --}}
    <livewire:app.component.user.toggle-status-modal />
</div>
