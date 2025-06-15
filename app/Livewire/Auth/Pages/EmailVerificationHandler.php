<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Traits\AuthRateLimiting;

#[Title('Verifikasi Email')]
#[Layout('livewire.layouts.auth')]
class EmailVerificationHandler extends Component
{
    use Toast, AuthRateLimiting;

    public bool $verificationProcessed = false;
    public bool $verificationSuccess = false;
    public string $message = '';

    public function mount(Request $request, string $id, string $hash): void
    {
        // Initialize default state
        $this->verificationProcessed = false;
        $this->verificationSuccess = false;
        $this->message = '';
        $this->resetRateLimitState();

        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectToDashboard($user);
            return;
        }

        // Process verification immediately
        $this->processVerification($request, $user, $id, $hash);
    }

    private function processVerification(Request $request, $user, string $id, string $hash): void
    {
        // Verifikasi signature URL
        if (!$request->hasValidSignature()) {
            $this->handleVerificationFailure('Link verifikasi tidak valid atau sudah kadaluarsa.');
            return;
        }

        // Cek apakah ID dan hash cocok
        if ((string) $id !== (string) $user->getKey()) {
            $this->handleVerificationFailure('Link verifikasi tidak sesuai dengan akun Anda.');
            return;
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            $this->handleVerificationFailure('Link verifikasi tidak valid.');
            return;
        }

        // Proses verifikasi
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            // Clear any existing rate limits for this user since verification is successful
            $this->clearAuthRateLimit('email_verification');

            $this->handleVerificationSuccess();
        } else {
            $this->handleVerificationFailure('Gagal memverifikasi email. Silakan coba lagi.');
        }
    }

    private function handleVerificationSuccess(): void
    {
        $this->verificationProcessed = true;
        $this->verificationSuccess = true;
        $this->message = 'Email Anda berhasil diverifikasi!';

        $this->success(
            title: 'Email Terverifikasi!',
            description: 'Selamat! Email Anda berhasil diverifikasi.',
            position: 'toast-top toast-end',
            timeout: 5000
        );
    }

    private function handleVerificationFailure(string $message): void
    {
        $this->verificationProcessed = true;
        $this->verificationSuccess = false;
        $this->message = $message;

        $this->error(
            title: 'Verifikasi Gagal',
            description: $message,
            position: 'toast-top toast-end',
            timeout: 5000
        );
    }

    public function continueToApp(): void
    {
        $this->redirectToDashboard(Auth::user());
    }

    public function resendVerification(): void
    {
        // Check rate limit before redirecting
        if ($this->checkAuthRateLimit('email_verification')) {
            $this->showRateLimitError('email_verification');
            return;
        }

        $this->redirect(route('verification.notice'), navigate: true);
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
        return view('livewire.auth.pages.email-verification-handler');
    }
}
