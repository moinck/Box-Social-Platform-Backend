<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // check user status
        if (!$user || $user->status !== 'active') {
            // logout the user
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support for assistance.'
            ], 401);
        }
        return $next($request);
    }
}
