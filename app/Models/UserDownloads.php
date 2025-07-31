<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserDownloads extends Model
{
    protected $table = 'user_downloads';
    
    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'total_downloads_used',
        'monthly_downloads_used',
        'current_month',
        'current_year',
        'last_reset_date',
        'plan_type',
        'monthly_limit',
        'total_limit',
        'carried_over_downloads',
        'expires_at',
    ];

    protected $casts = [
        'last_reset_date' => 'date',
        'expires_at' => 'timestamp',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    /**
     * Create download tracker for new subscription
     */
    public static function createForSubscription($subscription)
    {
        $currentDate = Carbon::now();
        $planType = $subscription->plan->slug ?? 'free-trial';
        $monthlyLimit = 0;
        $totalLimit = 0;
        if($planType != 'free-trial') {
            $monthlyLimit = 40;
            $totalLimit = 480;
        } else {
            $monthlyLimit = 3;
            $totalLimit = 3;
        }
        
        return self::create([
            'user_id' => $subscription->user_id,
            'user_subscription_id' => $subscription->id,
            'total_downloads_used' => 0,
            'monthly_downloads_used' => 0,
            'current_month' => $currentDate->month,
            'current_year' => $currentDate->year,
            'plan_type' => $planType,
            'monthly_limit' => $monthlyLimit,
            'total_limit' => $totalLimit,
            'carried_over_downloads' => 0,
            'expires_at' => $subscription->current_period_end,
        ]);
    }

    /**
     * Increment download count
     */
    public function incrementDownload()
    {
        // Check if we need to reset monthly count for premium plans
        if ($this->plan_type != 'free-trial') {
            $this->checkAndResetMonthly();
        }
        
        // Check if user can download
        if (!$this->canDownload()) {
            return false;
        }
        
        // Increment counters
        $this->total_downloads_used += 1;
        if ($this->plan_type != 'free-trial') {
            $this->monthly_downloads_used += 1;
        }
        
        $this->save();
        return true;
    }

    /**
     * Check if user can download
     */
    public function canDownload()
    {
        if ($this->plan_type == 'free-trial') {
            // Check if expired
            if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
                return false;
            }
            // Check total limit
            return $this->total_downloads_used < ($this->total_limit ?? 3);
        } else {
            // Premium plan - check monthly limit (including carried over)
            $this->checkAndResetMonthly();
            $effectiveLimit = $this->monthly_limit + $this->carried_over_downloads;
            return $this->monthly_downloads_used < $effectiveLimit;
        }
    }

    /**
     * Get remaining downloads
     */
    public function getRemainingDownloads()
    {
        if ($this->plan_type != 'free-trial') {
            if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
                return 0;
            }
            return max(0, ($this->total_limit ?? 3) - $this->total_downloads_used);
        } else {
            $this->checkAndResetMonthly();
            $effectiveLimit = $this->monthly_limit + $this->carried_over_downloads;
            return max(0, $effectiveLimit - $this->monthly_downloads_used);
        }
    }

    /**
     * Check and reset monthly downloads for premium plans
     */
    public function checkAndResetMonthly()
    {
        if ($this->plan_type !== 'premium') {
            return;
        }

        $currentDate = Carbon::now();
        
        // Check if we're in a new month
        if ($this->current_month !== $currentDate->month || $this->current_year !== $currentDate->year) {
            
            // Calculate carry over (unused downloads from previous month)
            $unusedDownloads = max(0, ($this->monthly_limit + $this->carried_over_downloads) - $this->monthly_downloads_used);
            
            // Reset for new month
            $this->monthly_downloads_used = 0;
            $this->current_month = $currentDate->month;
            $this->current_year = $currentDate->year;
            $this->last_reset_date = $currentDate->toDateString();
            $this->carried_over_downloads = $unusedDownloads; // Add unused to next month
            
            $this->save();
        }
    }

    /**
     * Manual reset method (for admin or testing)
     */
    public function resetDownloads()
    {
        if ($this->plan_type != 'free-trial') {
            $this->total_downloads_used = 0;
            $this->expires_at = Carbon::now()->addDays(3);
        } else {
            $this->monthly_downloads_used = 0;
            $this->carried_over_downloads = 0;
            $this->current_month = Carbon::now()->month;
            $this->current_year = Carbon::now()->year;
            $this->last_reset_date = Carbon::now()->toDateString();
        }
        
        $this->save();
    }

    /**
     * Get download statistics
     */
    public function getDownloadStats()
    {
        if ($this->plan_type == 'free-trial') {
            return [
                'used' => $this->total_downloads_used,
                'remaining' => max(0, ($this->total_limit ?? 3) - $this->total_downloads_used),
                'monthly_limit' => $this->monthly_limit,
                'total_limit' => $this->total_limit,
                // 'expires_at' => $this->expires_at,
                // 'expired' => $this->expires_at ? Carbon::now()->gt($this->expires_at) : false,
            ];
        } else {
            $this->checkAndResetMonthly();
            return [
                'used' => $this->monthly_downloads_used,
                'remaining' => $this->getRemainingDownloads(),
                'monthly_limit' => $this->monthly_limit,
                'monthly_remaining_limit' => $this->monthly_limit - $this->monthly_downloads_used,
                'total_limit' => $this->total_limit,
                'carried_over' => $this->carried_over_downloads,
                'effective_limit' => $this->monthly_limit + $this->carried_over_downloads,
                'current_period' => $this->current_month . '/' . $this->current_year,
                // 'last_reset' => $this->last_reset_date,
                // 'expires_at' => $this->expires_at,
            ];
        }
    }
}
