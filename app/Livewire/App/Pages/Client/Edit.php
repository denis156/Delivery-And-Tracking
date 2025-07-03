<?php

namespace App\Livewire\App\Pages\Client;

use App\Models\User;
use App\Models\Client;
use App\Class\Helper\UserHelper;
use App\Class\Helper\ClientHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

#[Title(ClientHelper::PAGE_TITLE_EDIT)]
#[Layout('livewire.layouts.app')]
class Edit extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * PROPERTIES
    // * ========================================

    public User $user;
    public ?Client $client;

    // * ========================================
    // * USER PROPERTIES
    // * ========================================

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|min:' . FormatHelper::MIN_PASSWORD_LENGTH . '|regex:' . FormatHelper::PASSWORD_REGEX)]
    public string $password = '';

    #[Validate('nullable|string|min:' . FormatHelper::MIN_PASSWORD_LENGTH . '|same:password')]
    public string $password_confirmation = '';

    #[Validate('nullable|image|max:' . FormatHelper::MAX_AVATAR_SIZE)]
    public $avatar;

    public bool $is_active = true;

    // * ========================================
    // * CLIENT PROPERTIES
    // * ========================================

    #[Validate('required|string|max:255')]
    public string $company_name = '';

    #[Validate('nullable|string|max:20')]
    public string $company_code = '';

    #[Validate('required|string')]
    public string $company_address = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|string|max:20')]
    public string $fax = '';

    #[Validate('nullable|string|max:30')]
    public string $tax_id = '';

    #[Validate('required|string|max:255')]
    public string $contact_person = '';

    #[Validate('required|string|max:20')]
    public string $contact_phone = '';

    #[Validate('required|string|email|max:255')]
    public string $contact_email = '';

    #[Validate('nullable|string|max:100')]
    public string $contact_position = '';

    #[Validate('nullable|numeric|between:-90,90')]
    public ?string $company_latitude = null;

    #[Validate('nullable|numeric|between:-180,180')]
    public ?string $company_longitude = null;

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

        // Load user data
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->is_active = $this->user->is_active;

        // Load client data
        $this->company_name = $this->client->company_name;
        $this->company_code = $this->client->company_code;
        $this->company_address = $this->client->company_address;
        $this->phone = $this->client->phone;
        $this->fax = $this->client->fax ?? '';
        $this->tax_id = $this->client->tax_id ?? '';
        $this->contact_person = $this->client->contact_person;
        $this->contact_phone = $this->client->contact_phone;
        $this->contact_email = $this->client->contact_email;
        $this->contact_position = $this->client->contact_position ?? '';
        $this->company_latitude = $this->client->company_latitude ? (string)$this->client->company_latitude : null;
        $this->company_longitude = $this->client->company_longitude ? (string)$this->client->company_longitude : null;
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function save(): void
    {
        // Validate email uniqueness excluding current user
        $this->validateOnly('email', [
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id
        ]);

        // Validate company code uniqueness excluding current client
        if (!empty($this->company_code)) {
            $this->validateOnly('company_code', [
                'company_code' => 'nullable|string|max:20|unique:clients,company_code,' . $this->client->id
            ]);
        }

        $this->validate();

        try {
            // Validate tax ID format if provided
            if (!empty($this->tax_id) && !ClientHelper::isValidTaxId($this->tax_id)) {
                $this->error('Format NPWP tidak valid. Gunakan format: XX.XXX.XXX.X-XXX.XXX', position: FormatHelper::TOAST_POSITION);
                return;
            }

            // Validate coordinates if provided
            if (!empty($this->company_latitude) && !empty($this->company_longitude)) {
                if (!ClientHelper::isValidIndonesianCoordinates((float)$this->company_latitude, (float)$this->company_longitude)) {
                    $this->error('Koordinat tidak valid untuk wilayah Indonesia', position: FormatHelper::TOAST_POSITION);
                    return;
                }
            }

            // Update user data
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'is_active' => $this->is_active,
            ];

            // Handle avatar upload
            if ($this->avatar) {
                $userData['avatar_url'] = $this->avatar->store('avatars', 'public');
            }

            // Handle password update
            if (!empty($this->password)) {
                $userData['password'] = bcrypt($this->password);
            }

            $this->user->update($userData);

            // Update client data
            $this->client->update([
                'company_name' => $this->company_name,
                'company_code' => $this->company_code,
                'company_address' => $this->company_address,
                'phone' => $this->phone,
                'fax' => $this->fax,
                'tax_id' => $this->tax_id,
                'contact_person' => $this->contact_person,
                'contact_phone' => $this->contact_phone,
                'contact_email' => $this->contact_email,
                'contact_position' => $this->contact_position,
                'company_latitude' => !empty($this->company_latitude) ? (float)$this->company_latitude : null,
                'company_longitude' => !empty($this->company_longitude) ? (float)$this->company_longitude : null,
            ]);

            $this->success(ClientHelper::TOAST_CLIENT_UPDATED, position: FormatHelper::TOAST_POSITION);
            $this->dispatch('userStatusUpdated');

            $this->redirect(route('app.client.view', $this->user), navigate: true);

        } catch (\Exception $e) {
            $this->error(ClientHelper::ERROR_SAVE_FAILED . ': ' . $e->getMessage(), position: FormatHelper::TOAST_POSITION);
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('app.client.view', $this->user), navigate: true);
    }

    public function toggleUserStatus(): void
    {
        $this->is_active = !$this->is_active;
        $this->success(
            $this->is_active ? ClientHelper::TOAST_STATUS_CHANGED_ACTIVE : ClientHelper::TOAST_STATUS_CHANGED_INACTIVE,
            position: FormatHelper::TOAST_POSITION
        );
    }

    public function generateCompanyCode(): void
    {
        $this->company_code = ClientHelper::generateClientCode();
        $this->success('Kode perusahaan berhasil digenerate: ' . $this->company_code, position: FormatHelper::TOAST_POSITION);
    }

    public function openChangeStatusModal(): void
    {
        $this->dispatch('openChangeStatusModal', $this->user->id);
    }

    public function openDeleteModal(): void
    {
        $this->dispatch('openDeleteUserModal', $this->user->id);
    }

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'togglePreviewStatus' => 'handleTogglePreviewStatus'
    ];

    public function handleTogglePreviewStatus(bool $newStatus): void
    {
        $this->is_active = $newStatus;
        $this->success(
            $this->is_active ? ClientHelper::TOAST_STATUS_CHANGED_ACTIVE : ClientHelper::TOAST_STATUS_CHANGED_INACTIVE,
            position: FormatHelper::TOAST_POSITION
        );
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
                'edit' => FormatHelper::getCommonIcon('edit'),
                'view' => FormatHelper::getCommonIcon('view'),
                'back' => FormatHelper::getCommonIcon('back'),
                'user' => UserHelper::getRoleIcon(UserHelper::ROLE_CLIENT),
                'name' => FormatHelper::getCommonIcon('user'),
                'email' => FormatHelper::getCommonIcon('email'),
                'password' => 'phosphor.lock',
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
                'close' => FormatHelper::getCommonIcon('close'),
                'reset' => FormatHelper::getCommonIcon('reset'),
                'delete' => FormatHelper::getCommonIcon('delete'),
                'status' => UserHelper::getStatusIcon('active'),
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
            'description' => UserHelper::getRoleDescription(UserHelper::ROLE_CLIENT),
        ];
    }

    /**
     * DYNAMIC: User status info from helper
     */
    #[Computed]
    public function userStatusInfo(): array
    {
        return [
            'label' => UserHelper::getStatusLabel($this->is_active),
            'color' => UserHelper::getStatusColor($this->is_active),
            'icon' => $this->is_active ? UserHelper::getStatusIcon('active') : UserHelper::getStatusIcon('inactive'),
        ];
    }

    /**
     * Check if form has changes from original data
     */
    #[Computed]
    public function hasChanges(): bool
    {
        return $this->name !== $this->user->name ||
               $this->email !== $this->user->email ||
               $this->is_active !== $this->user->is_active ||
               !empty($this->password) ||
               $this->avatar ||
               $this->company_name !== $this->client->company_name ||
               $this->company_code !== $this->client->company_code ||
               $this->company_address !== $this->client->company_address ||
               $this->phone !== $this->client->phone ||
               $this->fax !== ($this->client->fax ?? '') ||
               $this->tax_id !== ($this->client->tax_id ?? '') ||
               $this->contact_person !== $this->client->contact_person ||
               $this->contact_phone !== $this->client->contact_phone ||
               $this->contact_email !== $this->client->contact_email ||
               $this->contact_position !== ($this->client->contact_position ?? '') ||
               $this->company_latitude !== ($this->client->company_latitude ? (string)$this->client->company_latitude : null) ||
               $this->company_longitude !== ($this->client->company_longitude ? (string)$this->client->company_longitude : null);
    }

    /**
     * Check if form is valid for submission
     */
    #[Computed]
    public function isFormValid(): bool
    {
        $basicValid = !empty($this->name) &&
                     !empty($this->email) &&
                     !empty($this->company_name) &&
                     !empty($this->company_address) &&
                     !empty($this->phone) &&
                     !empty($this->contact_person) &&
                     !empty($this->contact_phone) &&
                     !empty($this->contact_email) &&
                     filter_var($this->email, FILTER_VALIDATE_EMAIL) &&
                     filter_var($this->contact_email, FILTER_VALIDATE_EMAIL);

        // If password is provided, validate it
        if (!empty($this->password)) {
            $basicValid = $basicValid &&
                         $this->password === $this->password_confirmation &&
                         strlen($this->password) >= 8;
        }

        return $basicValid;
    }

    /**
     * DYNAMIC: Preview data untuk status change modal
     */
    #[Computed]
    public function previewData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'company_name' => $this->company_name,
            'is_active' => $this->is_active,
            'role' => UserHelper::ROLE_CLIENT,
            'avatar_preview' => $this->avatar ? $this->avatar->temporaryUrl() : $this->user->avatar_url,
        ];
    }

    /**
     * DYNAMIC: Form validation status
     */
    #[Computed]
    public function formStatus(): array
    {
        if ($this->isFormValid && $this->hasChanges) {
            return [
                'status' => 'ready',
                'message' => ClientHelper::FORM_READY,
                'color' => 'success',
                'icon' => FormatHelper::getCommonIcon('success')
            ];
        } elseif ($this->hasChanges) {
            return [
                'status' => 'incomplete',
                'message' => ClientHelper::FORM_INCOMPLETE,
                'color' => 'warning',
                'icon' => FormatHelper::getCommonIcon('warning')
            ];
        } elseif ($this->isFormValid) {
            return [
                'status' => 'saved',
                'message' => ClientHelper::FORM_SAVED,
                'color' => 'info',
                'icon' => FormatHelper::getCommonIcon('info')
            ];
        } else {
            return [
                'status' => 'invalid',
                'message' => ClientHelper::FORM_INCOMPLETE,
                'color' => 'error',
                'icon' => FormatHelper::getCommonIcon('error')
            ];
        }
    }

    /**
     * DYNAMIC: Avatar placeholder generation
     */
    #[Computed]
    public function avatarPlaceholder(): string
    {
        return UserHelper::generateAvatarPlaceholder($this->name);
    }

    /**
     * DYNAMIC: Company display name preview
     */
    #[Computed]
    public function companyDisplayName(): string
    {
        if (!empty($this->company_name) && !empty($this->company_code)) {
            return ClientHelper::formatCompanyDisplayName($this->company_name, $this->company_code);
        }
        return $this->company_name;
    }

    /**
     * DYNAMIC: Company code display for preview
     */
    #[Computed]
    public function companyCodeDisplay(): string
    {
        return !empty($this->company_code) ? $this->company_code : 'AUTO-GENERATE';
    }

    /**
     * DYNAMIC: Coordinates validation status
     */
    #[Computed]
    public function coordinatesStatus(): array
    {
        if (empty($this->company_latitude) && empty($this->company_longitude)) {
            return [
                'status' => 'empty',
                'message' => 'Koordinat belum diisi',
                'color' => 'info'
            ];
        }

        if (!empty($this->company_latitude) && !empty($this->company_longitude)) {
            $isValid = ClientHelper::isValidIndonesianCoordinates(
                (float)$this->company_latitude,
                (float)$this->company_longitude
            );

            return [
                'status' => $isValid ? 'valid' : 'invalid',
                'message' => $isValid ? 'Koordinat valid untuk Indonesia' : 'Koordinat tidak valid untuk Indonesia',
                'color' => $isValid ? 'success' : 'error'
            ];
        }

        return [
            'status' => 'incomplete',
            'message' => 'Lengkapi koordinat latitude dan longitude',
            'color' => 'warning'
        ];
    }

    // * ========================================
    // * VALIDATION MESSAGES
    // * ========================================

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, angka, dan simbol (@$!%*?&).',
            'password_confirmation.min' => 'Konfirmasi kata sandi minimal 8 karakter.',
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'company_name.max' => 'Nama perusahaan maksimal 255 karakter.',
            'company_code.max' => 'Kode perusahaan maksimal 20 karakter.',
            'company_code.unique' => 'Kode perusahaan sudah digunakan.',
            'company_address.required' => 'Alamat perusahaan wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'fax.max' => 'Nomor fax maksimal 20 karakter.',
            'tax_id.max' => 'NPWP maksimal 30 karakter.',
            'contact_person.required' => 'Nama contact person wajib diisi.',
            'contact_person.max' => 'Nama contact person maksimal 255 karakter.',
            'contact_phone.required' => 'Telepon contact person wajib diisi.',
            'contact_phone.max' => 'Telepon contact person maksimal 20 karakter.',
            'contact_email.required' => 'Email contact person wajib diisi.',
            'contact_email.email' => 'Format email contact person tidak valid.',
            'contact_email.max' => 'Email contact person maksimal 255 karakter.',
            'contact_position.max' => 'Jabatan contact person maksimal 100 karakter.',
            'company_latitude.numeric' => 'Latitude harus berupa angka.',
            'company_latitude.between' => 'Latitude harus antara -90 dan 90.',
            'company_longitude.numeric' => 'Longitude harus berupa angka.',
            'company_longitude.between' => 'Longitude harus antara -180 dan 180.',
        ];
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.client.edit');
    }
}
