<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Categories;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoriesController extends Controller
{
    public function index()
    {
        return view('content.pages.categories.index');
    }

    public function categoriesDataTable()
    {
        $categories = Categories::where('parent_id', null)->latest()->get();

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('name', function ($category) {
                return $category->name;
            })
            ->addColumn('image', function ($category) {
                return '<img src="' . asset($category->image) . '" alt="' . $category->name . '" class="br-1" width="100" height="100">';
            })
            ->addColumn('description', function ($category) {
                return $category->description;
            })
            ->addColumn('status', function ($category) {
                $status = $category->status == true ? 'checked' : '';
                $title = '';
                if ($category->status == true) {
                    $title = 'Click To Disable Category';
                } else {
                    $title = 'Click To Enable Category';
                }

                $categoryId = Helpers::encrypt($category->id);
                return '<label class="switch">
                            <input type="checkbox" class="switch-input" ' . $status . ' data-id="' . $categoryId . '" id="category-status">
                            <span class="switch-toggle-slider" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $title . '">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->addColumn('created_at', function ($category) {
                return '<span data-order="' . $category->created_at . '">' . Helpers::dateFormate($category->created_at) . '</span>';
            })
            ->addColumn('action', function ($category) {
                $categoryId = Helpers::encrypt($category->id);
                return '
                    <a href="javascript:;" title="edit category" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-category-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-category-id="' . $categoryId . '"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="delete category" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-category-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-category-id="' . $categoryId . '"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['image', 'status', 'created_at', 'action'])
            ->make(true);
    }

    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_description' => 'required|string',
            'category_status' => 'required|string|in:active,inactive',
            'category_coming_soon' => 'required|string',
            'custom_label' => 'required_if:category_coming_soon,2',
            'subcategory_name' => 'nullable|array',
            'subcategory_name.*' => 'required|string|max:255',
            'subcategory_month' => 'nullable|array',
            'subcategory_month.*' => 'nullable|integer|min:1|max:12',
        ]);

        $image = $request->file('category_image');
        $image_url = Helpers::uploadImage('cat', $image, 'images/categories');

        $category = new Categories();
        $category->name = $request->category_name;
        $category->image = $image_url;
        $category->description = $request->category_description;
        $category->status = $request->category_status == 'active' ? true : false;
        $category->is_comming_soon = $request->category_coming_soon;
        $category->custom_label = $request->category_coming_soon == 2 ? $request->custom_label : null;
        $category->month_id = null; // Parent category does not have a month
        $category->save();

        $subCategoryName = [];

        if ($request->has('subcategory_name')) {
            foreach ($request->subcategory_name as $index => $subcategory_name) {
                $subcategory = new Categories();
                $subcategory->name = $subcategory_name;
                $subcategory->parent_id = $category->id;
                $subcategory->status = true; // default active
                $subcategory->month_id = null; // default null

                if ($request->has('subcategory_month') && isset($request->subcategory_month[$index]) && !empty($request->subcategory_month[$index])) {
                    $subcategory->month_id = (int)$request->subcategory_month[$index];
                }

                if (
                    $request->has('subcategory_coming_soon') &&
                    isset($request->subcategory_coming_soon[$index]) &&
                    $request->subcategory_coming_soon[$index] == 'on'
                ) {
                    $subcategory->is_comming_soon = true;
                } else {
                    $subcategory->is_comming_soon = false;
                }
                $subcategory->save();
                $subCategoryName[] = $subcategory->name;
            }
        }

        /** Activity Log */
        Helpers::activityLog([
            'title' => "Create Category & Sub-Category",
            'description' => "Admin Panel: Category Name: (" . $request->category_name . "). Sub-Category Name: (" . implode(', ', $subCategoryName) . ")",
            'url' => route('categories.store')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.'
        ]);
    }


    // public function edit($id)
    // {
    //     $categoryId = Helpers::decrypt($id);
    //     $category = Categories::with('children:id,name,parent_id,is_comming_soon,month_id')->find($categoryId);
    //     if ($category) {
    //         return response()->json([
    //             'success' => true,
    //             'data' => $category
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Category not found.'
    //         ]);
    //     }
    // }

    public function edit($id)
    {
        $categoryId = Helpers::decrypt($id);

        // Fetch category with its subcategories
        $category = Categories::with(['children:id,name,parent_id,month_id,is_comming_soon'])
            ->find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ]);
        }

        // Check if the category has month-based subcategories
        $months = Categories::where('parent_id', $categoryId)
            ->whereNotNull('month_id')
            ->orderBy('month_id')
            ->get(['id', 'name', 'month_id']);

        return response()->json([
            'success' => true,
            'data' => $category,
            'months' => $months
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'edit_category_name' => 'required|string|max:255',
            'edit_category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'edit_category_description' => 'required|string',
            'edit_category_status' => 'required|string|in:active,inactive',
            'edit_category_coming_soon' => 'required|string',
            'edit_custom_label' => 'required_if:edit_category_coming_soon,2',
            'edit_subcategory_ids' => 'nullable|json',
        ]);

        $categoryId = Helpers::decrypt($request->edit_category_id);
        $category = Categories::find($categoryId);

        if ($category) {
            $category->name = $request->edit_category_name;
            $category->description = $request->edit_category_description;
            $category->status = $request->edit_category_status == 'active' ? true : false;
            $category->is_comming_soon = $request->edit_category_coming_soon;
            $category->custom_label = isset($request->edit_custom_label) ? $request->edit_custom_label : null;

            if ($request->hasFile('edit_category_image')) {
                // delete old image
                if ($category->image) {
                    Helpers::deleteImage($category->image);
                }
                // upload new image
                $image = $request->file('edit_category_image');
                $image_url = Helpers::uploadImage('cat', $image, 'images/categories');
                $category->image = $image_url;
            }
            $category->save();

            // Update subcategories
            $subCategoryName = [];
            if ($request->has('edit_subcategory_ids') && $request->edit_subcategory_ids != null) {
                $subCategories = json_decode($request->edit_subcategory_ids, true);
                $subCategoryIds = array_column($subCategories, 'id');
                $subCategoryNames = array_column($subCategories, 'name');

                foreach ($subCategories as $subCategory) {
                    if ($subCategory['id'] == 0) {
                        // Create new subcategory
                        $subcategory = new Categories();
                        $subcategory->name = $subCategory['name'];
                        $subcategory->parent_id = $category->id;
                        $subcategory->is_comming_soon = $subCategory['coming_soon'];
                        $subcategory->status = true;

                        // Handle month_id for new subcategory
                        $subcategory->month_id = isset($subCategory['month_id']) && !empty($subCategory['month_id'])
                            ? (int)$subCategory['month_id']
                            : null;

                        $subcategory->save();
                    } else {
                        // Update existing subcategory
                        $subcategory = Categories::where('id', $subCategory['id'])->first();
                        if ($subcategory) {
                            $subcategory->name = $subCategory['name'];
                            $subcategory->is_comming_soon = $subCategory['coming_soon'];

                            // Handle month_id for existing subcategory
                            $subcategory->month_id = isset($subCategory['month_id']) && !empty($subCategory['month_id'])
                                ? (int)$subCategory['month_id']
                                : null;

                            $subcategory->save();
                        }
                    }
                    $subCategoryName[] = $subcategory->name;
                }

                // Delete subcategories that are not in the request
                $deleteSubcategories = Categories::where('parent_id', $category->id)
                    ->whereNotIn('id', $subCategoryIds)
                    ->whereNotIn('name', $subCategoryNames)
                    ->get();

                foreach ($deleteSubcategories as $deleteSubcategory) {
                    $deleteSubcategory->delete();
                }
            }

            /** Activity Log */
            Helpers::activityLog([
                'title' => "Update Category & Sub-Category",
                'description' => "Admin Panel: Category Name: (" . $request->edit_category_name . "). Sub-Category Name: (" . implode(', ', $subCategoryName) . ")",
                'url' => route('categories.update')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ]);
        }
    }

    /**
     * Function to delete category
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $categoryId = Helpers::decrypt($request->category_id);
        $category = Categories::find($categoryId);
        if ($category) {
            // delete old image
            if ($category->image) {
                Helpers::deleteImage($category->image);
            }
            $category->delete();

            /** Activity Log */
            Helpers::activityLog([
                'title' => "Delete Category",
                'description' => "Admin Panel: Deleted Category Name: " . $category->name,
                'url' => route('categories.delete')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ]);
        }
    }

    /**
     * change Category status
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $categoryId = Helpers::decrypt($request->id);
        $category = Categories::find($categoryId);
        if ($category) {
            $category->status = $category->status == true ? false : true;
            $category->save();

            /** Activity Log */
            Helpers::activityLog([
                'title' => "Change Category Status",
                'description' => "Admin Panel: Category Name : " . $category->name . ". Status is " . ($category->status ? "true" : "false"),
                'url' => route('categories.change-status')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category status updated successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ]);
        }
    }
}
