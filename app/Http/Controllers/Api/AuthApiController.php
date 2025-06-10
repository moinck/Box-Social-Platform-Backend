<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\User;
use App\Models\UserTokens;
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
        $validator = Validator::make([
            'verification_token' => $encryptedToken
        ], [
            'verification_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError('Invalid verification token', $validator->errors());
        }

        $verificationToken = $encryptedToken;

        $userToken = UserTokens::where(function ($query) use ($verificationToken) {
            $query->where('token', $verificationToken)
                ->where('type', 'email-verification')
                ->where('created_at', '>=', Carbon::now()->subMinutes(5)->toDateTimeString())
                ->where('is_used', false);
        })->first();

        if (!$userToken) {
            return $this->error('Invalid or expired verification token', 400);
        }

        // Find the user
        $user = User::find($userToken->user_id);

        if (!$user) {
            return $this->error('User not found', 404);
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

        $userToken->update([
            'is_used' => true
        ]);

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

        $userToken = UserTokens::where(function ($query) use ($request) {
            $query->where('token', $request->verification_token)
                ->where('type', 'email-verification')
                ->where('created_at', '>=', Carbon::now()->subMinutes(5)->toDateTimeString())
                ->where('is_used', false);
        })->first();

        if (!$userToken) {
            return $this->error('Invalid or expired verification token', 400);
        }

        // if there is user token
        if ($userToken) {
            $tokenCreatedAt = Carbon::parse($userToken->created_at);
            $fiveMinutesAgo = Carbon::now()->subMinutes(5);

            if ($tokenCreatedAt->lt($fiveMinutesAgo)) {
                $user = User::find($userToken->user_id);


                // Allow resending the verification email
                $token = Helpers::generateVarificationToken($user, $request);
                Helpers::sendVerificationMail($user, $token);

                // update old user token
                $userToken->update([
                    'is_used' => true
                ]);

                return $this->success([
                    'verification_token' => $token
                ], 'Verification email re-sent successfully.');

            } else {
                // Do not allow resending the verification email
                // Inform the user to wait for 5 minutes
                return $this->error('Please wait for 5 minutes before resending the verification email', 400);
            }
        }
    }
}
