<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Traits\AuthRateLimiting;

#[Title('Verifikasi Email')]
#[Layout('livewire.layouts.auth')]
class EmailVerification extends Component
{
    use Toast, AuthRateLimiting;

    public bool $emailVerified = false;

    public function mount(): void
    {
        // Initialize default state
        $this->emailVerified = false;
        $this->resetRateLimitState();

        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if (Auth::user()->hasVerifiedEmail()) {
            $this->emailVerified = true;
            $this->redirectToDashboard(Auth::user());
            return;
        }

        // Check initial rate limit state
        $this->checkAuthRateLimit('email_verification');
    }

    public function resendVerification(): void
    {
        // Check rate limit before proceeding
        if ($this->checkAuthRateLimit('email_verification')) {
            $this->showRateLimitError('email_verification');
            return;
        }

        try {
            $user = Auth::user();

            // Double-check if email is already verified
            if ($user->hasVerifiedEmail()) {
                $this->emailVerified = true;
                $this->redirectToDashboard($user);
                return;
            }

            // Send verification email
            $user->sendEmailVerificationNotification();

            $this->success(
                title: 'Email Verifikasi Dikirim!',
                description: 'Kami telah mengirim ulang email verifikasi ke ' . $user->email,
                position: 'toast-top toast-end',
                timeout: 5000
            );

            // Show remaining attempts if low
            if ($this->shouldShowRateLimitWarning('email_verification')) {
                $this->info(
                    title: 'Perhatian',
                    description: "Anda memiliki {$this->remainingAttempts} percobaan tersisa.",
                    position: 'toast-top toast-end',
                    timeout: 3000
                );
            }

        } catch (\Exception $e) {
            $this->error(
                title: 'Gagal Mengirim Email',
                description: 'Tidak dapat mengirim email verifikasi. Coba lagi nanti.',
                timeout: 4000
            );
        }
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirect(route('login'), navigate: true);
    }

    public function continueToApp(): void
    {
        $this->redirectToDashboard(Auth::user());
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
        return $this->getRateLimitInfo('email_verification');
    }

    public function render()
    {
        return view('livewire.auth.pages.email-verification');
    }
}
