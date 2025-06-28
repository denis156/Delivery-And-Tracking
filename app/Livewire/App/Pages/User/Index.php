<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use App\Class\Helper\DriverHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Title('Data Pengguna')]
#[Layout('livewire.layouts.app')]
class Index extends Component
{
    use Toast, WithPagination;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public string $search = '';
    public bool $drawer = false;
    public string $statusFilter = 'all';
    public string $roleFilter = 'all';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = FormatHelper::DEFAULT_PER_PAGE;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleUserDeleted',
        'userCreated' => 'handleUserCreated'
    ];

    // * ========================================
    // * LIVEWIRE HOOKS (Livewire 3 Standards)
    // * ========================================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    /**
     * Clear all filters and search
     */
    public function clear(): void
    {
        $this->reset(['search', 'statusFilter', 'roleFilter']);
        $this->sortBy = ['column' => 'created_at', 'direction' => 'desc'];
        $this->perPage = FormatHelper::DEFAULT_PER_PAGE;
        $this->resetPage();
        $this->success(UserHelper::TOAST_FILTER_CLEARED, position: FormatHelper::TOAST_POSITION);
    }

    /**
     * Handle after user deleted - refresh and check pagination
     */
    public function handleUserDeleted(): void
    {
        if ($this->users->count() === 0 && $this->users->currentPage() > 1) {
            $this->resetPage();
        }
        $this->success(UserHelper::TOAST_USER_DELETED, position: FormatHelper::TOAST_POSITION);
    }

    /**
     * Handle after user created (refresh dari halaman lain)
     */
    public function handleUserCreated(): void
    {
        $this->resetPage();
        $this->success(UserHelper::TOAST_USER_ADDED, position: FormatHelper::TOAST_POSITION);
    }

    /**
     * Open view user page
     */
    public function viewUser(int $userId): void
    {
        $this->redirect(route('app.user.view', $userId), navigate: true);
    }

    /**
     * Open change status modal
     */
    public function openChangeStatusModal(int $userId): void
    {
        $this->dispatch('openChangeStatusModal', $userId);
    }

    /**
     * Open delete user modal
     */
    public function openDeleteModal(int $userId): void
    {
        $this->dispatch('openDeleteUserModal', $userId);
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Standards)
    // * ========================================

    /**
     * Get users with filters and search (exclude drivers and clients)
     */
    public function getUsersProperty()
    {
        $query = User::excludeDrivers()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', UserHelper::ROLE_CLIENT);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                if ($this->statusFilter === 'active') {
                    $q->active();
                } else {
                    $q->inactive();
                }
            })
            ->when($this->roleFilter !== 'all', function ($q) {
                $q->whereHas('roles', function ($query) {
                    $query->where('name', $this->roleFilter);
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate($this->perPage);
    }

    /**
     * Get available roles for filter (only management and staff roles)
     */
    public function getRolesProperty(): array
    {
        return collect([
            'all' => UserHelper::getFilterLabel('all_roles')
        ])->merge(UserHelper::getManagementRoles() + UserHelper::getStaffRoles())->toArray();
    }

    /**
     * Get user statistics
     */
    public function getUserStatsProperty(): array
    {
        // Base query builder - hanya buat sekali
        $baseQuery = User::excludeDrivers()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', UserHelper::ROLE_CLIENT);
            });

        // Gunakan clone untuk setiap perhitungan agar query fresh
        $stats = [
            'totalUsers' => (clone $baseQuery)->count(),
            'activeUsers' => (clone $baseQuery)->active()->count(),
            'inactiveUsers' => (clone $baseQuery)->inactive()->count(),
            'deletedUsers' => User::onlyTrashed()
                ->excludeDrivers()
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', UserHelper::ROLE_CLIENT);
                })
                ->count(),
        ];

        return $stats;
    }

    /**
     * DYNAMIC: User UI configuration from helper
     */
    #[Computed]
    public function userUIConfig(): array
    {
        return [
            'icons' => [
                'add' => FormatHelper::getCommonIcon('add'),
                'view' => FormatHelper::getCommonIcon('view'),
                'edit' => FormatHelper::getCommonIcon('edit'),
                'delete' => FormatHelper::getCommonIcon('delete'),
                'back' => FormatHelper::getCommonIcon('back'),
                'list' => FormatHelper::getCommonIcon('list'),
                'user' => FormatHelper::getCommonIcon('user'),
                'users' => FormatHelper::getCommonIcon('users'),
                'success' => FormatHelper::getCommonIcon('success'),
                'warning' => FormatHelper::getCommonIcon('warning'),
                'error' => FormatHelper::getCommonIcon('error'),
                'info' => FormatHelper::getCommonIcon('info'),
            ],
            'colors' => [
                'active' => UserHelper::getStatusColor(UserHelper::STATUS_ACTIVE),
                'inactive' => UserHelper::getStatusColor(UserHelper::STATUS_INACTIVE),
            ],
            'labels' => [
                'active' => UserHelper::getStatusLabel(UserHelper::STATUS_ACTIVE),
                'inactive' => UserHelper::getStatusLabel(UserHelper::STATUS_INACTIVE),
            ]
        ];
    }

    /**
     * Get sort options for filter
     */
    public function getSortOptionsProperty(): array
    {
        return [
            ['id' => 'created_at', 'name' => FormatHelper::SORT_DATE_JOINED],
            ['id' => 'updated_at', 'name' => FormatHelper::SORT_LAST_UPDATED],
            ['id' => 'name', 'name' => FormatHelper::SORT_NAME],
            ['id' => 'email', 'name' => FormatHelper::SORT_EMAIL]
        ];
    }

    /**
     * Get status filter options
     */
    public function getStatusFilterOptionsProperty(): array
    {
        return [
            ['id' => 'all', 'name' => UserHelper::getFilterLabel('all_status')],
            ['id' => 'active', 'name' => UserHelper::getFilterLabel('active')],
            ['id' => 'inactive', 'name' => UserHelper::getFilterLabel('inactive')]
        ];
    }

    /**
     * Get sort direction options
     */
    public function getSortDirectionOptionsProperty(): array
    {
        return [
            ['id' => 'desc', 'name' => UserHelper::getFilterLabel('newest_to_oldest')],
            ['id' => 'asc', 'name' => UserHelper::getFilterLabel('oldest_to_newest')]
        ];
    }

    /**
     * Get per page options
     */
    public function getPerPageOptionsProperty(): array
    {
        return FormatHelper::getPerPageOptions();
    }

    /**
     * Check if any filter is active
     */
    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
            $this->statusFilter !== 'all' ||
            $this->roleFilter !== 'all' ||
            $this->sortBy['column'] !== 'created_at' ||
            $this->sortBy['direction'] !== 'desc';
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.user.index');
    }
}
