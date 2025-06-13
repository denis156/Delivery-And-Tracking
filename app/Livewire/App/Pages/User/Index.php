<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Daftar Pengguna')]
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
    public int $perPage = 12;

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
        $this->perPage = 12;
        $this->resetPage();
        $this->success('Filter berhasil dibersihkan.', position: 'toast-bottom');
    }

    /**
     * Handle after user deleted - refresh and check pagination
     */
    public function handleUserDeleted(): void
    {
        if ($this->users()->count() === 0 && $this->users()->currentPage() > 1) {
            $this->resetPage();
        }
        $this->success('User berhasil dihapus.', position: 'toast-bottom');
    }

    /**
     * Handle after user created (refresh dari halaman lain)
     */
    public function handleUserCreated(): void
    {
        $this->resetPage();
        $this->success('User berhasil ditambahkan.', position: 'toast-bottom');
    }

    /**
     * Open view user page
     */
    public function viewUser(int $userId): void
    {
        $this->redirect(route('app.user.view', $userId), navigate: true);
    }

    /**
     * Open toggle status modal
     */
    public function openToggleModal(int $userId): void
    {
        $this->dispatch('openToggleStatusModal', $userId);
    }

    /**
     * Open delete user modal
     */
    public function openDeleteModal(int $userId): void
    {
        $this->dispatch('openDeleteUserModal', $userId);
    }

    // * ========================================
    // * DATA METHODS (Using Model Scopes and Accessors)
    // * ========================================

    /**
     * Get users with filters and search (exclude drivers)
     */
    public function users()
    {
        $query = User::excludeDrivers()
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
                $q->where('role', $this->roleFilter);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate($this->perPage);
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Standards)
    // * ========================================

    /**
     * Get available roles for filter (exclude drivers)
     */
    public function getRolesProperty(): array
    {
        return collect([
            'all' => 'Semua Role'
        ])->merge(User::getRolesExcludeDrivers())->toArray();
    }

    /**
     * Get user statistics
     */
    public function getUserStatsProperty(): array
    {
        return [
            'totalUsers' => User::excludeDrivers()->count(),
            'activeUsers' => User::excludeDrivers()->active()->count(),
            'inactiveUsers' => User::excludeDrivers()->inactive()->count(),
            'deletedUsers' => User::onlyTrashed()->excludeDrivers()->count(),
        ];
    }

    /**
     * Get sort options for filter
     */
    public function getSortOptionsProperty(): array
    {
        return [
            ['id' => 'created_at', 'name' => 'Tanggal Bergabung'],
            ['id' => 'updated_at', 'name' => 'Terakhir Diperbarui'],
            ['id' => 'name', 'name' => 'Nama'],
            ['id' => 'email', 'name' => 'Email']
        ];
    }

    /**
     * Get status filter options
     */
    public function getStatusFilterOptionsProperty(): array
    {
        return [
            ['id' => 'all', 'name' => 'Semua Status'],
            ['id' => 'active', 'name' => 'Aktif'],
            ['id' => 'inactive', 'name' => 'Nonaktif']
        ];
    }

    /**
     * Get sort direction options
     */
    public function getSortDirectionOptionsProperty(): array
    {
        return [
            ['id' => 'desc', 'name' => 'Terbaru ke Lama / Z ke A'],
            ['id' => 'asc', 'name' => 'Lama ke Terbaru / A ke Z']
        ];
    }

    /**
     * Get per page options
     */
    public function getPerPageOptionsProperty(): array
    {
        return [
            ['value' => 6, 'label' => '6'],
            ['value' => 12, 'label' => '12'],
            ['value' => 24, 'label' => '24'],
            ['value' => 50, 'label' => '50']
        ];
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
        $users = $this->users();

        return view('livewire.app.pages.user.index', [
            'users' => $users,
            'roles' => $this->roles,
            'userStats' => $this->userStats,
            'sortOptions' => $this->sortOptions,
            'statusFilterOptions' => $this->statusFilterOptions,
            'sortDirectionOptions' => $this->sortDirectionOptions,
            'perPageOptions' => $this->perPageOptions,
            'hasActiveFilters' => $this->hasActiveFilters,
        ]);
    }
}
