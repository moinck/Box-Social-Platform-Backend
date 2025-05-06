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
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return redirect('/login')->withErrors($validator)->withInput();
        }
        $user = User::where('email', $request['email'])->first();
        
        if(!empty($user))
        {
            if(Hash::check($request['password'],$user->password)){
                Auth::login($user);
                return redirect('/home')->with('message','Logged-in');

            }
            else{
                return redirect()->back()->withInput()->with('pw-err-msg', 'Password is incorrect');
            }
        }
        else{
            return redirect()->back()->withInput()->with('email-err-msg', 'These credentials do not match our records.');
        }
    }

    public function logout(){
        Auth::logout(); 
        return redirect('/login');
    }
}
