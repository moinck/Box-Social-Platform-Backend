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

        try {
            DB::beginTransaction();

            // add fca number
            FcaNumbers::updateOrCreate([
                'fca_number' => $user->fca_number,
            ], [
                'fca_name' => $user->company_name,
            ]);
    
            // stock image delete
            // $user->imageStockManagement()->delete();
            $allDeleteImageData = ImageStockManagement::where('user_id', $userId)->get();
    
            foreach ($allDeleteImageData as $value) {
                Helpers::deleteImage($value->image_url);
                $value->delete();
            }
    
            // brand kit delete
            // $user->brandKit()->delete();
            $userBrandKit = BrandKit::where('user_id', $userId)->first();
            if (!empty($userBrandKit)) {
                Helpers::deleteImage($userBrandKit->logo);
                $userBrandKit->delete();
            }
    
            // subscription (all) delete
            // $user->subscription()->delete();
            $userSubscription = UserSubscription::where('user_id', $userId)->get();
            if (!empty($userSubscription)) {
                foreach ($userSubscription as $value) {
                    if ($value->plan_id != 1) {
                        // first cancel subscription from stripe
                        $this->stripe->subscriptions->cancel($value->stripe_subscription_id, [
                            'cancellation_details' => [
                                'comment' => 'user deleted their account',
                                'reason' => 'account_deleted',
                            ],
                        ]);
    
                        $value->delete();
                    } else {
                        $value->delete();
                    }
                }
            }
    
            // user downloads
            $userDownloads = UserDownloads::where('user_id', $userId)->get();
            if (!empty($userDownloads)) {
                foreach ($userDownloads as $value) {
                    $value->delete();
                }
            }
    
            // user template delete
            $userTemplate = UserTemplates::where('user_id', $userId)->get();
            if (!empty($userTemplate)) {
                foreach ($userTemplate as $value) {
                    Helpers::deleteImage($value->template_image);
                    $value->delete();
                }
            }
    
            // delete usertokens
            $userTokens = UserTokens::where('user_id', $userId)->get();
            if (!empty($userTokens)) {
                foreach ($userTokens as $value) {
                    $value->delete();
                }
            }
    
            $user->delete();
            DB::commit();
            return $this->success([],'Your account deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
            Helpers::sendErrorMailToDeveloper($e,'User account delete API.');
            return $this->error('something went wrong', 500);
        }
    }
}
