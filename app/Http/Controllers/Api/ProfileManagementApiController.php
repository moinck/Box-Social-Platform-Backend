<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return $this->error([
                'status' => false,
                'message' => 'Invalid token',
            ]);
        }
        $user = Auth::user();

        $user_id = $user->id;
        if (!$user_id) {
            return $this->error([
                'status' => false,
                'message' => 'Invalid user id',
            ]);
        }
        $user = User::find($user_id);
        if (!$user) {
            return $this->error([
                'status' => false,
                'message' => 'User not found',
            ]);
        }

        // make data array
        $data = [];
        $data['user'] = [
            'id' => Helpers::encrypt($user_id),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'company_name' => $user->company_name,
            'fca_number' => $user->fca_number,
            'website' => $user->website,
            'email' => $user->email,
            'phone_number' => $user->phone ?? null,
            'profile_image' => $user->profile_image ? asset($user->profile_image) : null,
        ];

        return $this->success([
            'status' => true,
            'message' => 'Profile fetched successfully',
            'data' => $data,
        ]);
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
        $user = Auth::user();
        $user_id = $user->id;
        if (!$user_id || $token != $user->api_token) {
            return $this->error([
                'status' => false,
                'message' => 'Invalid user id',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'company_name' => 'required',
            'fca_number' => 'required',
            'website' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'profile_image' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error([
                'status' => false,
                'message' => 'Invalid user id',
            ]);
        }

        $user_id = $user->id;
        if (!$user_id) {
            return $this->error([
                'status' => false,
                'message' => 'Invalid user id',
            ]);
        }
        $user = User::find($user_id);
        if (!$user) {
            return $this->error([
                'status' => false,
                'message' => 'User not found',
            ]);
        }

        $user->first_name = $request->full_name;
        $user->last_name = $request->full_name;
        $user->company_name = $request->company_name;
        $user->fca_number = $request->fca_number;
        $user->website = $request->website;
        $user->email = $request->email;
        $user->phone = $request->phone_number;
        $user->profile_image = $request->profile_image;
        $user->save();

        return $this->success([
            'status' => true,
            'message' => 'Profile updated successfully',
        ]);
    }
}
