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
use App\Traits\AuthRateLimiting;

#[Title('Reset Password')]
#[Layout('livewire.layouts.auth')]
class ResetPassword extends Component
{
    use Toast, AuthRateLimiting;

    public string $token = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required')]
    public string $password_confirmation = '';

    public bool $passwordReset = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        if (request()->has('email')) {
            $this->email = request()->get('email');
        }

        if (Auth::check()) {
            $this->redirectToDashboard(Auth::user());
        }

        $this->resetRateLimitState();
        $this->checkAuthRateLimit('password_reset', ['token' => $this->token]);
    }

    public function resetPassword(): void
    {
        // Check rate limit before processing
        if ($this->checkAuthRateLimit('password_reset', ['token' => $this->token])) {
            $this->showRateLimitError('password_reset');
            return;
        }

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
            // Clear rate limit for successful reset
            $this->clearAuthRateLimit('password_reset', ['token' => $this->token]);

            $this->passwordReset = true;

            $this->success(
                title: 'Password Berhasil Direset!',
                description: 'Password Anda telah berhasil diubah. Silakan login dengan password baru.',
                position: 'toast-top toast-end',
                timeout: 5000
            );
        } else {
            $this->handleResetFailure($status);

            // Show remaining attempts if low
            if ($this->shouldShowRateLimitWarning('password_reset')) {
                $this->info(
                    title: 'Perhatian',
                    description: "Anda memiliki {$this->remainingAttempts} percobaan tersisa.",
                    position: 'toast-top toast-end',
                    timeout: 3000
                );
            }
        }
    }

    private function handleResetFailure(string $status): void
    {
        $message = match($status) {
            Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kadaluarsa.',
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem.',
            default => 'Gagal mereset password. Silakan coba lagi.'
        };

        $this->error(
            title: 'Reset Password Gagal',
            description: $message,
            position: 'toast-top toast-end',
            timeout: 5000
        );
    }

    /**
     * Show rate limit error message
     */
    private function showRateLimitError(string $action): void
    {
        $message = $this->getRateLimitMessage($action);

        $this->error(
            title: 'Terlalu Banyak Percobaan',
            description: $message,
            position: 'toast-top toast-end',
            timeout: 5000
        );
    }

    public function goToLogin(): void
    {
        $this->redirect(route('login'), navigate: true);
    }

    public function requestNewLink(): void
    {
        $this->redirect(route('password.request'), navigate: true);
    }

    /**
     * Redirect user to appropriate dashboard
     */
    private function redirectToDashboard($user): void
    {
        $route = $user->isDriver() ? 'driver.dashboard' : 'app.dashboard';
        $this->redirect(route($route), navigate: true);
    }

    /**
     * Get rate limit information for the view
     */
    public function getRateLimitInfoProperty(): array
    {
        return $this->getRateLimitInfo('password_reset');
    }

    public function render()
    {
        return view('livewire.auth.pages.reset-password');
    }
}
