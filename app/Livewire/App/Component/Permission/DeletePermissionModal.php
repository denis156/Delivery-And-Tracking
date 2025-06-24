<?php

namespace App\Livewire\App\Component\Permission;

use Spatie\Permission\Models\Permission;
use Mary\Traits\Toast;
use Livewire\Component;

class DeletePermissionModal extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES
    // * ========================================

    public bool $showModal = false;
    public ?Permission $permission = null;
    public bool $processing = false;
    public string $confirmText = '';

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'openDeletePermissionModal' => 'openModal'
    ];

    // * ========================================
    // * VALIDATION
    // * ========================================

    protected $rules = [
        'confirmText' => 'required|string'
    ];

    protected $messages = [
        'confirmText.required' => 'Konfirmasi teks diperlukan untuk menghapus permission.'
    ];

    // * ========================================
    // * METHODS
    // * ========================================

    /**
     * Open modal dengan permission data
     */
    public function openModal(int $permissionId): void
    {
        $this->permission = Permission::with(['roles', 'users'])->find($permissionId);

        if (!$this->permission) {
            $this->error('Permission tidak ditemukan.', position: 'toast-top toast-end');
            return;
        }

        $this->showModal = true;
        $this->confirmText = '';
    }

    /**
     * Close modal dan reset data
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->permission = null;
        $this->processing = false;
        $this->confirmText = '';
        $this->resetValidation();
    }

    /**
     * Konfirmasi hapus permission
     */
    public function confirmDelete(): void
    {
        $this->validate();

        if (!$this->permission) {
            $this->error('Permission tidak ditemukan.', position: 'toast-top toast-end');
            $this->closeModal();
            return;
        }

        // Check if can be deleted
        if (!$this->canPermissionBeDeleted()) {
            $this->error('Permission masih digunakan oleh roles atau users.', position: 'toast-top toast-end');
            $this->closeModal();
            return;
        }

        // Validasi konfirmasi text
        if (strtolower($this->confirmText) !== strtolower($this->permission->name)) {
            $this->addError('confirmText', 'Nama permission tidak sesuai. Ketik "' . $this->permission->name . '" untuk konfirmasi.');
            return;
        }

        $this->processing = true;

        try {
            $permissionName = $this->permission->name;
            $this->permission->delete();

            $this->success("Permission {$permissionName} berhasil dihapus.", position: 'toast-top toast-end');

            // Emit event untuk refresh parent component
            $this->dispatch('permissionDeleted');

            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal menghapus permission. Silakan coba lagi.', position: 'toast-top toast-end');
            $this->processing = false;
        }
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    private function canPermissionBeDeleted(): bool
    {
        if (!$this->permission) return false;
        return $this->permission->roles()->count() === 0 && $this->permission->users()->count() === 0;
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    /**
     * Check if delete button should be enabled
     */
    public function getCanDeleteProperty(): bool
    {
        if (!$this->permission) return false;

        return $this->canPermissionBeDeleted() &&
               strtolower($this->confirmText) === strtolower($this->permission->name);
    }

    /**
     * Get usage info
     */
    public function getUsageInfoProperty(): array
    {
        if (!$this->permission) {
            return [
                'roles_count' => 0,
                'users_count' => 0,
                'total_usage' => 0,
                'can_be_deleted' => false,
            ];
        }

        $rolesCount = $this->permission->roles()->count();
        $usersCount = $this->permission->users()->count();

        return [
            'roles_count' => $rolesCount,
            'users_count' => $usersCount,
            'total_usage' => $rolesCount + $usersCount,
            'can_be_deleted' => $this->canPermissionBeDeleted(),
        ];
    }

    /**
     * Get warning message
     */
    public function getWarningMessageProperty(): string
    {
        if (!$this->permission) {
            return 'Permission tidak valid.';
        }

        $usage = $this->usageInfo;

        if (!$usage['can_be_deleted']) {
            $messages = [];

            if ($usage['roles_count'] > 0) {
                $messages[] = "{$usage['roles_count']} role(s)";
            }

            if ($usage['users_count'] > 0) {
                $messages[] = "{$usage['users_count']} user(s)";
            }

            return "Permission '{$this->permission->name}' masih digunakan oleh " . implode(' dan ', $messages) .
                   ". Hapus assignment terlebih dahulu sebelum menghapus permission.";
        }

        return "Apakah Anda yakin ingin menghapus permission '{$this->permission->name}'? Tindakan ini tidak dapat dibatalkan.";
    }

    /**
     * Get modal title
     */
    public function getModalTitleProperty(): string
    {
        if (!$this->permission) {
            return 'Hapus Permission';
        }

        return $this->usageInfo['can_be_deleted'] ? 'Konfirmasi Hapus Permission' : 'Tidak Dapat Menghapus Permission';
    }

    /**
     * Get modal color
     */
    public function getModalColorProperty(): string
    {
        return $this->usageInfo['can_be_deleted'] ? 'error' : 'warning';
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.component.permission.delete-permission-modal', [
            'canDelete' => $this->canDelete,
            'usageInfo' => $this->usageInfo,
            'warningMessage' => $this->warningMessage,
            'modalTitle' => $this->modalTitle,
            'modalColor' => $this->modalColor,
        ]);
    }
}
