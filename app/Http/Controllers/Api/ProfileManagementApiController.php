<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\SubscriptionPlans;
use App\Models\User;
use App\Models\UserSubscription;
use App\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileManagementApiController extends Controller
{
    use ResponseTrait;

    /**
     * Get user profile data
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // get token
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error('Invalid token');
        }
        $userId = Auth::user()->id;

        if (!$userId) {
            return $this->error('Invalid user id', 404);
        }

        $cacheKey = 'user_profile_' . $userId;

        $data = Cache::remember($cacheKey, env('CACHE_TIME'), function () use ($userId) {

            $user = User::with('subscription:id,user_id')->find($userId);
            if (!$user) {
                return null;
                // return $this->error('User not found', 404);
            }

            $profileUrl = $user->profile_image;
            if (!empty($profileUrl)) {
                // check if it is digitalocean url or not
                if (strpos($profileUrl, 'https://') !== 0) {
                    $profileUrl = asset($profileUrl);
                }
            }

            $is_take_free_plan = UserSubscription::where('user_id', $userId)->where('plan_id',1)->exists();
        
            $userSubscription = UserSubscription::where('user_id', $userId)
                ->latest()
                ->first();

            $is_plan_expiring = false;
            $is_plan_canceled = false;

            if ($userSubscription) {
                $is_plan_expiring = $userSubscription->current_period_end ? Carbon::now()->diffInHours(Carbon::parse($userSubscription->current_period_end), false) <= 24 : false;

                $hoursLeft = Carbon::now()->diffInHours(
                    Carbon::parse($userSubscription->current_period_end),
                    false
                );

                $is_plan_expiring = false;
                if ($userSubscription->plan_id == 2 && $hoursLeft && $hoursLeft >= 0 && $hoursLeft <= 24) {
                    $is_plan_expiring = true;
                }
                
                if ($userSubscription->is_subscription_cancel == true) {
                    $is_plan_canceled = true;
                }
            }

            $today = now();
            $cutoffDate = Carbon::create(2025, 10, 1); // Oct 1, 2025
            $plan_id = Helpers::encrypt(3); // £780 plan
            if ($userSubscription && $userSubscription->plan_id == 1 || !$userSubscription) {
                $plan_flag = 1;

                // Before Oct 1 → use £650 plan else £780 plan
                // $plan_id = $today->lt($cutoffDate)
                //     ? Helpers::encrypt(2) // £650 plan
                //     : Helpers::encrypt(3); // £780 plan

            } 
            // else if ($userSubscription && $userSubscription->plan_id == 2 && $userSubscription->is_subscription_cancel == true && $userSubscription->is_next_sub_continue == true) {
            //     $plan_id = Helpers::encrypt(2); // £650 plan 
            //     $plan_flag = 2;
            // } 
            else {
                // $plan_id = Helpers::encrypt(3); // £780 plan
                $plan_flag = 3;
            }

            // make data array
            $response_data = [];
            $response_data['user'] = [
                'id' => Helpers::encrypt($user->id),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'company_name' => $user->company_name,
                'fca_number' => $user->fca_number,
                'website' => $user->website,
                'email' => $user->email,
                'phone_number' => $user->phone ?? null,
                'authorisation_type' => $user->authorisation_type,
                'appointed_network' => $user->appointed_network,
                'company_type' => $user->company_type,
                'profile_image' => $profileUrl,
                'is_brandkit' => $user->hasBrandKit(),
                'is_subscribed' => $user->subscription ? true : false,
                'is_plan_expiring' => $is_plan_expiring,
                'is_plan_canceled' => $is_plan_canceled,
                'plan_id' => $plan_id,
                'plan_flag' => $plan_flag,
                'is_take_free_plan' => $is_take_free_plan,
            ];

            return $response_data;

        });

        if (!$data) {
            return $this->error('User not found', 404);
        }

        return $this->success($data, 'Profile fetched successfully');
    }

    /**
     * Update user profile data
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // need to change here
        $token = $request->bearerToken();
        $userId = Auth::user()->id;
        if (!$userId || !$token) {
            return $this->error('Invalid user id', 404);
        }
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'company_name' => 'required',
            'fca_number' => 'required',
            'website' => ['nullable','string','regex:/^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(\/.*)?$/i'],
            'email' => 'required|email',
            'authorisation_type' => 'required|numeric',
            'appointed_network'  => 'sometimes|required_if:authorisation_type,2|string|nullable',
            'company_type' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                'Validation failed',
                $validator->errors()
            );
        }

        if (!$userId) {
            return $this->error('User not found', 404);
        }

        $user = User::with('subscription:id,user_id')->find($userId);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->website = $request->website;
        $user->authorisation_type = $request->authorisation_type;
        $user->appointed_network = isset($request->appointed_network) ? $request->appointed_network : null;
        $user->company_type = $request->company_type;
        $user->save();

        $returnData = [];
        $returnData['user'] = [
            'id' => Helpers::encrypt($user->id),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'company_name' => $user->company_name,
            'fca_number' => $user->fca_number,
            'website' => $user->website,
            'email' => $user->email,
            'profile_image' => $user->profile_image ? $user->profile_image : null,
            'is_brandkit' => $user->hasBrandKit(),
            'is_subscribed' => $user->subscription ? true : false,
            'authorisation_type' => $user->authorisation_type,
            'appointed_network' => $user->appointed_network,
            'company_type' => $user->company_type,
        ];

        return $this->success($returnData, 'Profile updated successfully');
    }

    /**
     * Update user profile image
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profileUpdate(Request $request)
    {
        $token = $request->bearerToken();
        $userId = Auth::user()->id;
        if (!$userId || !$token) {
            return $this->error('Invalid user id', 404);
        }

        $validator = Validator::make($request->all(), [
            'profile_image' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                'Profile image is required',
                $validator->errors(),
            );
        }

        $uploadLogoUrl = $request->profile_image;
        $logoUrl = null;

        $user = User::find($userId);
        if (!$user) {
            return $this->error('User not found', 404);
        }
        $oldLogoUrl = $user->profile_image;
        if ($uploadLogoUrl) {

            // Check if it's base64 data
            if (strpos($uploadLogoUrl, 'data:image/') === 0) {
                $logoUrl = Helpers::handleBase64Image($uploadLogoUrl, 'profile', 'images/profile');
            } else {
                // If it's not base64, handle as before (URL or regular file)
                $logoUrl = Helpers::uploadImage('profile', $uploadLogoUrl, 'images/profile');
            }
        }

        $user->profile_image = $logoUrl;
        $user->save();

        $profileUrl = $user->profile_image;
        if (!empty($profileUrl)) {
            // check if it is digitalocean url or not
            if (strpos($profileUrl, 'https://') !== 0) {
                $profileUrl = asset($profileUrl);
            }
        }
        
        // delete old image
        if ($oldLogoUrl) {
            Helpers::deleteImage($oldLogoUrl);
        }

        $returnData = [];
        $returnData['user'] = [
            'id' => Helpers::encrypt($user->id),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'company_name' => $user->company_name,
            'fca_number' => $user->fca_number,
            'website' => $user->website,
            'email' => $user->email,
            'profile_image' => $profileUrl ?? null,
            'is_brandkit' => $user->hasBrandKit(),
            'is_subscribed' => $user->subscription ? true : false,
        ];

        return $this->success($returnData, 'Profile updated successfully');
    }

    /**
     * Update user password
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function passwordUpdate(Request $request)
    {
        $token = $request->bearerToken();
        $user = Auth::user();
        if (!$user || !$token) {
            return $this->error('Invalid user id', 404);
        }

        $validator = Validator::make($request->all(), [
            'new_password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                'Validation failed',
                $validator->errors(),
            );
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

         // 🔹 Logout from current device only
        $user->tokens()->delete();

        return response()->json(['status' => 'error','message' => 'Password changed.',], 401);

    }
}
