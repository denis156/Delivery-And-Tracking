<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

#[Title('Reset Password')]
#[Layout('livewire.layouts.auth')]
class ResetPassword extends Component
{
    use Toast;

    public string $token = '';

    #[Validate('required', message: 'Email wajib diisi')]
    #[Validate('email', message: 'Format email tidak valid')]
    public string $email = '';

    #[Validate('required', message: 'Password baru wajib diisi')]
    #[Validate('min:8', message: 'Password minimal 8 karakter')]
    #[Validate('confirmed', message: 'Konfirmasi password tidak sesuai')]
    public string $password = '';

    #[Validate('required', message: 'Konfirmasi password wajib diisi')]
    public string $password_confirmation = '';

    public bool $passwordReset = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->get('email', '');

        if (Auth::check()) {
            $this->redirectBasedOnRole();
        }
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset([
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token,
        ], function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            $this->passwordReset = true;
            $this->success('Password berhasil direset! Silakan login dengan password baru.');
        } else {
            $this->error('Token tidak valid atau sudah kadaluarsa.');
        }
    }

    public function goToLogin(): void
    {
        $this->redirect(route('login'), navigate: true);
    }

    public function requestNewLink(): void
    {
        $this->redirect(route('password.request'), navigate: true);
    }

    private function redirectBasedOnRole(): void
    {
        $route = Auth::user()->hasRole('driver') ? 'driver.dashboard' : 'app.dashboard';
        $this->redirect(route($route), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.pages.reset-password');
    }
}
