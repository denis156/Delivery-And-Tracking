{{-- Edit User Modal - Mary UI + DaisyUI Standards --}}
<div>
    <x-modal
        wire:model="showModal"
        :title="$user ? 'Edit Pengguna - ' . $user->name : 'Edit Pengguna'"
        subtitle="Ubah informasi pengguna"
        persistent
        separator
        class="backdrop-blur"
        box-class="border-2 border-warning max-w-4xl"
    >
        @if($loading)
            {{-- Loading State --}}
            <div class="flex items-center justify-center py-12">
                <x-loading class="loading-lg loading-spinner text-warning" />
                <span class="ml-3 text-lg">Memuat data pengguna...</span>
            </div>
        @elseif($processing)
            {{-- Processing State --}}
            <div class="flex items-center justify-center py-12">
                <x-loading class="loading-lg loading-spinner text-success" />
                <span class="ml-3 text-lg">Menyimpan perubahan...</span>
            </div>
        @elseif($user)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Form Section --}}
                <div class="lg:col-span-2">
                    <x-form wire:submit="update">
                        <div class="space-y-6">
                            {{-- Personal Information --}}
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                    <x-icon name="phosphor.identification-card" class="w-5 h-5 inline mr-2" />
                                    Informasi Pribadi
                                </h3>

                                {{-- Current Avatar Display --}}
                                <div class="flex items-center gap-4">
                                    <div class="avatar-group -space-x-6">
                                        {{-- Current Avatar --}}
                                        <div class="avatar">
                                            <div class="w-16 h-16">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="Current" class="rounded-full" />
                                                @else
                                                    <div class="w-full h-full bg-{{ $user->role_color }} text-{{ $user->role_color }}-content rounded-full flex items-center justify-center text-lg font-bold">
                                                        {{ $user->avatar_placeholder }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- New Avatar Preview --}}
                                        @if($avatar)
                                            <div class="avatar">
                                                <div class="w-16 h-16">
                                                    <img src="{{ $avatar->temporaryUrl() }}" alt="New" class="rounded-full" />
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <x-file
                                            label="Ubah Foto Profil"
                                            wire:model="avatar"
                                            accept="image/*"
                                            hint="Kosongkan jika tidak ingin mengubah. Maksimal 2MB."
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

                                {{-- Changes Indicator --}}
                                @if($hasChanges)
                                    <div class="bg-warning/10 border border-warning/30 rounded-lg p-3">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="phosphor.warning" class="w-4 h-4 text-warning" />
                                            <span class="text-sm text-warning font-medium">Ada perubahan yang belum disimpan</span>
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

                                <div class="bg-info/10 border border-info/30 rounded-lg p-3 mb-4">
                                    <p class="text-sm text-info">
                                        <x-icon name="phosphor.info" class="w-4 h-4 inline mr-1" />
                                        Kosongkan jika tidak ingin mengubah kata sandi
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Password --}}
                                    <x-password
                                        label="Kata Sandi Baru"
                                        wire:model.blur="password"
                                        placeholder="Kosongkan jika tidak diubah"
                                        hint="Minimal 8 karakter jika diisi"
                                    />

                                    {{-- Password Confirmation --}}
                                    <x-password
                                        label="Konfirmasi Kata Sandi"
                                        wire:model.blur="password_confirmation"
                                        placeholder="Ulangi kata sandi baru"
                                    />
                                </div>

                                {{-- Password Strength Indicator --}}
                                @if($password)
                                    <div class="bg-base-200 rounded-lg p-3">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-sm font-medium">Kekuatan Password Baru:</span>
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
                            </div>
                        </div>
                    </x-form>
                </div>

                {{-- Preview Section --}}
                <div class="lg:col-span-1">
                    <x-card title="Preview Perubahan" separator>
                        <x-slot:menu>
                            <x-icon name="phosphor.eye" class="w-5 h-5 text-warning" />
                        </x-slot:menu>

                        <div class="space-y-4">
                            {{-- Avatar Preview --}}
                            <div class="flex justify-center">
                                <div class="avatar">
                                    <div class="w-24 h-24 rounded-full ring ring-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} ring-offset-base-100 ring-offset-2">
                                        @if($avatar)
                                            <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" />
                                        @elseif($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="Current" class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full bg-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }} text-{{ $role ? \App\Models\User::getRoleColorByKey($role) : 'neutral' }}-content rounded-full flex items-center justify-center text-2xl font-bold">
                                                {{ $name ? strtoupper(substr($name, 0, 2)) : $user->avatar_placeholder }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- User Info --}}
                            <div class="text-center space-y-2">
                                <h4 class="font-semibold text-lg text-base-content">
                                    {{ $name ?: $user->name }}
                                </h4>
                                <p class="text-sm text-base-content/70">
                                    {{ $email ?: $user->email }}
                                </p>

                                {{-- Badges --}}
                                <div class="flex flex-wrap justify-center gap-2 mt-3">
                                    <div class="badge badge-{{ $role ? \App\Models\User::getRoleColorByKey($role) : $user->role_color }} badge-md">
                                        {{ $role ? \App\Models\User::getRoleLabelByKey($role) : $user->role_label }}
                                    </div>
                                    <div class="badge badge-{{ $is_active ? 'success' : 'warning' }} badge-md">
                                        {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Changes Summary --}}
                            <div class="space-y-3 pt-4 border-t border-base-300">
                                <h5 class="font-medium text-base-content">Ringkasan Perubahan:</h5>

                                @if($name !== $user->name)
                                    <div class="flex items-center gap-2 text-xs">
                                        <x-icon name="phosphor.pencil" class="w-3 h-3 text-warning" />
                                        <span>Nama: <del class="text-base-content/50">{{ $user->name }}</del> → <strong>{{ $name }}</strong></span>
                                    </div>
                                @endif

                                @if($email !== $user->email)
                                    <div class="flex items-center gap-2 text-xs">
                                        <x-icon name="phosphor.pencil" class="w-3 h-3 text-warning" />
                                        <span>Email: <del class="text-base-content/50">{{ $user->email }}</del> → <strong>{{ $email }}</strong></span>
                                    </div>
                                @endif

                                @if($role !== $user->role)
                                    <div class="flex items-center gap-2 text-xs">
                                        <x-icon name="phosphor.pencil" class="w-3 h-3 text-warning" />
                                        <span>Role: <del class="text-base-content/50">{{ $user->role_label }}</del> → <strong>{{ \App\Models\User::getRoleLabelByKey($role) }}</strong></span>
                                    </div>
                                @endif

                                @if($is_active !== $user->is_active)
                                    <div class="flex items-center gap-2 text-xs">
                                        <x-icon name="phosphor.pencil" class="w-3 h-3 text-warning" />
                                        <span>Status: <del class="text-base-content/50">{{ $user->status_label }}</del> → <strong>{{ $is_active ? 'Aktif' : 'Nonaktif' }}</strong></span>
                                    </div>
                                @endif

                                @if($password)
                                    <div class="flex items-center gap-2 text-xs">
                                        <x-icon name="phosphor.lock" class="w-3 h-3 text-info" />
                                        <span>Password akan diubah</span>
                                    </div>
                                @endif

                                @if($avatar)
                                    <div class="flex items-center gap-2 text-xs">
                                        <x-icon name="phosphor.image" class="w-3 h-3 text-success" />
                                        <span>Avatar akan diubah</span>
                                    </div>
                                @endif

                                @if(!$hasChanges)
                                    <div class="flex items-center gap-2 text-xs text-base-content/50">
                                        <x-icon name="phosphor.check" class="w-3 h-3" />
                                        <span>Tidak ada perubahan</span>
                                    </div>
                                @endif
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
                label="Batal"
                @click="$wire.closeModal()"
                :disabled="$processing || $loading"
                class="btn-ghost"
                icon="phosphor.x"
            />

            @if($user && !$loading)
                <x-button
                    label="Simpan Perubahan"
                    wire:click="update"
                    class="btn-warning"
                    icon="phosphor.check"
                    :disabled="!$hasChanges || $processing"
                    spinner="update"
                />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
