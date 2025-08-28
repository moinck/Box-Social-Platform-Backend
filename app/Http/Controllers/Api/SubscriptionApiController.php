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
            'payment_method' => [
                function ($attribute, $value, $fail) use ($request) {
                    $planId = Helpers::decrypt($request->plan_id);
                    if ($planId != 1 && empty($value)) {
                        $fail('The payment method field is required.');
                    }
                }
            ],
            'user_details' => [
                function ($attribute, $value, $fail) use ($request) {
                    $planId = Helpers::decrypt($request->plan_id);
                    if ($planId != 1 && empty($value)) {
                        $fail('The user details is required.');
                    }
                }
            ]
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
            $coupon_details = isset($request->coupon_details) ? $request->coupon_details : null;

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
                    // if user want to buy premium plan than inactive the free trial subscription
                    if ($planId != 1) {
                        $existingSubscription->status = 'inactive';
                        $existingSubscription->stripe_status = 'inactive';
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
                // $currentYear = $today->year;
                $cutoffDate = Carbon::create(2025, 10, 1); // Oct 1, 2025

                // Before Oct 1 → use £650 plan else £780 plan
                if ($today->lt($cutoffDate)) {
                    $subscriptionPlanDetail = SubscriptionPlans::where('id', 2)->first();
                } else {
                    $userSubscription = UserSubscription::where('user_id',$userId)->latest()->first();
                    $subscriptionPlanDetail = SubscriptionPlans::where('id', 3)->first();
                    if ($userSubscription && $userSubscription->plan_id == 2 && $userSubscription->is_subscription_cancel == true && $userSubscription->is_next_sub_continue == true) {
                        $subscriptionPlanDetail = SubscriptionPlans::where('id', 2)->first();
                    }
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
            $newSubscription->stripe_status = 'incomplete'; // Important: Set as incomplete
            $newSubscription->save();

            // send notification to admin
            Helpers::sendNotification($user, 'new-subscription');

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

            // $paymentMethod = $this->stripe->paymentMethods->create([
            //     'type' => 'card',
            //     'card' => ['token' => 'tok_visa'], // predefined test token
            // ]);

            $this->stripe->paymentMethods->attach(
                $request->payment_method,
                ['customer' => $userStripeCustomerId]
            );

            /** Create Subscription */
            $data = [
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
            ];

            // conditionally add coupon
            if (!empty($coupon_details) && !empty($coupon_details['coupon'])) {
                $data['discounts'] = [
                    ['coupon' => $coupon_details['coupon']]
                ];
            }

            $subscription = $this->stripe->subscriptions->create($data);

            // Store session ID for verification
            $newSubscription->stripe_subscription_id = $subscription->id;
            $newSubscription->stripe_payment_method_id = $request->payment_method;
            $newSubscription->client_secret = $subscription->latest_invoice->confirmation_secret->client_secret;
            if (!empty($coupon_details)) {
                $newSubscription->coupon_id = $coupon_details['coupon'];
                $newSubscription->coupon_code = $coupon_details['coupon_code'];
                $newSubscription->coupon_name = $coupon_details['coupon_name'];
                $newSubscription->coupon_type = $coupon_details['discount_type']; // discount_type == amount, percent
                $newSubscription->coupon_discount = $coupon_details['discount'];
                $newSubscription->coupon_currency = $coupon_details['currency'];
            }
            $newSubscription->save(); 

            // $confirmedPayment = $this->stripe->paymentIntents->confirm(
            //     explode('_secret', $subscription->latest_invoice->confirmation_secret->client_secret)[0],
            //     ['client_secret' => $subscription->latest_invoice->confirmation_secret->client_secret]
            // );
            
            $newPayment = new Payments();
            $newPayment->user_id = $newSubscription->user_id;
            $newPayment->user_subscription_id = $newSubscription->id;
            $newPayment->plan_name = $subscriptionPlanDetail->name;
            $newPayment->status = 'pending';
            $newPayment->amount = 0.00;
            $newPayment->coupon_discounted_amt = 0.00;
            $newPayment->currency = 'GBP';
            $newPayment->payment_type = 'subscription';
            $newPayment->payment_method = 'card';
            $newPayment->save();

            DB::commit();

            $returnData = [
                'subscription_plan' => 'premium-plan',
                'subscriptionId' => $subscription->id,
                'clientSecret' => $subscription->latest_invoice->confirmation_secret->client_secret,
                'user_subscription_id' => $encyptedId
            ];

            return $this->success($returnData, 'Subscription created successfuly.', 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            Helpers::sendErrorMailToDeveloper($e);
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
                'company_name' => !empty($user_details) ? $user_details['company_name'] : null,
                'address' => $user_details ? $user_details['address'] : null,
                'country' => $user_details ? $user_details['country'] : null,
                'email' => $user->email,
                'first_name' => $user_details ? $user_details['first_name'] : null,
                'last_name' => $user_details ? $user_details['last_name'] : null,
                'postal_code' => $user_details ? $user_details['postal_code'] : null,
                'state' => $user_details ? $user_details['state'] : null,
                'town_city' => $user_details ? $user_details['town_city'] : null,
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
            Helpers::sendErrorMailToDeveloper($e);
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
                $userSubscription->amount_paid = $invoice['total'] / 100;
                $userSubscription->currency = $invoice['currency'];
                $userSubscription->invoice_number = $invoice['number'];
                $userSubscription->coupon_discounted_amt = isset($invoice['total_discount_amounts'][0]['amount']) ? $invoice['total_discount_amounts'][0]['amount'] / 100 : null;
                $userSubscription->coupon_discount_id = isset($invoice['total_discount_amounts'][0]['discount']) ? $invoice['total_discount_amounts'][0]['discount'] : null;
                
                $userSubscription->response_meta = json_encode($invoice, JSON_PRETTY_PRINT);
                $userSubscription->total_download_limit = 40;
                $userSubscription->daily_download_limit = 0;
                $userSubscription->reset_date = now()->addMonths(1)->startOfMonth()->format('Y-m-d');


                $userSubscription->last_payment_date = now();
                $userSubscription->save();

                // Update payments record
                $newPayment = Payments::where('user_subscription_id',$userSubscription->id)->latest()->first();
                $newPayment->status = 'completed';
                $newPayment->amount = $invoice['total'] / 100;
                $newPayment->coupon_discounted_amt = isset($invoice['total_discount_amounts'][0]['amount']) ? $invoice['total_discount_amounts'][0]['amount'] / 100 : null;
                $newPayment->currency =  $invoice['currency'] ?? 'GBP';
                $newPayment->payment_method = $invoice['payment_settings']['payment_method_types'][0] ?? 'card';
                $newPayment->stripe_payment_intent_id = $userSubscription->stripe_payment_intent_id;
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

            $newPayment = Payments::where('user_subscription_id',$subscription->id)->latest()->first();
            $newPayment->status = 'failed';
            $newPayment->save();
        }
    }

    private function handleSubscriptionUpdated($stripeSubscription)
    {

        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription['id'])->first();

        if (!$subscription) {
            return;
        }

        $item = $stripeSubscription['items']['data'][0] ?? null;

        if ($stripeSubscription['status'] == "active" && $stripeSubscription['cancel_at_period_end'] == true) {
            $subscription->update([
                'is_subscription_cancel' => true,
                'current_period_start'  => $item ? Carbon::parse($item['current_period_start'])->format('Y-m-d H:i:s') : null,
                'current_period_end'    => $item ? Carbon::parse($item['current_period_end'])->format('Y-m-d H:i:s') : null,
                'cancelled_at' => now()
            ]);
        } else {
            $subscription->update([
                'stripe_status'         => $stripeSubscription['status'],
                'current_period_start'  => $item ? Carbon::parse($item['current_period_start'])->format('Y-m-d H:i:s') : null,
                'current_period_end'    => $item ? Carbon::parse($item['current_period_end'])->format('Y-m-d H:i:s') : null,
            ]);
        }

    }

    private function handleSubscriptionCancelled($stripeSubscription)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription['id'])->first();
        if ($subscription) {
            $subscription->status = 'canceled';
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
                'user_subscription_id' => 'required',
                'payment_intent_data' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user_subscription_id = Helpers::decrypt($request->user_subscription_id);

            $user_subscription = UserSubscription::with(['plan'])->where('id',$user_subscription_id)->first();

            if (!$user_subscription) {
                return $this->error('Subscription data not found.', 404);
            }

            $user_subscription->stripe_payment_intent_id = $request->payment_intent_data['id'];
            $user_subscription->stripe_payment_method_id = $request->payment_intent_data['payment_method'];
            $user_subscription->client_secret = $request->payment_intent_data['client_secret'];
            $user_subscription->save();

            $payments = Payments::where('user_subscription_id',$user_subscription_id)->latest()->first();
            $payments->stripe_payment_intent_id = $request->payment_intent_data['id'];
            $payments->save();
            
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

            if ($user_subscription->stripe_status == "paid" && $user_subscription->is_subscription_cancel != true) {
                return $this->success($returnData, 'Payment has been paid successfully.', 200);
            }

            return $this->error('Payment failed', 500);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("Somethign went wrong.", 500);
        }
    }

    /** Cancel Subscription */
    public function cancelSubscription(Request $request)
    {
        DB::beginTransaction();
        try {

            $authUser = Auth::user();
            $user_subscription = UserSubscription::where('user_id', $authUser->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (empty($user_subscription)) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'No active subscription found',
                    'data' => []
                ]);
            }

            if ($user_subscription) {
                $user_subscription->sub_cancel_reason = $request->reason;
                $user_subscription->child_sub_cancel_reason = $request->child_reason;
                // get plan detais
                $plan = SubscriptionPlans::where('id', $user_subscription->plan_id)->first();
                if ($plan->slug == 'free-trial') {
                    $user_subscription->is_subscription_cancel = true;
                    $user_subscription->cancelled_at = now();
                    $user_subscription->ends_at = now();
                    $user_subscription->save();
                }else{

                    $subscription = $this->stripe->subscriptions->update(
                        $user_subscription->stripe_subscription_id,
                        ['cancel_at_period_end' => true]
                    );

                    if ($subscription->cancel_at_period_end == true) {
                        $user_subscription->is_subscription_cancel = true;
                        $user_subscription->cancelled_at = now();
                        $user_subscription->save();
                    }

                }
            }

            if ($user_subscription->is_subscription_cancel) {
                $user_subscription['user_subscription_id'] = Helpers::encrypt($user_subscription->id);
                DB::commit();
                return $this->success($user_subscription,'Subscription cancelled successfully.', 200);
            }

            DB::rollBack();
            return $this->error('Subscription not cancelled. Please try again later.', 404);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->error('Something went wrong.', 500);
        }

    }

    /** Verify User Subscription Canceled */
    public function verifyUserSubscriptionCanceled(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_subscription_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user_subscription_id = Helpers::decrypt($request->user_subscription_id);

            $user_subscription = UserSubscription::with(['plan'])->where('id',$user_subscription_id)->first();

            if (!$user_subscription) {
                return $this->error('Subscription data not found.', 404);
            }

            if ($user_subscription->is_subscription_cancel == null) {
                return $this->success([], 'Webhook call not responsed.', 206);
            }

            if ($user_subscription->is_subscription_cancel == true) {
                return $this->success([],'Subscription was canceled.', 200); // resource/payment is no longer available because the user canceled.
            }

            return $this->error('Subscription was not canceled. Please try again later.', 500);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("Somethign went wrong.", 500);
        }
    }

    /** Are you want to continue the subscription? */
    public function continueSubscription(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'is_continue' => 'required',
                // 'type' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $isSubscriptionContinue = $request->is_continue;
            // $type = $request->type;
            $userId = Auth::id();

            $subscription = UserSubscription::where('user_id',$userId)->latest()->first();

            if (!$subscription) {
                return $this->error('Subscription data not found.', 404);
            }

            // if ($subscription->is_subscription_cancel == true && $type == 2) {
            //     $subscription->is_subscription_cancel = false;
            //     $subscription->is_next_sub_continue = $isSubscriptionContinue;
            // } else {
                $subscription->is_next_sub_continue = $isSubscriptionContinue;
            // }

            $subscription->save();

            return $this->success([],'Status changed', 200);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error('Something went wrong.', 500);
        }
    }

    /** Generate Subscription Invoice */
    public function generateSubscriptionInvoice(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required',
            ]);

            $subscriptionId = Helpers::decrypt($request->id);

            return Helpers::generateSubscriptionInvoice($subscriptionId);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("Something went wrong.", 500);
        }
    }

    /** Get Subscriptions Coupons */
    public function couponVerify(Request $request)
    {
        try {

            $request->validate([
                'code' => 'required',
            ]);

            $promotionCodes = $this->stripe->promotionCodes->all([
                'code' => $request->code,
                'limit' => 1,
                'expand' => ['data.coupon'],
            ]);

            if (empty($promotionCodes->data)) {
                return $this->success([], "Invalid coupon code.",422);
            }

            $promotionCode = $promotionCodes->data[0];
            $coupon = $promotionCode->coupon;

            $discount = null;
            $discount_type = null;

            if ($coupon->percent_off) {
                $discount = $coupon->percent_off;
                $discount_type = 'percent';
            } elseif ($coupon->amount_off) {
                $discount = number_format($coupon->amount_off / 100, 2);
                $discount_type = 'amount';
            }

            $response = [];
            if ($promotionCode->active == true) {
                $response[] = [
                    'coupon' => $coupon->id,
                    'coupon_code' => $promotionCode->code,
                    'coupon_name' => $coupon->name ?? 'No Name',
                    'discount' => $discount,
                    'discount_type'=> $discount_type,
                    'valid' => $promotionCode->active,
                    'currency' => $coupon->currency
                ];
                return $this->success($response, "Coupons fetched successfully.",200);
            }

            return $this->success([], "Invalid coupon code.",422);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("Something went wrong.", 500);
        }
    }
}
