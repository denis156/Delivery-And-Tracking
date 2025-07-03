<?php

namespace App\Livewire\App\Pages\Client;

use App\Models\User;
use App\Models\Client;
use App\Class\Helper\UserHelper;
use App\Class\Helper\ClientHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Title(ClientHelper::PAGE_TITLE_VIEW)]
#[Layout('livewire.layouts.app')]
class View extends Component
{
    use Toast;

    // * ========================================
    // * PROPERTIES
    // * ========================================

    public User $user;
    public ?Client $client;

    // * ========================================
    // * LIFECYCLE HOOKS
    // * ========================================

    public function mount(User $user): void
    {
        // Check if user is a client
        if (!$user->hasRole(UserHelper::ROLE_CLIENT)) {
            $this->error(ClientHelper::ERROR_NOT_CLIENT_FULL, position: FormatHelper::TOAST_POSITION);
            $this->redirect(route('app.client.index'), navigate: true);
            return;
        }

        $this->user = $user->load('client');
        $this->client = $this->user->client;

        if (!$this->client) {
            $this->error(ClientHelper::ERROR_NOT_CLIENT_FULL, position: FormatHelper::TOAST_POSITION);
            $this->redirect(route('app.client.index'), navigate: true);
            return;
        }
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    /**
     * Navigate to edit page
     */
    public function editClient(): void
    {
        $this->redirect(route('app.client.edit', $this->user), navigate: true);
    }

    /**
     * Navigate back to index
     */
    public function backToIndex(): void
    {
        $this->redirect(route('app.client.index'), navigate: true);
    }

    /**
     * Open change status modal
     */
    public function openChangeStatusModal(): void
    {
        $this->dispatch('openChangeStatusModal', $this->user->id);
    }

    /**
     * Open delete client modal
     */
    public function openDeleteModal(): void
    {
        $this->dispatch('openDeleteUserModal', $this->user->id);
    }

    /**
     * Open map modal to show company location
     */
    public function openMapModal(): void
    {
        if ($this->client->company_latitude && $this->client->company_longitude) {
            $this->dispatch('openMapModal', [
                'latitude' => $this->client->company_latitude,
                'longitude' => $this->client->company_longitude,
                'title' => $this->client->company_name,
                'address' => $this->client->company_address
            ]);
        }
    }

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleClientDeleted'
    ];

    public function handleClientDeleted(): void
    {
        $this->success(ClientHelper::TOAST_CLIENT_DELETED, position: FormatHelper::TOAST_POSITION);
        $this->redirect(route('app.client.index'), navigate: true);
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Style)
    // * ========================================

    /**
     * DYNAMIC: Client UI configuration from helper
     */
    #[Computed]
    public function clientUIConfig(): array
    {
        return [
            'icons' => [
                'view' => FormatHelper::getCommonIcon('view'),
                'edit' => FormatHelper::getCommonIcon('edit'),
                'delete' => FormatHelper::getCommonIcon('delete'),
                'back' => FormatHelper::getCommonIcon('back'),
                'user' => UserHelper::getRoleIcon(UserHelper::ROLE_CLIENT),
                'name' => FormatHelper::getCommonIcon('user'),
                'email' => FormatHelper::getCommonIcon('email'),
                'avatar' => 'phosphor.camera',
                'company_name' => ClientHelper::getClientFieldIcon('company_name'),
                'company_code' => ClientHelper::getClientFieldIcon('company_code'),
                'company_address' => ClientHelper::getClientFieldIcon('company_address'),
                'phone' => ClientHelper::getClientFieldIcon('phone'),
                'fax' => ClientHelper::getClientFieldIcon('fax'),
                'tax_id' => ClientHelper::getClientFieldIcon('tax_id'),
                'contact_person' => ClientHelper::getClientFieldIcon('contact_person'),
                'contact_phone' => ClientHelper::getClientFieldIcon('contact_phone'),
                'contact_email' => ClientHelper::getClientFieldIcon('contact_email'),
                'contact_position' => ClientHelper::getClientFieldIcon('contact_position'),
                'coordinates' => ClientHelper::getClientFieldIcon('coordinates'),
                'map' => 'phosphor.map-pin',
                'status' => $this->user->is_active ? UserHelper::getStatusIcon('active') : UserHelper::getStatusIcon('inactive'),
            ],
            'colors' => [
                'client_role' => UserHelper::getRoleColor(UserHelper::ROLE_CLIENT),
                'company_name' => ClientHelper::getClientFieldColor('company_name'),
                'company_code' => ClientHelper::getClientFieldColor('company_code'),
                'company_address' => ClientHelper::getClientFieldColor('company_address'),
                'phone' => ClientHelper::getClientFieldColor('phone'),
                'fax' => ClientHelper::getClientFieldColor('fax'),
                'tax_id' => ClientHelper::getClientFieldColor('tax_id'),
                'contact_person' => ClientHelper::getClientFieldColor('contact_person'),
                'contact_phone' => ClientHelper::getClientFieldColor('contact_phone'),
                'contact_email' => ClientHelper::getClientFieldColor('contact_email'),
                'contact_position' => ClientHelper::getClientFieldColor('contact_position'),
                'coordinates' => ClientHelper::getClientFieldColor('coordinates'),
                'status' => UserHelper::getStatusColor($this->user->is_active),
            ]
        ];
    }

    /**
     * DYNAMIC: User role info from helper
     */
    #[Computed]
    public function userRoleInfo(): array
    {
        return [
            'label' => UserHelper::getRoleLabel(UserHelper::ROLE_CLIENT),
            'color' => UserHelper::getRoleColor(UserHelper::ROLE_CLIENT),
            'icon' => UserHelper::getRoleIcon(UserHelper::ROLE_CLIENT),
        ];
    }

    /**
     * DYNAMIC: User status info from helper
     */
    #[Computed]
    public function userStatusInfo(): array
    {
        return [
            'label' => UserHelper::getStatusLabel($this->user->is_active),
            'color' => UserHelper::getStatusColor($this->user->is_active),
            'icon' => $this->user->is_active ? UserHelper::getStatusIcon('active') : UserHelper::getStatusIcon('inactive'),
        ];
    }

    /**
     * DYNAMIC: Company display info
     */
    #[Computed]
    public function companyDisplayInfo(): array
    {
        return [
            'name' => $this->client->company_name,
            'code' => $this->client->company_code,
            'display_name' => $this->client->company_display_name,
            'address' => $this->client->company_address,
            'short_address' => $this->client->short_address,
        ];
    }

    /**
     * DYNAMIC: Contact information
     */
    #[Computed]
    public function contactInfo(): array
    {
        return [
            'person' => $this->client->contact_person,
            'phone' => $this->client->formatted_contact_phone,
            'email' => $this->client->contact_email,
            'position' => $this->client->contact_position ?: ClientHelper::DEFAULT_EMPTY_VALUE,
        ];
    }

    /**
     * DYNAMIC: Company details
     */
    #[Computed]
    public function companyDetails(): array
    {
        return [
            'phone' => $this->client->formatted_phone,
            'fax' => $this->client->fax ?: ClientHelper::DEFAULT_EMPTY_VALUE,
            'tax_id' => $this->client->formatted_tax_id,
        ];
    }

    /**
     * DYNAMIC: Location information
     */
    #[Computed]
    public function locationInfo(): array
    {
        $hasCoordinates = $this->client->company_latitude && $this->client->company_longitude;

        return [
            'has_coordinates' => $hasCoordinates,
            'coordinates' => $hasCoordinates ? $this->client->formatted_coordinates : ClientHelper::DEFAULT_EMPTY_VALUE,
            'map_url' => $hasCoordinates ? $this->client->map_url : null,
            'is_valid' => $hasCoordinates ? $this->client->hasValidCoordinates() : false,
        ];
    }

    /**
     * DYNAMIC: User activity timestamps
     */
    #[Computed]
    public function userActivity(): array
    {
        return [
            'created_at' => FormatHelper::formatDateTimeForDisplay($this->user->created_at),
            'updated_at' => FormatHelper::formatDateTimeForDisplay($this->user->updated_at),
            'created_relative' => FormatHelper::formatRelativeTime($this->user->created_at),
            'updated_relative' => FormatHelper::formatRelativeTime($this->user->updated_at),
        ];
    }

    /**
     * DYNAMIC: Client statistics and metrics
     */
    #[Computed]
    public function clientMetrics(): array
    {
        // Get delivery orders count (if relationship exists)
        $deliveryOrdersCount = $this->client->deliveryOrders()->count();

        return [
            'delivery_orders_count' => $deliveryOrdersCount,
            'delivery_orders_label' => $deliveryOrdersCount . ' Surat Jalan',
            'status_since' => FormatHelper::formatRelativeTime($this->user->updated_at),
            'member_since' => FormatHelper::formatRelativeTime($this->user->created_at),
        ];
    }

    /**
     * DYNAMIC: Avatar placeholder generation
     */
    #[Computed]
    public function avatarPlaceholder(): string
    {
        return UserHelper::generateAvatarPlaceholder($this->user->name);
    }

    /**
     * DYNAMIC: Action buttons configuration
     */
    #[Computed]
    public function actionButtons(): array
    {
        return [
            'edit' => [
                'show' => true,
                'label' => FormatHelper::LABEL_EDIT,
                'icon' => $this->clientUIConfig['icons']['edit'],
                'color' => 'primary',
                'action' => 'editClient'
            ],
            'status' => [
                'show' => true,
                'label' => $this->user->is_active ? 'Nonaktifkan' : 'Aktifkan',
                'icon' => $this->clientUIConfig['icons']['status'],
                'color' => $this->user->is_active ? 'warning' : 'success',
                'action' => 'openChangeStatusModal'
            ],
            'delete' => [
                'show' => true,
                'label' => FormatHelper::LABEL_DELETE,
                'icon' => $this->clientUIConfig['icons']['delete'],
                'color' => 'error',
                'action' => 'openDeleteModal'
            ],
            'map' => [
                'show' => $this->locationInfo['has_coordinates'],
                'label' => 'Lihat Peta',
                'icon' => $this->clientUIConfig['icons']['map'],
                'color' => 'info',
                'action' => 'openMapModal'
            ]
        ];
    }

    /**
     * DYNAMIC: Breadcrumb navigation
     */
    #[Computed]
    public function breadcrumbs(): array
    {
        return [
            ['label' => 'Dashboard', 'route' => 'app.dashboard'],
            ['label' => ClientHelper::PAGE_TITLE_INDEX, 'route' => 'app.client.index'],
            ['label' => $this->client->company_name, 'current' => true],
        ];
    }

    /**
     * DYNAMIC: Tab sections for view
     */
    #[Computed]
    public function viewTabs(): array
    {
        return [
            'overview' => [
                'label' => 'Ringkasan',
                'icon' => 'phosphor.eye',
                'active' => true
            ],
            'company' => [
                'label' => 'Data Perusahaan',
                'icon' => 'phosphor.buildings',
                'active' => false
            ],
            'contact' => [
                'label' => 'Kontak',
                'icon' => 'phosphor.user-circle',
                'active' => false
            ],
            'location' => [
                'label' => 'Lokasi',
                'icon' => 'phosphor.map-pin',
                'active' => false
            ],
            'activity' => [
                'label' => 'Aktivitas',
                'icon' => 'phosphor.clock',
                'active' => false
            ]
        ];
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.client.view');
    }
}
