<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationApiController extends Controller
{
    Use ResponseTrait;

    /** User Notification List */
    public function list(Request $request)
    {
        try {

            $userId = Auth::user()->id;

            $notifications = Notification::where("user_id", $userId)->orderBy('id','DESC')->get();

            return $this->success($notifications,"Notification fetched.",200);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("SOmething went wrong.",500);
        }
    }
}
