<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user implements MustVerifyEmail and email is not verified
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            // For AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified.',
                    'redirect' => route('verification.notice')
                ], 409);
            }

            // For regular requests
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
