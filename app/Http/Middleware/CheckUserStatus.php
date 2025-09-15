<?php

namespace App\Http\Middleware;

use App\Models\UserDownloads;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($user) {
            //Check Subscription
            $currentDateTime = Carbon::now();
            $expiredSubscriptions = UserSubscription::where('current_period_end', '<', $currentDateTime)
                ->where('user_id',$user->id)
                ->where('status', 'active')
                ->whereIn('stripe_status', ['active','paid'])
                ->get();

            if ($expiredSubscriptions->isNotEmpty()) {

                foreach ($expiredSubscriptions as $subscription) {
                    DB::beginTransaction();
                    // Update subscription status
                    $subscription->status = 'ended';
                    $subscription->stripe_status = 'canceled';
                    $subscription->ends_at = $currentDateTime;
                    $subscription->save();
                    
                    // Also update related UserDownloads if exists
                    $userDownload = UserDownloads::where('user_subscription_id', $subscription->id)->first();
                    if ($userDownload) {
                        // Set expiration for the download tracker
                        $userDownload->expires_at = $currentDateTime;
                        $userDownload->save();
                    }
                    
                    DB::commit();
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Your subscription was expired.'
                ], 307);

            }
        }

        return $next($request);
    }
}
