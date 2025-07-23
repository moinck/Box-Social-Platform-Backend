<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Helpers\Helpers;
use App\Models\SubscriptionPlans;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserSubscription;
use App\Http\Controllers\Controller;
use App\Models\Payments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserSubscriptionNewApiController extends Controller
{
    private $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret_key'));
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $userId = Auth::user()->id;
            $user = User::find($userId);

            // Check for existing active subscription
            $existingSubscription = UserSubscription::where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already have an active subscription'
                ], 400);
            }

            // Get or create Stripe customer
            $userStripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Get plan details
            $planId = Helpers::decrypt($request->plan_id);
            $subscriptionPlanDetail = SubscriptionPlans::find($planId);

            if (!$subscriptionPlanDetail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid subscription plan'
                ], 400);
            }

            DB::beginTransaction();

            // Create incomplete subscription in DB
            $newSubscription = new UserSubscription();
            $newSubscription->user_id = $userId;
            $newSubscription->plan_id = $planId;
            $newSubscription->stripe_customer_id = $userStripeCustomerId;
            $newSubscription->stripe_price_id = $subscriptionPlanDetail->stripe_price_id;
            $newSubscription->total_download_limit = $subscriptionPlanDetail->total_download_limit ?? 0;
            $newSubscription->daily_download_limit = $subscriptionPlanDetail->daily_download_limit ?? 0;
            $newSubscription->status = 'incomplete'; // Important: Set as incomplete
            $newSubscription->save();

            if ($subscriptionPlanDetail->slug == 'free-trial') {
                $this->createFreeTrialSubscription($userId,$newSubscription->id);

                DB::commit();

                // return success response
                return response()->json([
                    'status' => true,
                    'message' => 'Subscription created successfully',
                    'data' => [
                        'subscription_plan' => 'free-trial',
                        'subscription_id' => Helpers::encrypt($newSubscription->id),
                        'subscription_status' => $newSubscription->status
                    ]
                ]);
            }

            $encyptedId = Helpers::encrypt($newSubscription->id);

            $successUrl = url(config('app.frontend_url') . '/user-subscription/success') . '?session_id={CHECKOUT_SESSION_ID}&subscription_id=' . $encyptedId;
            $cancelUrl = url(config('app.frontend_url') . '/user-subscription/cancel') . '?subscription_id=' . $encyptedId;

            $checkoutSession = $this->stripe->checkout->sessions->create([
                'customer' => $userStripeCustomerId,
                'mode' => 'subscription',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $subscriptionPlanDetail->stripe_price_id,
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    'user_id' => $userId,
                    'subscription_id' => $newSubscription->id
                ]
            ]);

            // Store session ID for verification
            $newSubscription->stripe_checkout_session_id = $checkoutSession->id;
            $newSubscription->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Checkout session created successfully',
                'data' => [
                    'subscription_plan' => 'premium-plan',
                    'checkout_url' => $checkoutSession->url,
                    'session_id' => $checkoutSession->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription create error: ' . $e->getMessage(), ['function' => 'subscribe', 'trace' => $e->getTraceAsString()]);
            Helpers::sendErrorMailToDeveloper($e);
            return response()->json([
                'status' => false,
                'message' => 'Oops!Failed to create subscription'
            ], 500);
        }
    }

    public function success(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $subscriptionId = $request->get('subscription_id');

            if (!$sessionId || !$subscriptionId) {
                return redirect(config('app.frontend_url') . '/subscription/error?message=Invalid session');
            }

            // Verify session with Stripe
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription', 'customer']
            ]);

            if ($session->payment_status !== 'paid') {
                return redirect(config('app.frontend_url') . '/subscription/error?message=Payment not completed');
            }

            $descyptedSubscriptionId = Helpers::decrypt($subscriptionId);

            DB::beginTransaction();

            $userSubscription = UserSubscription::with('plan:id,name,price')->find($descyptedSubscriptionId);
            
            if (!$userSubscription || $userSubscription->stripe_checkout_session_id !== $sessionId) {
                return redirect(config('app.frontend_url') . '/subscription/error?message=Invalid subscription');
            }

            // Get subscription details from Stripe
            $stripeSubscription = $session->subscription;

            $subscriptionItem = $stripeSubscription->items->data[0] ?? null;
            $trailStart = $stripeSubscription->trial_start;
            

            if ($subscriptionItem) {
                $userSubscription->current_period_start = date('Y-m-d H:i:s', $subscriptionItem->current_period_start);
                $userSubscription->current_period_end = date('Y-m-d H:i:s', $subscriptionItem->current_period_end);
            }
            
            // Update subscription with Stripe data
            $userSubscription->stripe_subscription_id = $stripeSubscription->id;
            $userSubscription->stripe_status = $stripeSubscription->status;
            $userSubscription->status = 'active';
            $userSubscription->amount_paid = $session->amount_total / 100; // Convert from cents
            $userSubscription->currency = $session->currency;            
            $userSubscription->trial_start = date('Y-m-d H:i:s', $trailStart);         
            
            if (!empty($stripeSubscription->default_payment_method)) {
                $userSubscription->stripe_payment_method_id = $stripeSubscription->default_payment_method;
            }

            // store full response from stripe
            $userSubscription->response_meta = json_encode($session, JSON_PRETTY_PRINT);
            
            $userSubscription->save();

            // Reset usage counters for new subscription
            // $userSubscription->resetUsageCounters();

            // create new payments record
            $newPayment = new Payments();
            $newPayment->user_id = $userSubscription->user_id;
            $newPayment->plan_name = $userSubscription->plan->name ?? "Subscription Plan";
            $newPayment->status = 'completed';
            $newPayment->amount = $session->amount_total / 100;
            $newPayment->currency =  $session->currency ?? 'GBP';
            $newPayment->payment_type = 'subscription';
            $newPayment->payment_method = $session->payment_settings->payment_method_types[0] ?? 'card';
            $newPayment->stripe_payment_intent_id = $session->payment_intent ?? ($userSubscription->stripe_payment_method_id ?? null);
            $newPayment->save();

            DB::commit();

            // return redirect(config('app.frontend_url') . '/subscription/success?subscription_id=' . $subscriptionId);
            return response()->json([
                'status' => true,
                'message' => 'Subscription Created successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription success error: ' . $e->getMessage(),['function' => 'success', 'data' => $e->getTraceAsString()]);
            Helpers::sendErrorMailToDeveloper($e);
            // return redirect(config('app.frontend_url') . '/subscription/error?message=Processing failed');
            return response()->json([
                'status' => false,
                'message' => 'Subscription creation failed',
            ]);
        }
    }

    public function cancel(Request $request)
    {
        try {
            $subscriptionId = $request->get('subscription_id');
            
            if ($subscriptionId) {
                $userSubscription = UserSubscription::find(Helpers::decrypt($subscriptionId));
                if ($userSubscription && $userSubscription->status === 'incomplete') {
                    $userSubscription->status = 'cancelled';
                    $userSubscription->stripe_status = 'canceled';
                    $userSubscription->save();
                }
            }

            // return redirect(config('app.frontend_url') . '/subscription/cancelled');
            return response()->json([
                'status' => true,
                'message' => 'Subscription cancelled successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription cancel error: ' . $e->getMessage(),['function' => 'cancel', 'data' => $e->getTraceAsString()]);
            // return redirect(config('app.frontend_url') . '/subscription/error?message=Cancellation failed');
            Helpers::sendErrorMailToDeveloper($e);
            return response()->json([
                'status' => false,
                'message' => 'Subscription cancellation failed',
            ]);
        }
    }

    // Webhook handler for Stripe events
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\Exception $e) {
            Log::error('Subscription webhook error: ' . $e->getMessage(),['function' => 'webhook', 'data' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event['type']) {
            case 'invoice.payment_succeeded':
                $this->handleSuccessfulPayment($event['data']['object']);
                break;
            case 'invoice.payment_failed':
                $this->handleFailedPayment($event['data']['object']);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event['data']['object']);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionCancelled($event['data']['object']);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function getOrCreateStripeCustomer($user)
    {
        if (!empty($user->stripe_customer_id)) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'metadata' => [
                'user_id' => $user->id
            ]
        ]);

        $user->stripe_customer_id = $customer->id;
        $user->save();

        return $customer->id;
    }

    private function handleSuccessfulPayment($invoice)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $invoice['subscription'])->first();
        if ($subscription) {
            $subscription->last_payment_date = now();
            $subscription->save();
        }
    }

    private function handleFailedPayment($invoice)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $invoice['subscription'])->first();
        if ($subscription) {
            $subscription->status = 'past_due';
            $subscription->save();
        }
    }

    private function handleSubscriptionUpdated($stripeSubscription)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription['id'])->first();
        if ($subscription) {
            $subscription->stripe_status = $stripeSubscription['status'];
            $subscription->current_period_start = date('Y-m-d H:i:s', $stripeSubscription['current_period_start']);
            $subscription->current_period_end = date('Y-m-d H:i:s', $stripeSubscription['current_period_end']);
            $subscription->save();
        }
    }

    private function handleSubscriptionCancelled($stripeSubscription)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription['id'])->first();
        if ($subscription) {
            $subscription->status = 'cancelled';
            $subscription->stripe_status = 'canceled';
            $subscription->ends_at = now();
            $subscription->save();
        }
    }


    public function getCurrentSubscription()
    {
        $subscription = UserSubscription::with('plan:id,name,price')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        $planDetails = [];
        if($subscription){
            $planDetails = [
                'id' => Helpers::encrypt($subscription->plan->id),
                'name' => $subscription->plan->name,
            ];
        }
            
        $returnData = [];
        if($subscription){
            $returnData = [
                'id' => Helpers::encrypt($subscription->id),
                'status' => $subscription->status,
                'amount_paid' => $subscription->amount_paid,
                'currency' => $subscription->currency,
                'current_period_start' => date("Y-m-d",strtotime($subscription->current_period_start)),
                'current_period_end' => date("Y-m-d",strtotime($subscription->current_period_end)),
                'total_download_limit' => $subscription->total_download_limit,
                'daily_download_limit' => $subscription->daily_download_limit,
                'download_used_today' => $subscription->downloads_used_today ?? 0,
                'remaining_download_limit' => abs($subscription->daily_download_limit - ($subscription->downloads_used_today ?? 0)),
                'plan_details' => $planDetails,
            ];
        }
        // $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->stripe_subscription_id);
        return response()->json([
            'status' => true,
            'data' => $returnData,
            'stripe_subscription' => []
        ]);
    }

    /**
     * Create free trial subscription
     */
    private function createFreeTrialSubscription($userId,$subscriptionId)
    {
        $userSubscription = UserSubscription::find($subscriptionId);
        $userSubscription->status = 'active';
        $userSubscription->amount_paid = 0;
        $userSubscription->currency = 'GBP';
        $userSubscription->total_download_limit = 3;
        $userSubscription->daily_download_limit = 3;
        $userSubscription->downloads_used_today = 0;
        $userSubscription->current_period_start = now();
        $userSubscription->current_period_end = now()->addDays(3);
        $userSubscription->trial_start = now();
        $userSubscription->trial_end = now()->addDays(3);
        $userSubscription->save();

        return true;
    }

    public function cancelSubscription()
    {
        $authUser = Auth::user();
        $subscription = UserSubscription::where('user_id', $authUser->id)
            ->where('status', 'active')
            ->first();

        if ($subscription) {

            // get plan detais
            $plan = SubscriptionPlans::where('id', $subscription->plan_id)->first();
            if ($plan->slug == 'free-trial') {
                $subscription->status = 'cancelled';
                $subscription->stripe_status = 'canceled';
                $subscription->cancelled_at = now();
                $subscription->ends_at = now();
                $subscription->save();
            }else{
                $subscription->status = 'cancelled';
                $subscription->stripe_status = 'canceled';
                $subscription->cancelled_at = now();
                $subscription->ends_at = now();
                $subscription->save();
                
                $cancelationDetail = [
                    'user_id' => $authUser->id,
                    'subscription_id' => $subscription->id,
                    'cancelation_reason' => 'User requested cancellation',
                    'cancelation_date' => now(),
                ];
    
                $this->stripe->subscriptions->cancel($subscription->stripe_subscription_id);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Subscription cancelled successfully',
        ]);
    }


    public function downloadLimit(Request $request)
    {
        $authUser = Auth::user();
        $subscription = UserSubscription::select('id','total_download_limit','daily_download_limit','downloads_used_today')
            ->where('user_id', $authUser->id)
            ->where('status', 'active')
            ->first();
        
        $totalDownloadLimit = $subscription->total_download_limit;
        $dailyDownloadLimit = $subscription->daily_download_limit;
        $remainingDownloadLimit = $dailyDownloadLimit - $subscription->downloads_used_today;

        if ($subscription) {
            if ($dailyDownloadLimit > 0) {
                // $subscription->daily_download_limit = $subscription->daily_download_limit - 1;
                $subscription->downloads_used_today = $subscription->downloads_used_today + 1;
                $subscription->save();

                $remainingDownloadLimit = $dailyDownloadLimit - $subscription->downloads_used_today;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Daily download limit exceeded',
                    'data' => [
                        'total_download_limit' => $totalDownloadLimit,
                        'daily_download_limit' => $dailyDownloadLimit,
                        'downloads_used_today' => $subscription->downloads_used_today,
                        'remaining_download_limit' => $remainingDownloadLimit,
                    ]
                ]);
            }

            $dailyDownloadLimit = $subscription->daily_download_limit;
        }

        $returnData = [
            'total_download_limit' => $totalDownloadLimit,
            'daily_download_limit' => $dailyDownloadLimit,
            'downloads_used_today' => $subscription->downloads_used_today,
            'remaining_download_limit' => $remainingDownloadLimit,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Subscription download limit updated successfully',
            'data' => $returnData,
        ]);
    }
    
}