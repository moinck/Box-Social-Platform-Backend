<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlans extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'interval',
        'interval_count',
        'trial_period_days',
        'daily_download_limit',
        'total_download_limit',
        'monthly_download_limit',
        'total_saved_limit',
        'monthly_saved_limit',
        'stripe_price_id',
        'stripe_product_id',
        'is_active',
        'is_trial',
        'is_popular',
        'features',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
        'is_popular' => 'boolean',
        'features' => 'array',
        'trial_period_days' => 'integer',
        'daily_download_limit' => 'integer',
        'total_download_limit' => 'integer',
        'monthly_download_limit' => 'integer',
        'total_saved_limit' => 'integer',
        'monthly_saved_limit' => 'integer',
        'interval_count' => 'integer',
        'sort_order' => 'integer'
    ];
}
