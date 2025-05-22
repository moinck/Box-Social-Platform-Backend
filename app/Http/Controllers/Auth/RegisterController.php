<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;


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
            'password' => [
            'required',
            'string',
            Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised(), // check if password was leaked in data breaches
            ],
            'company_name' => 'required|string',
            'fca_number' => 'required|numeric|min:6',
            'website' => 'nullable|string|url',

        ]);

        $messages = [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            // More specific messages can be added if needed
        ];

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
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
                        'id' => Helpers::encrypt($user->id),
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
            ], 200);
            
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
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email is not registered.',
                'data' => []
            ], 404);
        }

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 401);
        }

        // Authentication passed, generate token or proceed as needed
        $user = Auth::user();
        // For example, generate a token if using Laravel Sanctum or Passport
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => [
                    'user' => [
                        'id' => Helpers::encrypt($user->id),
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

        ], 200);

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

            return response()->json($returnResponse,422);
        }
    
        return response()->json($returnResponse,200);
    }



}
