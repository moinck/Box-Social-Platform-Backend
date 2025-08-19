<?php

namespace App\Http\Controllers;

use App\Exports\UsersSubscriptionExport;
use App\Helpers\Helpers;
use App\Models\UserDownloads;
use App\Models\UserSubscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class UserSubscriptionController extends Controller
{
    public function index()
    {
        return view('content.pages.user-subscription.index');
    }

    public function dataTable(Request $request)
    {

        $subscriptionPlan = isset($request->subscription_plan) ? $request->subscription_plan : null;

        $subscriptions = UserSubscription::with('user:id,first_name,last_name,email','plan:id,name')
            ->when($subscriptionPlan, function($query, $subscriptionPlan){
                if ($subscriptionPlan == 1) {
                    return $query->where('plan_id', 1);
                }
                return $query->whereIn('plan_id', [2,3]);
            })
            ->latest()
            ->get();

        return DataTables::of($subscriptions)
            ->addIndexColumn()
            ->addColumn('user', function ($subscription) {
                $name = $subscription->user->first_name . ' ' . $subscription->user->last_name;
                return $name;
            })
            ->addColumn('plan', function ($subscription) {
                return $subscription->plan->name;
            })
            ->addColumn('status', function ($subscription) {
                $button = '';
                if ($subscription->status == 'active') {
                    $button = '<span class="badge bg-label-success rounded-pill text-uppercase">'.$subscription->status.'</span>';
                } else {
                    $button = '<span class="badge bg-label-danger rounded-pill text-uppercase">'.$subscription->status.'</span>';
                }

                return $button;
            })
            ->addColumn('start_date', function ($subscription) {
                if ($subscription->status == 'active' || $subscription->current_period_start != null) {
                    return date('d-m-Y', strtotime($subscription->current_period_start));
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('end_date', function ($subscription) {
                if ($subscription->status == 'active' || $subscription->current_period_end != null) {
                    return date('d-m-Y', strtotime($subscription->current_period_end));
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('created_date', function ($subscription) {
                return Helpers::dateFormate($subscription->created_at);
            })
            ->addColumn('updated_date', function ($subscription) {
                return Helpers::dateFormate($subscription->updated_at);
            })
            ->addColumn('action', function ($subscription) {
                $id = Helpers::encrypt($subscription->id);
                $showRoute = route('subscription-management.show',$id);
                $deleteBtn = '<a href="javascript:void(0);" data-user-subscription-id="' . $id . '" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-user-subscription-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </a>';

                return '
                    <a href="' . $showRoute . '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Show">
                        <i class="ri-eye-line"></i>
                    </a>
                ';
            })
            ->rawColumns(['user', 'plan', 'status', 'start_date', 'end_date', 'created_date', 'updated_date', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $decyptId = Helpers::decrypt($id);

        $subscriptionData = UserSubscription::with('user:id,first_name,last_name,email,created_at','plan:id,name,price,features,currency')->findOrFail($decyptId);
        $userDownloads = UserDownloads::where('user_subscription_id', $decyptId)->latest()->first();
        // dd($userDownloads);

        return view('content.pages.user-subscription.show', compact('subscriptionData', 'userDownloads'));
    }

    /** Subscription Details Exports */
    public function exportSubscriptionDetails(Request $request)
    {
        try {

            $subscriptionPlan = isset($request->plan_id) ? $request->plan_id : null;

            $subscriptions = UserSubscription::with('user:id,first_name,last_name,email','plan:id,name')
                ->when($subscriptionPlan, function($query, $subscriptionPlan){
                    if ($subscriptionPlan == 1) {
                        return $query->where('plan_id', 1);
                    }
                    return $query->whereIn('plan_id', [2,3]);
                })
                ->when($request->subscription_table_search && $request->subscription_table_search != null, function ($query) use ($request) {
                   $search = $request->subscription_table_search;

                    $query->where(function ($query) use ($search) {
                        $query->whereHas('user', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        })
                        ->orWhereHas('plan', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    });
                })
                ->latest()
                ->get();

            $format = $request->format;
            $name = 'subscription_' . date('Y-m-d_g-s');
            if ($format == 'csv') {
                return Excel::download(new UsersSubscriptionExport($subscriptions), $name . '.csv');
            } else {
                return Excel::download(new UsersSubscriptionExport($subscriptions), $name . '.xlsx');
            }

        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong.']);
        }
    }
}
