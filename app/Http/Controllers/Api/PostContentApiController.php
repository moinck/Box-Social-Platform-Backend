<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\PostContent;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostContentApiController extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {
        // check token
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error('Invalid token', 401);
        }

        $postContent = PostContent::latest()->get();
        $resposeData = [];
        foreach ($postContent as $post) {
            $resposeData[] = [
                'id' => Helpers::encrypt($post->id),
                'category_id' => Helpers::encrypt($post->category_id),
                'title' => $post->title,
                'description' => $post->description,
            ];
        }

        return $this->success($resposeData, 'Post content fetched successfully');
    }

    public function show(Request $request, $id)
    {
        // check token
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error('Invalid token', 401);
        }

        $postContent = PostContent::where('category_id', Helpers::decrypt($id))->get();
        if (!$postContent) {
            return $this->error('Post content not found', 404);
        }

        $resturnData = [];
        foreach ($postContent as $post) {
            $resturnData[] = [
                'id' => Helpers::encrypt($post->id),
                'category_id' => Helpers::encrypt($post->category_id),
                'title' => $post->title,
                'description' => $post->description,
            ];
        }

        return $this->success($resturnData, 'Post content fetched successfully');
    }

    public function getData(Request $request)
    {
        // Token check
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error('Invalid token', 401);
        }
    
        // Validate input
        $validator = Validator::make($request->all(), [
            'category_ids' => 'nullable|array',
            'sub_category_ids' => 'nullable|array',
        ]);
    
        if ($validator->fails()) {
            return $this->validationError('Validation Error', $validator->errors());
        }
    
        // Decrypt category IDs
        $decryptedCategoryIds = [];
        if ($request->has('category_ids') && !empty($request->category_ids)) {
            $decryptedCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->category_ids);
        }
    
        // Get selected categories
        $categories = Categories::whereIn('id', $decryptedCategoryIds)->get();
    
        // Decrypt subcategory IDs
        $decryptedSubCategoryIds = [];
        if ($request->has('sub_category_ids') && !empty($request->sub_category_ids)) {
            $decryptedSubCategoryIds = array_map(function ($id) {
                return Helpers::decrypt(string: $id);
            }, $request->sub_category_ids);
        }
    
        $returnData = [];
    
        foreach ($categories as $category) {
            $query = PostContent::where('category_id', $category->id);
    
            // If subcategories are provided, filter by them or null
            if (!empty($decryptedSubCategoryIds)) {
                $query->where(function ($q) use ($decryptedSubCategoryIds) {
                    $q->whereIn('sub_category_id', $decryptedSubCategoryIds)
                      ->orWhereNull('sub_category_id');
                });
            }
    
            $postContents = $query->get();
    
            // Build data
            $returnData[$category->name] = $postContents->map(function ($post) {
                return [
                    'id' => Helpers::encrypt($post->id),
                    'category_id' => Helpers::encrypt($post->category_id),
                    'title' => $post->title,
                    'description' => $post->description,
                    'warning_message' => $post->warning_message,
                ];
            })->toArray(); // to ensure empty array instead of Collection
        }
    
        return $this->success($returnData, 'Post content fetched successfully');
    }    

    /**
     * Get post content by category
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCategoryPostContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|string',
            'sub_category_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation Error', $validator->errors());
        }

        $categoryId = $request->category_id;
        $subCategoryId = $request->sub_category_id;
        $postContent = PostContent::where(function ($query) use ($categoryId, $subCategoryId) {
            $query->where('category_id', Helpers::decrypt($categoryId))
                ->where('sub_category_id', Helpers::decrypt($subCategoryId));
        })->get();

        if (!$postContent) {
            return $this->error('Post content not found', 404);
        }

        $resturnData = [];
        foreach ($postContent as $post) {
            $resturnData[] = [
                'id' => Helpers::encrypt($post->id),
                'title' => $post->title,
            ];
        }

        return $this->success($resturnData, 'Post content fetched successfully');
    }
}
