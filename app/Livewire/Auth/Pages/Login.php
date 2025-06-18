<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

#[Title('Masuk ke')]
#[Layout('livewire.layouts.auth')]
class Login extends Component
{
    use Toast;

    #[Validate('required', message: 'Email wajib diisi')]
    #[Validate('email', message: 'Format email tidak valid')]
    public string $email = '';

    #[Validate('required', message: 'Password wajib diisi')]
    #[Validate('min:6', message: 'Password minimal 6 karakter')]
    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectBasedOnRole();
        }
    }

    public function login(): void
    {
        $this->validate();

        // Rate limiting key berdasarkan IP dan email
        $rateLimitKey = 'login-attempts:' . request()->ip() . ':' . strtolower($this->email);

        // Cek apakah sudah terlalu banyak percobaan - 6 kali pertama
        if (RateLimiter::tooManyAttempts($rateLimitKey, 6)) {
            // Setelah 6 kali, cek apakah sudah 3 kali lagi (total 9)
            $extendedKey = $rateLimitKey . ':extended';

            if (RateLimiter::tooManyAttempts($extendedKey, 3)) {
                $seconds = RateLimiter::availableIn($extendedKey);
                $minutes = ceil($seconds / 60);

                $this->error("Akun diblokir sementara. Coba lagi dalam {$minutes} menit.");
                return;
            }
        }

        if (Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            // Login berhasil - clear semua rate limit attempts
            RateLimiter::clear($rateLimitKey);
            RateLimiter::clear($rateLimitKey . ':extended');

            request()->session()->regenerate();
            $this->success('Login berhasil! Selamat datang, ' . Auth::user()->name);
            $this->redirectBasedOnRole();
        } else {
            // Login gagal - increment attempts
            if (RateLimiter::attempts($rateLimitKey) >= 6) {
                // Sudah 6 kali, masuk ke extended limiting
                $extendedKey = $rateLimitKey . ':extended';
                RateLimiter::increment($extendedKey, 1, 1800); // 30 menit untuk extended

                $remaining = RateLimiter::remaining($extendedKey, 3);
                if ($remaining > 0) {
                    $this->error("Email atau password tidak sesuai. Peringatan: Sisa {$remaining} percobaan sebelum akun diblokir 30 menit.");
                }
            } else {
                // Masih dalam 6 kali pertama
                RateLimiter::increment($rateLimitKey, 1, 900); // 15 menit untuk basic

                $remaining = RateLimiter::remaining($rateLimitKey, 6);
                $this->error("Email atau password tidak sesuai. Sisa percobaan: {$remaining}");
            }
        }
    }

    private function redirectBasedOnRole(): void
    {
        $route = Auth::user()->hasRole('driver') ? 'driver.dashboard' : 'app.dashboard';
        $this->redirect(route($route), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.pages.login');
    }
}
