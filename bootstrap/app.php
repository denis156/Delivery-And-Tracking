<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // * ========================================
        // * CUSTOM MIDDLEWARE ALIASES
        // * ========================================

        $middleware->alias([
            // Authentication Middleware dengan platform support
            'auth' => AuthMiddleware::class,

            // Email Verification Middleware
            'verified' => EnsureEmailIsVerified::class,

            // Guest Middleware dengan platform redirect
            'guest' => GuestMiddleware::class,
        ]);

        // * ========================================
        // * THROTTLE MIDDLEWARE CONFIGURATION
        // * ========================================

        // Configure throttle middleware to use Redis if available
        // untuk mendukung rate limiting yang sudah dibuat di AuthRateLimiterServiceProvider
        // Note: Tidak bisa menggunakan config() helper di sini karena aplikasi belum fully bootstrapped
        // Konfigurasi throttle with Redis akan diatur di AuthRateLimiterServiceProvider

        // * ========================================
        // * WEB MIDDLEWARE GROUP CUSTOMIZATION
        // * ========================================

        // Tambahkan middleware khusus ke web group jika diperlukan
        $middleware->web(append: [
            // Middleware tambahan untuk web routes bisa ditambahkan di sini
            // Misalnya: middleware untuk logging, analytics, etc.
        ]);

        // * ========================================
        // * API MIDDLEWARE GROUP CUSTOMIZATION
        // * ========================================

        // API middleware group configuration jika diperlukan di masa depan
        $middleware->api(prepend: [
            // Middleware khusus untuk API routes
            // Contoh: API versioning, CORS khusus, etc.
        ]);

        // * ========================================
        // * GLOBAL MIDDLEWARE CONFIGURATION
        // * ========================================

        // Tambahkan middleware global jika diperlukan
        // $middleware->append([
        //     // Global middleware yang dijalankan pada setiap request
        // ]);

        // * ========================================
        // * PRIORITY MIDDLEWARE
        // * ========================================

        // Set prioritas middleware jika diperlukan
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            AuthMiddleware::class, // Auth middleware diprioritaskan
            EnsureEmailIsVerified::class, // Email verification setelah auth
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // * ========================================
        // * EXCEPTION HANDLING CONFIGURATION
        // * ========================================

        // Custom exception handling untuk authentication errors
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            // Handle unauthenticated users
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'redirect' => route('login')
                ], 401);
            }

            // Redirect ke login dengan intended URL
            if ($request->isMethod('GET') && !$request->ajax()) {
                session(['url.intended' => $request->fullUrl()]);
            }

            return redirect()->route('login');
        });

        // Custom exception handling untuk rate limiting
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }

            // Untuk web requests, biarkan Laravel handle default behavior
            return null;
        });

        // Custom exception handling untuk email verification
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($e->getMessage() === 'Your email address is not verified.') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your email address is not verified.',
                        'redirect' => route('verification.notice')
                    ], 409);
                }

                return redirect()->route('verification.notice');
            }

            return null;
        });

        // Log authentication attempts untuk security monitoring
        $exceptions->report(function (\Illuminate\Auth\AuthenticationException $e) {
            Log::channel('security')->info('Authentication failed', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'timestamp' => now(),
            ]);
        });

        // Log rate limiting violations
        $exceptions->report(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            Log::channel('security')->warning('Rate limit exceeded', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'timestamp' => now(),
            ]);
        });

        // Don't report duplicate rate limiting exceptions untuk menghindari spam logs
        $exceptions->dontReportDuplicates();
    })
    ->create();
