<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BetaJoinNotification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BetaTesterController extends Controller
{
    public function sendBetaTesterMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $appMode = env('APP_ENV');
            $adminMail = User::where('role', 'admin')->first()->email;
            if ($appMode == 'live') {
                Mail::to('help@boxsocials.com')->send(new BetaJoinNotification($request->name, $request->email));
            }else{
                Mail::to($adminMail)->send(new BetaJoinNotification($request->name, $request->email));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Thanks for joining the waiting list!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send mail. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
