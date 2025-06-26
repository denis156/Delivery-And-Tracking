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
                        Tindakan ini akan menghapus pengguna secara permanen dan tidak dapat dibatalkan.
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
                            <div class="badge badge-{{ $userRoleColor }} badge-sm">
                                {{ $userRoleLabel }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Additional User Info -->
            <div class="bg-base-200 rounded-lg p-4 mb-4">
                <h5 class="font-semibold mb-2 text-base-content">Informasi Pengguna:</h5>
                <div class="grid grid-cols-1 gap-2 text-sm text-base-content/70">
                    <div class="flex items-center gap-2">
                        <x-icon name="phosphor.calendar" class="w-4 h-4" />
                        <span>Bergabung: {{ $user->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="phosphor.clock" class="w-4 h-4" />
                        <span>Update: {{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="phosphor.identification-badge" class="w-4 h-4" />
                        <span>Peran: {{ $userRoleLabel }}</span>
                    </div>
                </div>
            </div>

            <!-- Confirmation Input -->
            <div class="mb-4">
                <x-input
                    label="Konfirmasi Penghapusan"
                    hint="Ketik nama pengguna untuk mengkonfirmasi penghapusan"
                    placeholder="Ketik: {{ $user->name }}"
                    wire:model.live="confirmText"
                    icon="phosphor.keyboard"
                    :class="$canDelete ? 'input-success' : 'input-error'"
                />

                @if($canDelete)
                    <div class="flex items-center gap-2 mt-2 text-success">
                        <x-icon name="phosphor.check-circle" class="w-4 h-4" />
                        <span class="text-sm">Konfirmasi berhasil</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 mt-2 text-error">
                        <x-icon name="phosphor.x-circle" class="w-4 h-4" />
                        <span class="text-sm">Ketik nama pengguna yang tepat: "{{ $user->name }}"</span>
                    </div>
                @endif
            </div>

            <!-- What will happen -->
            <div class="bg-error/10 border border-error/20 rounded-lg p-4 mb-4">
                <h5 class="font-semibold mb-2 text-error">Yang akan terjadi:</h5>
                <ul class="text-sm text-base-content/80 space-y-1">
                    <li class="flex items-center gap-2">
                        <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                        Pengguna akan dihapus dari sistem
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                        Akses login akan dicabut secara permanen
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="phosphor.x" class="w-3 h-3 text-error" />
                        Data pengguna tidak dapat dipulihkan
                    </li>
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
                    label="Ya, Hapus Pengguna"
                    wire:click="confirmDelete"
                    class="btn-error"
                    icon="phosphor.trash"
                    :disabled="!$canDelete || $processing"
                    spinner
                />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
