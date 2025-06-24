<?php

namespace App\Livewire\App\Component\Permission;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Mary\Traits\Toast;
use Livewire\Component;

class AssignPermissionModal extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES
    // * ========================================

    public bool $showModal = false;
    public ?Permission $permission = null;
    public array $selectedRoles = [];
    public string $search = '';
    public bool $processing = false;
    public bool $selectAll = false;

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'openAssignPermissionModal' => 'openModal'
    ];

    // * ========================================
    // * METHODS
    // * ========================================

    /**
     * Open modal dengan permission data
     */
    public function openModal(int $permissionId): void
    {
        $this->permission = Permission::with('roles')->find($permissionId);

        if (!$this->permission) {
            $this->error('Permission tidak ditemukan.', position: 'toast-top toast-end');
            return;
        }

        $this->selectedRoles = $this->permission->roles()->pluck('id')->toArray();
        $this->showModal = true;
        $this->search = '';
        $this->updateSelectAll();
    }

    /**
     * Close modal dan reset data
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->permission = null;
        $this->selectedRoles = [];
        $this->search = '';
        $this->processing = false;
        $this->selectAll = false;
    }

    /**
     * Update select all based on current selection
     */
    public function updatedSearch(): void
    {
        $this->updateSelectAll();
    }

    public function updatedSelectedRoles(): void
    {
        $this->updateSelectAll();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedRoles = $this->getAvailableRoles()->pluck('id')->toArray();
        } else {
            $this->selectedRoles = [];
        }
    }

    private function updateSelectAll(): void
    {
        $availableRoles = $this->getAvailableRoles();
        $this->selectAll = $availableRoles->count() > 0 &&
                          count($this->selectedRoles) === $availableRoles->count();
    }

    /**
     * Save assignment changes
     */
    public function save(): void
    {
        if (!$this->permission) {
            $this->closeModal();
            return;
        }

        $this->processing = true;

        try {
            // Get current roles assigned to this permission
            $currentRoleIds = $this->permission->roles()->pluck('id')->toArray();

            // Get roles to assign (newly selected)
            $rolesToAssign = array_diff($this->selectedRoles, $currentRoleIds);

            // Get roles to revoke (previously selected but now unselected)
            $rolesToRevoke = array_diff($currentRoleIds, $this->selectedRoles);

            $assignedCount = 0;
            $revokedCount = 0;

            // Assign new roles
            foreach ($rolesToAssign as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    $role->givePermissionTo($this->permission);
                    $assignedCount++;
                }
            }

            // Revoke removed roles
            foreach ($rolesToRevoke as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    $role->revokePermissionTo($this->permission);
                    $revokedCount++;
                }
            }

            $message = [];
            if ($assignedCount > 0) {
                $message[] = "{$assignedCount} role(s) ditambahkan";
            }
            if ($revokedCount > 0) {
                $message[] = "{$revokedCount} role(s) dicabut";
            }

            if (empty($message)) {
                $message[] = "Tidak ada perubahan";
            }

            $this->success(
                "Assignment berhasil diperbarui! " . implode(', ', $message) . " untuk permission '{$this->permission->name}'.",
                position: 'toast-top toast-end'
            );

            // Emit event untuk refresh parent component
            $this->dispatch('permissionAssigned');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal memperbarui assignment. Silakan coba lagi.', position: 'toast-top toast-end');
            $this->processing = false;
        }
    }

    /**
     * Quick toggle role assignment
     */
    public function toggleRole(int $roleId): void
    {
        if (in_array($roleId, $this->selectedRoles)) {
            $this->selectedRoles = array_values(array_diff($this->selectedRoles, [$roleId]));
        } else {
            $this->selectedRoles[] = $roleId;
        }
        $this->updateSelectAll();
    }

    // * ========================================
    // * DATA METHODS
    // * ========================================

    public function getAvailableRoles()
    {
        return Role::when($this->search, function ($query) {
            $query->where('name', 'like', "%{$this->search}%");
        })
        ->orderBy('name')
        ->get();
    }

    public function getCurrentlyAssignedRoles()
    {
        return $this->permission ? $this->permission->roles : collect();
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    /**
     * Check if has changes
     */
    public function getHasChangesProperty(): bool
    {
        if (!$this->permission) return false;

        $originalRoleIds = $this->permission->roles()->pluck('id')->sort()->values()->toArray();
        $selectedRoleIds = collect($this->selectedRoles)->sort()->values()->toArray();

        return $originalRoleIds !== $selectedRoleIds;
    }

    /**
     * Get selected count
     */
    public function getSelectedCountProperty(): int
    {
        return count($this->selectedRoles);
    }

    /**
     * Get total roles
     */
    public function getTotalRolesProperty(): int
    {
        return $this->getAvailableRoles()->count();
    }

    /**
     * Get modal subtitle
     */
    public function getModalSubtitleProperty(): string
    {
        if (!$this->permission) {
            return '';
        }

        $currentCount = $this->getCurrentlyAssignedRoles()->count();
        $totalRoles = Role::count();

        return "Saat ini di-assign ke {$currentCount} dari {$totalRoles} role yang tersedia.";
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        $availableRoles = $this->getAvailableRoles();
        $currentlyAssignedRoles = $this->getCurrentlyAssignedRoles();

        return view('livewire.app.component.permission.assign-permission-modal', [
            'availableRoles' => $availableRoles,
            'currentlyAssignedRoles' => $currentlyAssignedRoles,
            'hasChanges' => $this->hasChanges,
            'selectedCount' => $this->selectedCount,
            'totalRoles' => $this->totalRoles,
            'modalSubtitle' => $this->modalSubtitle,
        ]);
    }
}
