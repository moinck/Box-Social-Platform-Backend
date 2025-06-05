<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
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
    public function verify(Request $request, $encryptedToken)
    {
        try {
            // Decrypt the token
            $tokenData = json_decode(Helpers::decrypt(urldecode($encryptedToken)), true);
            
            if (!$tokenData || !isset($tokenData['user_id'], $tokenData['email'], $tokenData['expires_at'])) {
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

        // Verify email matches
        if ($user->getEmailForVerification() !== $tokenData['email']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token'
            ], 400);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_verified' => $user->is_verified,
                    ]
                ]
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_verified' => $user->is_verified,
                ]
            ]
        ]);
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully'
        ]);
    }
}
