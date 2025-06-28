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
use Illuminate\Validation\Rule;

#[Title(DriverHelper::PAGE_TITLE_EDIT)]
#[Layout('livewire.layouts.app')]
class Edit extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public User $user;

    // User properties
    public string $name = '';
    public string $email = '';
    public $avatar;
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true; // Keep for display purposes

    // Driver properties
    public string $license_number = '';
    public string $license_type = '';
    public string $license_expiry = '';
    public string $phone = '';
    public string $address = '';
    public string $vehicle_type = '';
    public string $vehicle_plate = '';

    // Original values for change detection
    private array $originalData = [];

    // * ========================================
    // * VALIDATION RULES - Dynamic Method
    // * ========================================

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id)
            ],
            'avatar' => 'nullable|image|max:2048',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_plate' => 'nullable|string|max:20',
            'license_type' => 'required|string',
            'license_expiry' => 'required|date|after:today',
        ];

        // License number validation dengan driver check
        if ($this->user->driver) {
            $rules['license_number'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('drivers', 'license_number')->ignore($this->user->driver->id)
            ];
        } else {
            $rules['license_number'] = 'required|string|max:50|unique:drivers,license_number';
        }

        // Password validation hanya jika diisi
        if (!empty($this->password)) {
            $rules['password'] = 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/';
            $rules['password_confirmation'] = 'required|string|min:8|same:password';
        }

        return $rules;
    }

    // * ========================================
    // * LIFECYCLE HOOKS
    // * ========================================

    public function mount(User $user): void
    {
        $this->user = $user;

        // Safety check untuk non-driver
        if (!$user->isDriver()) {
            $this->error('User ini bukan sopir.');
            $this->redirect(route('app.driver.index'), navigate: true);
            return;
        }

        // Load user data
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_active = $user->is_active;

        // Load driver data jika ada, atau set default jika belum ada
        $driver = $user->driver;
        if ($driver) {
            $this->license_number = $driver->license_number ?? '';
            $this->license_type = $driver->license_type ?? '';
            $this->license_expiry = $driver->license_expiry ? $driver->license_expiry->format('Y-m-d') : '';
            $this->phone = $driver->phone ?? '';
            $this->address = $driver->address ?? '';
            $this->vehicle_type = $driver->vehicle_type ?? '';
            $this->vehicle_plate = $driver->vehicle_plate ?? '';
        } else {
            // Set default values untuk driver baru
            $this->license_number = '';
            $this->license_type = '';
            $this->license_expiry = '';
            $this->phone = '';
            $this->address = '';
            $this->vehicle_type = '';
            $this->vehicle_plate = '';
        }

        // Store original data untuk change detection
        $this->storeOriginalData();
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function update(): void
    {
        // Validate semua data
        $this->validate();

        try {
            // Update user data (tanpa mengubah is_active)
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            // Update password hanya jika diisi
            if (!empty($this->password)) {
                $userData['password'] = bcrypt($this->password);
            }

            // Handle avatar upload
            if ($this->avatar) {
                $userData['avatar_url'] = $this->avatar->store('avatars', 'public');
            }

            $this->user->update($userData);

            // Prepare driver data
            $driverData = [
                'license_number' => $this->license_number,
                'license_type' => $this->license_type,
                'license_expiry' => $this->license_expiry,
                'phone' => $this->phone,
                'address' => $this->address,
                'vehicle_type' => $this->vehicle_type,
                'vehicle_plate' => $this->vehicle_plate,
            ];

            // Cek apakah sudah ada record driver
            if ($this->user->driver) {
                // Update existing driver record
                $this->user->driver->update($driverData);
            } else {
                // Buat record driver baru
                $this->user->driver()->create($driverData);
            }

            // Refresh user model dan store original data baru
            $this->user->load('driver');
            $this->storeOriginalData();

            $this->success(DriverHelper::TOAST_DRIVER_UPDATED, position: FormatHelper::TOAST_POSITION);
            $this->dispatch('userUpdated');

            $this->redirect(route('app.driver.view', $this->user), navigate: true);

        } catch (\Exception $e) {
            $this->error(DriverHelper::ERROR_SAVE_FAILED . ': ' . $e->getMessage(), position: FormatHelper::TOAST_POSITION);
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('app.driver.view', $this->user), navigate: true);
    }

    public function backToList(): void
    {
        $this->redirect(route('app.driver.index'), navigate: true);
    }

    public function changeUserStatus(): void
    {
        $this->dispatch('openChangeStatusModal', $this->user->id);
    }

    public function deleteUser(): void
    {
        $this->dispatch('openDeleteUserModal', $this->user->id);
    }

    // * ========================================
    // * LISTENERS
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleUserDeleted'
    ];

    public function handleUserDeleted(): void
    {
        $this->success('Sopir berhasil dihapus.');
        $this->redirect(route('app.driver.index'), navigate: true);
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
                'name' => FormatHelper::getCommonIcon('user'),
                'email' => FormatHelper::getCommonIcon('email'),
                'edit' => FormatHelper::getCommonIcon('edit'),
                'view' => FormatHelper::getCommonIcon('view'),
                'back' => FormatHelper::getCommonIcon('back'),
                'delete' => FormatHelper::getCommonIcon('delete'),
                'pause' => UserHelper::getStatusIcon('inactive'),
                'play' => UserHelper::getStatusIcon('active'),
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
            'label' => UserHelper::getRoleLabel($this->user->role),
            'color' => UserHelper::getRoleColor($this->user->role),
            'icon' => UserHelper::getRoleIcon($this->user->role),
            'description' => UserHelper::getRoleDescription($this->user->role),
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
     * Check if form has changes
     */
    #[Computed]
    public function hasChanges(): bool
    {
        // Cek perubahan user data
        $userChanged = $this->name !== ($this->originalData['name'] ?? $this->user->name) ||
                       $this->email !== ($this->originalData['email'] ?? $this->user->email);

        // Cek perubahan driver data
        $driver = $this->user->driver;
        $driverChanged = false;

        if ($driver) {
            // Jika sudah ada driver record, bandingkan dengan data asli
            $driverChanged = $this->license_number !== ($this->originalData['license_number'] ?? ($driver->license_number ?? '')) ||
                            $this->license_type !== ($this->originalData['license_type'] ?? ($driver->license_type ?? '')) ||
                            $this->license_expiry !== ($this->originalData['license_expiry'] ?? ($driver->license_expiry ? $driver->license_expiry->format('Y-m-d') : '')) ||
                            $this->phone !== ($this->originalData['phone'] ?? ($driver->phone ?? '')) ||
                            $this->address !== ($this->originalData['address'] ?? ($driver->address ?? '')) ||
                            $this->vehicle_type !== ($this->originalData['vehicle_type'] ?? ($driver->vehicle_type ?? '')) ||
                            $this->vehicle_plate !== ($this->originalData['vehicle_plate'] ?? ($driver->vehicle_plate ?? ''));
        } else {
            // Jika belum ada driver record, cek apakah ada data yang diisi
            $driverChanged = !empty($this->license_number) ||
                            !empty($this->license_type) ||
                            !empty($this->license_expiry) ||
                            !empty($this->phone) ||
                            !empty($this->address) ||
                            !empty($this->vehicle_type) ||
                            !empty($this->vehicle_plate);
        }

        // Cek perubahan password dan avatar
        $passwordChanged = !empty($this->password);
        $avatarChanged = $this->avatar !== null;

        return $userChanged || $driverChanged || $passwordChanged || $avatarChanged;
    }

    /**
     * DYNAMIC: Form status for better UX
     */
    #[Computed]
    public function formStatus(): array
    {
        if ($this->hasChanges) {
            return [
                'status' => 'changed',
                'message' => 'Ada perubahan yang belum disimpan',
                'color' => 'warning',
                'icon' => FormatHelper::getCommonIcon('warning')
            ];
        } else {
            return [
                'status' => 'saved',
                'message' => 'Semua tersimpan',
                'color' => 'success',
                'icon' => FormatHelper::getCommonIcon('success')
            ];
        }
    }

    /**
     * DYNAMIC: Avatar placeholder generation
     */
    #[Computed]
    public function avatarPlaceholder(): string
    {
        return $this->name ? UserHelper::generateAvatarPlaceholder($this->name) : $this->user->avatar_placeholder;
    }

    /**
     * DYNAMIC: License status info for current data
     */
    #[Computed]
    public function currentLicenseInfo(): array
    {
        if (empty($this->license_expiry)) {
            return [
                'status' => 'no_date',
                'label' => 'Belum Ada Tanggal',
                'color' => 'warning',
                'icon' => DriverHelper::getLicenseStatusIcon('no_license'),
                'message' => 'Tentukan tanggal kadaluarsa SIM'
            ];
        }

        try {
            $expiryDate = \Carbon\Carbon::parse($this->license_expiry);
            $licenseStatus = DriverHelper::getLicenseStatus($expiryDate);
            $daysToExpiry = DriverHelper::getDaysToExpiry($expiryDate);

            return [
                'status' => $licenseStatus['status'],
                'label' => $licenseStatus['label'],
                'color' => $licenseStatus['color'],
                'icon' => $licenseStatus['icon'],
                'daysToExpiry' => $daysToExpiry,
                'message' => match($licenseStatus['status']) {
                    'expired' => 'SIM sudah kadaluarsa',
                    'expiring_soon' => 'SIM akan kadaluarsa dalam 90 hari',
                    'valid' => 'SIM masih berlaku',
                    default => 'Status tidak diketahui'
                }
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'invalid',
                'label' => 'Format Tidak Valid',
                'color' => 'error',
                'icon' => FormatHelper::getCommonIcon('error'),
                'message' => 'Format tanggal tidak valid'
            ];
        }
    }

    // * ========================================
    // * HELPER METHODS
    // * ========================================

    private function storeOriginalData(): void
    {
        $driver = $this->user->driver;

        $this->originalData = [
            'name' => $this->name,
            'email' => $this->email,
            'license_number' => $driver ? ($driver->license_number ?? '') : '',
            'license_type' => $driver ? ($driver->license_type ?? '') : '',
            'license_expiry' => $driver && $driver->license_expiry ? $driver->license_expiry->format('Y-m-d') : '',
            'phone' => $driver ? ($driver->phone ?? '') : '',
            'address' => $driver ? ($driver->address ?? '') : '',
            'vehicle_type' => $driver ? ($driver->vehicle_type ?? '') : '',
            'vehicle_plate' => $driver ? ($driver->vehicle_plate ?? '') : '',
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
            'password.required' => 'Kata sandi wajib diisi jika ingin diubah.',
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
        return view('livewire.app.pages.driver.edit');
    }
}
