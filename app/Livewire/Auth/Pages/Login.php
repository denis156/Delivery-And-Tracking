<?php

namespace App\Livewire\Auth\Pages;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            request()->session()->regenerate();
            $this->success('Login berhasil! Selamat datang, ' . Auth::user()->name);
            $this->redirectBasedOnRole();
        } else {
            $this->error('Email atau password tidak sesuai.');
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
