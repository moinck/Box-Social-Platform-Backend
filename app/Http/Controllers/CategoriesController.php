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
        $categories = Categories::latest()->get();

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('name', function ($category) {
                return $category->name;
            })
            ->addColumn('image', function ($category) {
                return '<img src="'.asset($category->image).'" alt="'.$category->name.'" class="img-fluid br-1" width="100" height="100">';
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

                return '<label class="switch" title="'.$title.'">
                            <input type="checkbox" class="switch-input" '.$status.' data-id="'.$category->id.'" id="category-status">
                            <span class="switch-toggle-slider">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->addColumn('created_at', function ($category) {
                return $category->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('action', function ($category) {
                return '
                    <a href="javascript:;" title="edit category" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-category-btn" data-category-id="'.$category->id.'"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="delete category" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-category-btn" data-category-id="'.$category->id.'"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['image', 'status', 'action'])
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
        ]);

        $image = $request->file('category_image');
        $image_url = Helpers::uploadImage('cat', $image, 'images/categories');

        $category = new Categories();
        $category->name = $request->category_name;
        $category->image = $image_url;
        $category->description = $request->category_description;
        $category->status = $request->category_status == 'active' ? true : false;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.'
        ]);
    }

    public function edit($id)
    {
        $category = Categories::find($id);
        if ($category) {
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ]);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'edit_category_name' => 'required|string|max:255',
            'edit_category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'edit_category_description' => 'required|string',
            'edit_category_status' => 'required|string|in:active,inactive',
        ]);

        $category = Categories::find($request->edit_category_id);
        if ($category) {
            $category->name = $request->edit_category_name;
            $category->description = $request->edit_category_description;
            $category->status = $request->edit_category_status == 'active' ? true : false;

            if ($request->hasFile('edit_category_image')) {
                // delete old image
                if ($category->image) {
                    unlink(public_path($category->image));
                }

                // upload new image
                $image = $request->file('edit_category_image');
                $image_url = Helpers::uploadImage('cat', $image, 'images/categories');
                $category->image = $image_url;
            }
            $category->save();

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
        $category = Categories::find($request->category_id);
        if ($category) {
            // delete old image
            if ($category->image) {
                unlink(public_path($category->image));
            }
            $category->delete();
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
        $category = Categories::find($request->id);
        if ($category) {
            $category->status = $category->status == true ? false : true;
            $category->save();

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
