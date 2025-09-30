<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CookiePolicy;
use App\Models\TermsAndCondition;
use App\ResponseTrait;
use Illuminate\Http\Request;

class TermsAndConditionApiController extends Controller
{
    use ResponseTrait;

    public function get()
    {
        $termsAndCondition = TermsAndCondition::first();

        $returnData = [];
        $returnData['title'] = $termsAndCondition->title;
        $returnData['description'] = $termsAndCondition->description;

        return $this->success($returnData, 'Terms and Condition fetched successfully');
    }

    /** Cookie Policy */
    public function cookiePolicy()
    {
        $cookiePolicy = CookiePolicy::first();

        $returnData = [];
        $returnData['title'] = $cookiePolicy->title;
        $returnData['description'] = $cookiePolicy->description;

        return $this->success($returnData, 'Cookie policy fetched successfully');

    }
}
