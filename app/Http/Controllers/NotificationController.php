<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        $notification = Notification::find(Helpers::decrypt($request->id));
        if ($notification) {
            $notification->is_read = true;
            $notification->save();
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.'
            ]);
        }
    }
}
