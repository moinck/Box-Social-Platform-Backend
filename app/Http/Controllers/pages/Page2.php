<?php

namespace App\Http\Controllers\pages;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\RegisterVerificationMail;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class Page2 extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('send_mail') && $request->send_mail == 1) {
            return $this->testMail();
        }
        // return view('content.pages.pages-page2');
        $verification_link = "www.youtube.com";
        return view('content.email.verify-email', compact('verification_link'));
    }

    // make test function to send mail
    public function testMail()
    {
        $user = User::find(4);
        $verification_link = "www.youtube.com";
        // Mail::to($user->email)->send(new RegisterVerificationMail($verification_link));
        $token = Helpers::sendVerificationMail($user);

        return response()->json([
            'success' => true,
            'message' => 'Mail sent successfully',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
            ]
        ]);
    }
}
