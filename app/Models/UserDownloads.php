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
        'saved_template_count',
        'monthly_saved_template_count',
        'current_month',
        'current_year',
        'last_reset_date',
        'plan_type',
        'monthly_limit',
        'total_limit',
        'monthly_saved_limit',
        'total_saved_limit',
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
        // $monthlyLimit = 0;
        // $totalLimit = 0;
        // $monthlySavedLimit = 0;
        // $totalSavedLimit = 0;
        if($planType != 'free-trial') {
            $monthlyLimit = $subscription->plan->monthly_download_limit;
            $totalLimit = $subscription->plan->total_download_limit;
            $monthlySavedLimit = $subscription->plan->monthly_saved_limit;
            $totalSavedLimit = $subscription->plan->total_saved_limit;
        } else {
            $monthlyLimit = $subscription->plan->monthly_download_limit;
            $totalLimit = $subscription->plan->total_download_limit;
            $monthlySavedLimit = $subscription->plan->monthly_saved_limit;
            $totalSavedLimit = $subscription->plan->total_saved_limit;
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
            'monthly_saved_limit' => $monthlySavedLimit,
            'total_saved_limit' => $totalSavedLimit,
        ]);
    }

    /**
     * Increment download count
     */
    public function incrementDownload($count = 1, $type=null)  // $type = 1 => For Saved, 2 => For Download
    {
        // Check if we need to reset monthly count for premium plans
        if ($this->plan_type != 'free-trial') {
            $this->checkAndResetMonthly();
        }

        if ($type == 2) {
            // Check if user can download
            if (!$this->canDownload()) {
                return false;
            }
            
            // Increment counters
            $this->total_downloads_used += $count;
            $this->monthly_downloads_used += $count;
            // if ($this->plan_type != 'free-trial') {
            // }
        } else if ($type == 1) {
            // Check if user can download
            if (!$this->canSaved()) {
                return false;
            }
            
            // Increment counters
            $this->saved_template_count += $count;
            $this->monthly_saved_template_count += $count;
            // if ($this->plan_type != 'free-trial') {
            // }
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
            // Check if total limit is exceeded then end subscription
            if ($this->total_downloads_used >= ($this->total_limit ?? 3)) {
                $this->user->subscription()->update([
                    'status' => 'ended',
                    'stripe_status' => 'canceled',
                    'cancelled_at' => Carbon::now(),
                    'ends_at' => Carbon::now(),
                ]);
                $this->expires_at = Carbon::now();
                $this->save();
                return false;
            }
            // Check total limit
            return $this->total_downloads_used < ($this->total_limit ?? 3);
        } else {
            // Premium plan - check monthly limit only
            $this->checkAndResetMonthly();
            return $this->monthly_downloads_used < $this->monthly_limit;
        }
    }

    /**
     * Check if user can saved
     */
    public function canSaved()
    {
        if ($this->plan_type == "free-trial") {
            // Check if expired
            if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
                return false;
            }
            //Check if total limit is exceeded then end subscription
            if ($this->saved_template_count >= ($this->total_saved_limit ?? 3)) {
                $this->user->subscription()->update([
                    'status' => 'ended',
                    'stripe_status' => 'canceled',
                    'cancelled_at' => Carbon::now(),
                    'ends_at' => Carbon::now(),
                ]);
                $this->expires_at = Carbon::now();
                $this->save();
                return false;
            }
            //Check total limit
            return $this->saved_template_count < ($this->total_saved_limit ?? 3);
        } else {
            //Premium plan - check monthly limit only
            $this->checkAndResetMonthly();
            return $this->monthly_saved_template_count < $this->monthly_saved_limit;
        }
    }

    /**
     * Get remaining downloads
     */
    public function getRemainingDownloads()
    {
        if ($this->plan_type == 'free-trial') {
            if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
                return 0;
            }
            return max(0, ($this->total_limit ?? 3) - $this->total_downloads_used);
        } else {
            $this->checkAndResetMonthly();
            return max(0, $this->monthly_limit - $this->monthly_downloads_used);
        }
    }

    /**
     * Get remaining saved
     */
    public function getRemainingSaved()
    {
        if ($this->plan_type == 'free-trial') {
            if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
                return 0;
            }
            return max(0, ($this->total_saved_limit ?? 3) - $this->saved_template_count);
        } else {
            $this->checkAndResetMonthly();
            return max(0, $this->monthly_saved_limit - $this->monthly_saved_template_count);
        }
    }

    /**
     * Check if this user needs monthly reset (for fallback)
     * This method can be called during user activity as a safety net
     */
    public function checkIfNeedsReset()
    {
        if ($this->plan_type == 'free-trial') {
            return false;
        }

        $currentDate = Carbon::now();
        
        // Check if we're in a new month and it's been more than a day since last reset
        return ($this->current_month !== $currentDate->month || $this->current_year !== $currentDate->year);
    }

    /**
     * Check and reset monthly downloads for premium plans
     */
    public function checkAndResetMonthly()
    {
        if ($this->plan_type == 'free-trial') {
            return;
        }

        $currentDate = Carbon::now();
        
        // Check if we're in a new month
        if ($this->current_month !== $currentDate->month || $this->current_year !== $currentDate->year) {
            
            // Calculate carry over (unused downloads from previous month)
            // $unusedDownloads = max(0, ($this->monthly_limit + $this->carried_over_downloads) - $this->monthly_downloads_used);
            
            // Reset for new month - unused downloads are lost
            $this->monthly_downloads_used = 0;
            $this->monthly_saved_template_count = 0;
            $this->current_month = $currentDate->month;
            $this->current_year = $currentDate->year;
            $this->last_reset_date = $currentDate->toDateString();
            $this->carried_over_downloads = 0; // Add unused to next month
            
            $this->save();
        }
    }

    /**
     * Manual reset method (for admin or testing)
     */
    public function resetDownloads()
    {
        if ($this->plan_type == 'free-trial') {
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
                //Start Downloads Limit
                'used' => $this->total_downloads_used,
                'remaining' => max(0, ($this->total_limit ?? 3) - $this->total_downloads_used),
                'monthly_limit' => $this->monthly_limit,
                'monthly_remaining_limit' => $this->monthly_limit - $this->monthly_downloads_used,
                'total_limit' => $this->total_limit,
                //Start Saved Limit
                'saved_used' => $this->saved_template_count,
                'saved_remaining' => max(0, ($this->total_saved_limit ?? 3) - $this->saved_template_count),
                'monthly_saved_limit' => $this->monthly_saved_limit,
                'saved_monthly_remaining_limit' => $this->monthly_saved_limit - $this->monthly_saved_template_count,
                'total_saved_limit' => $this->total_saved_limit,
                'current_period' => $this->current_month . '/' . $this->current_year,
                // 'expires_at' => $this->expires_at,
                // 'expired' => $this->expires_at ? Carbon::now()->gt($this->expires_at) : false,
            ];
        } else {
            $this->checkAndResetMonthly();
            return [
                //Start Download Limit
                'used' => $this->monthly_downloads_used,
                'remaining' => $this->getRemainingDownloads(),
                'monthly_limit' => $this->monthly_limit,
                'monthly_remaining_limit' => $this->monthly_limit - $this->monthly_downloads_used,
                'total_limit' => $this->total_limit,
                //Start Saved Limit
                'saved_used' => $this->saved_template_count,
                'saved_remaining' => $this->getRemainingSaved(),
                'monthly_saved_limit' => $this->monthly_saved_limit,
                'saved_monthly_remaining_limit' => $this->monthly_saved_limit - $this->monthly_saved_template_count,
                'total_saved_limit' => $this->total_saved_limit,
                // 'carried_over' => $this->carried_over_downloads,
                // 'effective_limit' => $this->monthly_limit + $this->carried_over_downloads,
                'current_period' => $this->current_month . '/' . $this->current_year,
                // 'last_reset' => $this->last_reset_date,
                // 'expires_at' => $this->expires_at,
            ];
        }
    }
}