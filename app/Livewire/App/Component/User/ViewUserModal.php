<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;

class ViewUserModal extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public bool $showModal = false;
    public ?User $user = null;
    public bool $loading = false;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'openViewUserModal' => 'openModal'
    ];

    // * ========================================
    // * ACTIONS
    // * ========================================

    /**
     * Open modal with user data
     */
    public function openModal(int $userId): void
    {
        $this->loading = true;
        $this->showModal = true;

        try {
            $this->user = User::findOrFail($userId);

            // Safety check untuk driver
            if ($this->user->isDriver()) {
                $this->error('Driver tidak dapat dilihat dari halaman ini.', position: 'toast-bottom');
                $this->closeModal();
                return;
            }

        } catch (\Exception $e) {
            $this->error('User tidak ditemukan.', position: 'toast-bottom');
            $this->closeModal();
            return;
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Close modal dan reset data
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->user = null;
        $this->loading = false;
    }

    /**
     * Open edit modal (dispatch to edit component)
     */
    public function editUser(): void
    {
        if ($this->user) {
            $this->dispatch('openEditUserModal', $this->user->id);
            $this->closeModal();
        }
    }

    /**
     * Open toggle status modal
     */
    public function toggleUserStatus(): void
    {
        if ($this->user) {
            $this->dispatch('openToggleStatusModal', $this->user->id);
            $this->closeModal();
        }
    }

    /**
     * Open delete modal
     */
    public function deleteUser(): void
    {
        if ($this->user) {
            $this->dispatch('openDeleteUserModal', $this->user->id);
            $this->closeModal();
        }
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    /**
     * Get user info for display (using model accessors)
     */
    public function getUserInfoProperty(): array
    {
        if (!$this->user) {
            return [];
        }

        return [
            'avatar' => $this->user->avatar, // Uses accessor from model
            'avatarPlaceholder' => $this->user->avatar_placeholder, // Uses accessor
            'name' => $this->user->name,
            'email' => $this->user->email,
            'roleLabel' => $this->user->role_label, // Uses accessor
            'roleColor' => $this->user->role_color, // Uses accessor
            'statusLabel' => $this->user->status_label, // Uses accessor
            'statusColor' => $this->user->status_color, // Uses accessor
            'isActive' => $this->user->is_active,
            'createdAt' => $this->user->created_at,
            'updatedAt' => $this->user->updated_at,
            'emailVerifiedAt' => $this->user->email_verified_at,
        ];
    }

    /**
     * Get user activity summary
     */
    public function getUserActivityProperty(): array
    {
        if (!$this->user) {
            return [];
        }

        return [
            'joinedDays' => $this->user->created_at->diffInDays(now()),
            'lastUpdateDays' => $this->user->updated_at->diffInDays(now()),
            'isEmailVerified' => !is_null($this->user->email_verified_at),
            'accountAge' => $this->user->created_at->diffForHumans(),
            'lastUpdate' => $this->user->updated_at->diffForHumans(),
        ];
    }

    /**
     * Get user permissions/capabilities (could be extended for role-based permissions)
     */
    public function getUserCapabilitiesProperty(): array
    {
        if (!$this->user) {
            return [];
        }

        $capabilities = [
            'canLogin' => $this->user->is_active,
            'hasEmailVerified' => !is_null($this->user->email_verified_at),
            'isManageable' => $this->user->isManageable(),
        ];

        // Role-based capabilities
        switch ($this->user->role) {
            case User::ROLE_ADMIN:
                $capabilities['canManageUsers'] = true;
                $capabilities['canViewReports'] = true;
                $capabilities['canManageSystem'] = true;
                break;
            case User::ROLE_MANAGER:
                $capabilities['canManageUsers'] = true;
                $capabilities['canViewReports'] = true;
                $capabilities['canManageSystem'] = false;
                break;
            case User::ROLE_CLIENT:
                $capabilities['canManageUsers'] = false;
                $capabilities['canViewReports'] = false;
                $capabilities['canManageSystem'] = false;
                break;
            default:
                $capabilities['canManageUsers'] = false;
                $capabilities['canViewReports'] = false;
                $capabilities['canManageSystem'] = false;
        }

        return $capabilities;
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.component.user.view-user-modal', [
            'userInfo' => $this->userInfo,
            'userActivity' => $this->userActivity,
            'userCapabilities' => $this->userCapabilities,
        ]);
    }
}
