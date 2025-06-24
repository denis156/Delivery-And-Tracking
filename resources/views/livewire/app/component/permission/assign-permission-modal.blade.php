{{-- Assign Permission Modal - Mary UI + DaisyUI Standards --}}
<div>
    <x-modal
        wire:model="showModal"
        title="Assign Permission ke Roles"
        :subtitle="$modalSubtitle"
        persistent
        separator
        class="backdrop-blur"
        box-class="border-2 border-primary max-w-4xl"
    >
        @if($permission)
            {{-- Permission Info Header --}}
            <div class="alert alert-info mb-4">
                <x-icon name="phosphor.key" class="w-6 h-6" />
                <div>
                    <h3 class="font-bold">Permission: {{ $permission->name }}</h3>
                    <div class="text-sm">
                        Guard: {{ $permission->guard_name }} | Total Roles Tersedia: {{ $totalRoles }}
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="mb-4">
                <x-input
                    placeholder="Cari roles berdasarkan nama..."
                    wire:model.live.debounce="search"
                    icon="phosphor.magnifying-glass"
                    clearable
                />
            </div>

            {{-- Select All Checkbox --}}
            @if($totalRoles > 0)
                <div class="flex items-center gap-2 mb-4 p-3 bg-base-200 rounded-lg">
                    <x-checkbox
                        wire:model.live="selectAll"
                        class="checkbox-primary"
                    />
                    <label class="text-sm font-medium">
                        Pilih semua roles yang ditampilkan ({{ $totalRoles }} role{{ $totalRoles > 1 ? 's' : '' }})
                    </label>
                </div>
            @endif

            {{-- Roles List --}}
            @if($availableRoles->count() > 0)
                <div class="max-h-96 overflow-y-auto space-y-2 mb-4">
                    @foreach($availableRoles as $role)
                        @php
                            $isSelected = in_array($role->id, $selectedRoles);
                            $isCurrentlyAssigned = $currentlyAssignedRoles->contains('id', $role->id);
                        @endphp

                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg hover:bg-base-300 transition-colors">
                            <div class="flex items-center gap-3">
                                {{-- Checkbox --}}
                                <x-checkbox
                                    wire:click="toggleRole({{ $role->id }})"
                                    :checked="$isSelected"
                                    class="checkbox-primary"
                                />

                                {{-- Role Info --}}
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/20 flex items-center justify-center">
                                        <x-icon name="phosphor.user-circle" class="w-4 h-4 text-secondary" />
                                    </div>
                                    <div>
                                        <h5 class="font-semibold text-base-content">{{ $role->name }}</h5>
                                        <p class="text-xs text-base-content/70">{{ $role->guard_name }} guard</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Status Badges --}}
                            <div class="flex items-center gap-2">
                                @if($isCurrentlyAssigned)
                                    <div class="badge badge-success badge-sm">
                                        <x-icon name="phosphor.check" class="w-3 h-3 mr-1" />
                                        Assigned
                                    </div>
                                @endif

                                @if($isSelected && !$isCurrentlyAssigned)
                                    <div class="badge badge-info badge-sm">
                                        <x-icon name="phosphor.plus" class="w-3 h-3 mr-1" />
                                        Will Assign
                                    </div>
                                @elseif(!$isSelected && $isCurrentlyAssigned)
                                    <div class="badge badge-warning badge-sm">
                                        <x-icon name="phosphor.minus" class="w-3 h-3 mr-1" />
                                        Will Revoke
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Selection Summary --}}
                <div class="bg-primary/10 border border-primary/30 rounded-lg p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon name="phosphor.selection-all" class="w-4 h-4 text-primary" />
                            <span class="text-sm font-medium text-primary">
                                {{ $selectedCount }} dari {{ $totalRoles }} role dipilih
                            </span>
                        </div>
                        @if($hasChanges)
                            <div class="badge badge-warning badge-sm">
                                <x-icon name="phosphor.warning" class="w-3 h-3 mr-1" />
                                Ada Perubahan
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-8">
                    <x-icon name="phosphor.user-circle-minus" class="w-16 h-16 mx-auto text-base-content/30 mb-4" />
                    <h3 class="text-lg font-semibold text-base-content/60 mb-2">
                        @if($search)
                            Tidak ada roles ditemukan
                        @else
                            Belum ada roles
                        @endif
                    </h3>
                    <p class="text-base-content/40">
                        @if($search)
                            Tidak ada roles yang cocok dengan pencarian "{{ $search }}"
                        @else
                            Belum ada roles yang tersedia dalam sistem
                        @endif
                    </p>
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

            @if($permission && $availableRoles->count() > 0)
                <x-button
                    label="Simpan Assignment"
                    wire:click="save"
                    class="btn-primary"
                    icon="phosphor.check"
                    :disabled="!$hasChanges || $processing"
                    spinner
                />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
