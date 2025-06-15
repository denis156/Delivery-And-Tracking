<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Providers\AuthRateLimiterServiceProvider;

trait AuthRateLimiting
{
    /**
     * Rate limiting state properties
     */
    public bool $isRateLimited = false;
    public int $rateLimitSeconds = 0;
    public int $remainingAttempts = 0;

    /**
     * Check if the given action is rate limited using Laravel's RateLimiter.
     *
     * @param string $action The action name (login, email-verification, etc.)
     * @param array $params Additional parameters for the rate limiter
     * @return bool True if rate limited, false otherwise
     */
    protected function checkAuthRateLimit(string $action, array $params = []): bool
    {
        $limits = AuthRateLimiterServiceProvider::getAuthRateLimits();

        if (!isset($limits[$action])) {
            return false;
        }

        $config = $limits[$action];
        $keys = $this->buildRateLimitKeys($action, $params);

        // Check each rate limit type
        foreach ($keys as $keyData) {
            $key = $keyData['key'];
            $maxAttempts = $keyData['max_attempts'];

            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $this->setRateLimitState($key, $maxAttempts, $keyData['type']);
                return true;
            }
        }

        // If not rate limited, update remaining attempts and hit the rate limiter
        $this->updateRemainingAttempts($keys);
        $this->hitRateLimiters($keys, $config['window_minutes']);

        return false;
    }

    /**
     * Build rate limit keys based on action and parameters.
     *
     * @param string $action
     * @param array $params
     * @return array
     */
    protected function buildRateLimitKeys(string $action, array $params = []): array
    {
        $limits = AuthRateLimiterServiceProvider::getAuthRateLimits()[$action];
        $keys = [];

        switch ($action) {
            case 'login':
                $email = $params['email'] ?? 'unknown';
                $keys = [
                    [
                        'key' => 'login:email:' . $email,
                        'max_attempts' => $limits['per_email'],
                        'type' => 'email'
                    ],
                    [
                        'key' => 'login:ip:' . request()->ip(),
                        'max_attempts' => $limits['per_ip'],
                        'type' => 'ip'
                    ],
                ];
                break;

            case 'email_verification':
                $userId = Auth::user()?->id ?: 'guest';
                $keys = [
                    [
                        'key' => 'verification:user:' . $userId,
                        'max_attempts' => $limits['per_user'],
                        'type' => 'user'
                    ],
                    [
                        'key' => 'verification:ip:' . request()->ip(),
                        'max_attempts' => $limits['per_ip'],
                        'type' => 'ip'
                    ],
                ];
                break;

            case 'forgot_password':
                $email = $params['email'] ?? 'unknown';
                $keys = [
                    [
                        'key' => 'forgot:email:' . $email,
                        'max_attempts' => $limits['per_email'],
                        'type' => 'email'
                    ],
                    [
                        'key' => 'forgot:ip:' . request()->ip(),
                        'max_attempts' => $limits['per_ip'],
                        'type' => 'ip'
                    ],
                ];
                break;

            case 'password_reset':
                $token = $params['token'] ?? 'no-token';
                $keys = [
                    [
                        'key' => 'reset:token:' . substr($token, 0, 10),
                        'max_attempts' => $limits['per_token'],
                        'type' => 'token'
                    ],
                    [
                        'key' => 'reset:ip:' . request()->ip(),
                        'max_attempts' => $limits['per_ip'],
                        'type' => 'ip'
                    ],
                ];
                break;

            default:
                $userId = Auth::user()?->id ?: 'guest';
                $keys = [
                    [
                        'key' => 'auth:user:' . $userId,
                        'max_attempts' => $limits['per_user'] ?? 30,
                        'type' => 'user'
                    ],
                    [
                        'key' => 'auth:ip:' . request()->ip(),
                        'max_attempts' => $limits['per_ip'] ?? 60,
                        'type' => 'ip'
                    ],
                ];
        }

        return $keys;
    }

    /**
     * Set the rate limit state when a limit is exceeded.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param string $type
     * @return void
     */
    protected function setRateLimitState(string $key, int $maxAttempts, string $type): void
    {
        $this->isRateLimited = true;
        $this->rateLimitSeconds = RateLimiter::availableIn($key);
        $this->remainingAttempts = 0;
    }

    /**
     * Update remaining attempts for the user.
     *
     * @param array $keys
     * @return void
     */
    protected function updateRemainingAttempts(array $keys): void
    {
        $minRemaining = PHP_INT_MAX;

        foreach ($keys as $keyData) {
            $currentAttempts = RateLimiter::attempts($keyData['key']);
            $remaining = max(0, $keyData['max_attempts'] - $currentAttempts);
            $minRemaining = min($minRemaining, $remaining);
        }

        $this->remainingAttempts = $minRemaining === PHP_INT_MAX ? 0 : $minRemaining;
    }

    /**
     * Hit all rate limiters for the action.
     *
     * @param array $keys
     * @param int $windowMinutes
     * @return void
     */
    protected function hitRateLimiters(array $keys, int $windowMinutes): void
    {
        $decayInSeconds = $windowMinutes * 60;

        foreach ($keys as $keyData) {
            RateLimiter::hit($keyData['key'], $decayInSeconds);
        }
    }

    /**
     * Clear rate limiters for successful actions (like successful login).
     *
     * @param string $action
     * @param array $params
     * @return void
     */
    protected function clearAuthRateLimit(string $action, array $params = []): void
    {
        $keys = $this->buildRateLimitKeys($action, $params);

        foreach ($keys as $keyData) {
            RateLimiter::clear($keyData['key']);
        }

        $this->resetRateLimitState();
    }

    /**
     * Reset rate limiting state properties.
     *
     * @return void
     */
    protected function resetRateLimitState(): void
    {
        $this->isRateLimited = false;
        $this->rateLimitSeconds = 0;
        $this->remainingAttempts = 0;
    }

    /**
     * Get a user-friendly rate limit message.
     *
     * @param string $action
     * @param string $type
     * @return string
     */
    protected function getRateLimitMessage(string $action, string $type = ''): string
    {
        $minutes = ceil($this->rateLimitSeconds / 60);

        $messages = [
            'login' => [
                'email' => 'Email ini sudah terlalu sering mencoba login.',
                'ip' => 'Terlalu banyak percobaan login dari IP Anda.',
                'default' => 'Terlalu banyak percobaan login.',
            ],
            'email_verification' => [
                'user' => 'Anda sudah terlalu sering meminta verifikasi email.',
                'ip' => 'Terlalu banyak permintaan verifikasi dari IP Anda.',
                'default' => 'Terlalu banyak permintaan verifikasi email.',
            ],
            'forgot_password' => [
                'email' => 'Email ini sudah terlalu sering meminta reset password.',
                'ip' => 'Terlalu banyak permintaan reset dari IP Anda.',
                'default' => 'Terlalu banyak permintaan reset password.',
            ],
            'password_reset' => [
                'token' => 'Token ini sudah terlalu sering digunakan.',
                'ip' => 'Terlalu banyak percobaan reset dari IP Anda.',
                'default' => 'Terlalu banyak percobaan reset password.',
            ],
        ];

        $actionMessages = $messages[$action] ?? $messages['login'];
        $message = $actionMessages[$type] ?? $actionMessages['default'];

        return $message . " Coba lagi dalam {$minutes} menit.";
    }

    /**
     * Show rate limit warning when approaching limits.
     *
     * @param string $action
     * @return bool True if should show warning
     */
    protected function shouldShowRateLimitWarning(string $action): bool
    {
        return !$this->isRateLimited && $this->remainingAttempts <= 2 && $this->remainingAttempts > 0;
    }

    /**
     * Get rate limit info for display in UI.
     *
     * @param string $action
     * @return array
     */
    protected function getRateLimitInfo(string $action): array
    {
        $limits = AuthRateLimiterServiceProvider::getAuthRateLimits()[$action] ?? [];

        return [
            'is_limited' => $this->isRateLimited,
            'remaining_attempts' => $this->remainingAttempts,
            'seconds_until_reset' => $this->rateLimitSeconds,
            'minutes_until_reset' => ceil($this->rateLimitSeconds / 60),
            'should_show_warning' => $this->shouldShowRateLimitWarning($action),
            'limits' => $limits,
        ];
    }
}
