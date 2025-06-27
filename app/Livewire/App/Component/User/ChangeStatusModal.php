<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ChangeStatusModal extends Component
{
    use Toast;

    // Properties
    public bool $showModal = false;
    public ?User $user = null;
    public bool $processing = false;
    public bool $isPreviewMode = false;
    public array $previewData = [];

    // Listeners
    protected $listeners = [
        'openChangeStatusModal' => 'openModal',
        'openChangeStatusPreview' => 'openPreviewModal'
    ];

    // Methods
    public function openModal(int $userId): void
    {
        $this->user = User::where('id', $userId)->first();

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-top toast-end');
            return;
        }

        $this->isPreviewMode = false;
        $this->showModal = true;
    }

    public function openPreviewModal(array $userData): void
    {
        $allRoles = UserHelper::getAllRoles();

        if (!isset($userData['role']) || !array_key_exists($userData['role'], $allRoles)) {
            $this->error('Role yang dipilih tidak valid.', position: 'toast-top toast-end');
            return;
        }

        $this->previewData = $userData;
        $this->isPreviewMode = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->user = null;
        $this->previewData = [];
        $this->isPreviewMode = false;
        $this->processing = false;
    }

    public function confirmChangeStatus(): void
    {
        if ($this->isPreviewMode) {
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
            $this->user->toggleStatus();

            $status = $this->user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->success("User {$this->user->name} berhasil {$status}.", position: 'toast-top toast-end');

            $this->dispatch('userStatusUpdated');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal mengubah status user. Silakan coba lagi.', position: 'toast-top toast-end');
            $this->processing = false;
        }
    }

    // Computed Properties
    #[Computed]
    public function currentUser(): ?object
    {
        if ($this->isPreviewMode) {
            return (object) $this->previewData;
        }
        return $this->user;
    }

    #[Computed]
    public function actionText(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return '';
        return $currentUser->is_active ? 'nonaktifkan' : 'aktifkan';
    }

    #[Computed]
    public function buttonClass(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return 'btn-primary';
        return $currentUser->is_active ? 'btn-warning' : 'btn-success';
    }

    #[Computed]
    public function icon(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return 'phosphor.question';
        return $currentUser->is_active ? 'phosphor.pause' : 'phosphor.play';
    }

    #[Computed]
    public function modalTitle(): string
    {
        if ($this->isPreviewMode) {
            return 'Preview: ' . ucfirst($this->actionText) . ' Pengguna';
        }
        return 'Konfirmasi ' . ucfirst($this->actionText) . ' Pengguna';
    }

    #[Computed]
    public function modalSubtitle(): string
    {
        if ($this->isPreviewMode) {
            return "Preview perubahan status {$this->userRoleLabel} yang akan dibuat";
        }
        return "Pastikan Anda yakin dengan tindakan ini untuk {$this->userRoleLabel}";
    }

    #[Computed]
    public function userRoleColor(): string
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

    #[Computed]
    public function userRoleLabel(): string
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

    #[Computed]
    public function roleDescription(): string
    {
        $currentUser = $this->currentUser;
        if (!$currentUser) return '';

        $roleKey = $this->isPreviewMode ?
            ($this->previewData['role'] ?? '') :
            ($this->user->getPrimaryRole() ?? '');

        return match($roleKey) {
            UserHelper::ROLE_DRIVER => $currentUser->is_active ?
                'Sopir tidak akan dapat login dan menerima pengiriman.' :
                'Sopir dapat login dan menerima pengiriman.',
            UserHelper::ROLE_CLIENT => $currentUser->is_active ?
                'Klien tidak akan dapat login dan melakukan pemesanan.' :
                'Klien dapat login dan melakukan pemesanan.',
            UserHelper::ROLE_ADMIN, UserHelper::ROLE_MANAGER => $currentUser->is_active ?
                'User tidak akan dapat login dan mengakses sistem.' :
                'User dapat login dan mengakses sistem.',
            default => $currentUser->is_active ?
                'User tidak akan dapat login ke sistem.' :
                'User dapat login ke sistem.'
        };
    }

    public function render()
    {
        return view('livewire.app.component.user.change-status-modal');
    }
}
