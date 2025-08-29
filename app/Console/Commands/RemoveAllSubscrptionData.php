<?php

namespace App\Console\Commands;

use App\Models\Payments;
use App\Models\UserDownloads;
use App\Models\UserSubscription;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class RemoveAllSubscrptionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-all-subscrption-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $userSubscriptions = UserSubscription::all();
            $userIds = [];

            $this->info('Removing ' . count($userSubscriptions) . ' subscription data...');

            if (!empty($userSubscriptions)) {
                foreach ($userSubscriptions as $value) {
                    $userIds[] = $value->user_id;
    
                    if ($value->plan_id != 1) {
    
                        if (!empty($value->stripe_subscription_id)) {
                            try {
    
                                $stripe = new StripeClient(config('services.stripe.secret_key'));
    
                                // Always fetch the subscription from Stripe
                                $stripeSub = $stripe->subscriptions->retrieve($value->stripe_subscription_id, []);
    
                                // Cancel if it's not already canceled
                                if ($stripeSub->status !== 'canceled') {
                                    // first cancel subscription from stripe
                                    $stripe->subscriptions->cancel($value->stripe_subscription_id, [
                                        'cancellation_details' => [
                                            'comment' => 'user deleted their account',
                                            // 'reason' => 'account_deleted',
                                        ],
                                    ]);
                                }
    
                            } catch (InvalidRequestException $e) {
                                // Most common: "No such subscription" (already deleted or never existed)
                                Log::warning("Stripe invalid request for subscription {$value->stripe_subscription_id} - {$value->id}: " . $e->getMessage());
                            } catch (ApiErrorException $e) {
                                // Any other Stripe API error (network, auth, etc.)
                                Log::error("Stripe API error for subscription {$value->stripe_subscription_id} - {$value->id}: " . $e->getMessage());
                            } catch (\Exception $e) {
                                // Catch-all for unexpected issues
                                Log::error("Unexpected error for subscription {$value->stripe_subscription_id} - {$value->id}: " . $e->getMessage());
                            }
                        }
    
                        $value->delete();
                    } else {
                        $value->delete();
                    }
                }
            }
            $this->info('Removed ' . count($userSubscriptions) . ' subscription data...');
            // delete data from userDownloads table
            UserDownloads::whereIn('user_id', $userIds)->delete();
            $this->info('Removed ' . count($userSubscriptions) . ' userDownloads data...');
    
            // delete data from payments table
            Payments::whereNotNull('user_id')->delete();
            $this->info('Removed ' . count($userSubscriptions) . ' payments data...');
            DB::commit();

            $this->info('All subscription data removed successfully...');
        } catch (Exception $th) {
            DB::rollBack();
            $this->error('Failed to remove subscription data: ' . $th->getMessage());
        }
    }
}
