<?php

namespace App\Livewire\App\Pages\Client;

use App\Models\User;
use App\Models\Client;
use App\Class\Helper\UserHelper;
use App\Class\Helper\ClientHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Title(ClientHelper::PAGE_TITLE_INDEX)]
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
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = FormatHelper::DEFAULT_PER_PAGE;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleClientDeleted',
        'userCreated' => 'handleClientCreated'
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
        $this->reset(['search', 'statusFilter']);
        $this->sortBy = ['column' => 'created_at', 'direction' => 'desc'];
        $this->perPage = FormatHelper::DEFAULT_PER_PAGE;
        $this->resetPage();
        $this->success(ClientHelper::TOAST_FILTER_CLEARED, position: FormatHelper::TOAST_POSITION);
    }

    /**
     * Handle after client deleted - refresh and check pagination
     */
    public function handleClientDeleted(): void
    {
        if ($this->clients->count() === 0 && $this->clients->currentPage() > 1) {
            $this->resetPage();
        }
        $this->success(ClientHelper::TOAST_CLIENT_DELETED, position: FormatHelper::TOAST_POSITION);
    }

    /**
     * Handle after client created (refresh dari halaman lain)
     */
    public function handleClientCreated(): void
    {
        $this->resetPage();
        $this->success(ClientHelper::TOAST_CLIENT_ADDED, position: FormatHelper::TOAST_POSITION);
    }

    /**
     * Open view client page
     */
    public function viewClient(int $userId): void
    {
        $this->redirect(route('app.client.view', $userId), navigate: true);
    }

    /**
     * Open change status modal
     */
    public function openChangeStatusModal(int $userId): void
    {
        $this->dispatch('openChangeStatusModal', $userId);
    }

    /**
     * Open delete client modal
     */
    public function openDeleteModal(int $userId): void
    {
        $this->dispatch('openDeleteUserModal', $userId);
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Standards)
    // * ========================================

    /**
     * DYNAMIC: Get clients with filters using helper for UI config
     */
    #[Computed]
    public function clients()
    {
        $query = User::onlyClients()
            ->with(['client'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhereHas('client', function ($clientQuery) {
                            $clientQuery->where('company_name', 'like', "%{$this->search}%")
                                ->orWhere('company_code', 'like', "%{$this->search}%")
                                ->orWhere('contact_person', 'like', "%{$this->search}%")
                                ->orWhere('phone', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                if ($this->statusFilter === 'active') {
                    $q->active();
                } else {
                    $q->inactive();
                }
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate($this->perPage);
    }

    /**
     * DYNAMIC: Client statistics with tooltips
     */
    #[Computed]
    public function clientStats(): array
    {
        // Base query builder - hanya buat sekali
        $baseQuery = User::onlyClients();

        // Gunakan clone untuk setiap perhitungan agar query fresh
        $totalClients = (clone $baseQuery)->count();
        $activeClients = (clone $baseQuery)->active()->count();
        $inactiveClients = (clone $baseQuery)->inactive()->count();

        return [
            'totalClients' => $totalClients,
            'activeClients' => $activeClients,
            'inactiveClients' => $inactiveClients,
            'totalClientsTooltip' => "Total {$totalClients} client terdaftar ({$activeClients} aktif, {$inactiveClients} nonaktif)",
            'activeClientsTooltip' => $totalClients > 0 ? number_format(($activeClients / $totalClients) * 100, 1) . '% dari total client dalam status aktif' : '0% dari total client dalam status aktif',
            'inactiveClientsTooltip' => $totalClients > 0 ? number_format(($inactiveClients / $totalClients) * 100, 1) . '% dari total client dalam status nonaktif' : '0% dari total client dalam status nonaktif',
        ];
    }

    /**
     * DYNAMIC: Client UI configuration from helper
     */
    #[Computed]
    public function clientUIConfig(): array
    {
        return [
            'icons' => [
                'user' => UserHelper::getRoleIcon(UserHelper::ROLE_CLIENT),
                'company' => ClientHelper::getClientFieldIcon('company_name'),
                'company_name' => ClientHelper::getClientFieldIcon('company_name'),
                'company_code' => ClientHelper::getClientFieldIcon('company_code'),
                'company_address' => ClientHelper::getClientFieldIcon('company_address'),
                'code' => ClientHelper::getClientFieldIcon('company_code'),
                'phone' => ClientHelper::getClientFieldIcon('phone'),
                'email' => FormatHelper::getCommonIcon('email'),
                'location' => ClientHelper::getClientFieldIcon('company_address'),
                'contact' => ClientHelper::getClientFieldIcon('contact_person'),
                'contact_person' => ClientHelper::getClientFieldIcon('contact_person'),
                'search' => FormatHelper::getCommonIcon('search'),
                'filter' => FormatHelper::getCommonIcon('filter'),
                'add' => FormatHelper::getCommonIcon('add'),
                'view' => FormatHelper::getCommonIcon('view'),
                'edit' => FormatHelper::getCommonIcon('edit'),
                'delete' => FormatHelper::getCommonIcon('delete'),
                'menu' => FormatHelper::getCommonIcon('menu'),
                'success' => FormatHelper::getCommonIcon('success'),
                'error' => FormatHelper::getCommonIcon('error'),
                'warning' => FormatHelper::getCommonIcon('warning'),
                'close' => FormatHelper::getCommonIcon('close'),
                'reset' => FormatHelper::getCommonIcon('reset'),
            ],
            'colors' => [
                'client_role' => UserHelper::getRoleColor(UserHelper::ROLE_CLIENT),
                'company' => ClientHelper::getClientFieldColor('company_name'),
                'company_name' => ClientHelper::getClientFieldColor('company_name'),
                'company_code' => ClientHelper::getClientFieldColor('company_code'),
                'company_address' => ClientHelper::getClientFieldColor('company_address'),
                'code' => ClientHelper::getClientFieldColor('company_code'),
                'phone' => ClientHelper::getClientFieldColor('phone'),
                'contact_person' => ClientHelper::getClientFieldColor('contact_person'),
            ],
            'labels' => [
                'client_role' => UserHelper::getRoleLabel(UserHelper::ROLE_CLIENT),
            ]
        ];
    }

    /**
     * DYNAMIC: Pagination info for display
     */
    #[Computed]
    public function paginationInfo(): array
    {
        $clients = $this->clients;

        return [
            'current' => "Menampilkan " . ($clients->firstItem() ?? 0) . " - " . ($clients->lastItem() ?? 0) . " dari " . $clients->total() . " client",
            'simple' => "Menampilkan " . $clients->count() . " client",
            'search' => "untuk pencarian \"{$this->search}\"",
            'mobile' => "Halaman " . $clients->currentPage() . " dari " . $clients->lastPage(),
        ];
    }

    /**
     * DYNAMIC: Pagination pages for navigation
     */
    #[Computed]
    public function paginationPages(): array
    {
        $clients = $this->clients;
        $pages = [];
        $maxPages = 4;
        $start = max($clients->currentPage() - intval($maxPages / 2), 1);
        $end = min($start + $maxPages, $clients->lastPage());
        $start = max($end - $maxPages, 1);

        // Add first page and dots if needed
        if ($start > 1) {
            $pages[] = ['type' => 'page', 'page' => 1, 'current' => false];
            if ($start > 2) {
                $pages[] = ['type' => 'dots'];
            }
        }

        // Add main pages
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = [
                'type' => 'page',
                'page' => $i,
                'current' => $i == $clients->currentPage()
            ];
        }

        // Add last page and dots if needed
        if ($end < $clients->lastPage()) {
            if ($end < $clients->lastPage() - 1) {
                $pages[] = ['type' => 'dots'];
            }
            $pages[] = ['type' => 'page', 'page' => $clients->lastPage(), 'current' => false];
        }

        return $pages;
    }

    /**
     * DYNAMIC: Active filters info for display
     */
    #[Computed]
    public function activeFiltersInfo(): array
    {
        $filters = [];

        if ($this->search) {
            $filters[] = [
                'icon' => FormatHelper::getCommonIcon('search'),
                'label' => 'Pencarian',
                'value' => $this->search
            ];
        }

        if ($this->statusFilter !== 'all') {
            $filters[] = [
                'icon' => UserHelper::getStatusIcon('active'),
                'label' => 'Status',
                'value' => $this->statusFilter === 'active' ? UserHelper::getFilterLabel('active') : UserHelper::getFilterLabel('inactive')
            ];
        }

        if ($this->sortBy['column'] !== 'created_at' || $this->sortBy['direction'] !== 'desc') {
            $sortLabel = collect($this->sortOptions)
                ->where('id', $this->sortBy['column'])
                ->first()['name'] ?? 'Unknown';

            $filters[] = [
                'icon' => FormatHelper::getCommonIcon('sort_asc'),
                'label' => 'Urutan',
                'value' => $sortLabel . ' (' . ($this->sortBy['direction'] === 'desc' ? UserHelper::getFilterLabel('newest_first') : UserHelper::getFilterLabel('oldest_first')) . ')'
            ];
        }

        return $filters;
    }

    /**
     * Get sort options for filter
     */
    #[Computed]
    public function sortOptions(): array
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
    #[Computed]
    public function statusFilterOptions(): array
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
    #[Computed]
    public function sortDirectionOptions(): array
    {
        return [
            ['id' => 'desc', 'name' => UserHelper::getFilterLabel('newest_to_oldest')],
            ['id' => 'asc', 'name' => UserHelper::getFilterLabel('oldest_to_newest')]
        ];
    }

    /**
     * Get per page options
     */
    #[Computed]
    public function perPageOptions(): array
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
    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== '' ||
            $this->statusFilter !== 'all' ||
            $this->sortBy['column'] !== 'created_at' ||
            $this->sortBy['direction'] !== 'desc';
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.client.index');
    }
}