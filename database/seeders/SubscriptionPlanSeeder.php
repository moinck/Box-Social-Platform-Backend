<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'description' => '3 days free trial with 3 downloads',
                'price' => 0.00,
                'currency' => 'USD',
                'interval' => 'day',
                'interval_count' => 3,
                'trial_period_days' => 3,
                'daily_download_limit' => null,
                'total_download_limit' => 3,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'is_active' => true,
                'is_trial' => true,
                'is_popular' => false,
                'features' => json_encode([
                    '3 days free access',
                    '3 total downloads',
                    'Basic support',
                    'No credit card required'
                ]),
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Annual Plan',
                'slug' => 'annual-plan',
                'description' => 'Annual subscription with 40 downloads per day',
                'price' => 99.00, // Adjust your price
                'currency' => 'USD',
                'interval' => 'year',
                'interval_count' => 1,
                'trial_period_days' => 0,
                'daily_download_limit' => 40,
                'total_download_limit' => null,
                'stripe_price_id' => 'price_1234567890', // Replace with your actual Stripe price ID
                'stripe_product_id' => 'prod_1234567890', // Replace with your actual Stripe product ID
                'is_active' => true,
                'is_trial' => false,
                'is_popular' => true,
                'features' => json_encode([
                    '40 downloads per day',
                    'Unlimited access for 1 year',
                    'Priority support',
                    'Cancel anytime',
                    'Admin-managed cancellation'
                ]),
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // You can add more plans here for future use
            [
                'name' => 'Monthly Plan',
                'slug' => 'monthly-plan',
                'description' => 'Monthly subscription with 40 downloads per day',
                'price' => 9.99,
                'currency' => 'USD',
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => 0,
                'daily_download_limit' => 40,
                'total_download_limit' => null,
                'stripe_price_id' => 'price_monthly_123', // Replace with actual Stripe price ID
                'stripe_product_id' => 'prod_1234567890',
                'is_active' => false, // Disabled for now
                'is_trial' => false,
                'is_popular' => false,
                'features' => json_encode([
                    '40 downloads per day',
                    'Monthly billing',
                    'Priority support',
                    'Cancel anytime'
                ]),
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('subscription_plans')->insert($plans);
    }
}
