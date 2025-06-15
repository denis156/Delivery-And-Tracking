<?php

namespace App\Livewire\App\Component\User;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class CreateUserModal extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public bool $showModal = false;
    public string $name = '';
    public string $email = '';
    public string $role = 'client';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;
    public $avatar;
    public bool $processing = false;

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'openCreateUserModal' => 'openModal'
    ];

    // * ========================================
    // * LIFECYCLE HOOKS (Livewire 3 Standards)
    // * ========================================

    public function mount(): void
    {
        $this->resetForm();
    }

    // * ========================================
    // * VALIDATION RULES (Laravel 12.x Standards)
    // * ========================================

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email',
                'max:255'
            ],
            'role' => [
                'required',
                'string',
                'in:manager,admin,client,petugas-lapangan,petugas-ruangan,petugas-gudang'
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'password_confirmation' => ['required'],
            'is_active' => ['boolean'],
            'avatar' => [
                'nullable',
                'image',
                'max:2048',
                'mimes:jpeg,jpg,png,gif',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'role' => 'peran pengguna',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'is_active' => 'status aktif',
            'avatar' => 'foto profil',
        ];
    }

    // * ========================================
    // * REAL-TIME VALIDATION (Livewire 3 updating hooks)
    // * ========================================

    public function updatedEmail(): void
    {
        $this->validateOnly('email');
    }

    public function updatedPassword(): void
    {
        $this->validateOnly('password');
    }

    public function updatedPasswordConfirmation(): void
    {
        if ($this->password) {
            $this->validateOnly('password_confirmation');
        }
    }

    public function updatedName(): void
    {
        $this->validateOnly('name');
    }

    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar');
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    /**
     * Open modal
     */
    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * Close modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Save new user
     */
    public function save(): void
    {
        $this->validate();
        $this->processing = true;

        try {
            $userData = [
                'name' => trim($this->name),
                'email' => strtolower(trim($this->email)),
                'role' => $this->role,
                'password' => Hash::make($this->password),
                'is_active' => $this->is_active,
                'email_verified_at' => now(),
            ];

            // Handle avatar upload
            if ($this->avatar) {
                try {
                    $avatarPath = $this->avatar->store('avatars', 'public');
                    $userData['avatar_url'] = $avatarPath;
                } catch (\Exception $e) {
                    $this->error(
                        title: 'Gagal upload avatar!',
                        description: 'Terjadi kesalahan saat mengupload foto profil.',
                        position: 'toast-bottom'
                    );
                    $this->processing = false;
                    return;
                }
            }

            $user = User::create($userData);

            $this->success(
                title: 'Pengguna berhasil dibuat!',
                description: "Pengguna {$user->name} telah ditambahkan ke sistem dengan peran {$user->role_label}.",
                position: 'toast-bottom',
                timeout: 5000
            );

            // Emit event untuk refresh parent component
            $this->dispatch('userCreated');
            $this->closeModal();

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $this->error(
                    title: 'Email sudah digunakan!',
                    description: 'Alamat email ini sudah terdaftar dalam sistem.',
                    position: 'toast-bottom'
                );
            } else {
                $this->error(
                    title: 'Gagal membuat pengguna!',
                    description: 'Terjadi kesalahan database. Silakan coba lagi.',
                    position: 'toast-bottom'
                );
            }
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());

            $this->error(
                title: 'Gagal membuat pengguna!',
                description: 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
                position: 'toast-bottom'
            );
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Reset form to initial state
     */
    public function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'password',
            'password_confirmation',
            'avatar',
            'processing'
        ]);
        $this->role = 'client';
        $this->is_active = true;
    }

    // * ========================================
    // * COMPUTED PROPERTIES (Livewire 3 Standards)
    // * ========================================

    /**
     * Get available roles for dropdown (exclude drivers)
     */
    public function getRolesProperty(): array
    {
        return User::getRolesExcludeDrivers();
    }

    /**
     * Check if form is valid for submit
     */
    public function getIsFormValidProperty(): bool
    {
        return !empty($this->name) &&
               !empty($this->email) &&
               !empty($this->role) &&
               !empty($this->password) &&
               !empty($this->password_confirmation) &&
               $this->password === $this->password_confirmation;
    }

    /**
     * Get password strength analysis
     */
    public function getPasswordStrengthProperty(): array
    {
        if (empty($this->password)) {
            return ['strength' => 0, 'text' => 'Belum diisi', 'color' => 'error'];
        }

        $score = 0;
        $feedback = [];

        // Length check
        if (strlen($this->password) >= 8) {
            $score += 20;
        } else {
            $feedback[] = 'Minimal 8 karakter';
        }

        // Uppercase check
        if (preg_match('/[A-Z]/', $this->password)) {
            $score += 20;
        } else {
            $feedback[] = 'Huruf besar';
        }

        // Lowercase check
        if (preg_match('/[a-z]/', $this->password)) {
            $score += 20;
        } else {
            $feedback[] = 'Huruf kecil';
        }

        // Number check
        if (preg_match('/\d/', $this->password)) {
            $score += 20;
        } else {
            $feedback[] = 'Angka';
        }

        // Symbol check
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $this->password)) {
            $score += 20;
        } else {
            $feedback[] = 'Simbol';
        }

        // Determine strength
        if ($score < 40) {
            return ['strength' => $score, 'text' => 'Lemah', 'color' => 'error', 'feedback' => $feedback];
        } elseif ($score < 80) {
            return ['strength' => $score, 'text' => 'Sedang', 'color' => 'warning', 'feedback' => $feedback];
        } else {
            return ['strength' => $score, 'text' => 'Kuat', 'color' => 'success', 'feedback' => []];
        }
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.component.user.create-user-modal', [
            'roles' => $this->roles,
            'passwordStrength' => $this->passwordStrength,
            'isFormValid' => $this->isFormValid,
        ]);
    }
}
