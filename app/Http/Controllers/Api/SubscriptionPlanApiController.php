<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlans;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubscriptionPlanApiController extends Controller
{
    use ResponseTrait;

    public function list()
    {
        $cacheKey = 'subscription_plans_list';

        // $returnData = Cache::remember($cacheKey, env('CACHE_TIME'), function () {
            $subscritionPlans = SubscriptionPlans::where('is_active', true)->get();

            $data = [];
            foreach ($subscritionPlans as $plan) {
                $data[] = [
                    'id' => Helpers::encrypt($plan->id),
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'interval' => $plan->interval,
                ];
            }

            // return $data;
        // });

        return $this->success($data, 'Subscription Plans List');
    }
}
