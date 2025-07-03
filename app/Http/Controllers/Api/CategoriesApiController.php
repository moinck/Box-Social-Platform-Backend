<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Categories;
use App\ResponseTrait;
use Illuminate\Http\Request;

class CategoriesApiController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        $categories = Categories::where(function ($query) {
            $query->where('status', true)
                ->where('parent_id', null);
        })->with('children:id,name,parent_id,is_comming_soon')->latest()->get();

        // send data to resource
        // $categoryCollection = CategoryResource::collection($categories);
        $categoryCollection = [];
        $commingSoonCategories = [];
        $notCommingSoonCategories = [];
        foreach ($categories as $category) {

            $isCommingSoon = $category->is_comming_soon;

            if ($isCommingSoon) {
                $commingSoonCategories[] = [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $category->name,
                    'image' => asset($category->image),
                    'is_comming_soon' => $isCommingSoon,
                    'sub_categories' => $category->children->map(function ($child) {
                        return [
                            'id' => Helpers::encrypt($child->id),
                            'name' => $child->name,
                            'is_comming_soon' => $child->is_comming_soon,
                        ];
                    }),
                ];
            } else {
                $notCommingSoonCategories[] = [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $category->name,
                    'image' => asset($category->image),
                    'is_comming_soon' => $isCommingSoon,
                    'sub_categories' => $category->children->map(function ($child) {
                        return [
                            'id' => Helpers::encrypt($child->id),
                            'name' => $child->name,
                            'is_comming_soon' => $child->is_comming_soon,
                        ];
                    }),
                ];
            }
        }

        $returnData = [
            'active' => $notCommingSoonCategories,
            'coming_soon' => $commingSoonCategories,
        ];

        return $this->success($returnData, 'Categories list.');
    }
}
