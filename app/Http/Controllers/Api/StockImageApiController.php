<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ImageStockManagement;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class StockImageApiController extends Controller
{
    use ResponseTrait;

    public function get(Request $request)
    {
        $adminId = User::where('role', 'admin')->first()->id;
        $imageData = ImageStockManagement::all();

        $authUser = Auth::user();

        $returnData = [];
        $admin = [];
        $user = [];
        foreach ($imageData as $key => $value) {
            if ($value->user_id == $adminId) {

                $fileExtension = pathinfo($value->image_url, PATHINFO_EXTENSION);
                // "jpeg?auto=compress&cs=tinysrgb&h=650&w=940", only take extension name
                $fileExtension = "image/" . explode('?', $fileExtension)[0];

                $admin[] = [
                    'id' => Helpers::encrypt($value->id),
                    'image_url' => $value->image_url,
                    'tag_name' => $value->tag_name,
                    'fileType' => $fileExtension,
                    'created_at' => $value->created_at,
                    'updated_at' => $value->updated_at,
                ];
            } else if ($value->user_id == $authUser->id) {
                $user[] = [
                    'id' => Helpers::encrypt($value->id),
                    'image_url' => asset($value->image_url),
                    'fileType' => "image/" . pathinfo($value->image_url, PATHINFO_EXTENSION),
                    'created_at' => $value->created_at,
                    'updated_at' => $value->updated_at,
                ];
            }
        }

        $returnData['admin'] = $admin;
        $returnData['user'] = $user;

        return $this->success($returnData, 'Stock Image Fetch successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->first());
        }

        $user = Auth::user();
        if (empty($user)) {
            return $this->error('User not found',404);
        }
        // upload image
        $imageUrl = Helpers::uploadImage('user_image', $request->image, 'image/user-images');

        $imageData = new ImageStockManagement();
        $imageData->user_id = $user->id;
        $imageData->image_url = $imageUrl;
        $imageData->save();

        $returnData = [];
        $returnData['image'] = [
            'id' => Helpers::encrypt($imageData->id),
            'image_url' => asset($imageData->image_url),
            'fileType' => "image/" . pathinfo($imageData->image_url, PATHINFO_EXTENSION),
            'created_at' => $imageData->created_at,
            'updated_at' => $imageData->updated_at,
        ];

        return $this->success($returnData, 'User Image added successfully');
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors()->first(), 422);
        }

        $decryptedImageId = Helpers::decrypt($request->image_id);
        if ($decryptedImageId == false) {
            return $this->error('Image ID is invalid.', 422);
        }

        $user = Auth::user();
        if (empty($user)) {
            return $this->error('User not found', 404);
        }

        $imageData = ImageStockManagement::where('id', $decryptedImageId)->first();
        if (empty($imageData)) {
            return $this->error('Image not found', 404);
        }

        if ($imageData->user_id != $user->id) {
            return $this->error('You are not authorized to delete this image', 403);
        }
        Helpers::deleteImage($imageData->image_url);
        $imageData->delete();

        return $this->success([], 'Image deleted successfully');
    }
}
