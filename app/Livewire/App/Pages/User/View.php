<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
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
        // Safety check untuk driver dan client
        if ($user->isDriver() || $user->isClient()) {
            $this->error('Pengguna ini tidak dapat dilihat dari halaman ini.', position: 'toast-top toast-end');
            $this->redirect(route('app.user.index'), navigate: true);
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
        $this->redirect(route('app.user.index'), navigate: true);
    }

    /**
     * Open change status modal
     */
    public function changeUserStatus(): void
    {
        $this->dispatch('openChangeStatusModal', $this->user->id);
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
        $this->success('User berhasil dihapus.', position: 'toast-top toast-end');
        $this->redirect(route('app.user.index'), navigate: true);
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
        $role = $this->user->getPrimaryRole();

        $capabilities = [
            'canLogin' => $this->user->is_active,
            'hasEmailVerified' => !is_null($this->user->email_verified_at),
            'isManageable' => $this->user->isManageable(),
        ];

        // Role-based capabilities menggunakan Helper
        if ($role) {
            $capabilities['canManageUsers'] = UserHelper::canManageUsers($role);
            $capabilities['canViewReports'] = UserHelper::isManagementRole($role);
            $capabilities['canManageSystem'] = $role === UserHelper::ROLE_ADMIN;
            $capabilities['canManageDeliveries'] = UserHelper::canManageDeliveries($role);
        } else {
            $capabilities['canManageUsers'] = false;
            $capabilities['canViewReports'] = false;
            $capabilities['canManageSystem'] = false;
            $capabilities['canManageDeliveries'] = false;
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

    /**
     * Get role description menggunakan Helper
     */
    public function getRoleDescriptionProperty(): string
    {
        $role = $this->user->getPrimaryRole();
        return $role ? UserHelper::getRoleDescription($role) : 'Belum memiliki role';
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
            'roleDescription' => $this->roleDescription,
        ]);
    }
}
