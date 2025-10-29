<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Imports\PostContentImport;
use App\Models\Categories;
use App\Models\Month as ModelsMonth;
use App\Models\PostContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Month;
use Yajra\DataTables\Facades\DataTables;

class PostContentController extends Controller
{
    public function index()
    {
        $categories = Categories::getActiveCategoeyList();
        $subCategories = Categories::whereNotNull(columns: 'parent_id')
            // ->where('is_comming_soon', false)
            ->select('id', 'name')
            ->get();
        
        return view('content.pages.admin.post-content.index', compact('categories', 'subCategories'));
    }

    public function create()
    {
        $categories = Categories::with('children:id,name,parent_id')
            ->where(function ($query) {
                $query->where('status', true)
                    ->where('parent_id', null);
                    // ->where('is_comming_soon', false);
            })
            ->orderBy('name', 'asc')
            ->get();
            
            $months = ModelsMonth::get();
          

        return view('content.pages.admin.post-content.create', compact('categories','months'));
    }

    public function subCategoryData(Request $request)
    {
        $categoryIds = (array) $request->input('category_ids', []);
        $monthIds = (array) $request->input('month_ids', []);

        if (empty($categoryIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid category IDs provided',
                'data' => [],
            ]);
        }

        // Step 1: Detect which categories have subcategories with month_id not null
        $categoriesWithMonth = Categories::whereIn('parent_id', $categoryIds)
            ->whereNotNull('month_id')
            ->distinct()
            ->pluck('parent_id')
            ->toArray();

        // Step 2: Categories that do not depend on month
        $categoriesWithoutMonth = array_diff($categoryIds, $categoriesWithMonth);

        // Step 3: Build the final query
        $query = Categories::query();

        // 3a. Add subcategories for non-month categories
        if (!empty($categoriesWithoutMonth)) {
            $query->whereIn('parent_id', $categoriesWithoutMonth)
                ->whereNull('month_id');
        }

        // 3b. Add subcategories for month-based categories only if month(s) are selected
        if (!empty($monthIds) && !empty($categoriesWithMonth)) {
            $query->orWhere(function ($q) use ($categoriesWithMonth, $monthIds) {
                $q->whereIn('parent_id', $categoriesWithMonth)
                ->whereIn('month_id', $monthIds);
            });
        }

        // Step 4: Fetch all matching subcategories
        $categories = $query->orderBy('name', 'asc')->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Subcategory Found',
                'data' => [],
                'debug' => [
                    'requested_category_ids' => $categoryIds,
                    'requested_month_ids' => $monthIds,
                    'with_month' => $categoriesWithMonth,
                    'without_month' => $categoriesWithoutMonth,
                ]
            ]);
        }

        // Step 5: Return
        return response()->json([
            'success' => true,
            'data' => $categories,
            'debug' => [
                'requested_category_ids' => $categoryIds,
                'requested_month_ids' => $monthIds,
                'with_month' => $categoriesWithMonth,
                'without_month' => $categoriesWithoutMonth,
            ]
        ]);
    }






    public function store(Request $request)
    {
        $request->validate([
            'post_title' => 'required|string|max:255',
            'post_category' => 'required|array',
            'post_category.*' => 'string', // We'll convert to int later
            'post_sub_category' => 'nullable|array',
            'post_sub_category.*' => 'string',
            'post_description' => 'required|string',
            'warning_message' => 'nullable|string',
            'months' => 'nullable|string',
            'months.*' => 'integer',
        ]);

        $categoryIds = [];
        foreach ($request->post_category as $cat) {
            if (is_string($cat)) {
                $categoryIds = array_merge($categoryIds, explode(',', $cat));
            } elseif (is_array($cat)) {
                $categoryIds = array_merge($categoryIds, $cat);
            }
        }
        $categoryIds = array_map('intval', $categoryIds);

        $subCategoryIds = [];
        if ($request->has('post_sub_category')) {
            foreach ($request->post_sub_category as $sub) {
                if (is_string($sub)) {
                    $subCategoryIds = array_merge($subCategoryIds, explode(',', $sub));
                } elseif (is_array($sub)) {
                    $subCategoryIds = array_merge($subCategoryIds, $sub);
                }
            }
            $subCategoryIds = array_map('intval', $subCategoryIds);
        }

        $subCategories = Categories::whereIn('id', $subCategoryIds)->get()->keyBy('id');

        $monthIds = [];
        if (!empty($request->months)) {
            $monthIds = array_map('intval', explode(',', $request->months));
        }

        foreach ($categoryIds as $catId) {
            if (!empty($subCategoryIds)) {
                // Fetch subcategories that belong to this category
                $validSubCategories = Categories::whereIn('id', $subCategoryIds)
                    ->where('parent_id', $catId)
                    ->get();

                if ($validSubCategories->isEmpty()) {
                    
                    $post = PostContent::create([
                        'title' => $request->post_title,
                        'category_id' => $catId,
                        'sub_category_id' => null,
                        'description' => $request->post_description,
                        'warning_message' => $request->warning_message,
                    ]);

                    if (!empty($monthIds)) {
                        foreach ($monthIds as $mId) {
                            DB::table('post_content_months')->insert([
                                'post_content_id' => $post->id,
                                'month_id' => $mId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                } else {
                    foreach ($validSubCategories as $sub) {
                        $post = PostContent::create([
                            'title' => $request->post_title,
                            'category_id' => $catId,
                            'sub_category_id' => $sub->id,
                            'description' => $request->post_description,
                            'warning_message' => $request->warning_message,
                        ]);

                        if (!empty($monthIds)) {
                            foreach ($monthIds as $mId) {
                                DB::table('post_content_months')->insert([
                                    'post_content_id' => $post->id,
                                    'month_id' => $mId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            } else {
                $post = PostContent::create([
                    'title' => $request->post_title,
                    'category_id' => $catId,
                    'sub_category_id' => null,
                    'description' => $request->post_description,
                    'warning_message' => $request->warning_message,
                ]);

                if (!empty($monthIds)) {
                    foreach ($monthIds as $mId) {
                        DB::table('post_content_months')->insert([
                            'post_content_id' => $post->id,
                            'month_id' => $mId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        Helpers::activityLog([
            'title' => "Create Post Content",
            'description' => "Admin Panel: Post Content is ".$request->post_title,
            'url' => route('post-content.store')
        ]);

        return redirect()->route('post-content')->with('success', 'Post Content Created Successfully');
    }


    public function edit($id)
    {
        $postContent = PostContent::find(Helpers::decrypt($id));
        $categories = Categories::where('parent_id', null)
            ->orderBy('name', 'asc')
            ->get();
        $subCategories = Categories::where('id', $postContent->category_id)
            ->orderBy('name', 'asc')
            ->get();

        return view('content.pages.admin.post-content.edit', compact('postContent', 'categories', 'subCategories'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'post_title' => 'required',
            'post_category' => 'required|exists:categories,id',
            'post_content_edit_sub_category' => 'nullable|exists:categories,id',
            'post_description' => 'required',
            'warning_message' => 'nullable',
        ]);

        PostContent::find($request->post_id)->update([
            'title' => $request->post_title,
            'category_id' => $request->post_category,
            'sub_category_id' => $request->post_content_edit_sub_category ?? null,
            'description' => $request->post_description,
            'warning_message' => $request->warning_message,
        ]);

        $postContent = PostContent::find($request->post_id);

        /** Activity Log */
        Helpers::activityLog([
            'title' => "Update Post Content",
            'description' => "Admin Panel: Post Content is ".$request->post_title.". Post Content Category: ".(isset($postContent->category) ? $postContent->category->name : '-').". Post Content Sub-Category: ".(isset($postContent->subCategory) ? $postContent->subCategory->name : '-'),
            'url' => route('post-content.update')
        ]);

        return redirect()->route('post-content')->with('success', 'Post Content Updated Successfully');
    }

    public function dataTable(Request $request)
    {
        $postContents = PostContent::with('category:id,name','subCategory:id,name')
            ->when($request->category_id && $request->category_id != 0, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->sub_category_id && $request->sub_category_id != 0, function ($query) use ($request) {
                $query->where('sub_category_id', $request->sub_category_id);
            })
            ->latest()->get();

        return DataTables::of($postContents)
            ->addIndexColumn()
            ->addColumn('post_title', function ($postContent) {
                return $postContent->title;
            })
            ->addColumn('post_category', function ($postContent) {
                return $postContent->category->name ?? "Uncategorized";
            })
            ->addColumn('post_sub_category', function ($postContent) {
                return $postContent->subCategory->name ?? "-";
            })
            ->addColumn('post_description', function ($postContent) {
                // just show some lines of description
                $description = str($postContent->description)->limit(40);
                return $description;
            })
            ->addColumn('created_date', function ($postContent) {
                return '<span data-order="' . $postContent->created_at . '">' . Helpers::dateFormate($postContent->created_at) . '</span>';
            })
            ->addColumn('updated_date', function ($postContent) {
                return '<span data-order="' . $postContent->updated_at . '">' . Helpers::dateFormate($postContent->updated_at) . '</span>';
            })
            ->addColumn('action', function ($postContent) {
                $postId = Helpers::encrypt($postContent->id);
                $editUrl = route('post-content.edit', $postId);

                return '
                    <a href="' . $editUrl . '" title="edit post content" class="btn btn-sm btn-text-secondary rounded-pill btn-icon"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="delete post content" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-post-content-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-id="' . $postId . '"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['action', 'post_description', 'created_date', 'updated_date'])
            ->make(true);
    }

    /**
     * function to destroy post content
     */
    public function destroy(Request $request)
    {
        $postId = Helpers::decrypt($request->post_id);
        $postContent = PostContent::find($postId);

        /** Activity Log */
        Helpers::activityLog([
            'title' => "Delete Post Content",
            'description' => "Admin Panel: Post Content is ".$request->post_title.". Post Content Category: ".(isset($postContent->category) ? $postContent->category->name : '-').". Post Content Sub-Category: ".(isset($postContent->subCategory) ? $postContent->subCategory->name : '-'),
            'url' => route('post-content.delete')
        ]);

        $postContent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post Content Deleted Successfully',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'post_content_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('post_content_file');
        Excel::import(new PostContentImport(), $file);

        return redirect()->back()->with('success', 'Post Content Imported Successfully');
    }
}
