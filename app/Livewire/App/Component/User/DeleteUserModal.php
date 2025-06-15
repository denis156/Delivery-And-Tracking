<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
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
        $this->user = User::find($userId);

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-bottom');
            return;
        }

        // Safety check untuk driver
        if ($this->user->role === 'driver') {
            $this->error('Driver tidak dapat dihapus dari halaman ini.', position: 'toast-bottom');
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
            $this->error('User tidak ditemukan.', position: 'toast-bottom');
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
            // Double check untuk driver
            if ($this->user->role === 'driver') {
                $this->error('Driver tidak dapat dihapus dari halaman ini.', position: 'toast-bottom');
                $this->closeModal();
                return;
            }

            $userName = $this->user->name;
            $this->user->delete();

            $this->success("User {$userName} berhasil dihapus.", position: 'toast-bottom');

            // Emit event untuk refresh parent component
            $this->dispatch('userDeleted');

            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal menghapus user. Silakan coba lagi.', position: 'toast-bottom');
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

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.component.user.delete-user-modal');
    }
}
