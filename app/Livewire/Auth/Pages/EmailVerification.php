<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

#[Title('Verifikasi Email')]
#[Layout('livewire.layouts.auth')]
class EmailVerification extends Component
{
    use Toast;

    public bool $emailVerified = false;

    public function mount(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if (Auth::user()->hasVerifiedEmail()) {
            $this->emailVerified = true;
            $this->redirectBasedOnRole();
            return;
        }
    }

    public function resendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->emailVerified = true;
            $this->redirectBasedOnRole();
            return;
        }

        // Rate limiting key berdasarkan user ID
        $rateLimitKey = 'email-verification:' . $user->id;

        // Cek rate limit - 6 percobaan per 10 menit
        if (RateLimiter::tooManyAttempts($rateLimitKey, 6)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            $this->error("Terlalu banyak permintaan verifikasi email. Coba lagi dalam {$minutes} menit.");
            return;
        }

        try {
            $user->sendEmailVerificationNotification();

            // Increment rate limit counter
            RateLimiter::increment($rateLimitKey, 1, 600); // 10 menit

            $remaining = RateLimiter::remaining($rateLimitKey, 6);
            $message = 'Email verifikasi telah dikirim ulang ke ' . $user->email;

            if ($remaining > 0) {
                $message .= ". Sisa percobaan: {$remaining}";
            }

            $this->success($message);
        } catch (\Exception $e) {
            // Increment rate limit counter bahkan jika gagal
            RateLimiter::increment($rateLimitKey, 1, 600);

            $this->error('Gagal mengirim email verifikasi. Coba lagi nanti.');
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
        $this->redirectBasedOnRole();
    }

    private function redirectBasedOnRole(): void
    {
        $route = Auth::user()->hasRole('driver') ? 'driver.dashboard' : 'app.dashboard';
        $this->redirect(route($route), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.pages.email-verification');
    }
}
