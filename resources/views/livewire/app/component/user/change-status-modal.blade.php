<div>
    <x-modal
        wire:model="showModal"
        :title="$this->modalTitle"
        :subtitle="$this->modalSubtitle"
        persistent
        separator
        class="backdrop-blur"
        :box-class="'border-2 ' . ($this->currentUser ? ($this->currentUser->is_active ? 'border-warning' : 'border-success') : 'border-base-300')"
    >
        @if($this->currentUser)
            <!-- User Info Card -->
            <x-card class="bg-base-200 mb-4" no-separator>
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    <div class="avatar">
                        <div class="w-12 h-12 rounded-full ring-2 ring-offset-2 ring-offset-base-100 {{ $this->currentUser->is_active ? 'ring-warning' : 'ring-success' }}">
                            @if($isPreviewMode && isset($previewData['avatar_preview']))
                                <img src="{{ $previewData['avatar_preview'] }}" alt="Preview" class="w-full h-full object-cover" />
                            @elseif(!$isPreviewMode && $user && $user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full bg-{{ $this->userRoleColor }} text-{{ $this->userRoleColor }}-content rounded-full flex items-center justify-center text-lg font-bold">
                                    {{ strtoupper(substr($this->currentUser->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Details -->
                    <div class="flex-1">
                        <h4 class="font-semibold text-base-content">{{ $this->currentUser->name }}</h4>
                        <p class="text-sm text-base-content/70 break-all">{{ $this->currentUser->email }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="badge badge-{{ $this->currentUser->is_active ? 'success' : 'warning' }} badge-sm">
                                {{ $this->currentUser->is_active ? 'Aktif' : 'Nonaktif' }}
                            </div>
                            <div class="badge badge-{{ $this->userRoleColor }} badge-sm">
                                {{ $this->userRoleLabel }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Confirmation Message -->
            <div class="alert alert-{{ $this->currentUser->is_active ? 'warning' : 'success' }} mb-4">
                <x-icon name="{{ $this->icon }}" class="w-6 h-6" />
                <div>
                    <h3 class="font-bold">
                        @if($isPreviewMode)
                            Preview: Apakah Anda ingin {{ $this->actionText }} {{ strtolower($this->userRoleLabel) }} ini?
                        @else
                            Apakah Anda yakin ingin {{ $this->actionText }} {{ strtolower($this->userRoleLabel) }} ini?
                        @endif
                    </h3>
                    <div class="text-sm mt-1">
                        {{ $this->roleDescription }}
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-sm text-base-content/70 mb-4">
                @if($isPreviewMode)
                    <div class="flex items-center gap-2 mb-1">
                        <x-icon name="phosphor.user-plus" class="w-4 h-4" />
                        <span>Akan dibuat sebagai: {{ $this->userRoleLabel }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="phosphor.clock" class="w-4 h-4" />
                        <span>Status awal: {{ $this->currentUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
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

                    {{-- Additional info for specific roles --}}
                    @if($user->isDriver() && $user->driver)
                        <div class="flex items-center gap-2 mt-1">
                            <x-icon name="phosphor.identification-card" class="w-4 h-4" />
                            <span>SIM: {{ $user->driver->license_type }} - {{ $user->driver->license_number }}</span>
                        </div>
                    @endif
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

            @if($this->currentUser)
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
