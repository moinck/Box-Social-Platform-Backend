<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Categories;
use App\Models\PostContent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PostContentController extends Controller
{
    public function index()
    {
        return view('content.pages.admin.post-content.index');
    }

    public function create()
    {
        $categories = Categories::where('parent_id', null)
            ->orderBy('name', 'asc')
            ->get();

        return view('content.pages.admin.post-content.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_title' => 'required',
            'post_category' => 'required|exists:categories,id',
            'post_description' => 'required',
        ]);

        PostContent::create([
            'title' => $request->post_title,
            'category_id' => $request->post_category,
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

        return view('content.pages.admin.post-content.edit', compact('postContent', 'categories'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'post_title' => 'required',
            'post_category' => 'required|exists:categories,id',
            'post_description' => 'required',
        ]);

        PostContent::find($request->post_id)->update([
            'title' => $request->post_title,
            'category_id' => $request->post_category,
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
                $description = str($postContent->description)->limit(60);
                return $description;
            })
            ->addColumn('created_date', function ($postContent) {
                return Helpers::dateFormate($postContent->created_at);
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
            ->rawColumns(['action', 'post_description'])
            ->make(true);
    }
}
