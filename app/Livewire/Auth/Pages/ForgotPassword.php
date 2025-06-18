<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

#[Title('Lupa Password')]
#[Layout('livewire.layouts.auth')]
class ForgotPassword extends Component
{
    use Toast;

    #[Validate('required', message: 'Email wajib diisi')]
    #[Validate('email', message: 'Format email tidak valid')]
    #[Validate('exists:users,email', message: 'Email tidak terdaftar dalam sistem')]
    public string $email = '';

    public bool $linkSent = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectBasedOnRole();
        }
    }

    public function sendResetLink(): void
    {
        $this->validate();
        $this->processSendReset();
    }

    public function resendLink(): void
    {
        // Full reset state
        $this->linkSent = false;
        $this->resetValidation();
        $this->resetErrorBag();

        // Process resend
        $this->processSendReset(true);
    }

    private function processSendReset(bool $isResend = false): void
    {
        // Rate limiting key berdasarkan IP dan email
        $rateLimitKey = 'forgot-password:' . request()->ip() . ':' . strtolower($this->email);

        // Cek rate limit - 5 percobaan per 15 menit
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            $this->error("Terlalu banyak permintaan reset password. Coba lagi dalam {$minutes} menit.");
            return;
        }

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            // Reset berhasil - clear rate limit attempts
            RateLimiter::clear($rateLimitKey);

            $this->linkSent = true;
            $message = $isResend
                ? 'Link reset password telah dikirim ulang ke ' . $this->email
                : 'Link reset password telah dikirim ke ' . $this->email;
            $this->success($message);
        } else {
            // Reset gagal - increment attempts
            RateLimiter::increment($rateLimitKey, 1, 900); // 15 menit

            $remaining = RateLimiter::remaining($rateLimitKey, 5);

            if ($remaining > 0) {
                $this->handleError($status, $remaining);
            } else {
                $seconds = RateLimiter::availableIn($rateLimitKey);
                $minutes = ceil($seconds / 60);
                $this->error("Terlalu banyak percobaan gagal. Coba lagi dalam {$minutes} menit.");
            }
        }
    }

    private function handleError(string $status, int $remaining = 0): void
    {
        $message = match($status) {
            Password::RESET_THROTTLED => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem.',
            default => 'Gagal mengirim link reset password. Coba lagi nanti.'
        };

        if ($remaining > 0) {
            $message .= " Sisa percobaan: {$remaining}";
        }

        $this->error($message);
    }

    public function backToLogin(): void
    {
        $this->redirect(route('login'), navigate: true);
    }

    private function redirectBasedOnRole(): void
    {
        $route = Auth::user()->hasRole('driver') ? 'driver.dashboard' : 'app.dashboard';
        $this->redirect(route($route), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.pages.forgot-password');
    }
}
