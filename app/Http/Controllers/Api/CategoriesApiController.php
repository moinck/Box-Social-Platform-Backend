<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Categories;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoriesApiController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {

        $cacheKey = 'categories_list';

        $returnData = Cache::remember($cacheKey, env('CACHE_TIME'), function () {

            $categories = Categories::where(function ($query) {
                $query->where('status', true)
                    ->where('parent_id', null);
            })->with('children:id,name,parent_id,is_comming_soon')->latest()->get();

            $commingSoonCategories = [];
            $notCommingSoonCategories = [];
            $customCategories = [];

            $formatCategory = function ($category) {
                return [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $category->name,
                    'image' => asset($category->image),
                    'is_comming_soon' => $category->is_comming_soon,
                    'custom_label' => $category->is_comming_soon == 1 ? "Coming Soon!" : $category->custom_label,
                    'sub_categories' => $category->children->map(function ($child) {
                        return [
                            'id' => Helpers::encrypt($child->id),
                            'name' => $child->name,
                            'is_comming_soon' => $child->is_comming_soon,
                        ];
                    }),
                ];
            };

            foreach ($categories as $category) {
                $formatted = $formatCategory($category);

                switch ($category->is_comming_soon) {
                    case 1:
                        $commingSoonCategories[] = $formatted;
                        break;
                    case 2:
                        $customCategories[] = $formatted;
                        break;
                    default:
                        $notCommingSoonCategories[] = $formatted;
                }
            }

            return [
                'active' => $notCommingSoonCategories,
                'coming_soon' => $commingSoonCategories,
                'custom' => $customCategories
            ];
        });

        return $this->success($returnData, 'Categories list.');
    }

    /**
     * Get list of all subcategories
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function subList(Request $request)
    {
        $subCategories = Categories::whereNotNull('parent_id')
            ->select('id', 'name')
            ->get()
            ->map(function ($subCategory) {
                return [
                    'id' => Helpers::encrypt($subCategory->id),
                    'name' => $subCategory->name,
                ];
            });

        return $this->success($subCategories, 'Sub categories list.');
    }

    /**
     * Get sub-categories of 1 main category
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getSubCategory($id)
    {
        $decryptId = Helpers::decrypt($id);

        $subCategory = Categories::where('parent_id', $decryptId)
            ->select('id', 'name')
            ->get()
            ->map(function ($subCategory) {
                return [
                    'id' => Helpers::encrypt($subCategory->id),
                    'name' => $subCategory->name,
                ];
            });

        return $this->success($subCategory, 'Sub category fetch successfully!.');
    }
}
