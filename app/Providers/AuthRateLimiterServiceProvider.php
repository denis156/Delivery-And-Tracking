<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AuthRateLimiterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureAuthRateLimiters();
    }

    /**
     * Configure rate limiters for authentication-related actions.
     */
    protected function configureAuthRateLimiters(): void
    {
        // Login Rate Limiting
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email', 'unknown');

            return [
                // Per email: 5 attempts every 5 minutes - prevent targeted attacks
                Limit::perMinutes(5, 5)->by('login:email:' . $email),

                // Per IP: 10 attempts every 5 minutes - prevent IP-based attacks
                Limit::perMinutes(5, 10)->by('login:ip:' . $request->ip()),

                // Global safety net: 50 attempts every 5 minutes - prevent large scale attacks
                Limit::perMinutes(5, 50)->by('login:global'),
            ];
        });

        // Email Verification Rate Limiting
        RateLimiter::for('email-verification', function (Request $request) {
            $userId = $request->user()?->id ?: 'guest';

            return [
                // Per user: 5 attempts every 5 minutes - more lenient for legitimate users
                Limit::perMinutes(5, 5)->by('verification:user:' . $userId),

                // Per IP: 15 attempts every 5 minutes - allow multiple users from same network
                Limit::perMinutes(5, 15)->by('verification:ip:' . $request->ip()),

                // Global safety net: 100 attempts every 5 minutes
                Limit::perMinutes(5, 100)->by('verification:global'),
            ];
        });

        // Forgot Password Rate Limiting
        RateLimiter::for('forgot-password', function (Request $request) {
            $email = $request->input('email', 'unknown');

            return [
                // Per email: 3 attempts every 5 minutes - prevent email spam
                Limit::perMinutes(5, 3)->by('forgot:email:' . $email),

                // Per IP: 5 attempts every 5 minutes - prevent IP abuse
                Limit::perMinutes(5, 5)->by('forgot:ip:' . $request->ip()),

                // Global safety net: 30 attempts every 5 minutes
                Limit::perMinutes(5, 30)->by('forgot:global'),
            ];
        });

        // Password Reset Rate Limiting (when submitting new password)
        RateLimiter::for('password-reset', function (Request $request) {
            $token = $request->input('token', 'no-token');

            return [
                // Per token: 5 attempts every 10 minutes - allow retries for form errors
                Limit::perMinutes(10, 5)->by('reset:token:' . substr($token, 0, 10)),

                // Per IP: 10 attempts every 10 minutes
                Limit::perMinutes(10, 10)->by('reset:ip:' . $request->ip()),

                // Global safety net: 50 attempts every 10 minutes
                Limit::perMinutes(10, 50)->by('reset:global'),
            ];
        });

        // Two-Factor Authentication Rate Limiting (if implemented later)
        RateLimiter::for('two-factor', function (Request $request) {
            $userId = $request->user()?->id ?: 'guest';

            return [
                // Per user: 5 attempts every 5 minutes - prevent brute force
                Limit::perMinutes(5, 5)->by('2fa:user:' . $userId),

                // Per IP: 10 attempts every 5 minutes
                Limit::perMinutes(5, 10)->by('2fa:ip:' . $request->ip()),
            ];
        });

        // Account Lockout Protection (for future implementation)
        RateLimiter::for('account-lockout', function (Request $request) {
            $email = $request->input('email', 'unknown');

            return [
                // Per email: 10 failed attempts in 30 minutes triggers lockout
                Limit::perMinutes(30, 10)->by('lockout:email:' . $email),
            ];
        });

        // General Authentication Actions (logout, profile updates, etc.)
        RateLimiter::for('auth-actions', function (Request $request) {
            $userId = $request->user()?->id ?: 'guest';

            return [
                // Per user: 30 actions every 5 minutes - generous for normal usage
                Limit::perMinutes(5, 30)->by('auth:user:' . $userId),

                // Per IP: 60 actions every 5 minutes
                Limit::perMinutes(5, 60)->by('auth:ip:' . $request->ip()),
            ];
        });
    }

    /**
     * Get rate limiter configuration for a specific auth action.
     * This helper method can be used in Livewire components.
     */
    public static function getAuthRateLimits(): array
    {
        return [
            'login' => [
                'per_email' => 5,
                'per_ip' => 10,
                'window_minutes' => 5,
            ],
            'email_verification' => [
                'per_user' => 5,
                'per_ip' => 15,
                'window_minutes' => 5,
            ],
            'forgot_password' => [
                'per_email' => 3,
                'per_ip' => 5,
                'window_minutes' => 5,
            ],
            'password_reset' => [
                'per_token' => 5,
                'per_ip' => 10,
                'window_minutes' => 10,
            ],
            'two_factor' => [
                'per_user' => 5,
                'per_ip' => 10,
                'window_minutes' => 5,
            ],
            'auth_actions' => [
                'per_user' => 30,
                'per_ip' => 60,
                'window_minutes' => 5,
            ],
        ];
    }
}
