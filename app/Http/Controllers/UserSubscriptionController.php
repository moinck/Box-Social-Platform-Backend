<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserSubscriptionController extends Controller
{
    public function index()
    {
        return view('content.pages.user-subscription.index');
    }

    public function dataTable(Request $request)
    {
        $subscriptions = UserSubscription::with('user:id,first_name,last_name,email','plan:id,name')
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
                return date('d-m-Y', strtotime($subscription->current_period_start));
            })
            ->addColumn('end_date', function ($subscription) {
                return date('d-m-Y', strtotime($subscription->current_period_end));
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

                return '
                    <a href="' . $showRoute . '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Show">
                        <i class="ri-eye-line"></i>
                    </a>
                    <a href="javascript:void(0);" data-user-subscription-id="' . $id . '" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-user-subscription-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </a>
                ';
            })
            ->rawColumns(['user', 'plan', 'status', 'start_date', 'end_date', 'created_date', 'updated_date', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $decyptId = Helpers::decrypt($id);

        $subscriptionData = UserSubscription::with('user:id,first_name,last_name','plan:id,name,price,features,currency')->findOrFail($decyptId);

        return view('content.pages.user-subscription.show', compact('subscriptionData'));
    }
}
