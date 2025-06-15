<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\AuthRateLimiting;

#[Title('Masuk ke')]
#[Layout('livewire.layouts.auth')]
class Login extends Component
{
    use Toast, AuthRateLimiting;

    #[Validate('required|email|max:255', message: [
        'required' => 'Email wajib diisi.',
        'email' => 'Format email tidak valid.',
    ])]
    public string $email = '';

    #[Validate('required|string|min:6', message: [
        'required' => 'Password wajib diisi.',
        'min' => 'Password minimal 6 karakter.',
    ])]
    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectToDashboard(Auth::user());
        }

        // Demo data untuk development
        if (app()->environment('local')) {
            $this->email = 'admin@artelia.dev';
        }

        $this->resetRateLimitState();
        $this->checkAuthRateLimit('login', ['email' => $this->email]);
    }

    public function updated($property): void
    {
        if ($property === 'email' && !empty($this->email)) {
            $this->checkAuthRateLimit('login', ['email' => $this->email]);
        }
    }

    public function login(): void
    {
        // Check rate limit before processing
        if ($this->checkAuthRateLimit('login', ['email' => $this->email])) {
            $this->showRateLimitError('login');
            return;
        }

        $this->validate();

        // Check user existence and status
        $user = User::where('email', $this->email)->first();

        if ($user && $user->trashed()) {
            $this->error(
                title: 'Akun Tidak Tersedia',
                description: 'Akun Anda telah dihapus dari sistem. Hubungi administrator.',
                timeout: 5000
            );
            return;
        }

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            $user = Auth::user();

            // Clear rate limiter for successful login
            $this->clearAuthRateLimit('login', ['email' => $this->email]);
            request()->session()->regenerate();

            $this->success(
                title: 'Login Berhasil!',
                description: 'Selamat datang, ' . $user->name . '!',
                position: 'toast-top toast-end',
                timeout: 3000
            );

            $this->redirectToDashboard($user);
        } else {
            $this->handleLoginFailure($user);
        }
    }

    private function handleLoginFailure($user): void
    {
        if ($user && !$user->is_active) {
            $this->warning(
                title: 'Akun Tidak Aktif',
                description: 'Akun Anda tidak aktif. Hubungi administrator.',
                timeout: 4000
            );
        } else {
            $this->warning(
                title: 'Login Gagal',
                description: 'Email atau password tidak sesuai.',
                timeout: 4000
            );
        }

        // Show remaining attempts if low
        if ($this->shouldShowRateLimitWarning('login')) {
            $this->info(
                title: 'Perhatian',
                description: "Anda memiliki {$this->remainingAttempts} percobaan tersisa.",
                position: 'toast-top toast-end',
                timeout: 3000
            );
        }
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

    // Demo methods untuk development
    public function fillDemoAdmin(): void
    {
        if (app()->environment('local')) {
            $this->email = 'admin@artelia.dev';
            $this->password = '@Password123';
        }
    }

    public function fillDemoManager(): void
    {
        if (app()->environment('local')) {
            $this->email = 'manager@artelia.dev';
            $this->password = '@Password123';
        }
    }

    public function fillDemoDriver(): void
    {
        if (app()->environment('local')) {
            $this->email = 'driver@artelia.dev';
            $this->password = '@Password123';
        }
    }

    public function fillDemoClient(): void
    {
        if (app()->environment('local')) {
            $this->email = 'client@artelia.dev';
            $this->password = '@Password123';
        }
    }

    /**
     * Get rate limit information for the view
     */
    public function getRateLimitInfoProperty(): array
    {
        return $this->getRateLimitInfo('login');
    }

    public function render()
    {
        return view('livewire.auth.pages.login');
    }
}
