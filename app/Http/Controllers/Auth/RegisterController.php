<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string',
            'fca_number' => 'required|numeric|min:6',
            'website' => 'required|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // Create the user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_name' => $request->company_name,
                'website' => $request->website,
                'fca_number' => $request->fca_number,
            ]);
            
            // Create an API token for the user
            $token = $user->createToken('auth_token')->plainTextToken;
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'company_name' => $user->company_name,
                        'website' => $user->website,
                        'fca_number' => $user->fca_number,
                        'created_at' => $user->created_at->format('d-m-Y h:i A'),
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
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
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        // Authentication passed, generate token or proceed as needed
        $user = Auth::user();
        // For example, generate a token if using Laravel Sanctum or Passport
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }
}
