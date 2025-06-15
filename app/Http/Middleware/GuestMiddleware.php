<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next, string $platform = 'app'): Response
    {
        // Jika user sudah login, redirect sesuai platform dan role
        if (Auth::check()) {
            $user = Auth::user();

            // Cek apakah user aktif
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.']);
            }

            return $this->redirectAuthenticatedUser($user);
        }

        // User belum login, lanjutkan ke halaman login
        return $next($request);
    }

    /**
     * Redirect authenticated user ke dashboard yang sesuai dengan role-nya
     */
    protected function redirectAuthenticatedUser($user): Response
    {
        if ($user->role === 'driver') {
            return redirect()->route('driver.dashboard');
        }

        // Role selain driver ke dashboard app
        return redirect()->route('app.dashboard');
    }
}
