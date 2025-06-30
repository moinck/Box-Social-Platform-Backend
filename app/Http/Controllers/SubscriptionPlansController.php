<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlans;

class SubscriptionPlansController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlans::where('is_active', 1)->get();
        return view('content.pages.plans.index', compact('plans'));
    }
}
