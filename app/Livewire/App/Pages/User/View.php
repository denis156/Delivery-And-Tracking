<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Lihat Pengguna')]
#[Layout('livewire.layouts.app')]
class View extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public User $user;

    // * ========================================
    // * LIFECYCLE HOOKS (Livewire 3 Standards)
    // * ========================================

    public function mount(User $user): void
    {
        // Safety check untuk driver
        if ($user->isDriver()) {
            $this->error('Driver tidak dapat dilihat dari halaman ini.', position: 'toast-bottom');
            $this->redirect(route('app.user'), navigate: true);
            return;
        }

        $this->user = $user;
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    /**
     * Navigate to edit page
     */
    public function editUser(): void
    {
        $this->redirect(route('app.user.edit', $this->user), navigate: true);
    }

    /**
     * Go back to user list
     */
    public function backToList(): void
    {
        $this->redirect(route('app.user'), navigate: true);
    }

    /**
     * Open toggle status modal
     */
    public function toggleUserStatus(): void
    {
        $this->dispatch('openToggleStatusModal', $this->user->id);
    }

    /**
     * Open delete modal
     */
    public function deleteUser(): void
    {
        $this->dispatch('openDeleteUserModal', $this->user->id);
    }

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleUserDeleted'
    ];

    /**
     * Handle user deleted - redirect to list
     */
    public function handleUserDeleted(): void
    {
        $this->success('User berhasil dihapus.', position: 'toast-bottom');
        $this->redirect(route('app.user'), navigate: true);
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Standards)
    // * ========================================

    /**
     * Get user activity summary
     */
    public function getUserActivityProperty(): array
    {
        return [
            'joinedDays' => $this->user->created_at->diffInDays(now()),
            'lastUpdateDays' => $this->user->updated_at->diffInDays(now()),
            'isEmailVerified' => !is_null($this->user->email_verified_at),
            'accountAge' => $this->user->created_at->diffForHumans(),
            'lastUpdate' => $this->user->updated_at->diffForHumans(),
        ];
    }

    /**
     * Get user permissions/capabilities
     */
    public function getUserCapabilitiesProperty(): array
    {
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

    /**
     * Get user statistics
     */
    public function getUserStatsProperty(): array
    {
        return [
            'totalLogins' => 0, // Placeholder - bisa diimplementasi jika ada log system
            'lastLogin' => null, // Placeholder - bisa diimplementasi jika ada log system
            'sessionsCount' => 0, // Placeholder - bisa diimplementasi jika perlu
        ];
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.user.view', [
            'userActivity' => $this->userActivity,
            'userCapabilities' => $this->userCapabilities,
            'userStats' => $this->userStats,
        ]);
    }
}
