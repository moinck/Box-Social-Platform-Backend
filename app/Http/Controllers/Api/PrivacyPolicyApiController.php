<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use App\ResponseTrait;
use Illuminate\Http\Request;

class PrivacyPolicyApiController extends Controller
{
    use ResponseTrait;

    public function get(Request $request)
    {
        $privacyPolicy = PrivacyPolicy::first();

        return $this->success($privacyPolicy, 'Privacy Policy fetched successfully');
    }
}
