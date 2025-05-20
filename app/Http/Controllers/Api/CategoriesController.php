<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Categories;
use App\ResponseTrait;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        $categories = Categories::where('status', 1)->latest()->get();
        $categoryCollection = CategoryResource::collection($categories);

        return $this->success($categoryCollection, 'Categories list.');
    }
}
