{{-- Delete Permission Modal - Mary UI + DaisyUI Standards --}}
<div>
    <x-modal
        wire:model="showModal"
        :title="$modalTitle"
        subtitle="Tindakan ini tidak dapat dibatalkan"
        persistent
        separator
        class="backdrop-blur"
        :box-class="'border-2 border-' . $modalColor"
    >
        @if($permission)
            {{-- Warning Alert --}}
            <div class="alert alert-{{ $modalColor }} mb-4">
                <x-icon name="phosphor.{{ $usageInfo['can_be_deleted'] ? 'warning' : 'x-circle' }}" class="w-6 h-6" />
                <div>
                    <h3 class="font-bold">
                        {{ $usageInfo['can_be_deleted'] ? 'Peringatan!' : 'Tidak Dapat Dihapus!' }}
                    </h3>
                    <div class="text-sm">{{ $warningMessage }}</div>
                </div>
            </div>

            {{-- Permission Info Card --}}
            <x-card class="bg-base-200 mb-4" no-separator>
                <div class="flex items-center gap-4">
                    {{-- Icon --}}
                    <div class="w-12 h-12 rounded-full bg-{{ $modalColor }}/20 flex items-center justify-center">
                        <x-icon name="phosphor.key" class="w-6 h-6 text-{{ $modalColor }}" />
                    </div>

                    {{-- Permission Details --}}
                    <div class="flex-1">
                        <h4 class="font-semibold text-base-content">{{ $permission->name }}</h4>
                        <p class="text-sm text-base-content/70">{{ $permission->guard_name }} guard</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="badge badge-info badge-sm">
                                {{ $permission->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Usage Information --}}
            @if($usageInfo['total_usage'] > 0)
                <div class="bg-base-200 rounded-lg p-4 mb-4">
                    <h5 class="font-semibold mb-3 text-base-content">Penggunaan Permission:</h5>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Roles Count --}}
                        <div class="text-center p-3 bg-base-300 rounded-lg">
                            <div class="text-xl font-bold text-secondary">{{ $usageInfo['roles_count'] }}</div>
                            <div class="text-xs text-base-content/70">Role(s)</div>
                        </div>
                        {{-- Users Count --}}
                        <div class="text-center p-3 bg-base-300 rounded-lg">
                            <div class="text-xl font-bold text-accent">{{ $usageInfo['users_count'] }}</div>
                            <div class="text-xs text-base-content/70">User(s)</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- What will happen --}}
            @if($usageInfo['can_be_deleted'])
                <div class="bg-error/10 border border-error/20 rounded-lg p-4 mb-4">
                    <h5 class="font-semibold mb-2 text-error">Yang akan terjadi:</h5>
                    <ul class="text-sm text-base-content/80 space-y-1">
                        <li class="flex items-center gap-2">
                            <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                            Permission akan dihapus dari sistem
                        </li>
                        <li class="flex items-center gap-2">
                            <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                            Semua assignment ke roles dan users akan dicabut
                        </li>
                        <li class="flex items-center gap-2">
                            <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                            Data permission tidak dapat dipulihkan
                        </li>
                    </ul>
                </div>

                {{-- Confirmation Input --}}
                <div class="mb-4">
                    <x-input
                        label="Konfirmasi Penghapusan"
                        hint="Ketik nama permission untuk mengkonfirmasi penghapusan"
                        placeholder="Ketik: {{ $permission->name }}"
                        wire:model.live="confirmText"
                        icon="phosphor.keyboard"
                        :class="$this->canDelete ? 'input-success' : 'input-error'"
                    />

                    @if($this->canDelete)
                        <div class="flex items-center gap-2 mt-2 text-success">
                            <x-icon name="phosphor.check-circle" class="w-4 h-4" />
                            <span class="text-sm">Konfirmasi berhasil</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 mt-2 text-error">
                            <x-icon name="phosphor.x-circle" class="w-4 h-4" />
                            <span class="text-sm">Ketik nama permission yang tepat: "{{ $permission->name }}"</span>
                        </div>
                    @endif
                </div>
            @else
                {{-- Cannot Delete Info --}}
                <div class="bg-warning/10 border border-warning/20 rounded-lg p-4 mb-4">
                    <h5 class="font-semibold mb-2 text-warning">Untuk menghapus permission ini:</h5>
                    <ul class="text-sm text-base-content/80 space-y-1">
                        @if($usageInfo['roles_count'] > 0)
                            <li class="flex items-center gap-2">
                                <x-icon name="phosphor.user-circle" class="w-3 h-3 text-warning" />
                                Cabut permission dari {{ $usageInfo['roles_count'] }} role(s)
                            </li>
                        @endif
                        @if($usageInfo['users_count'] > 0)
                            <li class="flex items-center gap-2">
                                <x-icon name="phosphor.users" class="w-3 h-3 text-warning" />
                                Cabut permission dari {{ $usageInfo['users_count'] }} user(s)
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        @else
            {{-- Loading State --}}
            <div class="flex items-center justify-center py-8">
                <x-loading class="loading-lg" />
                <span class="ml-3">Memuat data permission...</span>
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

            @if($permission)
                @if($usageInfo['can_be_deleted'])
                    <x-button
                        label="Ya, Hapus Permission"
                        wire:click="confirmDelete"
                        class="btn-error"
                        icon="phosphor.trash"
                        :disabled="!$this->canDelete || $processing"
                        spinner
                    />
                @else
                    <x-button
                        label="Tutup"
                        @click="$wire.closeModal()"
                        class="btn-neutral"
                        icon="phosphor.check"
                    />
                @endif
            @endif
        </x-slot:actions>
    </x-modal>
</div>
