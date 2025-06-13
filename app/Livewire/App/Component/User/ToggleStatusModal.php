<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;

class ToggleStatusModal extends Component
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
        'openToggleStatusModal' => 'openModal',
        'openToggleStatusPreview' => 'openPreviewModal'
    ];

    // * ========================================
    // * METHODS
    // * ========================================

    /**
     * Open modal dengan user data (untuk edit/view)
     */
    public function openModal(int $userId): void
    {
        $this->user = User::find($userId);

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-bottom');
            return;
        }

        // Safety check untuk driver
        if ($this->user->role === 'driver') {
            $this->error('Status driver tidak dapat diubah dari halaman ini.', position: 'toast-bottom');
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
     * Konfirmasi toggle status user (hanya untuk edit/view)
     */
    public function confirmToggleStatus(): void
    {
        if ($this->isPreviewMode) {
            // Untuk preview mode, emit event ke parent untuk toggle status
            $newStatus = !$this->previewData['is_active'];
            $this->dispatch('togglePreviewStatus', $newStatus);
            $this->closeModal();
            return;
        }

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-bottom');
            $this->closeModal();
            return;
        }

        $this->processing = true;

        try {
            // Double check untuk driver
            if ($this->user->role === 'driver') {
                $this->error('Status driver tidak dapat diubah dari halaman ini.', position: 'toast-bottom');
                $this->closeModal();
                return;
            }

            $oldStatus = $this->user->is_active;
            $this->user->update(['is_active' => !$this->user->is_active]);

            $status = $this->user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->success("User {$this->user->name} berhasil {$status}.", position: 'toast-bottom');

            // Emit event untuk refresh parent component
            $this->dispatch('userStatusUpdated');

            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal mengubah status user. Silakan coba lagi.', position: 'toast-bottom');
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

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.component.user.toggle-status-modal', [
            'currentUser' => $this->currentUser,
            'modalTitle' => $this->modalTitle,
            'modalSubtitle' => $this->modalSubtitle,
        ]);
    }
}
