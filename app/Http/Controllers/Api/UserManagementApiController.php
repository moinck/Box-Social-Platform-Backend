<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\FcaNumbers;
use App\Models\ImageStockManagement;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\UserSubscription;
use App\Models\UserTemplates;
use App\Models\UserTokens;
use App\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class UserManagementApiController extends Controller
{
    use ResponseTrait;

    private $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret_key'));
    }

    public function deleteUserAccount(Request $request)
    {
        $userId = auth()->user()->id;

        $user = User::find($userId);
        if (!$user) {
            return $this->error('User not found', 404);
        }

        $deleteUser = Helpers::deleteUserData($userId);
        if ($deleteUser === true) {
            return $this->success([], 'Your Account deleted successfully');
        } else {
            return $this->error('Something went wrong', 500);
        }
    }
}
