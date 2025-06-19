<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Categories;
use App\Models\PostTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PostTemplateController extends Controller
{
    public function index()
    {
        $categories = Categories::select(['id','name'])
            ->where(function ($query) {
                $query->where('status', true)
                    ->where('parent_id', null);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('content.pages.admin.post-template.index', compact('categories'));
    }

    public function dataTable(Request $request)
    {
        $postTemplates = PostTemplate::with('category','postContent','designStyle')
            ->where(function ($query) use ($request) {
                if ($request->category) {
                    $query->where('category_id', $request->category);
                }
                if ($request->has('status') && $request->status != '') {
                    $query->where('status', $request->status);
                }
            })
            ->latest()->get();

        return DataTables::of($postTemplates)
            ->addIndexColumn()
            ->addColumn('template_image', function ($data) {
                $categoryName = $data->category->name;
                return '<img src="'.asset($data->template_image).'" alt="'.$data->template_name.'" class="br-1 template-image" data-category="'.$categoryName.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Click To View Image" width="80" height="100">';
            })
            ->addColumn('post_content', function ($data) {
                return $data->postContent->title ?? "-";
            })
            ->addColumn('category', function ($data) {
                return $data->category->name;
            })
            ->addColumn('design_style', function ($data) {
                return $data->designStyle->name ?? "-";
            })
            ->addColumn('status', function ($data) {
                $status = $data->status == true ? 'checked' : '';
                $title = '';
                if ($data->status == true) {
                    $title = 'Click To Disable Post Template';
                } else {
                    $title = 'Click To Enable Post Template';
                }

                $postTemplateId = Helpers::encrypt($data->id);
                return '<label class="switch">
                            <input type="checkbox" class="switch-input" '.$status.' data-id="'.$postTemplateId.'" id="post-template-status">
                            <span class="switch-toggle-slider" data-bs-toggle="tooltip" data-bs-placement="bottom" title="'.$title.'">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->addColumn('raw_status', function ($data) {
                return $data->status;
            })
            ->addColumn('created_at', function ($data) {
                return Helpers::dateFormate($data->created_at);
            })
            ->addColumn('action', function ($data) {
                $postTemplateId = Helpers::encrypt($data->id);
                return '
                    <a href="javascript:;" title="edit post template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="'.$postTemplateId.'"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="delete post template" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="'.$postTemplateId.'"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['action','post_content','template_image','design_style','status','created_at'])
            ->make(true);
    }

    public function destroy(Request $request)
    {
        $decryptedId = Helpers::decrypt($request->post_template_id);
        $postTemplate = PostTemplate::find($decryptedId);
        if ($postTemplate) {
            // delete post-template image
            Helpers::deleteImage($postTemplate->template_image);
            $postTemplate->delete();
            return response()->json([
                'success' => true,
                'message' => 'Post Template deleted successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post Template not found.'
            ]);
        }
    }

    public function changeStatus(Request $request)
    {
        $decryptedId = Helpers::decrypt($request->id);
        $postTemplate = PostTemplate::find($decryptedId);
        if ($postTemplate) {
            $postTemplate->status = !$postTemplate->status;
            $postTemplate->save();
            return response()->json([
                'success' => true,
                'message' => 'Post Template status changed successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post Template not found.'
            ]);
        }
    }
}
