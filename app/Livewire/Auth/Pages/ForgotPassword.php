<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

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

        // Clear existing tokens to bypass throttling
        DB::table('password_reset_tokens')->where('email', $this->email)->delete();

        // Process resend
        $this->processSendReset(true);
    }

    private function processSendReset(bool $isResend = false): void
    {
        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->linkSent = true;
            $message = $isResend
                ? 'Link reset password telah dikirim ulang ke ' . $this->email
                : 'Link reset password telah dikirim ke ' . $this->email;
            $this->success($message);
        } else {
            $this->handleError($status);
        }
    }

    private function handleError(string $status): void
    {
        $message = match($status) {
            Password::RESET_THROTTLED => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem.',
            default => 'Gagal mengirim link reset password. Coba lagi nanti.'
        };

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
