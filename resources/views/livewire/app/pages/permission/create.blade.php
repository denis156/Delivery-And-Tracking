{{-- Create Permission Page - Mary UI + DaisyUI Standards --}}
<div>
    {{-- HEADER --}}
    <x-header title="Tambah Permission Baru" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <div class="breadcrumbs text-sm hidden lg:block">
                <ul>
                    <li><a href="{{ route('app.dashboard') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('app.permission.index') }}" wire:navigate>Permission</a></li>
                    <li>Tambah Permission</li>
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
                    {{-- Permission Preview --}}
                    <div class="text-center space-y-4">
                        @php
                            $previewCategory = $name ? $this->getPermissionCategory($name) : 'general';
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
                                {{ $name ?: 'Nama Permission' }}
                            </h3>
                            <p class="text-base-content/70">
                                Guard: {{ $guard_name }}
                            </p>

                            {{-- Category & Guard Badges --}}
                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <div class="badge badge-{{ $previewColor }} badge-lg">
                                    <x-icon name="{{ $previewIcon }}" class="w-4 h-4 mr-1" />
                                    {{ ucfirst($previewCategory) }}
                                </div>
                                <div class="badge badge-info badge-lg">
                                    <x-icon name="phosphor.shield" class="w-4 h-4 mr-1" />
                                    {{ ucfirst($guard_name) }}
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
                            :label="$createMultiple ? 'Mode Single' : 'Mode Multiple'"
                            wire:click="$toggle('createMultiple')"
                            class="btn-accent btn-outline btn-block"
                            :icon="$createMultiple ? 'phosphor.list' : 'phosphor.stack'"
                        />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- FORM SECTION --}}
        <div class="lg:col-span-2">
            <x-card title="Informasi Permission Baru" separator>
                <x-slot:menu>
                    <x-icon name="phosphor.key" class="w-5 h-5 text-primary" />
                </x-slot:menu>

                <x-form wire:submit="save">
                    <div class="space-y-6">
                        {{-- Mode Toggle --}}
                        <div class="alert alert-info">
                            <x-icon name="phosphor.{{ $createMultiple ? 'stack' : 'key' }}" class="w-5 h-5" />
                            <div>
                                <h4 class="font-semibold">Mode: {{ $createMultiple ? 'Multiple Permissions' : 'Single Permission' }}</h4>
                                <p class="text-sm">
                                    {{ $createMultiple
                                        ? 'Buat beberapa permission sekaligus dengan memasukkan satu nama per baris.'
                                        : 'Buat satu permission dengan nama dan konfigurasi yang spesifik.'
                                    }}
                                </p>
                            </div>
                        </div>

                        {{-- Permission Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.key" class="w-5 h-5 inline mr-2" />
                                Informasi Permission
                            </h3>

                            @if($createMultiple)
                                {{-- Multiple Names --}}
                                <x-textarea
                                    label="Daftar Permission"
                                    wire:model.live="multipleNames"
                                    placeholder="view users&#10;create users&#10;edit users&#10;delete users"
                                    hint="Masukkan satu nama permission per baris"
                                    rows="8"
                                    required
                                />
                            @else
                                {{-- Single Name --}}
                                <x-input
                                    label="Nama Permission"
                                    wire:model.live="name"
                                    placeholder="Contoh: view users"
                                    icon="phosphor.key"
                                    clearable
                                    required
                                />
                            @endif

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

                        {{-- Permission Suggestions --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-base-content border-b border-base-300 pb-2">
                                <x-icon name="phosphor.lightbulb" class="w-5 h-5 inline mr-2" />
                                Saran Permission
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($suggestions as $category => $categoryPermissions)
                                    <x-card class="bg-base-200" no-separator>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-semibold text-sm">{{ $category }}</h4>
                                                <x-button
                                                    label="Load All"
                                                    wire:click="loadCategorySuggestions('{{ $category }}')"
                                                    class="btn-primary btn-xs"
                                                    icon="phosphor.download"
                                                />
                                            </div>
                                            <div class="space-y-1">
                                                @foreach(array_slice($categoryPermissions, 0, 3) as $suggestion)
                                                    <x-button
                                                        label="{{ $suggestion }}"
                                                        wire:click="useSuggestion('{{ $suggestion }}')"
                                                        class="btn-ghost btn-xs w-full justify-start"
                                                        icon="phosphor.plus"
                                                    />
                                                @endforeach
                                                @if(count($categoryPermissions) > 3)
                                                    <p class="text-xs text-base-content/50">
                                                        +{{ count($categoryPermissions) - 3 }} lainnya
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </x-card>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <x-slot:actions separator>
                        <x-button
                            label="Simpan Permission"
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
</div>
