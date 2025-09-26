<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ImageStockManagement;
use App\Models\User;
use App\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StockImageApiController extends Controller
{
    use ResponseTrait;

    public function get(Request $request)
    {
        $adminIds = User::where('role', 'admin')->get()->pluck('id');
        $searchQuery = $request->search ?? '';

        $limit = $request->limit ?? 25; // default to 25 if not provided
        $page = $request->offset ?? 1; // treat 'offset' as page number
        $page = $page == 0 ? 1 : $page;
        $realOffset = ($page - 1) * $limit;

        $totalAdminImageCount = ImageStockManagement::whereIn('user_id', $adminIds)->count();

        $adminImages = ImageStockManagement::select('id','image_url','tag_name')
            ->whereIn('user_id', $adminIds)
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('tag_name', 'like', "%{$searchQuery}%");
            })
            ->latest()
            ->offset($realOffset)
            ->limit($limit)
            ->get();

        if ($request->bearerToken()) {
            $token = $request->bearerToken();
            $tokenId = explode('|', $token)[0];
            $authUser = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->first();
            if (empty($authUser)) {
                $authUserImages = ImageStockManagement::select('id','image_url')
                    ->whereNotIn('user_id',$adminIds)
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        $query->where('tag_name', 'like', "%{$searchQuery}%");
                    })
                    ->latest()
                    ->get();
            } else {
                $authUserImages = ImageStockManagement::select('id','image_url')
                    ->where('user_id', $authUser->tokenable_id)
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        $query->where('tag_name', 'like', "%{$searchQuery}%");
                    })
                    ->latest()
                    ->get();
            }
        } else {
            $authUserImages = ImageStockManagement::select('id','image_url')
                ->whereNotIn('user_id',$adminIds)
                ->when($searchQuery, function ($query) use ($searchQuery) {
                    $query->where('tag_name', 'like', "%{$searchQuery}%");
                })
                ->latest()
                ->get();
        }

        $returnData = [];
        $adminImagesData = [];
        $userImagesData = [];
        $searchTopics = [];

        // admin images
        foreach ($adminImages as $key => $value) {
            $fileExtension = pathinfo($value->image_url, PATHINFO_EXTENSION);
            // "jpeg?auto=compress&cs=tinysrgb&h=650&w=940", only take extension name
            $fileExtension = "image/" . explode('?', $fileExtension)[0];

            $adminImagesData[] = [
                'id' => Helpers::encrypt($value->id),
                'image_url' => $value->image_url ? $value->image_url : '',
                'tag_name' => $value->tag_name,
                'fileType' => $fileExtension,
            ];
        }

        // auth user images
        foreach ($authUserImages as $key => $value) {
            $fileExtension = pathinfo($value->image_url, PATHINFO_EXTENSION);
            $fileExtension = "image/" . explode('?', $fileExtension)[0];
            $userImagesData[] = [
                'id' => Helpers::encrypt($value->id),
                'image_url' => $value->image_url ? asset($value->image_url) : '',
                'fileType' => $fileExtension,
            ];
        }

        // search topics
        $searchTopics = ImageStockManagement::select('tag_name')
            ->whereNotNull('tag_name')
            ->latest()
            ->pluck('tag_name')
            ->unique()
            ->toArray();

        $returnData['limit'] = $limit;
        $returnData['total_images'] = $totalAdminImageCount;
        $returnData['page'] = $page;
        $returnData['admin'] = $adminImagesData;
        $returnData['user'] = $userImagesData;
        $returnData['searchTopics'] = $searchTopics;

        return $this->success($returnData, 'Stock Image Fetch successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors()->first());
        }

        $user = Auth::user();
        if (empty($user)) {
            return $this->error('User not found', 404);
        }
        // upload image
        $imageUrl = Helpers::uploadImage('user_image', $request->image, 'images/user-images');

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

    /** Get Uploaded Stock Image */
    public function getUploadedStockImage(Request $request)
    {
        try {

            $limit = $request->limit ?? 25; // default to 25 if not provided
            $page = $request->offset ?? 1; // treat 'offset' as page number
            $page = $page == 0 ? 1 : $page;
            $realOffset = ($page - 1) * $limit;

            $authUserImages = [];
            $authUserId = Auth::user()->id;
            // if ($request->bearerToken()) {
            //     $token = $request->bearerToken();
            //     $tokenId = explode('|', $token)[0];
            //     $authUser = DB::table('personal_access_tokens')
            //         ->where('id', $tokenId)
            //         ->first();
            // }
            $totalUserImageCount = ImageStockManagement::where('user_id', $authUserId)->count() ?? 0;
            $authUserImages = ImageStockManagement::select('id','image_url')
                    ->where('user_id', $authUserId)
                    ->latest()
                    ->offset($realOffset)
                    ->limit($limit)
                    ->get();

            $returnData = [];
            $userImagesData = [];

            // auth user images
            foreach ($authUserImages as $key => $value) {
                $fileExtension = pathinfo($value->image_url, PATHINFO_EXTENSION);
                $fileExtension = "image/" . explode('?', $fileExtension)[0];
                $userImagesData[] = [
                    'id' => Helpers::encrypt($value->id),
                    'image_url' => $value->image_url ? asset($value->image_url) : '',
                    'fileType' => $fileExtension,
                ];
            }

            $returnData['limit'] = $limit;
            $returnData['total_images'] = $totalUserImageCount;
            $returnData['page'] = $page;
            $returnData['user'] = $userImagesData;

            return $this->success($returnData, 'Stock Image Fetch successfully');

        } catch (Exception $e) {
            Log::error($e);
            return $this->error('Something wentwrong.', 500);
        }
    }

    /** Admin Upload Stock Image */
    public function uploadStockImage(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'image' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->validationError('Validation failed', $validator->errors()->first());
            }

            $user = User::where('id',1)->where('role','admin')->first();
            if(empty($user)) {
                return $this->error('User not found', 404);
            }
            // upload image
            $imageUrl = Helpers::uploadImage('admin_image', $request->image, 'images/admin-stock-images');

            $imageData = new ImageStockManagement();
            $imageData->user_id = $user->id;
            $imageData->image_url = $imageUrl;
            $imageData->is_admin_uploaded = 1;
            $imageData->save();

            $returnData = [];
            $returnData['image'] = [
                'id' => Helpers::encrypt($imageData->id),
                'image_url' => asset($imageData->image_url),
                'fileType' => "image/" . pathinfo($imageData->image_url, PATHINFO_EXTENSION),
                'created_at' => $imageData->created_at,
                'updated_at' => $imageData->updated_at,
            ];

            return $this->success($returnData, 'Stock image uploaded successfully');

        } catch (Exception $e) {
            Log::error($e);
            return $this->error('Something went wrong.',500);
        }
    }

    /** Get Admin Uploaded Stock Image */
    public function getAdminUploadedStockImage(Request $request)
    {
        $user = User::where('role', 'admin')->where('id',1)->first();
        $searchQuery = $request->search ?? '';

        $limit = $request->limit ?? 25; // default to 25 if not provided
        $page = $request->offset ?? 1; // treat 'offset' as page number
        $page = $page == 0 ? 1 : $page;
        $realOffset = ($page - 1) * $limit;

        $totalAdminImageCount = ImageStockManagement::where('user_id', $user->id)->where('is_admin_uploaded',1)->count();

        $adminImages = ImageStockManagement::select('id','image_url','tag_name')
            ->where('user_id', $user->id)
            ->where('is_admin_uploaded',1)
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('tag_name', 'like', "%{$searchQuery}%");
            })
            ->latest()
            ->offset($realOffset)
            ->limit($limit)
            ->get();

        $returnData = [];
        $adminImagesData = [];
        $searchTopics = [];

        // admin images
        foreach ($adminImages as $key => $value) {
            $fileExtension = pathinfo($value->image_url, PATHINFO_EXTENSION);
            // "jpeg?auto=compress&cs=tinysrgb&h=650&w=940", only take extension name
            $fileExtension = "image/" . explode('?', $fileExtension)[0];

            $adminImagesData[] = [
                'id' => Helpers::encrypt($value->id),
                'image_url' => $value->image_url ? $value->image_url : '',
                'tag_name' => $value->tag_name,
                'fileType' => $fileExtension,
            ];
        }

        // search topics
        $searchTopics = ImageStockManagement::select('tag_name')
            ->whereNotNull('tag_name')
            ->latest()
            ->pluck('tag_name')
            ->unique()
            ->toArray();

        $returnData['limit'] = $limit;
        $returnData['total_images'] = $totalAdminImageCount;
        $returnData['page'] = $page;
        $returnData['admin'] = $adminImagesData;
        $returnData['searchTopics'] = $searchTopics;

        return $this->success($returnData, 'Stock Image Fetch successfully');
    }

}
