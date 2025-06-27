<?php

namespace App\Livewire\App\Pages\Driver;

use App\Models\User;
use App\Models\Driver;
use App\Class\Helper\UserHelper;
use App\Class\Helper\DriverHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

#[Title('Tambah Sopir Baru')]
#[Layout('livewire.layouts.app')]
class Create extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * USER PROPERTIES
    // * ========================================

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/')]
    public string $password = '';

    #[Validate('required|string|min:8|same:password')]
    public string $password_confirmation = '';

    #[Validate('nullable|image|max:2048')]
    public $avatar;

    public bool $is_active = true;

    // * ========================================
    // * DRIVER PROPERTIES
    // * ========================================

    #[Validate('required|string|max:50|unique:drivers,license_number')]
    public string $license_number = '';

    #[Validate('required|string')]
    public string $license_type = '';

    #[Validate('required|date|after:today')]
    public string $license_expiry = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|string')]
    public string $address = '';

    #[Validate('nullable|string|max:100')]
    public string $vehicle_type = '';

    #[Validate('nullable|string|max:20')]
    public string $vehicle_plate = '';

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function save(): void
    {
        $this->validate();

        try {
            // Create user first
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
                'avatar_url' => $this->avatar?->store('avatars', 'public'),
                'is_active' => $this->is_active,
            ]);

            // Assign driver role
            $user->assignRole(UserHelper::ROLE_DRIVER);

            // Create driver profile
            Driver::create([
                'user_id' => $user->id,
                'license_number' => $this->license_number,
                'license_type' => $this->license_type,
                'license_expiry' => $this->license_expiry,
                'phone' => $this->phone,
                'address' => $this->address,
                'vehicle_type' => $this->vehicle_type,
                'vehicle_plate' => $this->vehicle_plate,
            ]);

            $this->success('Sopir berhasil dibuat!');
            $this->dispatch('userCreated');

            $this->redirect(route('app.driver.view', $user), navigate: true);

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat membuat sopir: ' . $e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'name', 'email', 'password', 'password_confirmation', 'avatar',
            'license_number', 'license_type', 'license_expiry', 'phone',
            'address', 'vehicle_type', 'vehicle_plate'
        ]);
        $this->is_active = true;
        $this->success('Form berhasil direset.');
    }

    public function cancel(): void
    {
        $this->redirect(route('app.driver.index'), navigate: true);
    }

    public function toggleUserStatus(): void
    {
        $this->is_active = !$this->is_active;
        $this->success('Status sopir diubah ke ' . ($this->is_active ? 'aktif' : 'nonaktif'));
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
        $this->success('Status preview diubah ke ' . ($this->is_active ? 'aktif' : 'nonaktif'));
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Style)
    // * ========================================

    /**
     * DYNAMIC: License types from helper
     */
    #[Computed]
    public function licenseTypes(): array
    {
        return DriverHelper::getAllLicenseTypes();
    }

    /**
     * DYNAMIC: Driver UI configuration from helper
     */
    #[Computed]
    public function driverUIConfig(): array
    {
        return [
            'icons' => [
                'add' => FormatHelper::getCommonIcon('add'),
                'user' => UserHelper::getRoleIcon(UserHelper::ROLE_DRIVER),
                'name' => FormatHelper::getCommonIcon('user'),
                'email' => FormatHelper::getCommonIcon('email'),
                'password' => 'phosphor.lock',
                'avatar' => 'phosphor.camera',
                'license_type' => DriverHelper::getDriverFieldIcon('license_type'),
                'license_number' => DriverHelper::getDriverFieldIcon('license_number'),
                'license_expiry' => DriverHelper::getDriverFieldIcon('license_expiry'),
                'phone' => DriverHelper::getDriverFieldIcon('phone'),
                'address' => DriverHelper::getDriverFieldIcon('address'),
                'vehicle_type' => DriverHelper::getDriverFieldIcon('vehicle_type'),
                'vehicle_plate' => DriverHelper::getDriverFieldIcon('vehicle_plate'),
            ],
            'colors' => [
                'driver_role' => UserHelper::getRoleColor(UserHelper::ROLE_DRIVER),
                'license_type' => DriverHelper::getDriverFieldColor('license_type'),
                'license_number' => DriverHelper::getDriverFieldColor('license_number'),
                'license_expiry' => DriverHelper::getDriverFieldColor('license_expiry'),
                'phone' => DriverHelper::getDriverFieldColor('phone'),
                'address' => DriverHelper::getDriverFieldColor('address'),
                'vehicle_type' => DriverHelper::getDriverFieldColor('vehicle_type'),
                'vehicle_plate' => DriverHelper::getDriverFieldColor('vehicle_plate'),
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
            'label' => UserHelper::getRoleLabel(UserHelper::ROLE_DRIVER),
            'color' => UserHelper::getRoleColor(UserHelper::ROLE_DRIVER),
            'icon' => UserHelper::getRoleIcon(UserHelper::ROLE_DRIVER),
            'description' => UserHelper::getRoleDescription(UserHelper::ROLE_DRIVER),
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
     * Check if form has any data
     */
    #[Computed]
    public function hasData(): bool
    {
        return !empty($this->name) || !empty($this->email) || !empty($this->license_number) ||
               !empty($this->phone) || !empty($this->password) || $this->avatar;
    }

    /**
     * Check if form is valid for submission
     */
    #[Computed]
    public function isFormValid(): bool
    {
        return !empty($this->name) &&
               !empty($this->email) &&
               !empty($this->password) &&
               !empty($this->password_confirmation) &&
               !empty($this->license_number) &&
               !empty($this->license_type) &&
               !empty($this->license_expiry) &&
               !empty($this->phone) &&
               $this->password === $this->password_confirmation &&
               filter_var($this->email, FILTER_VALIDATE_EMAIL) &&
               strlen($this->password) >= 8;
    }

    /**
     * DYNAMIC: Preview data untuk status change modal
     */
    #[Computed]
    public function previewData(): array
    {
        return [
            'name' => $this->name ?: 'Nama Sopir',
            'email' => $this->email ?: 'email@example.com',
            'is_active' => $this->is_active,
            'role' => UserHelper::ROLE_DRIVER,
            'avatar_preview' => $this->avatar ? $this->avatar->temporaryUrl() : null,
        ];
    }

    /**
     * DYNAMIC: Form validation status
     */
    #[Computed]
    public function formStatus(): array
    {
        if ($this->isFormValid) {
            return [
                'status' => 'valid',
                'message' => 'Siap untuk disimpan!',
                'color' => 'success',
                'icon' => FormatHelper::getCommonIcon('success')
            ];
        } elseif ($this->hasData) {
            return [
                'status' => 'incomplete',
                'message' => 'Lengkapi semua field wajib',
                'color' => 'warning',
                'icon' => FormatHelper::getCommonIcon('warning')
            ];
        } else {
            return [
                'status' => 'empty',
                'message' => 'Mulai mengisi form untuk preview',
                'color' => 'info',
                'icon' => FormatHelper::getCommonIcon('info')
            ];
        }
    }

    /**
     * DYNAMIC: Avatar placeholder generation
     */
    #[Computed]
    public function avatarPlaceholder(): string
    {
        return $this->name ? UserHelper::generateAvatarPlaceholder($this->name) : 'S';
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
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, angka, dan simbol (@$!%*?&).',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'password_confirmation.min' => 'Konfirmasi kata sandi minimal 8 karakter.',
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
            'license_number.required' => 'Nomor SIM wajib diisi.',
            'license_number.max' => 'Nomor SIM maksimal 50 karakter.',
            'license_number.unique' => 'Nomor SIM sudah digunakan.',
            'license_type.required' => 'Jenis SIM wajib dipilih.',
            'license_expiry.required' => 'Tanggal kadaluarsa SIM wajib diisi.',
            'license_expiry.date' => 'Format tanggal tidak valid.',
            'license_expiry.after' => 'Tanggal kadaluarsa harus setelah hari ini.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'vehicle_type.max' => 'Jenis kendaraan maksimal 100 karakter.',
            'vehicle_plate.max' => 'Plat nomor maksimal 20 karakter.',
        ];
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.driver.create');
    }
}
