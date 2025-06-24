<?php

namespace App\Livewire\App\Pages\Permission;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Mary\Traits\Toast;
use App\Class\StatusHelper;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Detail Permission')]
#[Layout('livewire.layouts.app')]
class View extends Component
{
    use Toast, WithPagination;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public Permission $permission;
    public string $activeTab = 'info';
    public string $rolesSearch = '';
    public string $usersSearch = '';
    public bool $showAssignModal = false;
    public bool $showDeleteModal = false;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'permissionUpdated' => '$refresh',
        'permissionDeleted' => 'handlePermissionDeleted',
        'permissionAssigned' => '$refresh',
    ];

    // * ========================================
    // * LIFECYCLE HOOKS
    // * ========================================

    public function mount(Permission $permission): void
    {
        $this->permission = $permission;
    }

    // * ========================================
    // * REAL-TIME UPDATES
    // * ========================================

    public function updatedRolesSearch(): void
    {
        $this->resetPage('roles-page');
    }

    public function updatedUsersSearch(): void
    {
        $this->resetPage('users-page');
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->reset(['rolesSearch', 'usersSearch']);
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function refreshData(): void
    {
        $this->permission = $this->permission->fresh();
        $this->success(
            title: 'Data diperbarui!',
            description: 'Informasi permission telah diperbarui.',
            position: 'toast-top toast-end'
        );
    }

    public function editPermission(): void
    {
        $this->redirect(route('app.permission.edit', $this->permission), navigate: true);
    }

    public function deletePermission(): void
    {
        if (!StatusHelper::canPermissionBeDeleted($this->permission)) {
            $this->error(
                title: 'Tidak dapat menghapus!',
                description: 'Permission masih digunakan oleh roles atau users.',
                position: 'toast-top toast-end'
            );
            return;
        }

        $this->dispatch('openDeletePermissionModal', $this->permission->id);
    }

    public function openAssignModal(): void
    {
        $this->dispatch('openAssignPermissionModal', $this->permission->id);
    }

    public function revokeFromRole(int $roleId): void
    {
        try {
            $role = Role::findOrFail($roleId);
            $role->revokePermissionTo($this->permission);

            $this->permission = $this->permission->fresh();

            $this->success(
                title: 'Permission dicabut!',
                description: "Permission berhasil dicabut dari role '{$role->name}'.",
                position: 'toast-top toast-end'
            );
        } catch (\Exception $e) {
            $this->error(
                title: 'Gagal mencabut permission!',
                description: 'Terjadi kesalahan sistem.',
                position: 'toast-top toast-end'
            );
        }
    }

    public function handlePermissionDeleted(): void
    {
        $this->redirect(route('app.permission.index'), navigate: true);
    }

    public function backToIndex(): void
    {
        $this->redirect(route('app.permission.index'), navigate: true);
    }

    // * ========================================
    // * DATA METHODS
    // * ========================================

    public function getRolesWithPermission()
    {
        return $this->permission->roles()
            ->when($this->rolesSearch, function ($query) {
                $query->where('name', 'like', "%{$this->rolesSearch}%");
            })
            ->paginate(10, ['*'], 'roles-page');
    }

    public function getUsersWithPermission()
    {
        // Users dengan direct permission + users via roles
        $directUserIds = $this->permission->users()->pluck('id')->toArray();

        $roleUserIds = User::whereHas('roles', function ($query) {
            $query->whereHas('permissions', function ($q) {
                $q->where('id', $this->permission->id);
            });
        })->pluck('id')->toArray();

        $allUserIds = array_unique(array_merge($directUserIds, $roleUserIds));

        return User::whereIn('id', $allUserIds)
            ->when($this->usersSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->usersSearch}%")
                      ->orWhere('email', 'like', "%{$this->usersSearch}%");
                });
            })
            ->with('roles')
            ->paginate(10, ['*'], 'users-page');
    }

    public function getAvailableRoles()
    {
        return Role::whereDoesntHave('permissions', function ($query) {
            $query->where('id', $this->permission->id);
        })->get();
    }

    // * ========================================
    // * HELPER METHODS USING STATUSHELPER
    // * ========================================

    public function getPermissionCategory(string $name): string
    {
        return StatusHelper::getPermissionCategory($name);
    }

    public function getPermissionColor(string $category): string
    {
        return StatusHelper::getPermissionColor($category);
    }

    public function getPermissionIcon(string $category): string
    {
        return StatusHelper::getPermissionIcon($category);
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    public function getPermissionStatsProperty(): array
    {
        $rolesCount = $this->permission->roles()->count();
        $directUsersCount = $this->permission->users()->count();

        $viaRolesUsersCount = User::whereHas('roles', function ($query) {
            $query->whereHas('permissions', function ($q) {
                $q->where('id', $this->permission->id);
            });
        })->count();

        $totalUsers = $directUsersCount + $viaRolesUsersCount;

        return [
            'roles_count' => $rolesCount,
            'direct_users_count' => $directUsersCount,
            'via_roles_users_count' => $viaRolesUsersCount,
            'total_users_count' => $totalUsers,
        ];
    }

    public function getTabsProperty(): array
    {
        $stats = $this->permissionStats;

        return [
            [
                'id' => 'info',
                'label' => 'Informasi',
                'icon' => 'phosphor.info',
                'badge' => null,
            ],
            [
                'id' => 'roles',
                'label' => 'Roles',
                'icon' => 'phosphor.user-circle',
                'badge' => $stats['roles_count'],
            ],
            [
                'id' => 'users',
                'label' => 'Users',
                'icon' => 'phosphor.users',
                'badge' => $stats['total_users_count'],
            ],
        ];
    }

    public function getCanBeDeletedProperty(): bool
    {
        return StatusHelper::canPermissionBeDeleted($this->permission);
    }

    public function getBreadcrumbsProperty(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'url' => route('app.dashboard'),
            ],
            [
                'label' => 'Permission',
                'url' => route('app.permission.index'),
            ],
            [
                'label' => $this->getPermissionDisplayName(),
                'url' => null,
            ],
        ];
    }

    public function getPermissionCategoryProperty(): string
    {
        return StatusHelper::getPermissionCategory($this->permission->name);
    }

    public function getPermissionColorProperty(): string
    {
        return StatusHelper::getPermissionColor($this->permissionCategory);
    }

    public function getPermissionIconProperty(): string
    {
        return StatusHelper::getPermissionIcon($this->permissionCategory);
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    private function getPermissionDisplayName(): string
    {
        return ucwords(str_replace(['-', '_', '.'], ' ', $this->permission->name));
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        $rolesWithPermission = $this->getRolesWithPermission();
        $usersWithPermission = $this->getUsersWithPermission();
        $availableRoles = $this->getAvailableRoles();

        return view('livewire.app.pages.permission.view', [
            'rolesWithPermission' => $rolesWithPermission,
            'usersWithPermission' => $usersWithPermission,
            'availableRoles' => $availableRoles,
            'permissionStats' => $this->permissionStats,
            'tabs' => $this->tabs,
            'canBeDeleted' => $this->canBeDeleted,
            'breadcrumbs' => $this->breadcrumbs,
            'permissionCategory' => $this->permissionCategory,
            'permissionColor' => $this->permissionColor,
            'permissionIcon' => $this->permissionIcon,
        ]);
    }
}
