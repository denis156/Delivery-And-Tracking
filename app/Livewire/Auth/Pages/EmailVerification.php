<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

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

        try {
            $user->sendEmailVerificationNotification();
            $this->success('Email verifikasi telah dikirim ulang ke ' . $user->email);
        } catch (\Exception $e) {
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
