<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Traits\AuthRateLimiting;

#[Title('Lupa Password')]
#[Layout('livewire.layouts.auth')]
class ForgotPassword extends Component
{
    use Toast, AuthRateLimiting;

    #[Validate('required|email|max:255|exists:users,email', message: [
        'required' => 'Email wajib diisi.',
        'email' => 'Format email tidak valid.',
        'exists' => 'Email tidak terdaftar dalam sistem.',
    ])]
    public string $email = '';

    public bool $linkSent = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectToDashboard(Auth::user());
        }

        $this->resetRateLimitState();
        $this->checkInitialRateLimit();
    }

    public function updated($property): void
    {
        if ($property === 'email' && !empty($this->email)) {
            $this->checkInitialRateLimit();
        }
    }

    public function sendResetLink(): void
    {
        // Check rate limit before processing
        if ($this->checkAuthRateLimit('forgot_password', ['email' => $this->email])) {
            $this->showRateLimitError('forgot_password');
            return;
        }

        $this->validate();

        // Send reset link
        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->linkSent = true;

            $this->success(
                title: 'Link Berhasil Dikirim!',
                description: 'Link reset password telah dikirim ke ' . $this->email,
                position: 'toast-top toast-end',
                timeout: 5000
            );

            // Show remaining attempts if low
            if ($this->shouldShowRateLimitWarning('forgot_password')) {
                $this->info(
                    title: 'Perhatian',
                    description: "Anda memiliki {$this->remainingAttempts} percobaan tersisa.",
                    position: 'toast-top toast-end',
                    timeout: 3000
                );
            }
        } else {
            $this->warning(
                title: 'Gagal Mengirim Link',
                description: 'Tidak dapat mengirim link reset password. Coba lagi nanti.',
                position: 'toast-top toast-end',
                timeout: 4000
            );
        }
    }

    public function resendLink(): void
    {
        $this->reset(['linkSent']);

        if ($this->checkAuthRateLimit('forgot_password', ['email' => $this->email])) {
            $this->showRateLimitError('forgot_password');
            return;
        }

        $this->info('Mengirim ulang link...', timeout: 2000);
        $this->sendResetLink();
    }

    public function backToLogin(): void
    {
        $this->redirect(route('login'), navigate: true);
    }

    /**
     * Check initial rate limit without hitting the limiter
     */
    private function checkInitialRateLimit(): void
    {
        // This is just to update the UI state, not to actually hit the rate limiter
        $this->checkAuthRateLimit('forgot_password', ['email' => $this->email]);
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
        return $this->getRateLimitInfo('forgot_password');
    }

    public function render()
    {
        return view('livewire.auth.pages.forgot-password');
    }
}
