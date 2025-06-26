<div>
    <x-modal
        wire:model="showModal"
        :title="$modalTitle"
        :subtitle="$modalSubtitle"
        persistent
        separator
        class="backdrop-blur"
        :box-class="'border-2 ' . ($currentUser ? ($currentUser->is_active ? 'border-warning' : 'border-success') : 'border-base-300')"
    >
        @if($currentUser)
            <!-- User Info Card -->
            <x-card class="bg-base-200 mb-4" no-separator>
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    <div class="avatar">
                        <div class="w-12 h-12 rounded-full ring-2 ring-offset-2 ring-offset-base-100 {{ $currentUser->is_active ? 'ring-warning' : 'ring-success' }}">
                            @if($isPreviewMode && isset($previewData['avatar_preview']))
                                <img src="{{ $previewData['avatar_preview'] }}" alt="Preview" class="w-full h-full object-cover" />
                            @elseif(!$isPreviewMode && $user && $user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full bg-{{ $userRoleColor }} text-{{ $userRoleColor }}-content rounded-full flex items-center justify-center text-lg font-bold">
                                    {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Details -->
                    <div class="flex-1">
                        <h4 class="font-semibold text-base-content">{{ $currentUser->name }}</h4>
                        <p class="text-sm text-base-content/70 break-all">{{ $currentUser->email }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="badge badge-{{ $currentUser->is_active ? 'success' : 'warning' }} badge-sm">
                                {{ $currentUser->is_active ? 'Aktif' : 'Nonaktif' }}
                            </div>
                            <div class="badge badge-{{ $userRoleColor }} badge-sm">
                                {{ $userRoleLabel }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Confirmation Message -->
            <div class="alert alert-{{ $currentUser->is_active ? 'warning' : 'success' }} mb-4">
                <x-icon name="{{ $this->icon }}" class="w-6 h-6" />
                <div>
                    <h3 class="font-bold">
                        @if($isPreviewMode)
                            Preview: Apakah Anda ingin {{ $this->actionText }} pengguna ini?
                        @else
                            Apakah Anda yakin ingin {{ $this->actionText }} pengguna ini?
                        @endif
                    </h3>
                    <div class="text-sm mt-1">
                        @if($currentUser->is_active)
                            @if($isPreviewMode)
                                Pengguna akan dibuat dalam status nonaktif dan tidak dapat login ke sistem.
                            @else
                                Pengguna akan dinonaktifkan dan tidak dapat login ke sistem.
                            @endif
                        @else
                            @if($isPreviewMode)
                                Pengguna akan dibuat dalam status aktif dan dapat login ke sistem.
                            @else
                                Pengguna akan diaktifkan dan dapat login ke sistem kembali.
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-sm text-base-content/70 mb-4">
                @if($isPreviewMode)
                    <div class="flex items-center gap-2 mb-1">
                        <x-icon name="phosphor.user-plus" class="w-4 h-4" />
                        <span>Akan dibuat sebagai: {{ $userRoleLabel }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="phosphor.clock" class="w-4 h-4" />
                        <span>Status awal: {{ $currentUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 mb-1">
                        <x-icon name="phosphor.calendar" class="w-4 h-4" />
                        <span>Bergabung: {{ $user->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="phosphor.clock" class="w-4 h-4" />
                        <span>Terakhir diperbarui: {{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                @endif
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

            @if($currentUser)
                <x-button
                    :label="$isPreviewMode ? 'Terapkan' : ucfirst($this->actionText)"
                    wire:click="confirmChangeStatus"
                    :class="$this->buttonClass"
                    :icon="$this->icon"
                    :disabled="$processing"
                    spinner
                />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
