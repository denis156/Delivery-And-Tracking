<?php

namespace App\Livewire\App\Pages\User;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

#[Title('Edit Pengguna')]
#[Layout('livewire.layouts.app')]
class Edit extends Component
{
    use Toast, WithFileUploads;

    // * ========================================
    // * PROPERTIES (Livewire 3 Standards)
    // * ========================================

    public User $user;
    public string $name = '';
    public string $email = '';
    public string $role = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;
    public $avatar;

    // Original values for comparison
    public string $originalEmail = '';

    // * ========================================
    // * LIFECYCLE HOOKS (Livewire 3 Standards)
    // * ========================================

    public function mount(User $user): void
    {
        // Safety check untuk driver
        if ($user->isDriver()) {
            $this->error('Driver tidak dapat diedit dari halaman ini.', position: 'toast-bottom');
            $this->redirect(route('app.user'), navigate: true);
            return;
        }

        $this->user = $user;

        // Load user data into form
        $this->name = $user->name;
        $this->email = $user->email;
        $this->originalEmail = $user->email;
        $this->role = $user->role;
        $this->is_active = $user->is_active;
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
                'regex:/^[a-zA-Z\s\'-\.]+$/u'  // Allow letters, spaces, apostrophes, hyphens, dots
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email,' . $this->user->id,
                'max:255'
            ],
            'role' => [
                'required',
                'string',
                'in:manager,admin,client,petugas-lapangan,petugas-ruangan,petugas-gudang'
            ],
            'password' => [
                'nullable',
                'min:8',
                'max:255',
                // Custom password rules for better UX
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'password_confirmation' => [
                'nullable',
                'same:password'
            ],
            'is_active' => ['boolean'],
            'avatar' => [
                'nullable',
                'image',
                'max:2048', // 2MB
                'mimes:jpeg,jpg,png,webp',
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

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 2 karakter.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'name.regex' => 'Nama lengkap hanya boleh berisi huruf, spasi, tanda kutip, strip, dan titik.',

            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar dalam sistem.',
            'email.max' => 'Alamat email maksimal 255 karakter.',

            'role.required' => 'Peran pengguna wajib dipilih.',
            'role.in' => 'Peran pengguna yang dipilih tidak valid.',

            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.max' => 'Kata sandi maksimal 255 karakter.',
            'password.regex' => 'Kata sandi harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol.',

            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok dengan kata sandi.',

            'avatar.image' => 'File yang diupload harus berupa gambar.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
            'avatar.mimes' => 'Foto profil harus berformat JPEG, JPG, PNG, atau WebP.',
            'avatar.dimensions' => 'Dimensi foto profil minimal 100x100px dan maksimal 2000x2000px.',
        ];
    }

    // * ========================================
    // * REAL-TIME VALIDATION (Livewire 3 updating hooks)
    // * ========================================

    public function updatedName(): void
    {
        $this->validateOnly('name');
    }

    public function updatedEmail(): void
    {
        $this->validateOnly('email');
    }

    public function updatedRole(): void
    {
        $this->validateOnly('role');
    }

    public function updatedPassword(): void
    {
        // Clear error bag untuk kedua field password
        $this->resetErrorBag(['password', 'password_confirmation']);

        if (!empty($this->password)) {
            $this->validateOnly('password');

            // Validate confirmation jika sudah diisi
            if (!empty($this->password_confirmation)) {
                $this->validatePasswordConfirmation();
            }
        } else {
            // Clear password confirmation when password is cleared
            $this->password_confirmation = '';
        }
    }

    public function updatedPasswordConfirmation(): void
    {
        // Clear error bag untuk confirmation
        $this->resetErrorBag('password_confirmation');

        if (!empty($this->password) && !empty($this->password_confirmation)) {
            $this->validatePasswordConfirmation();
        }
    }

    /**
     * Validate password confirmation with proper error handling
     */
    private function validatePasswordConfirmation(): void
    {
        if ($this->password !== $this->password_confirmation) {
            $this->addError('password_confirmation', 'Konfirmasi kata sandi tidak cocok dengan kata sandi.');
        }
    }

    public function updatedAvatar(): void
    {
        if ($this->avatar) {
            $this->validateOnly('avatar');
        }
    }

    public function updatedIsActive(): void
    {
        $this->validateOnly('is_active');
    }

    // * ========================================
    // * ACTIONS
    // * ========================================

    /**
     * Update user
     */
    public function update(): void
    {
        $this->validate();

        try {
            $userData = [
                'name' => trim($this->name),
                'email' => strtolower(trim($this->email)),
                'role' => $this->role,
                'is_active' => $this->is_active,
            ];

            // Update password only if provided
            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            // Handle avatar upload
            if ($this->avatar) {
                try {
                    // Delete old avatar if exists
                    if ($this->user->avatar_url) {
                        Storage::disk('public')->delete($this->user->avatar_url);
                    }

                    $avatarPath = $this->avatar->store('avatars', 'public');
                    $userData['avatar_url'] = $avatarPath;
                } catch (\Exception $e) {
                    $this->error(
                        title: 'Gagal upload avatar!',
                        description: 'Terjadi kesalahan saat mengupload foto profil.',
                        position: 'toast-bottom'
                    );
                    return;
                }
            }

            $this->user->update($userData);

            $this->success(
                title: 'Pengguna berhasil diperbarui!',
                description: "Data pengguna {$this->user->name} telah diperbarui.",
                position: 'toast-bottom',
                timeout: 5000,
                redirectTo: route('app.user.view', $this->user)
            );

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $this->error(
                    title: 'Email sudah digunakan!',
                    description: 'Alamat email ini sudah terdaftar dalam sistem.',
                    position: 'toast-bottom'
                );
            } else {
                $this->error(
                    title: 'Gagal memperbarui pengguna!',
                    description: 'Terjadi kesalahan database. Silakan coba lagi.',
                    position: 'toast-bottom'
                );
            }
        } catch (\Exception $e) {
            Log::error('User update failed: ' . $e->getMessage());

            $this->error(
                title: 'Gagal memperbarui pengguna!',
                description: 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
                position: 'toast-bottom'
            );
        }
    }

    /**
     * Cancel and go back to view
     */
    public function cancel(): void
    {
        $this->redirect(route('app.user.view', $this->user), navigate: true);
    }

    /**
     * Go back to user list
     */
    public function backToList(): void
    {
        $this->redirect(route('app.user'), navigate: true);
    }

    /**
     * Open toggle status modal
     */
    public function toggleUserStatus(): void
    {
        $this->dispatch('openToggleStatusModal', $this->user->id);
    }

    /**
     * Open delete modal
     */
    public function deleteUser(): void
    {
        $this->dispatch('openDeleteUserModal', $this->user->id);
    }

    // * ========================================
    // * LISTENERS (Livewire 3 Standards)
    // * ========================================

    protected $listeners = [
        'userStatusUpdated' => '$refresh',
        'userDeleted' => 'handleUserDeleted'
    ];

    /**
     * Handle user deleted - redirect to list
     */
    public function handleUserDeleted(): void
    {
        $this->success('User berhasil dihapus.', position: 'toast-bottom');
        $this->redirect(route('app.user'), navigate: true);
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
               ($this->password === $this->password_confirmation);
    }

    /**
     * Get password strength analysis (only if password provided)
     */
    public function getPasswordStrengthProperty(): array
    {
        if (empty($this->password)) {
            return [
                'strength' => 0,
                'text' => 'Tidak diubah',
                'color' => 'info',
                'feedback' => []
            ];
        }

        $score = 0;
        $feedback = [];
        $password = $this->password;

        // Length check (20 points)
        if (strlen($password) >= 8) {
            $score += 20;
        } else {
            $feedback[] = 'Minimal 8 karakter';
        }

        // Bonus for longer passwords
        if (strlen($password) >= 12) {
            $score += 10;
        }

        // Uppercase check (15 points)
        if (preg_match('/[A-Z]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Huruf besar (A-Z)';
        }

        // Lowercase check (15 points)
        if (preg_match('/[a-z]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Huruf kecil (a-z)';
        }

        // Number check (20 points)
        if (preg_match('/\d/', $password)) {
            $score += 20;
        } else {
            $feedback[] = 'Angka (0-9)';
        }

        // Symbol check (20 points)
        if (preg_match('/[@$!%*?&]/', $password)) {
            $score += 20;
        } else {
            $feedback[] = 'Simbol (@$!%*?&)';
        }

        // Determine strength level
        if ($score < 40) {
            return [
                'strength' => $score,
                'text' => 'Lemah',
                'color' => 'error',
                'feedback' => $feedback
            ];
        } elseif ($score < 70) {
            return [
                'strength' => $score,
                'text' => 'Sedang',
                'color' => 'warning',
                'feedback' => $feedback
            ];
        } elseif ($score < 90) {
            return [
                'strength' => $score,
                'text' => 'Kuat',
                'color' => 'success',
                'feedback' => $feedback
            ];
        } else {
            return [
                'strength' => 100,
                'text' => 'Sangat Kuat',
                'color' => 'success',
                'feedback' => []
            ];
        }
    }

    /**
     * Check if any data has changed
     */
    public function getHasChangesProperty(): bool
    {
        return $this->name !== $this->user->name ||
               $this->email !== $this->user->email ||
               $this->role !== $this->user->role ||
               $this->is_active !== $this->user->is_active ||
               !empty($this->password) ||
               $this->avatar !== null;
    }

    // * ========================================
    // * RENDER
    // * ========================================

    public function render()
    {
        return view('livewire.app.pages.user.edit', [
            'roles' => $this->roles,
            'passwordStrength' => $this->passwordStrength,
            'isFormValid' => $this->isFormValid,
            'hasChanges' => $this->hasChanges,
        ]);
    }
}
