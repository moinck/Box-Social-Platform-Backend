<?php

namespace App\Http\Controllers\pages;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\RegisterVerificationMail;
use App\Mail\UserTemplateSendMail;
use App\Models\UserTemplates;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class Page2 extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('send_notification') && $request->send_notification == 1) {
            return $this->testNotification($request->user_id);
        }

        if ($request->has('send_mail') && $request->send_mail == 1) {
            return $this->testMail();
        }
        if ($request->has('mail_logs') && $request->mail_logs == 1) {
            return $this->mailLogs();
        }
        // return view('content.pages.pages-page2');
        $verification_link = "www.youtube.com";
        $reset_password_link = "www.youtube.com";
        $user = User::find(1);
        // return view('content.email.verify-email', compact('verification_link'));
        // return view('content.email.reset-password-email', compact('reset_password_link','user'));
        // return view('content.email.custom-email', compact('verification_link','user'));

        $data = [];
        $data['user'] = $user;
        $data['template'] = UserTemplates::find(3);
        $type = 'store';
        // Mail::to("pratikdev.iihglobal@gmail.com")->send(new UserTemplateSendMail($data));

        return view('content.email.send-user-template', compact('data','type'));
    }

    // make test function to send mail
    public function testMail()
    {
        $user = User::find(1);
        $verification_link = "www.youtube.com";
        // Mail::to($user->email)->send(new RegisterVerificationMail($verification_link));
        // $token = Helpers::sendVerificationMail($user,$verification_link);
        // Mail::to("pratikdev.iihglobal@gmail.com")->send(new RegisterVerificationMail($verification_link));
        Helpers::sendMail(new RegisterVerificationMail($verification_link) ,"pratikdev.iihglobal@gmail.com");

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

    public function testNotification($id)
    {
        $user = User::find($id);
        Helpers::sendNotification($user, 'new-registration');

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
            ]
        ]);
    }

    public function mailLogs()
    {
        return view('content.pages.mail-logs');
    }
}
