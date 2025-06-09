<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    use ResponseTrait;

    /**
     * Verify email
     */
    // public function verifyEmail(EmailVerificationRequest $request)
    public function verify($encryptedToken)
    {
        try {
            // Decrypt the token
            $tokenData = json_decode(Helpers::decrypt(urldecode($encryptedToken)), true);
            
            if (!$tokenData || !isset($tokenData['user_id'], $tokenData['expires_at'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification token'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token'
            ], 400);
        }

        // Check if token has expired
        if (Carbon::now()->timestamp > $tokenData['expires_at']) {
            return response()->json([
                'success' => false,
                'message' => 'Verification link has expired'
            ], 400);
        }

        // Find the user
        $user = User::find($tokenData['user_id']);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return $this->success([], 'Email already verified');
        }

        // gernerte token
        $LoginToken = $user->createToken('auth_token')->plainTextToken;
        // check does user have brandkit
        $isBrandkit = BrandKit::where('user_id', $user->id)->exists() ? true : false;

        // Mark email as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        $returnData = [
            'user' => [
                'id' => Helpers::encrypt($user->id),
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'is_verified' => $user->is_verified,
            ],
            'access_token' => $LoginToken,
            'token_type' => 'Bearer',
            'is_brandkit' => $isBrandkit,
        ];

        return $this->success($returnData, 'Email verified successfully');
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $request->validate([
            'verification_token' => 'required'
        ]);

        // Decrypt the token
        $tokenData = json_decode(Helpers::decrypt(urldecode($request->verification_token)), true);
            
        if (!$tokenData || !isset($tokenData['user_id'], $tokenData['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token'
            ], 400);
        }

        $user = User::where('id', $tokenData['user_id'])->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success([], 'Email already verified');
        }

        // check timestamp from token (5 mintues)
        if (Carbon::now()->timestamp > $tokenData['expires_at']) {
            return $this->error('Verification link has expired', 400);
        }

        // also chcek if 5 mintues has passed since last verification email
        if (Carbon::now()->timestamp - $tokenData['expires_at'] < 300) {
            return $this->error('Please wait 5 minutes before resending the verification email', 400);
        }   

        $token = Helpers::sendVerificationMail($user);

        return $this->success([
            'verification_token' => $token
        ], 'Verification email re-sent successfully.');
    }
}
