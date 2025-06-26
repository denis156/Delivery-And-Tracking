<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use Mary\Traits\Toast;
use Livewire\Component;

class ChangeStatusModal extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES
    // * ========================================

    public bool $showModal = false;
    public ?User $user = null;
    public bool $processing = false;

    // For create page preview
    public bool $isPreviewMode = false;
    public array $previewData = [];

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'openChangeStatusModal' => 'openModal',
        'openChangeStatusPreview' => 'openPreviewModal'
    ];

    // * ========================================
    // * METHODS
    // * ========================================

    /**
     * Open modal dengan user data (untuk edit/view)
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
            $this->error('Status pengguna ini tidak dapat diubah.', position: 'toast-top toast-end');
            return;
        }

        $this->isPreviewMode = false;
        $this->showModal = true;
    }

    /**
     * Open modal untuk preview di create page
     */
    public function openPreviewModal(array $userData): void
    {
        // Validate role yang diizinkan untuk preview
        $allowedRoles = UserHelper::getManagementRoles() + UserHelper::getStaffRoles();

        if (!isset($userData['role']) || !array_key_exists($userData['role'], $allowedRoles)) {
            $this->error('Role yang dipilih tidak dapat dikelola statusnya.', position: 'toast-top toast-end');
            return;
        }

        $this->previewData = $userData;
        $this->isPreviewMode = true;
        $this->showModal = true;
    }

    /**
     * Close modal dan reset data
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->user = null;
        $this->previewData = [];
        $this->isPreviewMode = false;
        $this->processing = false;
    }

    /**
     * Konfirmasi toggle status user
     */
    public function confirmChangeStatus(): void
    {
        if ($this->isPreviewMode) {
            // Untuk preview mode, emit event ke parent untuk toggle status
            $newStatus = !$this->previewData['is_active'];
            $this->dispatch('togglePreviewStatus', $newStatus);
            $this->closeModal();
            return;
        }

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-top toast-end');
            $this->closeModal();
            return;
        }

        $this->processing = true;

        try {
            // Double check untuk role yang tidak diizinkan
            if ($this->user->isDriver() || $this->user->isClient()) {
                $this->error('Status pengguna ini tidak dapat diubah.', position: 'toast-top toast-end');
                $this->closeModal();
                return;
            }

            // Use model method untuk toggle status
            $this->user->toggleStatus();

            $status = $this->user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->success("User {$this->user->name} berhasil {$status}.", position: 'toast-top toast-end');

            // Emit event untuk refresh parent component
            $this->dispatch('userStatusUpdated');

            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal mengubah status user. Silakan coba lagi.', position: 'toast-top toast-end');
            $this->processing = false;
        }
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    /**
     * Get current user data (real user or preview)
     */
    public function getCurrentUserProperty(): ?object
    {
        if ($this->isPreviewMode) {
            return (object) $this->previewData;
        }
        return $this->user;
    }

    /**
     * Get action text based on current status
     */
    public function getActionTextProperty(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return '';

        return $currentUser->is_active ? 'nonaktifkan' : 'aktifkan';
    }

    /**
     * Get button class based on current status
     */
    public function getButtonClassProperty(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return 'btn-primary';

        return $currentUser->is_active ? 'btn-warning' : 'btn-success';
    }

    /**
     * Get icon based on current status
     */
    public function getIconProperty(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return 'phosphor.question';

        return $currentUser->is_active ? 'phosphor.pause' : 'phosphor.play';
    }

    /**
     * Get modal title
     */
    public function getModalTitleProperty(): string
    {
        if ($this->isPreviewMode) {
            return 'Preview: ' . ucfirst($this->actionText) . ' Pengguna';
        }
        return 'Konfirmasi ' . ucfirst($this->actionText) . ' Pengguna';
    }

    /**
     * Get modal subtitle
     */
    public function getModalSubtitleProperty(): string
    {
        if ($this->isPreviewMode) {
            return 'Preview perubahan status pengguna yang akan dibuat';
        }
        return 'Pastikan Anda yakin dengan tindakan ini';
    }

    /**
     * Get user role color menggunakan Helper
     */
    public function getUserRoleColorProperty(): string
    {
        if ($this->isPreviewMode) {
            return UserHelper::getRoleColor($this->previewData['role'] ?? UserHelper::ROLE_CLIENT);
        }

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
        if ($this->isPreviewMode) {
            return UserHelper::getRoleLabel($this->previewData['role'] ?? UserHelper::ROLE_CLIENT);
        }

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
        return view('livewire.app.component.user.change-status-modal', [
            'currentUser' => $this->currentUser,
            'modalTitle' => $this->modalTitle,
            'modalSubtitle' => $this->modalSubtitle,
            'userRoleColor' => $this->userRoleColor,
            'userRoleLabel' => $this->userRoleLabel,
        ]);
    }
}
