<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = User::with('subscription:id,user_id')->find($userId);
        if (!$user) {
            return $this->error('User not found', 404);
        }

        // make data array
        $data = [];
        $data['user'] = [
            'id' => Helpers::encrypt($user->id),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'company_name' => $user->company_name,
            'fca_number' => $user->fca_number,
            'website' => $user->website,
            'email' => $user->email,
            'phone_number' => $user->phone ?? null,
            'profile_image' => $user->profile_image ? asset($user->profile_image) : null,
            'is_brandkit' => $user->hasBrandKit(),
            'is_subscribed' => $user->subscription ? true : false,
        ];

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
            'website' => 'nullable|url',
            'email' => 'required|email',
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
        $user->company_name = $request->company_name;
        $user->fca_number = $request->fca_number;
        $user->website = $request->website;
        $user->email = $request->email;
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
            'profile_image' => $user->profile_image ? asset($user->profile_image) : null,
            'is_brandkit' => $user->hasBrandKit(),
            'is_subscribed' => $user->subscription ? true : false,
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
            'profile_image' => $user->profile_image ? asset($user->profile_image) : null,
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

        return $this->success([], 'Password updated successfully');
    }
}
