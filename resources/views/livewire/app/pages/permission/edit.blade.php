{{-- Edit Permission Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Edit Permission - {{ $permission->name }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.dashboard') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('app.permission.index') }}" wire:navigate>Permission</a></li>
                    <li><a href="{{ route('app.permission.view', $permission) }}" wire:navigate>{{ $permission->name }}</a></li>
                    <li>Edit</li>
                </ul>
            </div>
        </x-slot:middle>
    </x-header>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ACTIONS & PREVIEW SECTION --}}
        <div class="lg:col-span-1">
            <x-card title="Actions & Preview" separator sticky>
                <x-slot:menu>
                    <x-icon name="phosphor.gear" class="w-5 h-5 text-warning" />
                </x-slot:menu>

                <div class="space-y-6">
                    {{-- Permission Preview --}}
                    <div class="text-center space-y-4">
                        @php
                            $previewCategory = $name ? $this->getPermissionCategory($name) : $permissionCategory;
                            $previewColor = $this->getPermissionColor($previewCategory);
                            $previewIcon = $this->getPermissionIcon($previewCategory);
                        @endphp

                        {{-- Icon Preview --}}
                        <div class="flex justify-center">
                            <div class="w-32 h-32 rounded-full bg-{{ $previewColor }}/20 flex items-center justify-center">
                                <x-icon name="{{ $previewIcon }}" class="w-16 h-16 text-{{ $previewColor }}" />
                            </div>
                        </div>

                        {{-- Name & Details Preview --}}
                        <div>
                            <h3 class="text-xl font-bold text-base-content">
                                {{ $name ?: $permission->name }}
                            </h3>
                            <p class="text-base-content/70">
                                Guard: {{ $guard_name ?: $permission->guard_name }}
                            </p>

                            {{-- Category & Guard Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-{{ $previewColor }} badge-lg">
                                    <x-icon name="{{ $previewIcon }}" class="w-4 h-4 mr-1" />
                                    {{ ucfirst($previewCategory) }}
                                </div>
                                <div class="badge badge-info badge-lg">
                                    <x-icon name="phosphor.shield" class="w-4 h-4 mr-1" />
                                    {{ ucfirst($guard_name ?: $permission->guard_name) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Save Status Alert --}}
                    @if($hasChanges)
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

                    {{-- Usage Warning --}}
                    @if($usageStats['total_usage'] > 0)
                        <div class="bg-info/10 border border-info/30 rounded-lg p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="phosphor.info" class="w-4 h-4 text-info" />
                                <span class="text-sm text-info font-medium">Permission Sedang Digunakan</span>
                            </div>
                            <div class="text-xs text-info/80">
                                <p>{{ $usageStats['roles_count'] }} role(s) dan {{ $usageStats['users_count'] }} user(s)</p>
                                <p>Perubahan akan mempengaruhi semua assignment yang ada.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Guard Warning --}}
                    @if($guardWarning)
                        <div class="bg-error/10 border border-error/30 rounded-lg p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="phosphor.warning-circle" class="w-4 h-4 text-error" />
                                <span class="text-sm text-error font-medium">Peringatan Guard</span>
                            </div>
                            <p class="text-xs text-error/80">{{ $guardWarning }}</p>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="space-y-2 pt-4 border-t border-base-300">
                        <x-button
                            label="Kembali ke Detail"
                            wire:click="viewPermission"
                            class="btn-info btn-block"
                            icon="phosphor.arrow-left"
                        />

                        <x-button
                            label="Reset Form"
                            wire:click="resetForm"
                            class="btn-secondary btn-block"
                            icon="phosphor.arrow-counter-clockwise"
                            :disabled="!$hasChanges"
                        />

                        <x-button
                            label="Hapus Permission"
                            wire:click="deletePermission"
                            class="btn-error btn-outline btn-block"
                            icon="phosphor.trash"
                            :disabled="!$canBeDeleted"
                        />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Edit Informasi Permission" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.pencil" class="w-5 h-5 text-warning" />
                </x-slot:menu>

                <x-form wire:submit="save">
                    <div class="space-y-6">
                        {{-- Permission Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.key" class="w-5 h-5 inline mr-2" />
                                Informasi Permission
                            </h3>

                            {{-- Name --}}
                            <x-input
                                label="Nama Permission"
                                wire:model.live="name"
                                placeholder="Contoh: view users"
                                icon="phosphor.key"
                                clearable
                                required
                            />

                            {{-- Guard Selection --}}
                            <x-select
                                label="Guard"
                                wire:model.live="guard_name"
                                :options="collect($this->guards)->map(fn($label, $value) => ['id' => $value, 'name' => $label])->values()->toArray()"
                                option-value="id"
                                option-label="name"
                                icon="phosphor.shield"
                                required
                            />

                            {{-- Changes Alert --}}
                            @if($hasChanges)
                                <div class="alert alert-warning">
                                    <x-icon name="phosphor.warning" class="w-5 h-5" />
                                    <div>
                                        <h4 class="font-semibold">Ada Perubahan yang Belum Disimpan</h4>
                                        <p class="text-sm">Pastikan untuk menyimpan perubahan sebelum meninggalkan halaman.</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Guard Description --}}
                            <div class="bg-info/10 border border-info/30 rounded-lg p-3">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="badge badge-info badge-lg">
                                        {{ ucfirst($guard_name) }}
                                    </div>
                                    <span class="text-sm font-medium text-info">Deskripsi Guard</span>
                                </div>
                                <p class="text-sm text-base-content/70">
                                    @if($guard_name === 'web')
                                        Guard untuk aplikasi web dengan session-based authentication.
                                    @elseif($guard_name === 'api')
                                        Guard untuk API dengan token-based authentication.
                                    @else
                                        Pilih guard untuk menentukan konteks permission.
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Current Usage Information --}}
                        @if($usageStats['total_usage'] > 0)
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                    <x-icon name="phosphor.users" class="w-5 h-5 inline mr-2" />
                                    Penggunaan Saat Ini
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Roles --}}
                                    <div class="bg-base-200 rounded-lg p-4">
                                        <div class="flex items-center gap-3 mb-2">
                                            <x-icon name="phosphor.user-circle" class="w-5 h-5 text-secondary" />
                                            <span class="font-semibold">Roles</span>
                                        </div>
                                        <div class="text-2xl font-bold text-secondary">{{ $usageStats['roles_count'] }}</div>
                                        <p class="text-sm text-base-content/70">role(s) menggunakan permission ini</p>
                                    </div>

                                    {{-- Users --}}
                                    <div class="bg-base-200 rounded-lg p-4">
                                        <div class="flex items-center gap-3 mb-2">
                                            <x-icon name="phosphor.users" class="w-5 h-5 text-accent" />
                                            <span class="font-semibold">Users</span>
                                        </div>
                                        <div class="text-2xl font-bold text-accent">{{ $usageStats['users_count'] }}</div>
                                        <p class="text-sm text-base-content/70">user(s) memiliki permission ini</p>
                                    </div>
                                    </div>

                                <div class="alert alert-info">
                                    <x-icon name="phosphor.info" class="w-5 h-5" />
                                    <div>
                                        <h4 class="font-semibold">Dampak Perubahan</h4>
                                        <p class="text-sm">
                                            Mengubah nama permission akan mempengaruhi {{ $usageStats['total_usage'] }} assignment yang ada.
                                            Pastikan perubahan yang Anda lakukan sudah sesuai.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button
                            label="Simpan Perubahan"
                            type="submit"
                            class="btn-warning"
                            icon="phosphor.check"
                            :disabled="!$hasChanges || !$isFormValid"
                            spinner="save"
                        />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- MODAL COMPONENTS --}}
    {{-- Delete Permission Modal --}}
    <livewire:app.component.permission.delete-permission-modal />
</div>
