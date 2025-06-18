<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register Spatie Permission Middleware Aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle Spatie Permission UnauthorizedException
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }

            // Redirect berdasarkan role user
            $user = Auth::user();
            if ($user) {
                if ($user->hasRole('driver')) {
                    return redirect()->route('driver.dashboard')
                        ->with('error', 'Akses ditolak. Anda akan diarahkan ke dashboard driver.');
                } else {
                    return redirect()->route('app.dashboard')
                        ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman tersebut.');
                }
            }

            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        });
    })->create();
