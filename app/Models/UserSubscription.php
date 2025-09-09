<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SubscriptionPlans;
use App\Models\User;
use App\Models\UserDownloads;
use Illuminate\Support\Facades\Cache;

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
        'client_secret',
        'stripe_payment_intent_id',
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
        'total_saved_limit',
        'daily_download_limit',
        'downloads_used_today',
        'total_downloads_used',
        'reset_date',
        'is_next_sub_continue',
        'is_subscription_cancel',
        'sub_cancel_reason',
        'child_sub_cancel_reason',
        'invoice_number',
        'coupon_id',
        'coupon_code',
        'coupon_name',
        'coupon_type',
        'coupon_discount',
        'coupon_currency',
        'coupon_discount_id',
        'coupon_discounted_amt'
    ];

    /** Boot method on clear cache */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            Cache::forget('user_subscription_' . $user->id);
        });

        static::updated(function ($user) {
            Cache::forget('user_subscription_' . $user->id);
        });

        static::deleted(function ($user) {
            Cache::forget('user_subscription_' . $user->id);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlans::class);
    }

    // UPDATE DAILY DOWNLOAD LIMIT
    public function updateDailyDownloadLimit()
    {
        $this->downloads_used_today = $this->downloads_used_today + 1;
        $this->save();
    }

    protected static function booted()
    {
        // Automatically create download tracker when subscription is created
        static::created(function ($subscription) {
            UserDownloads::createForSubscription($subscription);
        });
    }

    public function downloadTracker()
    {
        return $this->hasOne(UserDownloads::class, 'user_subscription_id');
    }

    /**
     * Get remaining downloads for current period
     */
    public function getRemainingDownloads()
    {
        $tracker = $this->downloadTracker;
        
        return $tracker->getRemainingDownloads();
    }

    /**
     * Record a download (post download or creation - both count as 1)
     */
    public function recordDownload($count = 1, $type=null) // $type = 1 => For Saved, 2 => For Download
    {
        $tracker = $this->downloadTracker;
        
        return $tracker->incrementDownload($count,$type);
    }

    /**
     * Check if user can download
     */
    public function canDownload()
    {
        $tracker = $this->downloadTracker;
        
        return $tracker->canDownload();
    }

    /**
     * Check if user can saved
     */
    public function canSaved()
    {
        $tracker = $this->downloadTracker;

        return $tracker->canSaved();
    }
}
