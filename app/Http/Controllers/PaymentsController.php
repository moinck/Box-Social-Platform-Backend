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
        $payments = Payments::with('user:id,first_name,last_name,email','subscription:id,stripe_subscription_id')
            ->latest()
            ->get();

        return DataTables::of($payments)
            ->addIndexColumn()
            ->addColumn('user', function ($payment) {
                $name = $payment->user->first_name . ' ' . $payment->user->last_name;
                return $name;
            })
            ->addColumn('subscription_id', function ($payment) {
                return $payment->subscription->stripe_subscription_id ?? "N/A";
            })
            ->addColumn('payment_id', function ($payment) {
                return $payment->stripe_payment_intent_id ?? "N/A";
            })
            ->addColumn('plan', function ($payment) {
                return $payment->plan_name;
            })
            ->addColumn('amount', function ($payment) {
                return $payment->amount . ' ' . strtoupper($payment->currency);
            })
            ->addColumn('coupon_discounted_amt', function ($payment) {
                $discountAmount = $payment->coupon_discounted_amt ?? null;
                if ($discountAmount) {
                    return $discountAmount . ' ' . strtoupper($payment->currency);
                }
                return "N/A";
            })
            ->addColumn('payment_method', function ($payment) {
                return $payment->payment_method ? strtoupper($payment->payment_method) : 'N/A';
            })
            ->addColumn('created_date', function ($payment) {
                return Helpers::dateFormate($payment->created_at);
            })
            ->rawColumns(['user', 'plan', 'amount', 'payment_method', 'created_date', 'subscription_id', 'payment_id', 'coupon_discounted_amt'])
            ->make(true);
    }
}
