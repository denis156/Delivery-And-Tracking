<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ?string $platform = null): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return $this->redirectToLogin($request, $platform);
        }

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

        // Validasi akses berdasarkan platform dan role jika platform ditentukan
        if ($platform && !$this->hasAccessToPlatform($user, $platform)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->redirectToCorrectPlatform($user);
        }

        // Auto-detect platform berdasarkan URL jika platform tidak ditentukan
        if (!$platform) {
            $platform = $this->detectPlatformFromRequest($request);

            if ($platform && !$this->hasAccessToPlatform($user, $platform)) {
                return $this->redirectToCorrectPlatform($user);
            }
        }

        return $next($request);
    }

    /**
     * Detect platform dari request URL
     */
    protected function detectPlatformFromRequest(Request $request): ?string
    {
        $path = $request->path();

        if (str_starts_with($path, 'driver/')) {
            return 'driver';
        }

        if (str_starts_with($path, 'app/')) {
            return 'app';
        }

        return null;
    }

    /**
     * Cek apakah user memiliki akses ke platform tertentu
     */
    protected function hasAccessToPlatform($user, string $platform): bool
    {
        if ($platform === 'driver') {
            return $user->role === 'driver';
        }

        // Platform app - semua role kecuali driver
        return $user->role !== 'driver';
    }

    /**
     * Redirect user ke platform yang sesuai dengan role-nya
     */
    protected function redirectToCorrectPlatform($user): Response
    {
        if ($user->role === 'driver') {
            return redirect()->route('driver.dashboard');
        }

        return redirect()->route('app.dashboard');
    }

    /**
     * Alihkan pengguna yang tidak terautentikasi ke halaman login.
     *
     * Metode ini menyimpan URL yang dimaksud untuk permintaan GET yang bukan AJAX,
     * dan mengembalikan respon JSON yang menunjukkan status tidak terautentikasi untuk permintaan AJAX.
     * Jika tidak, maka akan dialihkan ke rute login utama.
     *
     * @param Request $request Permintaan HTTP saat ini.
     * @param string|null $platform Platform yang sedang diakses (app/driver)
     * @return Response Respon pengalihan ke rute login atau respon JSON untuk permintaan AJAX.
     */
    protected function redirectToLogin(Request $request, ?string $platform): Response
    {
        // Simpan intended URL
        if ($request->isMethod('GET') && !$request->ajax()) {
            session(['url.intended' => $request->fullUrl()]);
        }

        // AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Redirect ke login utama (bukan per platform)
        return redirect()->route('login');
    }
}
