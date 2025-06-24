<?php

namespace App\Livewire\App\Pages\Permission;

use Spatie\Permission\Models\Permission;
use Mary\Traits\Toast;
use App\Class\StatusHelper;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Daftar Permission')]
#[Layout('livewire.layouts.app')]
class Index extends Component
{
    use Toast, WithPagination;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public string $search = '';
    public bool $drawer = false;
    public string $categoryFilter = 'all';
    public string $guardFilter = 'all';
    public string $assignmentFilter = 'all';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = 12;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'permissionDeleted' => 'handlePermissionDeleted',
        'permissionAssigned' => '$refresh',
        'permissionCreated' => 'handlePermissionCreated'
    ];

    // * ========================================
    // * LIVEWIRE HOOKS
    // * ========================================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedGuardFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignmentFilter(): void
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

    public function clear(): void
    {
        $this->reset(['search', 'categoryFilter', 'guardFilter', 'assignmentFilter']);
        $this->sortBy = ['column' => 'created_at', 'direction' => 'desc'];
        $this->perPage = 12;
        $this->resetPage();
        $this->success('Filter berhasil dibersihkan.', position: 'toast-top toast-end');
    }

    public function handlePermissionDeleted(): void
    {
        if ($this->permissions()->count() === 0 && $this->permissions()->currentPage() > 1) {
            $this->resetPage();
        }
        $this->success('Permission berhasil dihapus.', position: 'toast-top toast-end');
    }

    public function handlePermissionCreated(): void
    {
        $this->resetPage();
        $this->success('Permission berhasil ditambahkan.', position: 'toast-top toast-end');
    }

    public function viewPermission(int $permissionId): void
    {
        $this->redirect(route('app.permission.view', $permissionId), navigate: true);
    }

    public function openAssignModal(int $permissionId): void
    {
        $this->dispatch('openAssignPermissionModal', $permissionId);
    }

    public function openDeleteModal(int $permissionId): void
    {
        $this->dispatch('openDeletePermissionModal', $permissionId);
    }

    // * ========================================
    // * DATA METHODS
    // * ========================================

    public function permissions()
    {
        $query = Permission::with(['roles', 'users'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->guardFilter !== 'all', function ($q) {
                $q->where('guard_name', $this->guardFilter);
            })
            ->when($this->assignmentFilter !== 'all', function ($q) {
                if ($this->assignmentFilter === 'with_roles') {
                    $q->whereHas('roles');
                } else {
                    $q->whereDoesntHave('roles');
                }
            })
            ->when($this->categoryFilter !== 'all', function ($q) {
                $category = $this->categoryFilter;
                $q->where(function ($query) use ($category) {
                    switch ($category) {
                        case 'user':
                            $query->where('name', 'like', '%user%');
                            break;
                        case 'role':
                            $query->where('name', 'like', '%role%');
                            break;
                        case 'permission':
                            $query->where('name', 'like', '%permission%');
                            break;
                        case 'system':
                            $query->where(function ($q) {
                                $q->where('name', 'like', '%system%')
                                  ->orWhere('name', 'like', '%admin%');
                            });
                            break;
                        case 'delivery':
                            $query->where(function ($q) {
                                $q->where('name', 'like', '%delivery%')
                                  ->orWhere('name', 'like', '%order%');
                            });
                            break;
                        case 'driver':
                            $query->where('name', 'like', '%driver%');
                            break;
                    }
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate($this->perPage);
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

    public function getCategoriesProperty(): array
    {
        return [
            'all' => 'Semua Kategori',
            'user' => 'User Management',
            'role' => 'Role Management',
            'permission' => 'Permission Management',
            'system' => 'System Administration',
            'delivery' => 'Delivery Management',
            'driver' => 'Driver Management',
        ];
    }

    public function getGuardsProperty(): array
    {
        return [
            'all' => 'Semua Guard',
            'web' => 'Web',
            'api' => 'API',
        ];
    }

    public function getPermissionStatsProperty(): array
    {
        return [
            'total' => Permission::count(),
            'with_roles' => Permission::whereHas('roles')->count(),
            'without_roles' => Permission::whereDoesntHave('roles')->count(),
            'by_guard' => Permission::selectRaw('guard_name, COUNT(*) as count')
                ->groupBy('guard_name')
                ->pluck('count', 'guard_name')
                ->toArray(),
        ];
    }

    public function getSortOptionsProperty(): array
    {
        return [
            ['id' => 'created_at', 'name' => 'Tanggal Dibuat'],
            ['id' => 'updated_at', 'name' => 'Terakhir Diperbarui'],
            ['id' => 'name', 'name' => 'Nama Permission'],
        ];
    }

    public function getAssignmentFilterOptionsProperty(): array
    {
        return [
            ['id' => 'all', 'name' => 'Semua Permission'],
            ['id' => 'with_roles', 'name' => 'Yang Sudah Di-assign'],
            ['id' => 'without_roles', 'name' => 'Belum Di-assign'],
        ];
    }

    public function getSortDirectionOptionsProperty(): array
    {
        return [
            ['id' => 'desc', 'name' => 'Terbaru ke Lama / Z ke A'],
            ['id' => 'asc', 'name' => 'Lama ke Terbaru / A ke Z']
        ];
    }

    public function getPerPageOptionsProperty(): array
    {
        return [
            ['value' => 6, 'label' => '6'],
            ['value' => 12, 'label' => '12'],
            ['value' => 24, 'label' => '24'],
            ['value' => 50, 'label' => '50']
        ];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
               $this->categoryFilter !== 'all' ||
               $this->guardFilter !== 'all' ||
               $this->assignmentFilter !== 'all' ||
               $this->sortBy['column'] !== 'created_at' ||
               $this->sortBy['direction'] !== 'desc';
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    public function canPermissionBeDeleted(Permission $permission): bool
    {
        return StatusHelper::canPermissionBeDeleted($permission);
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        $permissions = $this->permissions();

        return view('livewire.app.pages.permission.index', [
            'permissions' => $permissions,
            'categories' => $this->categories,
            'guards' => $this->guards,
            'permissionStats' => $this->permissionStats,
            'sortOptions' => $this->sortOptions,
            'assignmentFilterOptions' => $this->assignmentFilterOptions,
            'sortDirectionOptions' => $this->sortDirectionOptions,
            'perPageOptions' => $this->perPageOptions,
            'hasActiveFilters' => $this->hasActiveFilters,
        ]);
    }
}
