<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\ImageStockManagement;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\UserSubscription;
use App\Models\UserTemplates;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementApiController extends Controller
{
    use ResponseTrait;

    public function deleteAccount(Request $request)
    {
        $userId = Auth::user()->id;

        $user = User::find($userId);
        if (!$user) {
            return $this->error('User not found', 404);
        }
        // stock image delete
        // $user->imageStockManagement()->delete();
        $allDeleteImageData = ImageStockManagement::where('user_id', $userId)->get();

        foreach ($allDeleteImageData as $value) {
            Helpers::deleteImage($value->image_url);
            $value->delete();
        }

        // brand kit delete
        // $user->brandKit()->delete();
        $userBrandKit = BrandKit::where('user_id', $userId)->get();
        if (!empty($userBrandKit)) {
            Helpers::deleteImage($userBrandKit->logo);
            $userBrandKit->delete();
        }

        // subscription (all) delete
        // $user->subscription()->delete();
        $userSubscription = UserSubscription::where('user_id', $userId)->get();
        if (!empty($userSubscription)) {
            foreach ($userSubscription as $value) {
                $value->delete();
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

        $user->delete();
        return $this->success([],'Account deleted successfully');
    }
}
