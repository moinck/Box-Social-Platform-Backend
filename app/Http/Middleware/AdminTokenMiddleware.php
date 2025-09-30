<?php

namespace App\Http\Middleware;

use App\Models\UserTokens;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AdminTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from header or query
        $providedToken = $request->header('Authorization')
            ?? $request->header('token');

        // Strip 'Bearer ' if present
        if ($providedToken && str_starts_with($providedToken, 'Bearer ')) {
            $providedToken = substr($providedToken, 7);
        }

        if (!$providedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin access token is required.',
            ], 401);
        }

        // Check for valid token: not used and created within the last 1 day
        $validToken = UserTokens::where('token', $providedToken)
            ->where('type', 'admin-access-token')
            ->where('is_used', false)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->first();

        if (!$validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired admin access token.',
            ], 401);
        }

        return $next($request);
    }
}
