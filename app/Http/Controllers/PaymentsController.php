<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Models\Payments;
use App\Models\UserSubscription;
use Yajra\DataTables\Facades\DataTables;

class PaymentsController extends Controller
{
    public function index()
    {
        return view('content.pages.payments.index');
    }

    public function dataTable(Request $request)
    {
        $payments = Payments::with('user:id,first_name,last_name,email')
            ->latest()
            ->get();

        return DataTables::of($payments)
            ->addIndexColumn()
            ->addColumn('user', function ($payment) {
                $name = $payment->user->first_name . ' ' . $payment->user->last_name;
                return $name;
            })
            // ->addColumn('payment_id', function ($payment) {
            //     if ($payment->stripe_payment_intent_id) {
            //         $subscription = UserSubscription::select('id','stripe_subscription_id')->where('stripe_payment_method_id', $payment->stripe_payment_intent_id)->first();
            //         return $subscription ? $subscription->stripe_subscription_id : 'N/A';
            //     }
            //     return 'N/A';
            // })
            ->addColumn('plan', function ($payment) {
                return $payment->plan_name;
            })
            ->addColumn('amount', function ($payment) {
                return $payment->amount . ' ' . strtoupper($payment->currency);
            })
            ->addColumn('payment_method', function ($payment) {
                return $payment->payment_method ? strtoupper($payment->payment_method) : 'N/A';
            })
            ->addColumn('created_date', function ($payment) {
                return Helpers::dateFormate($payment->created_at);
            })
            ->rawColumns(['user', 'plan', 'amount', 'payment_method', 'created_date'])
            ->make(true);
    }
}
