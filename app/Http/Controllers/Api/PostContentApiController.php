<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\PostContent;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostContentApiController extends Controller
{
    use ResponseTrait;
    
    public function index(Request $request)
    {
        // check token
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error([
                'status' => false,
                'message' => 'Invalid token',
            ]);
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

        return $this->success([
            'status' => true,
            'message' => 'Post content fetched successfully',
            'data' => $resposeData,
        ]);
    }

    public function show(Request $request, $id)
    {
        // check token
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error([
                'status' => false,
                'message' => 'Invalid token',
            ]);
        }

        $postContent = PostContent::where('category_id', Helpers::decrypt($id))->get();
        if (!$postContent) {
            return $this->error([
                'status' => false,
                'message' => 'Post content not found',
            ]);
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

        return $this->success([
            'status' => true,
            'message' => 'Post content fetched successfully',
            'data' => $resturnData,
        ]);
    }
}
