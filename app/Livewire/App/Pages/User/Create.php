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

#[Title('Tambah Pengguna')]
#[Layout('livewire.layouts.app')]
class Create extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $role = '';

    #[Validate('required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/')]
    public string $password = '';

    #[Validate('required|string|min:8|same:password')]
    public string $password_confirmation = '';

    #[Validate('nullable|image|max:2048')]
    public $avatar;

    public bool $is_active = true;

    // * ========================================
    // * ACTIONS
    // * ========================================

    public function save(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'avatar_url' => $this->avatar?->store('avatars', 'public'),
            'is_active' => $this->is_active,
        ]);

        $user->assignRole($this->role);

        $this->success('Pengguna berhasil dibuat!');
        $this->dispatch('userCreated');

        $this->redirect(route('app.user.view', $user), navigate: true);
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'role', 'password', 'password_confirmation', 'avatar']);
        $this->is_active = true;
        $this->success('Form berhasil direset.');
    }

    public function cancel(): void
    {
        $this->redirect(route('app.user.index'), navigate: true);
    }

    public function toggleUserStatus(): void
    {
        $this->is_active = !$this->is_active;
        $this->success('Status user diubah ke ' . ($this->is_active ? 'aktif' : 'nonaktif'));
    }

    // * ========================================
    // * COMPUTED PROPERTIES
    // * ========================================

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'role.required' => 'Peran pengguna wajib dipilih.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, angka, dan simbol (@$!%*?&).',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'password_confirmation.min' => 'Konfirmasi kata sandi minimal 8 karakter.',
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
        ];
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
                'user' => FormatHelper::getCommonIcon('user'),
                'users' => FormatHelper::getCommonIcon('users'),
                'email' => FormatHelper::getCommonIcon('email'),
                'success' => FormatHelper::getCommonIcon('success'),
                'warning' => FormatHelper::getCommonIcon('warning'),
                'info' => FormatHelper::getCommonIcon('info'),
                'reset' => FormatHelper::getCommonIcon('reset'),
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

    public function getRolesProperty(): array
    {
        return collect(UserHelper::getManagementRoles())
            ->merge(UserHelper::getStaffRoles())
            ->toArray();
    }

    public function getHasDataProperty(): bool
    {
        return !empty($this->name) || !empty($this->email) || !empty($this->role) || !empty($this->password) || $this->avatar;
    }

    public function getIsFormValidProperty(): bool
    {
        return !empty($this->name) &&
               !empty($this->email) &&
               !empty($this->role) &&
               !empty($this->password) &&
               !empty($this->password_confirmation) &&
               $this->password === $this->password_confirmation &&
               filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.user.create');
    }
}
