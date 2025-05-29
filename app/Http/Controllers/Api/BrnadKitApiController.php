<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Models\BrandKit;
use App\Models\SocialMedia;
use Faker\Extension\Helper;

class BrnadKitApiController extends Controller
{
    use ResponseTrait;

    public function store(Request $request)
    {

        $request->merge([
            'user_id' => Helpers::decrypt($request->user_id)
        ]);

        $decryptedUserId = $request->user_id;
        if ($decryptedUserId == false) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is invalid.',
                'errors' => 'User ID is invalid.',
            ], 422);
        }

        $rules = [
            'email' => 'required|email',
            'company_name' => 'required|string|max:255',
            'logo' => 'required',
            'user_id' => 'required|string|exists:users,id',
            'address' => 'required|string|max:500',
            'state' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'website' => 'nullable|url',
            'postal_code' => 'required|string|max:20',
            'show_email_on_post' => 'nullable|boolean',
            'show_phone_number_on_post' => 'nullable|boolean',
            'show_website_on_post' => 'nullable|boolean',
            'social_media_icon_show' => 'nullable|array',
            'show_address_on_post' => 'nullable|boolean',
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

        $brandKitObj = BrandKit::where('user_id', $decryptedUserId)->first();

        if (empty($brandKitObj)) {
            $brandKitObj = new BrandKit();
        }

        $uploadLogoUrl = $request->logo;
        $logoUrl = null;
        $oldLogoUrl = $brandKitObj->logo;
        if ($uploadLogoUrl) {
            // $logoUrl = Helpers::uploadImageFromUrl('brand_kit',$uploadLogoUrl, 'images/brand-kit-logos');
            // $logoUrl = Helpers::uploadImage('brand_kit', $uploadLogoUrl, 'images/brand-kit-logos');

            // Check if it's base64 data
            if (strpos($uploadLogoUrl, 'data:image/') === 0) {
                $logoUrl = Helpers::handleBase64Image($uploadLogoUrl,'brand_kit','images/brand-kit-logos');
            } else {
                // If it's not base64, handle as before (URL or regular file)
                $logoUrl = Helpers::uploadImage('brand_kit', $uploadLogoUrl, 'images/brand-kit-logos');
            }
        }

        $brandKitObj->logo = $logoUrl;
        $brandKitObj->user_id = $decryptedUserId;
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
        $brandKitObj->show_address_on_post = $request->show_address_on_post;
        $brandKitObj->color = json_encode($request->color);
        $brandKitObj->font = json_encode($request->font);
        $brandKitObj->design_style = $request->design_style;
        $brandKitObj->save();

        if ($oldLogoUrl) {
            Helpers::deleteImage($oldLogoUrl);
        }

        $socialMediaIconArr = [];
        if (!empty($request->social_media_icon_show)) {
            foreach ($request->social_media_icon_show as $key => $value) {
                $socialMediaIconArr[] = $value;
            }

            $SocialMediaObj = SocialMedia::where('brand_kits_id', $brandKitObj->id)->first();

            if (empty($SocialMediaObj)) {
                $SocialMediaObj = new SocialMedia();
            }

            $SocialMediaObj->brand_kits_id = $brandKitObj->id;
            $SocialMediaObj->social_media_icon = json_encode($socialMediaIconArr, 1);
            $SocialMediaObj->save();
        }

        $brandKitObj = BrandKit::where('user_id', $decryptedUserId)->first();

        $SocialMediaObj = SocialMedia::where('brand_kits_id', $brandKitObj->id)->first();
        $SocialMediaIcon = [];
        if (!empty($SocialMediaObj)) {
            $SocialMediaIcon = json_decode($SocialMediaObj->social_media_icon_show);
        }

        return response()->json([
            'success' => true,
            'message' => 'BrandKit updated successfully',
            'data' => [
                "id" => Helpers::encrypt($brandKitObj->id),
                "user_id" => Helpers::encrypt($brandKitObj->user_id),
                "logo" => asset($brandKitObj->logo),
                "color" => (!empty($brandKitObj->color)) ? json_decode($brandKitObj->color, true) : null,
                "company_name" => $brandKitObj->company_name,
                "font" => (!empty($brandKitObj->font)) ? json_decode($brandKitObj->font, 1) : null,
                "email" => $brandKitObj->email,
                "address" => $brandKitObj->address,
                "state" => $brandKitObj->state,
                "phone" => $brandKitObj->phone,
                "country" => $brandKitObj->country,
                "website" => $brandKitObj->website,
                "postal_code" => $brandKitObj->postal_code,
                "show_email_on_post" => $brandKitObj->show_email_on_post,
                "show_phone_number_on_post" => $brandKitObj->show_phone_number_on_post,
                "show_website_on_post" => $brandKitObj->show_website_on_post,
                "show_address_on_post" => $brandKitObj->show_address_on_post,
                "social_media_icon_show" => $SocialMediaIcon,
                "design_style" => $brandKitObj->design_style
            ],
        ], 200);
    }

    public function get(Request $request)
    {
        // check barer token
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $brandKitObj = BrandKit::where('user_id', $user->id)->first();

        $SocialMediaObj = SocialMedia::where('brand_kits_id', $brandKitObj->id)->first();
        $SocialMediaIcon = [];
        if (!empty($SocialMediaObj)) {
            $SocialMediaIcon = json_decode($SocialMediaObj->social_media_icon_show);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Fetched Successfully.',
            'data' => [
                "id" => Helpers::encrypt($brandKitObj->id),
                "user_id" => Helpers::encrypt($brandKitObj->user_id),
                "logo" => asset($brandKitObj->logo),
                "color" => (!empty($brandKitObj->color)) ? json_decode($brandKitObj->color, true) : null,
                "company_name" => $brandKitObj->company_name,
                "font" => (!empty($brandKitObj->font)) ? json_decode($brandKitObj->font, 1) : null,
                "email" => $brandKitObj->email,
                "address" => $brandKitObj->address,
                "state" => $brandKitObj->state,
                "phone" => $brandKitObj->phone,
                "country" => $brandKitObj->country,
                "website" => $brandKitObj->website,
                "postal_code" => $brandKitObj->postal_code,
                "show_email_on_post" => $brandKitObj->show_email_on_post,
                "show_phone_number_on_post" => $brandKitObj->show_phone_number_on_post,
                "show_website_on_post" => $brandKitObj->show_website_on_post,
                "show_address_on_post" => $brandKitObj->show_address_on_post,
                "social_media_icon_show" => $SocialMediaIcon,
                "design_style" => $brandKitObj->design_style
            ],
        ], 200);
    }
}
