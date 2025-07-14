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

        $returnData = [];
        $returnData['title'] = $privacyPolicy->title;
        $returnData['description'] = $privacyPolicy->description;

        return $this->success($returnData, 'Privacy Policy fetched successfully');
    }
}
