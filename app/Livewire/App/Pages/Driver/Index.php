<?php

namespace App\Livewire\App\Pages\Driver;

use App\Models\User;
use App\Models\Driver;
use App\Class\Helper\UserHelper;
use App\Class\Helper\DriverHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Title('Data Sopir')]
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
    public string $licenseFilter = 'all';
    public string $licenseStatusFilter = 'all';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = 12;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleDriverDeleted',
        'userCreated' => 'handleDriverCreated'
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

    public function updatedLicenseFilter(): void
    {
        $this->resetPage();
    }

    public function updatedLicenseStatusFilter(): void
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
        $this->reset(['search', 'statusFilter', 'licenseFilter', 'licenseStatusFilter']);
        $this->sortBy = ['column' => 'created_at', 'direction' => 'desc'];
        $this->perPage = 12;
        $this->resetPage();
        $this->success('Filter berhasil dibersihkan.', position: 'toast-top toast-end');
    }

    /**
     * Handle after driver deleted - refresh and check pagination
     */
    public function handleDriverDeleted(): void
    {
        if ($this->drivers->count() === 0 && $this->drivers->currentPage() > 1) {
            $this->resetPage();
        }
        $this->success('Driver berhasil dihapus.', position: 'toast-top toast-end');
    }

    /**
     * Handle after driver created (refresh dari halaman lain)
     */
    public function handleDriverCreated(): void
    {
        $this->resetPage();
        $this->success('Driver berhasil ditambahkan.', position: 'toast-top toast-end');
    }

    /**
     * Open view driver page
     */
    public function viewDriver(int $userId): void
    {
        $this->redirect(route('app.driver.view', $userId), navigate: true);
    }

    /**
     * Open change status modal
     */
    public function openChangeStatusModal(int $userId): void
    {
        $this->dispatch('openChangeStatusModal', $userId);
    }

    /**
     * Open delete driver modal
     */
    public function openDeleteModal(int $userId): void
    {
        $this->dispatch('openDeleteUserModal', $userId);
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Standards)
    // * ========================================

    /**
     * DYNAMIC: Get drivers with filters using helper for UI config
     */
    #[Computed]
    public function drivers()
    {
        $query = User::onlyDrivers()
            ->with(['driver'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhereHas('driver', function ($driverQuery) {
                            $driverQuery->where('license_number', 'like', "%{$this->search}%")
                                ->orWhere('vehicle_plate', 'like', "%{$this->search}%")
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
            ->when($this->licenseFilter !== 'all', function ($q) {
                $q->whereHas('driver', function ($query) {
                    $query->where('license_type', $this->licenseFilter);
                });
            })
            ->when($this->licenseStatusFilter !== 'all', function ($q) {
                $q->whereHas('driver', function ($query) {
                    if ($this->licenseStatusFilter === 'expired') {
                        $query->where('license_expiry', '<', now());
                    } elseif ($this->licenseStatusFilter === 'expiring_soon') {
                        $query->whereBetween('license_expiry', [now(), now()->addDays(90)]);
                    } else {
                        $query->where('license_expiry', '>', now()->addDays(90));
                    }
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate($this->perPage);
    }

    /**
     * DYNAMIC: License types with helper integration
     */
    #[Computed]
    public function licenseTypes(): array
    {
        return collect([
            'all' => 'Semua Jenis SIM'
        ])->merge(DriverHelper::getAllLicenseTypes())->toArray();
    }

    /**
     * DYNAMIC: Driver statistics with tooltips
     */
    #[Computed]
    public function driverStats(): array
    {
        // Base query builder - hanya buat sekali
        $baseQuery = User::onlyDrivers();

        // Gunakan clone untuk setiap perhitungan agar query fresh
        $totalDrivers = (clone $baseQuery)->count();
        $activeDrivers = (clone $baseQuery)->active()->count();
        $inactiveDrivers = (clone $baseQuery)->inactive()->count();
        $expiredLicenses = (clone $baseQuery)->whereHas('driver', function ($q) {
            $q->where('license_expiry', '<', now());
        })->count();

        return [
            'totalDrivers' => $totalDrivers,
            'activeDrivers' => $activeDrivers,
            'inactiveDrivers' => $inactiveDrivers,
            'expiredLicenses' => $expiredLicenses,
            'totalDriversTooltip' => "Total {$totalDrivers} driver terdaftar ({$activeDrivers} aktif, {$inactiveDrivers} nonaktif)",
            'activeDriversTooltip' => $totalDrivers > 0 ? number_format(($activeDrivers / $totalDrivers) * 100, 1) . '% dari total driver dalam status aktif' : '0% dari total driver dalam status aktif',
            'inactiveDriversTooltip' => $totalDrivers > 0 ? number_format(($inactiveDrivers / $totalDrivers) * 100, 1) . '% dari total driver dalam status nonaktif' : '0% dari total driver dalam status nonaktif',
            'expiredLicensesTooltip' => "{$expiredLicenses} driver dengan SIM yang sudah kadaluarsa",
        ];
    }

    /**
     * DYNAMIC: Driver UI configuration from helper
     */
    #[Computed]
    public function driverUIConfig(): array
    {
        return [
            'icons' => [
                'user' => UserHelper::getRoleIcon(UserHelper::ROLE_DRIVER),
                'license' => DriverHelper::getDriverFieldIcon('license_type'),
                'vehicle' => DriverHelper::getDriverFieldIcon('vehicle_type'),
                'phone' => DriverHelper::getDriverFieldIcon('phone'),
                'email' => 'phosphor.envelope-simple',
                'location' => DriverHelper::getDriverFieldIcon('address'),
            ],
            'colors' => [
                'driver_role' => UserHelper::getRoleColor(UserHelper::ROLE_DRIVER),
                'license' => DriverHelper::getDriverFieldColor('license_type'),
                'vehicle' => DriverHelper::getDriverFieldColor('vehicle_type'),
                'phone' => DriverHelper::getDriverFieldColor('phone'),
            ],
            'labels' => [
                'driver_role' => UserHelper::getRoleLabel(UserHelper::ROLE_DRIVER),
            ]
        ];
    }

    /**
     * DYNAMIC: Pagination info for display
     */
    #[Computed]
    public function paginationInfo(): array
    {
        $drivers = $this->drivers;

        return [
            'current' => "Menampilkan <span class=\"font-semibold\">" . ($drivers->firstItem() ?? 0) . "</span> - <span class=\"font-semibold\">" . ($drivers->lastItem() ?? 0) . "</span> dari <span class=\"font-semibold\">" . $drivers->total() . "</span> driver",
            'simple' => "Menampilkan <span class=\"font-semibold\">" . $drivers->count() . "</span> driver",
            'search' => "untuk pencarian \"<span class=\"font-semibold text-primary\">{$this->search}</span>\"",
            'mobile' => "Halaman " . $drivers->currentPage() . " dari " . $drivers->lastPage(),
        ];
    }

    /**
     * DYNAMIC: Pagination pages for navigation
     */
    #[Computed]
    public function paginationPages(): array
    {
        $drivers = $this->drivers;
        $pages = [];
        $maxPages = 4;
        $start = max($drivers->currentPage() - intval($maxPages / 2), 1);
        $end = min($start + $maxPages, $drivers->lastPage());
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
                'current' => $i == $drivers->currentPage()
            ];
        }

        // Add last page and dots if needed
        if ($end < $drivers->lastPage()) {
            if ($end < $drivers->lastPage() - 1) {
                $pages[] = ['type' => 'dots'];
            }
            $pages[] = ['type' => 'page', 'page' => $drivers->lastPage(), 'current' => false];
        }

        return $pages;
    }

    /**
     * DYNAMIC: License filter options with helper integration
     */
    #[Computed]
    public function licenseFilterOptions(): array
    {
        return collect([
            ['id' => 'all', 'name' => 'Semua Jenis SIM']
        ])->merge(
            collect(DriverHelper::getAllLicenseTypes())->map(fn($label, $value) => [
                'id' => $value,
                'name' => $label
            ])->values()
        )->toArray();
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
                'icon' => 'phosphor.magnifying-glass',
                'label' => 'Pencarian',
                'value' => $this->search
            ];
        }

        if ($this->statusFilter !== 'all') {
            $filters[] = [
                'icon' => 'phosphor.check-circle',
                'label' => 'Status',
                'value' => $this->statusFilter === 'active' ? 'Aktif' : 'Nonaktif'
            ];
        }

        if ($this->licenseFilter !== 'all') {
            $licenseLabel = collect($this->licenseFilterOptions)
                ->where('id', $this->licenseFilter)
                ->first()['name'] ?? 'Unknown';

            $filters[] = [
                'icon' => 'phosphor.identification-card',
                'label' => 'Jenis SIM',
                'value' => $licenseLabel
            ];
        }

        if ($this->licenseStatusFilter !== 'all') {
            $licenseStatusLabel = collect($this->licenseStatusFilterOptions)
                ->where('id', $this->licenseStatusFilter)
                ->first()['name'] ?? 'Unknown';

            $filters[] = [
                'icon' => 'phosphor.warning',
                'label' => 'Status SIM',
                'value' => $licenseStatusLabel
            ];
        }

        if ($this->sortBy['column'] !== 'created_at' || $this->sortBy['direction'] !== 'desc') {
            $sortLabel = collect($this->sortOptions)
                ->where('id', $this->sortBy['column'])
                ->first()['name'] ?? 'Unknown';

            $filters[] = [
                'icon' => 'phosphor.sort-ascending',
                'label' => 'Urutan',
                'value' => $sortLabel . ' (' . ($this->sortBy['direction'] === 'desc' ? 'Terbaru ke Lama' : 'Lama ke Terbaru') . ')'
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
            ['id' => 'all', 'name' => 'Semua Status'],
            ['id' => 'active', 'name' => 'Aktif'],
            ['id' => 'inactive', 'name' => 'Nonaktif']
        ];
    }

    /**
     * Get license status filter options
     */
    #[Computed]
    public function licenseStatusFilterOptions(): array
    {
        return [
            ['id' => 'all', 'name' => 'Semua Status SIM'],
            ['id' => 'valid', 'name' => 'SIM Berlaku'],
            ['id' => 'expiring_soon', 'name' => 'Akan Kadaluarsa'],
            ['id' => 'expired', 'name' => 'Kadaluarsa']
        ];
    }

    /**
     * Get sort direction options
     */
    #[Computed]
    public function sortDirectionOptions(): array
    {
        return [
            ['id' => 'desc', 'name' => 'Terbaru ke Lama / Z ke A'],
            ['id' => 'asc', 'name' => 'Lama ke Terbaru / A ke Z']
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
            $this->licenseFilter !== 'all' ||
            $this->licenseStatusFilter !== 'all' ||
            $this->sortBy['column'] !== 'created_at' ||
            $this->sortBy['direction'] !== 'desc';
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.driver.index');
    }
}
