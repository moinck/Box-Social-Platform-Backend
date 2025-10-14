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

    public function getDataOld(Request $request)
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
                    'sub_category_id' => $post->sub_category_id ? Helpers::encrypt($post->sub_category_id) : null,
                    'title' => $post->title,
                    'description' => $post->description,
                    'warning_message' => $post->warning_message,
                ];
            })->toArray(); // to ensure empty array instead of Collection
        }
    
        return $this->success($returnData, 'Post content fetched successfully');
    }    

    /** Get Post Content New API */
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

        // Decrypt category IDs if provided
        $decryptedCategoryIds = $request->has('category_ids') && !empty($request->category_ids)
            ? array_map(fn($id) => Helpers::decrypt($id), $request->category_ids)
            : [];

        // Decrypt subcategory IDs if provided
        $decryptedSubCategoryIds = !empty($request->sub_category_ids)
            ? array_map(fn($id) => Helpers::decrypt($id), $request->sub_category_ids)
            : [];

        // Get categories
        $categories = Categories::whereIn('id', $decryptedCategoryIds)->get();

        // Fetch all PostContents in a single query, filtering by category and subcategory
        $postContents = PostContent::whereIn('category_id', $categories->pluck('id'))
            ->when(!empty($decryptedSubCategoryIds), function ($query) use ($decryptedSubCategoryIds) {
                $query->where(function ($q) use ($decryptedSubCategoryIds) {
                    $q->whereIn('sub_category_id', $decryptedSubCategoryIds)
                    ->orWhereNull('sub_category_id');
                });
            })
            ->get();

        // Group post contents by category_id
        $grouped = $postContents->groupBy('category_id');

        $returnData = $categories->mapWithKeys(function ($category) use ($grouped) {
            $posts = $grouped->get($category->id, collect())->map(function ($post) {
                return [
                    'id' => Helpers::encrypt($post->id),
                    'category_id' => Helpers::encrypt($post->category_id),
                    'sub_category_id' => $post->sub_category_id ? Helpers::encrypt($post->sub_category_id) : null,
                    'title' => $post->title,
                    'description' => $post->description,
                    'warning_message' => $post->warning_message,
                ];
            })->toArray();

            return [$category->name => $posts];
        })->toArray();

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
            'category_id' => 'nullable|array',
            'category_id.*' => 'string',
            'sub_category_id' => 'nullable|array',
            'sub_category_id.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation Error', $validator->errors());
        }
        
        
        
        // Get category and subcategory arrays
        $categoryIds = $request->category_id ?? [];
        $subCategoryIds = $request->sub_category_id ?? [];
        
        // Decrypt all IDs if needed
        $categoryIds = array_map(fn($id) => Helpers::decrypt($id), $categoryIds);
        $subCategoryIds = array_map(fn($id) => Helpers::decrypt($id), $subCategoryIds);
        
        // Query posts by multiple categories or subcategories
        $postContent = PostContent::when(!empty($categoryIds), function ($query) use ($categoryIds) {
            return $query->whereIn('category_id', $categoryIds);
        })->when(!empty($subCategoryIds), function ($query) use ($subCategoryIds) {
            return $query->whereIn('sub_category_id', $subCategoryIds);
        })->get();
    
    
        // $categoryId = $request->category_id;
        // $subCategoryId = $request->sub_category_id;
        
        // $postContent = PostContent::when($categoryId, function ($query) use ($categoryId) {
        //     return $query->where('category_id', Helpers::decrypt($categoryId));
        // })->when($subCategoryId, function ($query) use ($subCategoryId) {
        //     return $query->where('sub_category_id', Helpers::decrypt($subCategoryId));
        // })->get();

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
