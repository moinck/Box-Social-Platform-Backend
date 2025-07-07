<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRegisterRequest;
use App\Models\BrandKit;
use App\ResponseTrait;
use Illuminate\Auth\Events\Registered;
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
    use ResponseTrait;

    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-register-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function GetAllUser()
    {
        $userData = User::query();
        return DataTables::of($userData)
            ->addColumn('name', function ($data) {
                return $data['first_name'] . ' ' . $data['last_name'];
            })
            ->addColumn('action', function ($data) {
                return '<a href="javascript:;" title="View" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ri-eye-line"></i></a>';
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
            'fca_number' => 'required|numeric|min:6|unique:users,fca_number',
            'website' => 'nullable|string|url',
        ], [
            // General password messages
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.mixed' => 'Password must contain both uppercase and lowercase letters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one symbol.',
            'password.uncompromised' => 'Please choose a strong password for security reasons.',
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
                'is_verified' => false,
            ]);

            // send notification of new registration
            Helpers::sendNotification($user, "new-registration");

            // Send verification email
            $token = Helpers::generateVarificationToken($user, $request, 'email-verification');
            Helpers::sendVerificationMail($user, $token);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for verification link.',
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
                        'is_verified' => $user->is_verified ? true : false,
                    ],
                    'verification_token' => Helpers::encrypt($token),
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
            'email' => 'required|email|exists:users,email',
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

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found.',
                'data' => []
            ], 404);
        }

        // check account status
        if ($user->status != 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.',
                'data' => []
            ], 403);
        }

        // Authentication passed, generate token or proceed as needed
        // $user = Auth::user();
        // $user = User::where('email', $request->email)->first();

        // Check if email is verified
        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Email not verified. Please verify your email first.',
        //         'data' => [
        //             'user' => [
        //                 'id' => Helpers::encrypt($user->id),
        //                 'first_name' => $user->first_name,
        //                 'last_name' => $user->last_name,
        //                 'email' => $user->email,
        //                 'is_verified' => $user->is_verified,
        //             ]
        //         ]
        //     ], 403);
        // }
        // For example, generate a token if using Laravel Sanctum or Passport
        $token = $user->createToken('auth_token', ['*'], now()->addDays(3))->plainTextToken;

        // check does user have brandkit
        $isBrandkit = BrandKit::where('user_id', $user->id)->exists() ? true : false;

        return response()->json([
            'success' => true,
            'message' => 'Login successfully.',
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
                    'is_verified' => $user->is_verified,
                    'is_brandkit' => $isBrandkit,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],

        ], 200);

    }

    public function checkFca(Request $request)
    {
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

        $response = Http::withHeaders($headers)->get('https://register.fca.org.uk/services/V0.1/Firm/' . $request->fca_number);
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

            return response()->json($returnResponse, 422);
        }

        return response()->json($returnResponse, 200);
    }

    /**
     * Logout api
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout successfully.',
        ], 200);
    }
}
