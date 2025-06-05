<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next, $redirectToRoute = null)
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {
            
            return response()->json([
                'success' => false,
                'message' => 'Your email address is not verified. Please verify your email first.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ], 403);
        }

        return $next($request);
    }
}
