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
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


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

    public function checkFca(Request $request){
        $fcaNumber = $request->fca_number;

        if (empty($fcaNumber)) {
            return response()->json([
                'status' => '0',
                'Message' => 'FCA number is required.'
            ]);
        }

        $returnResponse = [];
        $headers = [
            'x-auth-email' => config('app.FCA_Auth_EMAIL'),
            'x-auth-key' => config('app.FCA_Auth_KEY'),
            'Content-Type' => 'application/json',
        ];
    
        $response = Http::withHeaders($headers)->get('https://register.fca.org.uk/services/V0.1/Firm/'.$request->fca_number);
        $data = $response->json();

        
    
        if (!empty($data['Data'][0]["Name"])) {
            $nameUrl = $data['Data'][0]["Name"];
    
            $responseName = Http::withHeaders($headers)->get($nameUrl);
            $nameData = $responseName->json();
    
            $companyName = $nameData['Data'][0]['Current Names'][0]['Name'] ?? '';
    
            $returnResponse = [
                'status' => '1',
                'Company Name' => $companyName
            ];
        } else {
            $returnResponse = [
                'status' => '0',
                'Message' => 'Please Enter Valide FCA Number'
            ];
        }
    
        return response()->json($returnResponse);
    }



}
