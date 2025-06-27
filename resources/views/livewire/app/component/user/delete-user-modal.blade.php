<div>
    <x-modal
        wire:model="showModal"
        title="Hapus Pengguna - Konfirmasi Diperlukan"
        subtitle="Tindakan ini tidak dapat dibatalkan"
        persistent
        separator
        class="backdrop-blur"
        box-class="border-2 border-error"
    >
        @if($user)
            <!-- Danger Alert -->
            <div class="alert alert-error mb-4">
                <x-icon name="phosphor.warning" class="w-6 h-6" />
                <div>
                    <h3 class="font-bold">Peringatan!</h3>
                    <div class="text-sm">
                        Tindakan ini akan menghapus {{ strtolower($this->userRoleLabel) }} secara permanen dan tidak dapat dibatalkan.
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <x-card class="bg-base-200 mb-4" no-separator>
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    <x-avatar
                        :placeholder="$user->avatar_placeholder"
                        :image="$user->avatar"
                        class="w-12 h-12 ring-2 ring-error ring-offset-2 ring-offset-base-100"
                    />

                    <!-- User Details -->
                    <div class="flex-1">
                        <h4 class="font-semibold text-base-content">{{ $user->name }}</h4>
                        <p class="text-sm text-base-content/70">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="badge badge-{{ $user->status_color }} badge-sm">
                                {{ $user->status_label }}
                            </div>
                            <div class="badge badge-{{ $this->userRoleColor }} badge-sm">
                                {{ $this->userRoleLabel }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Additional User Info -->
            <div class="bg-base-200 rounded-lg p-4 mb-4">
                <h5 class="font-semibold mb-2 text-base-content">Informasi {{ $this->userRoleLabel }}:</h5>
                <div class="grid grid-cols-1 gap-2 text-sm text-base-content/70">
                    @foreach($this->additionalInfo as $key => $value)
                        <div class="flex items-center gap-2">
                            @switch($key)
                                @case('bergabung')
                                    <x-icon name="phosphor.calendar" class="w-4 h-4" />
                                    <span>Bergabung: {{ $value }}</span>
                                    @break
                                @case('update')
                                    <x-icon name="phosphor.clock" class="w-4 h-4" />
                                    <span>Update: {{ $value }}</span>
                                    @break
                                @case('role')
                                    <x-icon name="phosphor.identification-badge" class="w-4 h-4" />
                                    <span>Peran: {{ $value }}</span>
                                    @break
                                @case('sim')
                                    <x-icon name="phosphor.identification-card" class="w-4 h-4" />
                                    <span>Nomor SIM: {{ $value }}</span>
                                    @break
                                @case('jenis_sim')
                                    <x-icon name="phosphor.identification-badge" class="w-4 h-4" />
                                    <span>Jenis SIM: {{ $value }}</span>
                                    @break
                                @case('telepon')
                                    <x-icon name="phosphor.phone" class="w-4 h-4" />
                                    <span>Telepon: {{ $value }}</span>
                                    @break
                                @default
                                    <x-icon name="phosphor.info" class="w-4 h-4" />
                                    <span>{{ ucfirst($key) }}: {{ $value }}</span>
                            @endswitch
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Confirmation Input -->
            <div class="mb-4">
                <x-input
                    label="Konfirmasi Penghapusan"
                    hint="Ketik nama {{ strtolower($this->userRoleLabel) }} untuk mengkonfirmasi penghapusan"
                    placeholder="Ketik: {{ $user->name }}"
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
                        <span class="text-sm">Ketik nama {{ strtolower($this->userRoleLabel) }} yang tepat: "{{ $user->name }}"</span>
                    </div>
                @endif
            </div>

            <!-- What will happen -->
            <div class="bg-error/10 border border-error/20 rounded-lg p-4 mb-4">
                <h5 class="font-semibold mb-2 text-error">Yang akan terjadi:</h5>
                <ul class="text-sm text-base-content/80 space-y-1">
                    @foreach($this->deleteConsequences as $consequence)
                        <li class="flex items-center gap-2">
                            <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                            {{ $consequence }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <!-- Loading State -->
            <div class="flex items-center justify-center py-8">
                <x-loading class="loading-lg" />
                <span class="ml-3">Memuat data pengguna...</span>
            </div>
        @endif

        <!-- Modal Actions -->
        <x-slot:actions>
            <x-button
                label="Batal"
                @click="$wire.closeModal()"
                :disabled="$processing"
                class="btn-ghost"
                icon="phosphor.x"
            />

            @if($user)
                <x-button
                    label="Ya, Hapus {{ $this->userRoleLabel }}"
                    wire:click="confirmDelete"
                    class="btn-error"
                    icon="phosphor.trash"
                    :disabled="!$this->canDelete || $processing"
                    spinner
                />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
