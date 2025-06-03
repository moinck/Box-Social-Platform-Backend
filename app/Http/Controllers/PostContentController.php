<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Imports\PostContentImport;
use App\Models\Categories;
use App\Models\PostContent;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PostContentController extends Controller
{
    public function index()
    {
        return view('content.pages.admin.post-content.index');
    }

    public function create()
    {
        $categories = Categories::with('children:id,name,parent_id')->where('parent_id', null)
            ->orderBy('name', 'asc')
            ->get();

        return view('content.pages.admin.post-content.create', compact('categories'));
    }

    public function subCategoryData(Request $request)
    {
        $categories = Categories::with('children:id,name,parent_id')->where('parent_id', $request->category_id)
            ->orderBy('name', 'asc')
            ->get();
        
        if($categories->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No Subcategory Found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_title' => 'required',
            'post_category' => 'required|exists:categories,id',
            'post_sub_category' => 'nullable|exists:categories,id',
            'post_description' => 'required',
        ]);

        PostContent::create([
            'title' => $request->post_title,
            'category_id' => $request->post_category,
            'sub_category_id' => $request->post_sub_category,
            'description' => $request->post_description,
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
        ]);

        PostContent::find($request->post_id)->update([
            'title' => $request->post_title,
            'category_id' => $request->post_category,
            'sub_category_id' => $request->post_content_edit_sub_category ?? null,
            'description' => $request->post_description,
        ]);

        return redirect()->route('post-content')->with('success', 'Post Content Updated Successfully');
    }

    public function dataTable(Request $request)
    {
        $postContents = PostContent::with('category')->latest()->get();

        return DataTables::of($postContents)
            ->addIndexColumn()
            ->addColumn('post_title', function ($postContent) {
                return $postContent->title;
            })
            ->addColumn('post_category', function ($postContent) {
                return $postContent->category->name;
            })
            ->addColumn('post_description', function ($postContent) {
                // just show some lines of description
                $description = str($postContent->description)->limit(40);
                return $description;
            })
            ->addColumn('created_date', function ($postContent) {
                return Helpers::dateFormate($postContent->created_at);
            })
            ->addColumn('updated_date', function ($postContent) {
                return Helpers::dateFormate($postContent->updated_at);
            })
            ->addColumn('action', function ($postContent) {
                $postId = Helpers::encrypt($postContent->id);
                $editUrl = route('post-content.edit', $postId);

                return '
                    <a href="'.$editUrl.'" title="edit post content" class="btn btn-sm btn-text-secondary rounded-pill btn-icon"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="delete post content" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-post-content-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-id="'.$postId.'"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['action', 'post_description','created_date', 'updated_date'])
            ->make(true);
    }

    /**
     * function to destroy post content
     */
    public function destroy(Request $request)
    {
        $postId = Helpers::decrypt($request->post_id);
        $postContent = PostContent::find($postId);
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
