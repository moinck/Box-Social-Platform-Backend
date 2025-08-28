<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSubscriptionHistoryApiController extends Controller
{
    use ResponseTrait;

    /** User Subscription History */
    public function userSubscriptionHistory(Request $request)
    {
        $user = Auth::user();

        $searchQuery = $request->search;

        $subscriptions = UserSubscription::with(['user:id,first_name,last_name,email', 'plan:id,name'])
            ->where('user_id', $user->id)
            ->when($searchQuery, function ($query) use ($user, $searchQuery) {
                $query->where('user_id', $user->id)
                    ->where(function ($query) use ($searchQuery) {
                        $query->whereHas('plan', function ($query) use ($searchQuery) {
                            $query->where('name', 'like', "%$searchQuery%");
                        })
                        ->orWhereHas('user', function ($query) use ($searchQuery) {
                            $query->where('first_name', 'like', "%$searchQuery%")
                                ->orWhere('last_name', 'like', "%$searchQuery%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchQuery . '%']);
                        })
                        ->orWhere('status', 'like', "%$searchQuery%");
                    });
            })
            ->latest()
            ->get();

        if (empty($subscriptions)) {
            return $this->error('No subscription history', 404);
        }

        $returnData = [];

        foreach ($subscriptions as $val) {

            $start_date = ($val->status === 'active' || $val->current_period_start)
                ? Carbon::parse($val->current_period_start)->format('d-m-Y')
                : null;

            $end_date = ($val->status === 'active' || $val->current_period_end)
                ? Carbon::parse($val->current_period_end)->format('d-m-Y')
                : null;

            $cancelled_at = $val->cancelled_at ? Helpers::dateFormate($val->cancelled_at) : null;

            $returnData[] = [
                'id' => Helpers::encrypt($val->id),
                'user_id' => Helpers::encrypt($val->user_id),
                'user_name' => $val->user->first_name . ' ' . $val->user->last_name,
                'plan_id' => $val->plan_id,
                'plan' => $val->plan->name,
                'status' => $val->status,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'cancelled_at' => $cancelled_at,
                'is_plan_canceled' => $val->is_subscription_cancel == true ? true : false,
                'created_date' => Helpers::dateFormate($val->created_at),
                'updated_date' => Helpers::dateFormate($val->updated_at),
                'coupon_name' => $val->coupon_name,
                'coupon_code' => $val->coupon_code,
                'coupon_discount' => $val->coupon_discount,
                'coupon_currency' => $val->coupon_currency
            ];
        }

        return $this->success($returnData, 'Subscription history');
    }
}
