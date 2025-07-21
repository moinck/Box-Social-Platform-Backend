<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helpers;
use App\Models\SubscriptionPlans;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserSubscriptionApiController extends Controller
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
            $userId = Auth::id();
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

            // Create pending subscription in DB
            $newSubscription = new UserSubscription();
            $newSubscription->user_id = $userId;
            $newSubscription->plan_id = $planId;
            $newSubscription->stripe_customer_id = $userStripeCustomerId;
            $newSubscription->stripe_price_id = $subscriptionPlanDetail->stripe_price_id;
            $newSubscription->total_download_limit = $subscriptionPlanDetail->total_download_limit;
            $newSubscription->daily_download_limit = $subscriptionPlanDetail->daily_download_limit;
            $newSubscription->status = 'pending'; // Important: Set as pending
            $newSubscription->save();

            $successUrl = url('/api/user-subscription/success') . '?session_id={CHECKOUT_SESSION_ID}&subscription_id=' . $newSubscription->id;
            $cancelUrl = url('/api/user-subscription/cancel') . '?subscription_id=' . $newSubscription->id;

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
                    'checkout_url' => $checkoutSession->url,
                    'session_id' => $checkoutSession->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create subscription: ' . $e->getMessage()
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

            DB::beginTransaction();

            $userSubscription = UserSubscription::find($subscriptionId);
            
            if (!$userSubscription || $userSubscription->stripe_checkout_session_id !== $sessionId) {
                return redirect(config('app.frontend_url') . '/subscription/error?message=Invalid subscription');
            }

            // Get subscription details from Stripe
            $stripeSubscription = $session->subscription;
            
            // Update subscription with Stripe data
            $userSubscription->stripe_subscription_id = $stripeSubscription->id;
            $userSubscription->stripe_status = $stripeSubscription->status;
            $userSubscription->status = 'active';
            $userSubscription->current_period_start = date('Y-m-d H:i:s', $stripeSubscription->current_period_start);
            $userSubscription->current_period_end = date('Y-m-d H:i:s', $stripeSubscription->current_period_end);
            $userSubscription->amount_paid = $session->amount_total / 100; // Convert from cents
            $userSubscription->currency = $session->currency;
            
            if (!empty($stripeSubscription->default_payment_method)) {
                $userSubscription->stripe_payment_method_id = $stripeSubscription->default_payment_method;
            }

            $userSubscription->response_meta = json_encode([
                'session_id' => $sessionId,
                'customer_email' => $session->customer_details->email ?? null,
                'payment_intent' => $session->payment_intent ?? null
            ]);
            
            $userSubscription->save();

            // Reset usage counters for new subscription
            $userSubscription->resetUsageCounters();

            DB::commit();

            return redirect(config('app.frontend_url') . '/subscription/success?subscription_id=' . $subscriptionId);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription success error: ' . $e->getMessage());
            return redirect(config('app.frontend_url') . '/subscription/error?message=Processing failed');
        }
    }

    public function cancel(Request $request)
    {
        try {
            $subscriptionId = $request->get('subscription_id');
            
            if ($subscriptionId) {
                $userSubscription = UserSubscription::find($subscriptionId);
                if ($userSubscription && $userSubscription->status === 'pending') {
                    $userSubscription->status = 'cancelled';
                    $userSubscription->stripe_status = 'canceled';
                    $userSubscription->save();
                }
            }

            return redirect(config('app.frontend_url') . '/subscription/cancelled');

        } catch (\Exception $e) {
            Log::error('Subscription cancel error: ' . $e->getMessage());
            return redirect(config('app.frontend_url') . '/subscription/error?message=Cancellation failed');
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
            'name' => $user->name,
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
}
