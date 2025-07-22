<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SubscriptionPlans;
use App\Models\User;

class UserSubscription extends Model
{
    protected $table = "user_subscriptions";

    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_subscription_id',
        'stripe_checkout_session_id',
        'stripe_customer_id',
        'stripe_price_id',
        'stripe_status',
        'stripe_payment_method_id',
        'response_meta',
        'amount_paid',
        'currency',
        'last_payment_date',
        'failed_payment_attempts',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_start',
        'trial_end',
        'cancel_at_period_end',
        'cancelled_at',
        'ends_at',
        'admin_extended_until',
        'admin_notes',
        'cancellation_bonus_used',
        'cancellation_bonus_granted_at',
        'cancellation_bonus_days',
        'total_download_limit',
        'daily_download_limit',
        'downloads_used_today',
        'total_downloads_used',
        'daily_reset_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlans::class);
    }
}
