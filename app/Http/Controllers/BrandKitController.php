<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BrandKit;

class BrandKitController extends Controller
{
    public function GetData(Request $request){

    }

    public function Store(Request $request){

        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);

        }

        $brandKitObj = new BrandKit();
        $brandKitObj->logo = $request->logo;
        $brandKitObj->user_id = $request->user_id;
        $brandKitObj->company_name = $request->company_name;
        $brandKitObj->email = $request->email;
        $brandKitObj->address = $request->address;
        $brandKitObj->state = $request->state;
        $brandKitObj->phone = $request->phone;
        $brandKitObj->country = $request->country;
        $brandKitObj->website = $request->website;
        $brandKitObj->postal_code = $request->postal_code;
        $brandKitObj->show_email_on_post = $request->show_email_on_post;
        $brandKitObj->show_phone_number_on_post = $request->show_phone_number_on_post;
        $brandKitObj->show_website_on_post = $request->show_website_on_post;
        $brandKitObj->social_media_icon_show = json_encode($request->social_media_icon_show);
        $brandKitObj->design_style = json_encode($request->design_style);
        $brandKitObj->save();


    }
}
