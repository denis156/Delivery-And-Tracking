<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use App\Class\Helper\UserHelper;
use App\Class\Helper\DriverHelper;
use App\Class\Helper\FormatHelper;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

#[Title(UserHelper::PAGE_TITLE_EDIT)]
#[Layout('livewire.layouts.app')]
class Edit extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public User $user;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255')]
    public string $email = '';

    #[Validate('required|string')]
    public string $role = '';

    #[Validate('nullable|image|max:' . FormatHelper::MAX_AVATAR_SIZE)]
    public $avatar;

    #[Validate('nullable|string|min:' . FormatHelper::MIN_PASSWORD_LENGTH . '|regex:' . FormatHelper::PASSWORD_REGEX)]
    public string $password = '';

    #[Validate('nullable|string|min:' . FormatHelper::MIN_PASSWORD_LENGTH)]
    public string $password_confirmation = '';

    public bool $is_active = true;

    // Original values for change detection - DIPERBAIKI: Inisialisasi dengan array kosong
    private array $originalData = [
        'name' => '',
        'email' => '',
        'role' => '',
        'is_active' => true,
    ];

    // * ========================================
    // * LIFECYCLE HOOKS
    // * ========================================

    public function mount(User $user): void
    {
        $this->user = $user;

        // DIPERBAIKI: Inisialisasi originalData di awal sebelum safety check
        $this->originalData = [
            'name' => '',
            'email' => '',
            'role' => '',
            'is_active' => true,
        ];

        // Safety check untuk driver dan client
        if ($user->isDriver() || $user->isClient()) {
            $this->error('Pengguna ini tidak dapat diedit dari halaman ini.');
            $this->redirect(route('app.user.index'), navigate: true);
            return;
        }

        // Load user data into form
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->getPrimaryRole() ?? UserHelper::ROLE_ADMIN;
        $this->is_active = $user->is_active;

        // DIPERBAIKI: Set originalData setelah semua property diload
        $this->originalData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
        ];
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function update(): void
    {
        // Custom validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id,
            'role' => 'required|string',
            'avatar' => 'nullable|image|max:2048',
        ];

        // Add password validation only if password is provided
        if (!empty($this->password)) {
            $rules['password'] = 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/';
            $rules['password_confirmation'] = 'required|string|min:8|same:password';
        }

        $this->validate($rules);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
        ];

        // Update password only if provided
        if (!empty($this->password)) {
            $userData['password'] = bcrypt($this->password);
        }

        // Handle avatar upload
        if ($this->avatar) {
            $userData['avatar_url'] = $this->avatar->store('avatars', 'public');
        }

        $this->user->update($userData);

        // Update role if changed
        $currentRole = $this->user->getPrimaryRole();
        if ($currentRole !== $this->role) {
            $this->user->setRole($this->role);
        }

        $this->success(UserHelper::TOAST_USER_UPDATED, position: FormatHelper::TOAST_POSITION);
        $this->dispatch('userUpdated');

        $this->redirect(route('app.user.view', $this->user), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('app.user.view', $this->user), navigate: true);
    }

    public function backToList(): void
    {
        $this->redirect(route('app.user.index'), navigate: true);
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
        $this->success('User berhasil dihapus.');
        $this->redirect(route('app.user.index'), navigate: true);
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    /**
     * DYNAMIC: User UI configuration from helper
     */
    #[Computed]
    public function userUIConfig(): array
    {
        return [
            'icons' => [
                'edit' => FormatHelper::getCommonIcon('edit'),
                'user' => FormatHelper::getCommonIcon('user'),
                'users' => FormatHelper::getCommonIcon('users'),
                'view' => FormatHelper::getCommonIcon('view'),
                'delete' => FormatHelper::getCommonIcon('delete'),
                'email' => FormatHelper::getCommonIcon('email'),
                'success' => FormatHelper::getCommonIcon('success'),
                'warning' => FormatHelper::getCommonIcon('warning'),
                'arrow_right' => FormatHelper::getCommonIcon('arrow_right'),
                'back' => FormatHelper::getCommonIcon('back'),
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

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'role.required' => 'Peran pengguna wajib dipilih.',
            'password.required' => 'Kata sandi wajib diisi jika ingin diubah.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, angka, dan simbol (@$!%*?&).',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'password_confirmation.min' => 'Konfirmasi kata sandi minimal 8 karakter.',
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
        ];
    }

    public function getRolesProperty(): array
    {
        return collect(UserHelper::getManagementRoles())
            ->merge(UserHelper::getStaffRoles())
            ->toArray();
    }

    // DIPERBAIKI: Tambah null check dan array_key_exists untuk mencegah error
    public function getHasChangesProperty(): bool
    {
        // Pastikan originalData memiliki semua key yang diperlukan
        $originalData = array_merge([
            'name' => '',
            'email' => '',
            'role' => '',
            'is_active' => true,
        ], $this->originalData);

        return $this->name !== $originalData['name'] ||
               $this->email !== $originalData['email'] ||
               $this->role !== $originalData['role'] ||
               $this->is_active !== $originalData['is_active'] ||
               !empty($this->password) ||
               $this->avatar !== null;
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.user.edit');
    }
}
