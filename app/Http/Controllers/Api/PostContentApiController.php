<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
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
        // check token
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error('Invalid token', 401);
        }

        $validator = Validator::make($request->all(), [
            'category_ids' => 'nullable|array',
            'sub_category_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation Error', $validator->errors());
        }

        $postContent = PostContent::withCount('category');

        // filter bt categories
        if ($request->has('category_ids') && $request->category_ids != []) {
            $decryptedCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->category_ids);
            $postContent->whereIn('category_id', $decryptedCategoryIds);
        }

        if ($request->has('sub_category_ids') && $request->sub_category_ids != []) {
            $decryptedSubCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->sub_category_ids);
            $postContent->whereIn('sub_category_id', $decryptedSubCategoryIds);
        }
        $postContent = $postContent->get();

        if (!$postContent) {
            return $this->error('Post content not found', 404);
        }

        $resturnData = [];
        foreach ($postContent as $post) {
            $categoryName = $post->category->name;
            // $categoryPostCount = PostContent::where('category_id', $post->category_id)->count();
            // $customName = $categoryName . ' (' . $categoryPostCount . ')';

            $resturnData[$categoryName][] = [
                'id' => Helpers::encrypt($post->id),
                'category_id' => Helpers::encrypt($post->category_id),
                'title' => $post->title,
                'description' => $post->description,
                'warning_message' => $post->warning_message,
            ];
        }

        return $this->success($resturnData, 'Post content fetched successfully');
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
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation Error', $validator->errors());
        }

        $categoryId = $request->category_id;
        $postContent = PostContent::where('category_id', Helpers::decrypt($categoryId))->get();
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
