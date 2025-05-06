<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-register-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function GetAllUser(){
        $userData = User::query();
        return DataTables::of($userData)
        ->addColumn('name', function($data){
            return $data['first_name'].' '.$data['last_name'] ;
        })
        ->addColumn('action', function($data){
           return  '<a href="javascript:;" title="View" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ri-eye-line"></i></a>';
        })
        ->make(true);
    }

    public function register(AuthRegisterRequest $request)
    {

        $user = User::create([
            'first_name' => 'Supper',
            'last_name' => 'Admin',
            'email' => 'admin.box@yopmail.com',
            'password' => Hash::make('adminbox123'),
            'status' => 'active',
            'role' => 'admin',
        ]);
        return response()->json($user);
    }

    public function login(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], 401);
        }

        // Authentication passed, generate token or proceed as needed
        $user = Auth::user();
        // For example, generate a token if using Laravel Sanctum or Passport
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }
}
