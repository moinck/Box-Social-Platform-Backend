<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDownloads;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Validator;

class UserDownloadsManagementApiController extends Controller
{
    use ResponseTrait;

    /**
     * Get Details of current plan downloads state
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function currentState()
    {
        $authUserId = Auth::user()->id;
        $subscription = UserSubscription::select('id','total_download_limit','daily_download_limit','downloads_used_today')
            ->with('downloadTracker')
            ->where('user_id', $authUserId)
            ->where('status', 'active')
            ->first();
        
        if (empty($subscription)) {
            return $this->error('No active subscription found');
        }

        $downloadCountStats = $subscription->downloadTracker->getDownloadStats();
        
        return $this->success($downloadCountStats,'Current state fetched successfully');
    }

    /**
     * Increment download count
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function incrementDownload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'increse_count' => 'nullable|numeric|gt:0',
        ]);
        $today = Carbon::now();

        if ($validator->fails()) {
            return $this->validationError('Validation failed',$validator->errors(),422);
        }
        
        $authUserId = Auth::user()->id;
        $subscription = UserSubscription::select('id','total_download_limit','daily_download_limit','downloads_used_today','plan_id')
            ->with('downloadTracker')
            ->where('user_id', $authUserId)
            ->where('status', 'active')
            ->first();
        
        if (empty($subscription)) {
            return $this->error('No active subscription found');
        }

        $downloadCountStats = [];
        if ($subscription) {
            $increaseCount = $request->has('increse_count') && $request->increse_count > 0 ? $request->increse_count : 1;
            $downloadCountStats = $subscription->downloadTracker->getDownloadStats();
            $remainingMonthlyDownloads = $downloadCountStats['monthly_remaining_limit'];

            if ($remainingMonthlyDownloads < $increaseCount) {
                return $this->error('No enough remaining downloads',422);
            }
            // check if user can download
            if($subscription->canDownload()){
                $subscription->recordDownload($increaseCount, 2); // 1 => For Saved, 2 => For Download
                $downloadCountStats = $subscription->downloadTracker->getDownloadStats();

                // if plan is free & all limit is used then mark current subscription as ended
                if ($subscription->plan_id == 1 && ($downloadCountStats['total_limit'] == $downloadCountStats['used']) && ($downloadCountStats['total_saved_limit'] == $downloadCountStats['saved_used'])) {
                    $subscription->status = 'inactive';
                    $subscription->stripe_status = 'inactive';
                    $subscription->cancelled_at = $today;
                    $subscription->ends_at = $today;
                    $subscription->save();

                    $subscription->downloadTracker->update([
                        'expires_at' => $today
                    ]);
                }

                return $this->success($downloadCountStats,'Subscription download limit updated successfully');
            } else {
                
                $downloadCountStats = $subscription->downloadTracker->getDownloadStats();
                return $this->success($downloadCountStats,'Subscription download limit exceeded');
            }
        }
    }

    /** 
     * Increment Saved Count
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function incrementSavedTemplateCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'increase_count' => 'nullable|numeric|gt:0',
        ]);
        $today = Carbon::now();

        if ($validator->fails()) {
            return $this->validationError('Validation failed',$validator->errors(),422);
        }

        $authUserId = Auth::user()->id;
        $subscription = UserSubscription::select('id','total_download_limit','daily_download_limit','downloads_used_today','plan_id')
            ->with('downloadTracker')
            ->where('user_id', $authUserId)
            ->where('status','active')
            ->first();
        
        if (empty($subscription)) {
            return $this->error('No active subscription found.');
        }

        $savedCountStats = [];

        if ($subscription) {
            $increaseCount = $request->has('increase_count') && $request->increase_count > 0 ? $request->increase_count : 1;
            $savedCountStats = $subscription->downloadTracker->getDownloadStats();
            $remainingMonthlySaved = $savedCountStats['saved_monthly_remaining_limit'];

            if ($remainingMonthlySaved < $increaseCount) {
                return $this->error('no enough remaining saved',422);
            }
            //Check if user can saved
            if ($subscription->canSaved()) {
                $subscription->recordDownload($increaseCount, 1); // 1 => For Saved, 2 => For Download
                $savedCountStats = $subscription->downloadTracker->getDownloadStats();

                if ($subscription->plan_id == 1 && ($savedCountStats['total_limit'] == $savedCountStats['used']) && ($savedCountStats['total_saved_limit'] == $savedCountStats['saved_used'])) {
                    $subscription->status = 'inactive';
                    $subscription->stripe_status = 'inactive';
                    $subscription->cancelled_at = $today;
                    $subscription->ends_at = $today;
                    $subscription->save();

                    $subscription->downloadTracker->update([
                        'expires_at' => $today
                    ]);
                }

                return $this->success($savedCountStats,'Subscription saved limit updated successfully');

            } else {
                $savedCountStats = $subscription->downloadTracker->getDownloadStats();
                return $this->success($savedCountStats,'Subscription saved limit exceeded');
            }

        }


    }
}
