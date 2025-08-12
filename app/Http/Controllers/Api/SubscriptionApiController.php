<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Payments;
use App\Models\SubscriptionPlans;
use App\Models\User;
use App\Models\UserSubscription;
use App\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Customer;
use Stripe\StripeClient;

class SubscriptionApiController extends Controller
{
    use ResponseTrait;

    private $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret_key'));
    }

    /** Subscribe User Plan */
    public function userPlanSubscribe(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required',
            'payment_method' => 'required',
            'user_details' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $planId = Helpers::decrypt($request->plan_id);
            $userId = Auth::user()->id;
            $user = User::find($userId);
            $user_details = $request->user_details;

            // Get plan details
            $subscriptionPlanDetail = SubscriptionPlans::find($planId);

            if (!$subscriptionPlanDetail) {
                return $this->error('Invalid subscription plan', 400);
            }

            // Check for existing active subscription
            $existingSubscription = UserSubscription::where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            if ($existingSubscription) {
                // check if it is free trial subscription
                if ($existingSubscription->plan_id == 1 && $existingSubscription->status == 'active') {
                    // if user want to buy premium plan than cancel the free trial subscription
                    if ($planId != 1) {
                        $existingSubscription->status = 'cancelled';
                        $existingSubscription->stripe_status = 'canceled';
                        $existingSubscription->cancelled_at = now();
                        $existingSubscription->ends_at = now();
                        $existingSubscription->save();
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'You already have an active free trial subscription'
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'You already have an active premium subscription'
                    ], 400);
                }
            }

            // check if they have ever had a free trial before
            if ($planId == 1) {
                $hasUsedFreeTrial = UserSubscription::where('user_id', $userId)
                    ->where('plan_id', 1)
                    ->exists();
                
                if ($hasUsedFreeTrial) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You have already used your free trial. Please choose a premium plan.'
                    ], 400);
                }
            }

            // Get or create Stripe customer
            $userStripeCustomerId = $this->getOrCreateStripeCustomer($user,$user_details);
            if ($subscriptionPlanDetail->slug != 'free-trial') {
                $today = now();
                $currentYear = $today->year;
                $cutoffDate = Carbon::create($currentYear, 10, 1); // Oct 1, 2025

                // Before Oct 1 → use £650 plan else £780 plan
                if ($today->lt($cutoffDate)) {
                    $subscriptionPlanDetail = SubscriptionPlans::where('id', 2)->first();
                } else {
                    $subscriptionPlanDetail = SubscriptionPlans::where('id', 3)->first();
                }
            }

            // Create incomplete subscription in DB
            $newSubscription = new UserSubscription();
            $newSubscription->user_id = $userId;
            $newSubscription->plan_id = $subscriptionPlanDetail->id;
            $newSubscription->stripe_customer_id = $userStripeCustomerId;
            $newSubscription->stripe_price_id = $subscriptionPlanDetail->stripe_price_id;
            $newSubscription->total_download_limit = $subscriptionPlanDetail->total_download_limit ?? 0;
            $newSubscription->daily_download_limit = $subscriptionPlanDetail->daily_download_limit ?? 0;
            $newSubscription->status = 'incomplete'; // Important: Set as incomplete
            $newSubscription->save();

            // if FREE-TRIAL plan create free subscription without stripe
            if ($subscriptionPlanDetail->slug == 'free-trial') {
                $this->createFreeTrialSubscription($userId,$newSubscription->id);

                DB::commit();

                // return success response
                return response()->json([
                    'status' => true,
                    'message' => 'Free trial subscription created successfully',
                    'data' => [
                        'subscription_plan' => 'free-trial',
                        'subscription_id' => Helpers::encrypt($newSubscription->id),
                        'subscription_status' => 'active'
                    ]
                ]);
            }

            $encyptedId = Helpers::encrypt($newSubscription->id);

            $this->stripe->paymentMethods->attach(
                $request->payment_method,
                ['customer' => $userStripeCustomerId]
            );

            /** Create Subscription */
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $userStripeCustomerId,
                'items' => [[ 'price' => $subscriptionPlanDetail->stripe_price_id ]],
                'payment_behavior' => 'default_incomplete',
                'default_payment_method' => $request->payment_method, // set here
                'payment_settings' => [
                    'payment_method_types' => ['card'],
                    'save_default_payment_method' => 'on_subscription',
                ],
                'expand' => ['latest_invoice.confirmation_secret', 'pending_setup_intent'],
                'metadata' => [
                    'user_id' => $userId,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'subscription_id' => $encyptedId
                ]
            ]);

            // Store session ID for verification
            $newSubscription->stripe_subscription_id = $subscription->id;
            $newSubscription->save();   

            DB::commit();

            $returnData = [
                'subscription_plan' => 'premium-plan',
                'subscriptionId' => $subscription->id,
                'clientSecret' => $subscription->latest_invoice->confirmation_secret->client_secret,
                'user_subscription_id' => $encyptedId
            ];

            return $this->success($returnData, 'Subscription created successfuly.', 200);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error('Oops!Failed to create subscription', 500);
        }
    }

    private function getOrCreateStripeCustomer($user,$user_details=null)
    {
        if (!empty($user->stripe_customer_id)) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'metadata' => [
                'user_id' => $user->id,
                'fca_number' => $user->fca_number ?? null,
                'company_name' => $user_details ? $user_details->company_name : null,
                'address' => $user_details ? $user_details->address : null,
                'country' => $user_details ? $user_details->country : null,
                'email' => $user_details ? $user_details->email : null,
                'first_name' => $user_details ? $user_details->first_name : null,
                'last_name' => $user_details ? $user_details->last_name : null,
                'postal_code' => $user_details ? $user_details->postal_code : null,
                'state' => $user_details ? $user_details->state : null,
                'town_city' => $user_details ? $user_details->town_city : null,
            ]
        ]);

        $user->stripe_customer_id = $customer->id;
        $user->save();

        return $customer->id;
    }

    /**
     * Create free trial subscription
     */
    private function createFreeTrialSubscription($userId,$subscriptionId)
    {
        $userSubscription = UserSubscription::find($subscriptionId);
        $userSubscription->status = 'active';
        $userSubscription->stripe_status = 'active';
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
            // Log::error('Subscription webhook error: ' . $e->getMessage(),['function' => 'webhook', 'data' => $e->getTraceAsString()]);
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

    private function handleSuccessfulPayment($invoice)
    {
        $obj_data = !blank($invoice['lines']['data']) ? $invoice['lines']['data'] : null;

        if ($obj_data) {
            $stripe_subscription_id = $obj_data[0]['parent']['subscription_item_details']['subscription'];
            $period_start = $obj_data[0]['period']['start'];
            $period_end = $obj_data[0]['period']['end'];

            $userSubscription = UserSubscription::where('stripe_subscription_id', $stripe_subscription_id)->first();
            if ($userSubscription) {
                $userSubscription->current_period_start = Carbon::parse($period_start)->format('Y-m-d H:i:s');
                $userSubscription->current_period_end = Carbon::parse($period_end)->format('Y-m-d H:i:s');
                $userSubscription->stripe_status = $invoice['status'];
                $userSubscription->status = 'active';
                $userSubscription->amount_paid = $invoice['amount_total'] / 100;
                $userSubscription->currency = $invoice['currency'];

                if (!empty($invoice['default_payment_method'])) {
                    $userSubscription->stripe_payment_method_id = $invoice['default_payment_method'];
                }

                $userSubscription->response_meta = json_encode($invoice, JSON_PRETTY_PRINT);
                $userSubscription->total_download_limit = 40;
                $userSubscription->daily_download_limit = 0;
                $userSubscription->reset_date = now()->addMonths(1)->startOfMonth()->format('Y-m-d');


                $userSubscription->last_payment_date = now();
                $userSubscription->save();

                // create new payments record
                $newPayment = new Payments();
                $newPayment->user_id = $userSubscription->user_id;
                $newPayment->plan_name = $userSubscription->plan->name ?? "Subscription Plan";
                $newPayment->status = 'completed';
                $newPayment->amount = $invoice['amount_total'] / 100;
                $newPayment->currency =  $invoice['currency'] ?? 'GBP';
                $newPayment->payment_type = 'subscription';
                $newPayment->payment_method = $invoice['payment_settings']['payment_method_types'] ?? 'card';
                $newPayment->stripe_payment_intent_id = $userSubscription->stripe_payment_method_id ? $invoice['default_payment_method'] : null;
                $newPayment->save();
            }
        }
    }

    private function handleFailedPayment($invoice)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $invoice['subscription'])->first();
        if ($subscription) {
            $subscription->response_meta = json_encode($invoice, JSON_PRETTY_PRINT);
            $subscription->status = 'past_due';
            $subscription->save();
        }
    }

    private function handleSubscriptionUpdated($stripeSubscription)
    {

        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription['id'])->first();

        if (!$subscription) {
            return;
        }

        $item = $stripeSubscription['items']['data'][0] ?? null;

        $subscription->update([
            'stripe_status'         => $stripeSubscription['status'],
            'current_period_start'  => $item ? Carbon::parse($item['current_period_start'])->format('Y-m-d H:i:s') : null,
            'current_period_end'    => $item ? Carbon::parse($item['current_period_end'])->format('Y-m-d H:i:s') : null,
        ]);
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

    /** Subscripiton Plan Verification */
    public function userSubscriptionVerify(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                // 'payment_intent_id' => 'required',
                // 'payment_method' => 'required',
                'user_subscription_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // $payment_intent_id = $request->payment_intent_id;
            // $payment_method = $request->payment_method;
            $user_subscription_id = Helpers::decrypt($request->user_subscription_id);

            // $paymentIntent = $this->stripe->paymentIntents->retrieve($payment_intent_id,[]);

            $user_subscription = UserSubscription::with(['plan'])->where('id',$user_subscription_id)->first();

            if (!$user_subscription) {
                return $this->error('Subscription data not found.', 404);
            }
            
            $returnData = [
                'id' => $request->user_subscription_id,
                'plan_id' => $user_subscription->plan_id,
                'stripe_subscription_id' => $user_subscription->stripe_subscription_id,
                'stripe_customer_id' => $user_subscription->stripe_customer_id,
                'stripe_price_id' => $user_subscription->stripe_price_id,
                'stripe_payment_method_id' => $user_subscription->stripe_payment_method_id,
                'plan' => $user_subscription->plan,
                'response_meta' => $user_subscription->response_meta,
                'amount_paid' => $user_subscription->amount_paid,
                'currency' => $user_subscription->currency,
                'status' => $user_subscription->status,
                'current_period_start' => $user_subscription->current_period_start,
                'current_period_end' => $user_subscription->current_period_end,
                'last_payment_date' => $user_subscription->last_payment_date,
                'total_download_limit' => $user_subscription->total_download_limit,
                'daily_download_limit' => $user_subscription->daily_download_limit,
                'downloads_used_today' => $user_subscription->downloads_used_today,
                'total_downloads_used' => $user_subscription->total_downloads_used,
            ];

            if ($user_subscription->response_meta == null) {
                return $this->success([], 'Webhook call not responsed.', 206);
            }

            if ($user_subscription->stripe_status == "paid") {
                return $this->success($returnData, 'Payment has been paid successfully.', 200);
            } else {
                return $this->error('Payment failed', 500);
            }

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("Somethign went wrong.", 500);
        }
    }
}
