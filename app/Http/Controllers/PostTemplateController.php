<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Categories;
use App\Models\PostTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Yajra\DataTables\Facades\DataTables;

class PostTemplateController extends Controller
{
    public function index()
    {
        $categories = Categories::getActiveCategoeyList();

        return view('content.pages.admin.post-template.index', compact('categories'));
    }

    public function dataTable(Request $request)
    {
        $postTemplates = PostTemplate::with('category:id,name', 'postContent:id,title', 'designStyle:id,name', 'subCategory:id,name')
            ->when($request->has('category') && $request->category != '', function ($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->when($request->has('status') && $request->status != '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->select([
                'id',
                'template_name',
                'template_image',
                'category_id',
                'sub_category_id',
                'design_style_id',
                'post_content_id',
                'status',
                'created_at',
            ])
            ->latest()->get();

        return DataTables::of($postTemplates)
            ->addIndexColumn()
            ->addColumn('template_image', function ($data) {
                $categoryName = $data->category->name;
                return '<img src="' . asset($data->template_image) . '" alt="' . $data->template_name . '" class="br-1 template-image" data-category="' . $categoryName . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Click To View Image" width="80" height="100">';
            })
            ->addColumn('post_content', function ($data) {
                return $data->postContent->title ?? "-";
            })
            ->addColumn('category', function ($data) {
                return $data->category->name;
            })
            ->addColumn('sub_category', function ($data) {
                return $data->subCategory->name ?? "-";
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
                            <input type="checkbox" class="switch-input" ' . $status . ' data-id="' . $postTemplateId . '" id="post-template-status">
                            <span class="switch-toggle-slider" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $title . '">
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
                $editUrl = "http://178.128.45.173:9163/admin/edit-templates?id=" . $postTemplateId;
                // $downloadUrl = route('download.document', $postTemplateId);
                // $downloadBtn = '<a href="' . $downloadUrl . '" title="Download post template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon download-post-template-btn"
                //         data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-file-download-line"></i></a>';

                return '
                    <a href="javascript::void(0);" title="Duplicate post template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon duplicate-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-file-copy-line"></i></a>
                    <a href="' . $editUrl . '" title="Edit post template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="Delete post template" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['action', 'post_content', 'template_image', 'design_style', 'status', 'created_at'])
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

    public function duplicate(Request $request)
    {
        $decryptedId = Helpers::decrypt($request->post_template_id);
        $postTemplate = PostTemplate::find($decryptedId);
        // dd($postTemplate);
        if ($postTemplate) {
            $templateImage = $postTemplate->template_image;

        // Generate new unique filename
        $extension = pathinfo($templateImage, PATHINFO_EXTENSION);
        $newFilename = 'admin_template_'.time().'.'.$extension;
        
        $uploadNewFile = new UploadedFile($templateImage, $newFilename, $extension, null, true);

        $newUrl = Helpers::uploadImage('admin_template', $uploadNewFile, 'images/admin-post-templates');

        // Create the duplicate record
        $newPostTemplate = $postTemplate->replicate();
        $newPostTemplate->template_image = $newUrl;
        $newPostTemplate->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Duplicate Post Template created successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post Template not found.'
            ]);
        }
    }
}
