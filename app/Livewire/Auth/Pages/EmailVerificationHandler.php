<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

#[Title('Verifikasi Email')]
#[Layout('livewire.layouts.auth')]
class EmailVerificationHandler extends Component
{
    use Toast;

    public bool $verificationProcessed = false;
    public bool $verificationSuccess = false;
    public string $message = '';

    public function mount(Request $request, string $id, string $hash): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectBasedOnRole();
            return;
        }

        $this->processVerification($request, $user, $id, $hash);
    }

    private function processVerification(Request $request, $user, string $id, string $hash): void
    {
        $this->verificationProcessed = true;

        // Basic validation
        if (!$request->hasValidSignature() ||
            (string) $id !== (string) $user->getKey() ||
            !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {

            $this->verificationSuccess = false;
            $this->message = 'Link verifikasi tidak valid atau sudah kadaluarsa.';
            $this->error($this->message);
            return;
        }

        // Process verification
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $this->verificationSuccess = true;
            $this->message = 'Email Anda berhasil diverifikasi!';
            $this->success($this->message);
        } else {
            $this->verificationSuccess = false;
            $this->message = 'Gagal memverifikasi email. Silakan coba lagi.';
            $this->error($this->message);
        }
    }

    public function continueToApp(): void
    {
        $this->redirectBasedOnRole();
    }

    public function resendVerification(): void
    {
        $this->redirect(route('verification.notice'), navigate: true);
    }

    private function redirectBasedOnRole(): void
    {
        $route = Auth::user()->hasRole('driver') ? 'driver.dashboard' : 'app.dashboard';
        $this->redirect(route($route), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.pages.email-verification-handler');
    }
}
