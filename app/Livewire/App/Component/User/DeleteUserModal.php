<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use Mary\Traits\Toast;
use Livewire\Component;

class DeleteUserModal extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES
    // * ========================================

    public bool $showModal = false;
    public ?User $user = null;
    public bool $processing = false;
    public string $confirmText = '';

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'openDeleteUserModal' => 'openModal'
    ];

    // * ========================================
    // * VALIDATION
    // * ========================================

    protected $rules = [
        'confirmText' => 'required|string'
    ];

    protected $messages = [
        'confirmText.required' => 'Konfirmasi teks diperlukan untuk menghapus user.'
    ];

    // * ========================================
    // * METHODS
    // * ========================================

    /**
     * Open modal dengan user data
     */
    public function openModal(int $userId): void
    {
        $this->user = User::excludeDrivers()
            ->where('id', $userId)
            ->first();

        if (!$this->user) {
            $this->error('User tidak ditemukan atau tidak dapat diakses.', position: 'toast-top toast-end');
            return;
        }

        // Safety check untuk role yang tidak diizinkan
        if ($this->user->isDriver() || $this->user->isClient()) {
            $this->error('Pengguna ini tidak dapat dihapus.', position: 'toast-top toast-end');
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
        $this->user = null;
        $this->processing = false;
        $this->confirmText = '';
        $this->resetValidation();
    }

    /**
     * Konfirmasi hapus user
     */
    public function confirmDelete(): void
    {
        $this->validate();

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-top toast-end');
            $this->closeModal();
            return;
        }

        // Validasi konfirmasi text
        if (strtolower($this->confirmText) !== strtolower($this->user->name)) {
            $this->addError('confirmText', 'Nama user tidak sesuai. Ketik "' . $this->user->name . '" untuk konfirmasi.');
            return;
        }

        $this->processing = true;

        try {
            // Double check untuk role yang tidak diizinkan
            if ($this->user->isDriver() || $this->user->isClient()) {
                $this->error('Pengguna ini tidak dapat dihapus.', position: 'toast-top toast-end');
                $this->closeModal();
                return;
            }

            $userName = $this->user->name;
            $this->user->delete();

            $this->success("User {$userName} berhasil dihapus.", position: 'toast-top toast-end');

            // Emit event untuk refresh parent component
            $this->dispatch('userDeleted');

            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal menghapus user. Silakan coba lagi.', position: 'toast-top toast-end');
            $this->processing = false;
        }
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    /**
     * Check if delete button should be enabled
     */
    public function getCanDeleteProperty(): bool
    {
        if (!$this->user) return false;

        return strtolower($this->confirmText) === strtolower($this->user->name);
    }

    /**
     * Get user role color menggunakan Helper
     */
    public function getUserRoleColorProperty(): string
    {
        if ($this->user) {
            $role = $this->user->getPrimaryRole();
            return $role ? UserHelper::getRoleColor($role) : 'neutral';
        }

        return 'neutral';
    }

    /**
     * Get user role label menggunakan Helper
     */
    public function getUserRoleLabelProperty(): string
    {
        if ($this->user) {
            $role = $this->user->getPrimaryRole();
            return $role ? UserHelper::getRoleLabel($role) : 'Tidak ada role';
        }

        return 'Tidak ada role';
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.component.user.delete-user-modal', [
            'canDelete' => $this->canDelete,
            'userRoleColor' => $this->userRoleColor,
            'userRoleLabel' => $this->userRoleLabel,
        ]);
    }
}
