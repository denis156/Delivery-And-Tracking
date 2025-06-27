<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Computed;

class DeleteUserModal extends Component
{
    use Toast;

    // Properties
    public bool $showModal = false;
    public ?User $user = null;
    public bool $processing = false;
    public string $confirmText = '';

    // Listeners
    protected $listeners = [
        'openDeleteUserModal' => 'openModal'
    ];

    // Validation
    protected $rules = [
        'confirmText' => 'required|string'
    ];

    protected $messages = [
        'confirmText.required' => 'Konfirmasi teks diperlukan untuk menghapus user.'
    ];

    // Methods
    public function openModal(int $userId): void
    {
        $this->user = User::where('id', $userId)->first();

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-top toast-end');
            return;
        }

        $this->showModal = true;
        $this->confirmText = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->user = null;
        $this->processing = false;
        $this->confirmText = '';
        $this->resetValidation();
    }

    public function confirmDelete(): void
    {
        $this->validate();

        if (!$this->user) {
            $this->error('User tidak ditemukan.', position: 'toast-top toast-end');
            $this->closeModal();
            return;
        }

        if (strtolower($this->confirmText) !== strtolower($this->user->name)) {
            $this->addError('confirmText', 'Nama user tidak sesuai. Ketik "' . $this->user->name . '" untuk konfirmasi.');
            return;
        }

        $this->processing = true;

        try {
            $userName = $this->user->name;
            $userRole = $this->user->getPrimaryRole();

            // Special handling untuk driver
            if ($this->user->isDriver() && $this->user->driver) {
                $this->user->driver->delete();
            }

            $this->user->delete();

            $roleLabel = UserHelper::getRoleLabel($userRole ?? '');
            $this->success("{$roleLabel} {$userName} berhasil dihapus.", position: 'toast-top toast-end');

            $this->dispatch('userDeleted');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Gagal menghapus user. Silakan coba lagi.', position: 'toast-top toast-end');
            $this->processing = false;
        }
    }

    // Computed Properties
    #[Computed]
    public function canDelete(): bool
    {
        if (!$this->user) return false;
        return strtolower($this->confirmText) === strtolower($this->user->name);
    }

    #[Computed]
    public function userRoleColor(): string
    {
        if ($this->user) {
            $role = $this->user->getPrimaryRole();
            return $role ? UserHelper::getRoleColor($role) : 'neutral';
        }
        return 'neutral';
    }

    #[Computed]
    public function userRoleLabel(): string
    {
        if ($this->user) {
            $role = $this->user->getPrimaryRole();
            return $role ? UserHelper::getRoleLabel($role) : 'Tidak ada role';
        }
        return 'Tidak ada role';
    }

    #[Computed]
    public function additionalInfo(): array
    {
        if (!$this->user) return [];

        $info = [
            'bergabung' => $this->user->created_at->format('d M Y H:i'),
            'update' => $this->user->updated_at->diffForHumans(),
            'role' => $this->userRoleLabel,
        ];

        // Add driver-specific info
        if ($this->user->isDriver() && $this->user->driver) {
            $driver = $this->user->driver;
            $info['sim'] = $driver->license_number ?? '-';
            $info['jenis_sim'] = $driver->license_type ?? '-';
            $info['telepon'] = $driver->phone ?? '-';
        }

        return $info;
    }

    #[Computed]
    public function deleteConsequences(): array
    {
        if (!$this->user) return [];

        $consequences = [
            'Pengguna akan dihapus dari sistem',
            'Akses login akan dicabut secara permanen',
            'Data pengguna tidak dapat dipulihkan'
        ];

        $role = $this->user->getPrimaryRole();

        switch ($role) {
            case UserHelper::ROLE_DRIVER:
                $consequences[] = 'Data sopir dan SIM akan ikut terhapus';
                $consequences[] = 'Riwayat pengiriman tetap tersimpan';
                break;
            case UserHelper::ROLE_CLIENT:
                $consequences[] = 'Riwayat pesanan tetap tersimpan';
                break;
            case UserHelper::ROLE_ADMIN:
            case UserHelper::ROLE_MANAGER:
                $consequences[] = 'Semua aktivitas administratif akan terhenti';
                break;
        }

        return $consequences;
    }

    public function render()
    {
        return view('livewire.app.component.user.delete-user-modal');
    }
}
