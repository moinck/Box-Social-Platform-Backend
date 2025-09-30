<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
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

            $notifications = Notification::where("user_id", $userId)
                ->where('type','!=','new-registration')
                ->orderBy('id','DESC')
                ->get();

            $returnData = [];
            foreach ($notifications as $notification) {
                $returnData[] = [
                    'id' => Helpers::encrypt($notification->id),
                    'type' => $notification->type,
                    'title' => $notification->tital,
                    'message' => $notification->body,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->format('d-m-Y H:i A'),
                ];
            }

            return $this->success($returnData,"Notification fetched.",200);

        } catch (Exception $e) {
            Log::error($e);
            return $this->error("Something went wrong.",500);
        }
    }
}
