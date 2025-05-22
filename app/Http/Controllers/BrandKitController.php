<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BrandKit;

class BrandKitController extends Controller
{
    public function GetData(Request $request){
        $brandKitObj = BrandKit::where('user_id',$request->user_id)->first();
        

        return response()->json([
            'success' => true,
            'message' => 'Data Fetched Successfully.',
            'data' => [
                $brandKitObj
                ],
        ], 201);
    }

    public function Store(Request $request){

        $rules = [
            'email' => 'required|email',
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'user_id' => 'required|integer|exists:users,id',
            'address' => 'nullable|string|max:500',
            'state' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url',
            'postal_code' => 'nullable|string|max:20',
            'show_email_on_post' => 'nullable|boolean',
            'show_phone_number_on_post' => 'nullable|boolean',
            'show_website_on_post' => 'nullable|boolean',
            'social_media_icon_show' => 'nullable|array',
        ];
        
        $messages = [
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'company_name.required' => 'The company name is required.',
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'User does not exist.',
            'website.url' => 'Please enter a valid URL.',
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);
       
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
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
        // $brandKitObj->social_media_icon_show = json_encode($request->social_media_icon_show);
        $brandKitObj->color = json_encode($request->color);
        $brandKitObj->font = json_encode($request->font);
        $brandKitObj->design_style = $request->design_style;
        $brandKitObj->save();

        $socialMediaIconArr = [];
        if(!empty($request->social_media_icon_show)){
            foreach ($request->social_media_icon_show as $key => $value) {
                $socialMediaIconArr[] = $value;
            }
            $brandKitObj->socialMedia()->updateOrCreate(
                ['brand_kit_id' => $brandKitObj->id],
                ['social_media_icon_show' => json_encode($socialMediaIconArr)]
            );
        }
       

        return response()->json([
            'success' => true,
            'message' => 'BrandKit updated successfully.',
            'data' => [
                $brandKitObj
                ],
        ], 200);


    }
}
