<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Notification;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function dataTable()
    {
        $notifications = Notification::where('is_read', false)->latest()->get();

        return DataTables::of($notifications)
            ->addIndexColumn()
            ->addColumn('title', function ($notification) {
                return $notification->tital;
            })
            ->addColumn('message', function ($notification) {
                return $notification->body;
            })
            ->addColumn('type', function ($notification) {
                $retrunData = "";
                if ($notification->type == "new-registration") {
                    $retrunData = "New Registration";
                }else if ($notification->type == "new-contact-us") {
                    $retrunData = "New Feedback";
                }
                return $retrunData;
            })
            ->addColumn('action', function ($notification) {
                $encyptedId = Helpers::encrypt($notification->id);
                return '
                    <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon notification-mark-as-read-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Mark as Read" data-notification-id="'.$encyptedId.'"><i class="ri-mail-check-fill"></i></a>
                ';
            })
            ->rawColumns(['title','message','type','action'])
            ->make(true);
    }

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
