<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlans;
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
                'currency' => 'GBP',
                'interval' => 'day',
                'interval_count' => 3,
                'trial_period_days' => 3,
                'daily_download_limit' => null,
                'total_download_limit' => 3,
                'stripe_price_id' => 'price_1RZWqLR0DcXT6U52RNFuFUkY',
                'stripe_product_id' => 'prod_SUVsWY6A37Y6CS',
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
                'currency' => 'GBP',
                'interval' => 'year',
                'interval_count' => 1,
                'trial_period_days' => 0,
                'daily_download_limit' => 40,
                'total_download_limit' => null,
                'stripe_price_id' => 'price_1234567890', // Replace with your actual Stripe price ID
                'stripe_product_id' => 'prod_1234567890', // Replace with your actual Stripe product ID
                'is_active' => false,
                'is_trial' => false,
                'is_popular' => false,
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
                'currency' => 'GBP',
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => 0,
                'daily_download_limit' => 40,
                'total_download_limit' => null,
                'stripe_price_id' => 'price_monthly_123', // Replace with actual Stripe price ID
                'stripe_product_id' => 'prod_1234567890',
                'is_active' => true, // Disabled for now
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
            ],
            [
                'name' => 'Box Socials Premium Plan',
                'slug' => 'box-socials-premium-plan',
                'description' => 'Premium annual plan with comprehensive social media features',
                'price' => 780.00,
                'currency' => 'GBP', // Based on £65 in the image
                'interval' => 'year',
                'interval_count' => 1,
                'trial_period_days' => 3, // Free 3 day trial mentioned
                'daily_download_limit' => 40, // Based on "40 posts a day" feature
                'total_download_limit' => null,
                'stripe_price_id' => 'price_1Rm6spR0DcXT6U527MVUtt9v', // Replace with actual Stripe price ID
                'stripe_product_id' => 'prod_ShVuLFcw5okX2y', // Replace with actual Stripe product ID
                'is_active' => true,
                'is_trial' => false,
                'is_popular' => true,
                'features' => json_encode([
                    '£65 GBP per month (65*12 = 780 GBP)',
                    'Thousands of social media content written by financial services experts',
                    'Access to an ever-growing library of design templates',
                    'Access to millions of royalty free stock images updated every week',
                    'Ability to create and download 40 posts a day in just a few clicks',
                    'PFA posts such as Tax, Business Protection, Investments, Will Writing, Pensions',
                    'Gain first access to no extra charge future developments',
                    'Additional Topics such as Commercial Finance, Bridging Finance, Second Charges',
                    'AI Compliance checked social media content',
                    'Built-in scheduling tool for social media accounts',
                    'Content suitable for various platforms (Twitter & WhatsApp)',
                    '3 day free trial included'
                ]),
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // DB::table('subscription_plans')->insert($plans);
        foreach ($plans as $plan) {
            SubscriptionPlans::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
