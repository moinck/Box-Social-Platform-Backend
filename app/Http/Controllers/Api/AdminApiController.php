<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\DesignStyles;
use App\Models\PostContent;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function index()
    {
        $designStyles = DesignStyles::select('id', 'name')->get()->map(function ($designStyle) {
            return [
                'id' => Helpers::encrypt($designStyle->id),
                'name' => $designStyle->name,
            ];
        });

        $categories = Categories::where('parent_id', null)
            ->select('id', 'name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $category->name,
                ];
            });
        
        $subCategories = Categories::whereNotNull('parent_id')
            ->select('id', 'name','parent_id')
            ->get()
            ->map(function ($subCategory) {
                return [
                    'id' => Helpers::encrypt($subCategory->id),
                    'name' => $subCategory->name,
                    'parent_id' => Helpers::encrypt($subCategory->parent_id),
                ];
            });

        $postContents = PostContent::select('id', 'category_id', 'title')->get()->map(function ($postContent) {
            return [
                'id' => Helpers::encrypt($postContent->id),
                'category_id' => Helpers::encrypt($postContent->category_id),
                'title' => $postContent->title,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Admin API',
            'data' => [
                'designStyles' => $designStyles,
                'categories' => $categories,
                'subCategories' => $subCategories,
                'postContents' => $postContents,
            ],
        ]);
    }
}
