<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlans;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                'name' => 'Box Socials Premium Plan',
                'slug' => 'box-socials-premium-plan-before-oct-2025',
                'description' => 'Premium plan for users subscribed before Oct 1, 2025',
                'price' => 650.00,
                'currency' => 'GBP',
                'interval' => 'year',
                'interval_count' => 1,
                'trial_period_days' => 3,
                'daily_download_limit' => null,
                'total_download_limit' => 480,
                'stripe_price_id' => 'price_1RqCFAR0DcXT6U523bfrSdJJ', // Replace with actual Stripe price ID
                'stripe_product_id' => 'prod_ShVuLFcw5okX2y',
                'is_active' => true,
                'is_trial' => false,
                'is_popular' => false,
                'features' => json_encode([
                    '£650 GBP per year (Before Oct 2025)',
                    'Thousands of social media content written by financial services experts',
                    'Access to an ever-growing library of design templates',
                    'Access to millions of royalty free stock images updated every week',
                    'Ability to create and download 40 posts a Month in just a few clicks',
                    'PFA posts such as Tax, Business Protection, Investments, Will Writing, Pensions',
                    'Gain first access to no extra charge future developments',
                    'Additional Topics such as Commercial Finance, Bridging Finance, Second Charges',
                    'AI Compliance checked social media content',
                    'Built-in scheduling tool for social media accounts',
                    'Content suitable for various platforms (Twitter & WhatsApp)',
                ]),
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Box Socials Premium Plan',
                'slug' => 'box-socials-premium-plan-after-oct-2025',
                'description' => 'Premium plan for users subscribed after Oct 1, 2025',
                'price' => 780.00,
                'currency' => 'GBP',
                'interval' => 'year',
                'interval_count' => 1,
                'trial_period_days' => 3,
                'daily_download_limit' => null,
                'total_download_limit' => 480,
                'stripe_price_id' => 'price_1Rm6spR0DcXT6U527MVUtt9v', // Replace with actual Stripe price ID
                'stripe_product_id' => 'prod_ShVuLFcw5okX2y',
                'is_active' => true,
                'is_trial' => false,
                'is_popular' => true,
                'features' => json_encode([
                    '£780 GBP per year (After Oct 2025)',
                    'Thousands of social media content written by financial services experts',
                    'Access to an ever-growing library of design templates',
                    'Access to millions of royalty free stock images updated every week',
                    'Ability to create and download 40 posts a Month in just a few clicks',
                    'PFA posts such as Tax, Business Protection, Investments, Will Writing, Pensions',
                    'Gain first access to no extra charge future developments',
                    'Additional Topics such as Commercial Finance, Bridging Finance, Second Charges',
                    'AI Compliance checked social media content',
                    'Built-in scheduling tool for social media accounts',
                    'Content suitable for various platforms (Twitter & WhatsApp)',
                ]),
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];


        // DB::table('subscription_plans')->insert($plans);
        try {
            DB::beginTransaction();
            foreach ($plans as $plan) {
                SubscriptionPlans::updateOrCreate(['slug' => $plan['slug']], $plan);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubscriptionPlanSeeder Error: ' . $e->getMessage());
        }
    }
}
