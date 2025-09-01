<?php

namespace App\Console\Commands;

use App\Jobs\FreeSubscriptionLastDay;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendLastDayTrialSubscriptionMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendLastDaySubMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command sends a reminder email to users on the last day of their free trial.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate  = Carbon::now()->startOfDay();
        $firstOctDate = Carbon::create(2025, 10, 1)->startOfDay();

        // Base query
        $query = UserSubscription::with(['user', 'plan'])
            ->where('plan_id', 1)
            ->where('status', 'active');
        
        // Add condition based on before/after Oct 1
        if ($currentDate->lt($firstOctDate)) {
            $query->where('current_period_start', '<', $firstOctDate->endOfDay());
            $type = 1;
        } else {
            $query->where('current_period_start', '>=', $firstOctDate->endOfDay());
            $type = 2;
        }

        // Get subscriptions
        $subscriptions = $query->get();

        // Process subscriptions
        foreach ($subscriptions as $val) {
            $current_period_end       = Carbon::parse($val->current_period_end)->startOfDay();
            $period_end_previous_day  = $current_period_end->copy()->subDay();

            if ($period_end_previous_day->equalTo($currentDate)) {
                dispatch(new FreeSubscriptionLastDay($val, $type));
            }
        }

    }
}
