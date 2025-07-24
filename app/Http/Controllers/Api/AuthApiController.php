<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\ForgetPasswordMail;
use App\Models\BrandKit;
use App\Models\User;
use App\Models\UserTokens;
use App\ResponseTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthApiController extends Controller
{
    use ResponseTrait;

    /**
     * Verify email
     */
    // public function verifyEmail(EmailVerificationRequest $request)
    public function verify(Request $request)
    {
        $validator = Validator::make([
            'verification_token' => $request->verification_token
        ], [
            'verification_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError('Invalid verification token', $validator->errors());
        }

        $verificationToken = $request->verification_token;

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
            return $this->error('Email is already verified', 400);
        }

        // gernerte token
        $LoginToken = $user->createToken('auth_token', ['*'], now()->addDays(3))->plainTextToken;
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
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'company_name' => $user->company_name,
                'website' => $user->website,
                'fca_number' => $user->fca_number,
                'created_at' => $user->created_at->format('d-m-Y h:i A'),
                'is_verified' => $user->is_verified,
                'is_brandkit' => $isBrandkit,
            ],
            'access_token' => $LoginToken,
            'token_type' => 'Bearer',
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

        $decryptedToken = Helpers::decrypt($request->verification_token);
        if (!$decryptedToken) {
            return $this->error('Invalid or expired verification token', 400);
        }

        // check old token
        $userToken = UserTokens::where(function ($query) use ($decryptedToken) {
            $query->where('token', $decryptedToken)
                ->where('type', 'email-verification')
                // ->where('created_at', '>=', Carbon::now()->subMinutes(5)->toDateTimeString())
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

                // check if user is not verified
                if ($user->hasVerifiedEmail()) {
                    return $this->error('Email is already verified', 400);
                }

                // Allow resending the verification email
                $token = Helpers::generateVarificationToken($user, $request, 'email-verification');
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

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make([
            'email' => $request->email
        ], [
            'email' => 'required|email|exists:users,email'
        ],[
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.exists' => 'Email does not exist',
        ]);
    
        if ($validator->fails()) {
            return $this->validationError('Validation error', $validator->errors());
        }
    
        $user = User::where('email', $request->email)->first();
    
        if ($user) {
            // Check if ANY recent token exists within last 5 minutes
            $recentToken = UserTokens::where([
                'user_id' => $user->id,
                'type' => 'forget-password'
            ])->where('created_at', '>=', Carbon::now()->subMinutes(5))
                ->first(); 
    
            // Check if ANY recent token exists within last 5 minutes
            if ($recentToken) {
                // Log suspicious activity
                Log::warning('Password reset attempt within 5 minutes', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
    
                return $this->error('You can only request a password reset email after 5 minutes from the last request.', 429);
            } else {
                // Invalidate previous tokens
                UserTokens::where([
                    'user_id' => $user->id,
                    'type' => 'forget-password'
                ])->update(['is_used' => true]);
    
                $token = Helpers::generateVarificationToken($user, $request, 'forget-password');
                $encyptedToken = Helpers::encrypt($token);
                Mail::to($user->email)->send(new ForgetPasswordMail($encyptedToken, $user));
    
                return $this->success([
                    'verification_token' => $encyptedToken
                ], 'Password reset email sent successfully');
            }
        } else {
            return $this->error('User Not Found', 404);
        }
    }

    public function resendForgetPassword(Request $request)
    {
        $validator = Validator::make([
            'email' => $request->email
        ], [
            'email' => 'required|email|exists:users,email'
        ],[
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.exists' => 'Email does not exist',
        ]);
    
        if ($validator->fails()) {
            return $this->validationError('Validation error', $validator->errors());
        }
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return $this->error('User Not Found', 404);
        }
    
        // Check if a recent token exists within last 5 minutes
        $recentToken = UserTokens::where([
            'user_id' => $user->id,
            'type' => 'forget-password'
        ])
        ->where('created_at', '>=', Carbon::now()->subMinutes(5))
        ->latest()
        ->first();
    
        if ($recentToken) {
            return $this->error('You can only resend a password reset email after 5 minutes.', 429);
        }
    
        // Invalidate all previous tokens
        UserTokens::where([
            'user_id' => $user->id,
            'type' => 'forget-password'
        ])->update(['is_used' => true]);
    
        // Generate and send new token
        $token = Helpers::generateVarificationToken($user, $request, 'forget-password');
        $encyptedToken = Helpers::encrypt($token);
        Mail::to($user->email)->send(new ForgetPasswordMail($encyptedToken, $user));
    
        return $this->success([
            'verification_token' => $encyptedToken
        ], 'Password reset email resent successfully');
    }
    

    public function resetPassword(Request $request)
    {
        $validator = Validator::make([
            'token' => $request->token,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation
        ], [
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
            'password_confirmation' => 'required|same:password',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.mixed' => 'Password must contain both uppercase and lowercase letters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one symbol.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.same' => 'Password confirmation must match the password.',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }
        // decrypt token
        $decryptedToken = Helpers::decrypt($request->token);

        if (!$decryptedToken) {
            return $this->error('Invalid or expired reset token', 400);
        }

        $userToken = UserTokens::where([
            'token' => $decryptedToken,
            'type' => 'forget-password',
            'is_used' => false
        ])->where('created_at', '>=', Carbon::now()->subMinutes(5))->first();

        if (!$userToken) {
            return $this->error('Invalid or expired reset token', 400);
        }

        $user = User::find($userToken->user_id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Invalidate ALL password reset tokens for this user
        UserTokens::where([
            'user_id' => $user->id,
            'type' => 'forget-password'
        ])->update(['is_used' => true]);
        return $this->success([], 'Password reset successfully');
    }
}
