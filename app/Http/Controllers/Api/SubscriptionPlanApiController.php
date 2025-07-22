<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlans;
use App\ResponseTrait;
use Illuminate\Http\Request;

class SubscriptionPlanApiController extends Controller
{
    use ResponseTrait;

    public function list()
    {
        $subscritionPlans = SubscriptionPlans::where('is_active', true)->get();

        $returnData = [];
        foreach ($subscritionPlans as $plan) {
            $returnData[] = [
                'id' => Helpers::encrypt($plan->id),
                'name' => $plan->name,
                'price' => $plan->price,
                'currency' => $plan->currency,
            ];
        }

        return $this->success($returnData, 'Subscription Plans List');
    }
}
