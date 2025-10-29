<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Categories;
use App\Models\PostTemplate;
use App\Models\UserTokens;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class PostTemplateController extends Controller
{
    public function index()
    {
        // Mark old tokens as used and delete
        UserTokens::where('type', 'admin-access-token')
            ->where('is_used', false)
            ->where('created_at', '<', Carbon::now()->subDay())
            ->delete();

        // Get the latest unused token created within last 1 day
        $adminToken = UserTokens::where('type', 'admin-access-token')
            ->where('is_used', false)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->latest()
            ->first();

        // If no valid token, create new one
        if (!$adminToken) {
            $adminToken = UserTokens::create([
                'user_id' => 1,
                'token' => Str::random(60),
                'type' => 'admin-access-token',
                'is_used' => false,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // added delay for tokens
        sleep(1);

        $currentAdminToken = $adminToken->token;
        // set token in session
        Session::put('admin_access_token', $currentAdminToken);
        $categories = Categories::getActiveCategoeyList();
        $subCategories = Categories::whereNotNull(columns: 'parent_id')
            // ->where('is_comming_soon', false)
            ->select('id', 'name')
            ->get();

        return view('content.pages.admin.post-template.index', compact('categories', 'currentAdminToken','subCategories'));
    }

    public function dataTable(Request $request)
    {
        $postTemplates = PostTemplate::whereHas('category')
            ->with('category:id,name', 'postContent:id,title', 'designStyle:id,name', 'subCategory:id,name')
            ->when($request->has('category') && $request->category != '', function ($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->when($request->sub_category_id && $request->sub_category_id != 0, function ($query) use ($request) {
                $query->where('sub_category_id', $request->sub_category_id);
            })
            ->when($request->has('status') && $request->status != '', function ($query) use ($request) {
                if ($request->status == 1) {
                    $query->where('status', true);
                } elseif ($request->status == 2) {
                    $query->where('status', "!=", true);
                }
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
            ->addColumn('checkbox', function ($data) {
                return '<input type="checkbox" class="form-check-input template-checkbox" name="template_id[]" value="' . Helpers::encrypt($data->id) . '">';
            })
            ->addColumn('template_image', function ($data) {
                $categoryName = $data->category->name;
                $templateImage = $data->template_image;
                if (!str_starts_with($templateImage, 'https://')) {
                    $templateImage = asset($templateImage);
                }
                return '<img src="' . $templateImage . '" alt="' . $data->template_name . '" class="br-1 template-image" data-category="' . $categoryName . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Click To View Image" width="80" height="100">';
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
            // ->addColumn('design_style', function ($data) {
            //     return $data->designStyle->name ?? "-";
            // })
            ->addColumn('status', function ($data) {
                $status = $data->status == true ? 'checked' : '';
                $title = '';
                if ($data->status == true) {
                    $title = 'Click To Disable Design Template';
                } else {
                    $title = 'Click To Enable Design Template';
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
                return '<span data-order="' . $data->created_at . '">' . Helpers::dateFormate($data->created_at) . '</span>';
            })
            ->addColumn('action', function ($data) {
                $postTemplateId = Helpers::encrypt($data->id);
                $adminAccessToken = Session::get('admin_access_token') ?? '';
                $editUrl = config('app.frontend_url') . "/admin/edit-templates?id=" . $postTemplateId . '&token=' . $adminAccessToken;
                // $downloadUrl = route('download.document', $postTemplateId);
                // $downloadBtn = '<a href="' . $downloadUrl . '" title="Download Design Template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon download-post-template-btn"
                //         data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-file-download-line"></i></a>';

                return '
                    <a href="javascript::void(0);" title="Duplicate design template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon duplicate-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-file-copy-line"></i></a>
                    <a href="' . $editUrl . '" title="Edit design template" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="Delete design template" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-post-template-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-post-template-id="' . $postTemplateId . '"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['checkbox','action', 'post_content', 'template_image', 'status', 'created_at'])
            ->make(true);
    }

    public function destroy(Request $request)
    {
        $decryptedIds = collect($request->post_template_id)->map(function ($encryptedId) {
            return Helpers::decrypt($encryptedId);
        })->toArray();

        $postTemplates = PostTemplate::select('id','template_image')->whereIn('id',$decryptedIds)->get();
        if ($postTemplates->isNotEmpty()) {
            foreach ($postTemplates as $key => $value) {

                /** Activity Log */
                Helpers::activityLog([
                    'title' => "Delete Design Template",
                    'description' => "Admin Panel: Design Template is ".$value->template_name.". Design Template Content: ".(isset($value->postContent) ? $value->postContent->title : '-').". Design Template Category: ".(isset($value->category) ? $value->category->name : '-').". Design Template Sub-Category: ".(isset($value->subCategory) ? $value->subCategory->name : '-'),
                    'url' => route('post-content.delete')
                ]);

                // delete post-template image
                Helpers::deleteImage($value->template_image);
                $value->delete();
            }
            return response()->json([
                'success' => true,
                'message' => 'Design Template deleted successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Design Template not found.'
            ]);
        }
    }

    public function changeStatus(Request $request)
    {

        $type = $request->type;
        $decryptedIds = collect($request->post_template_id)->map(function ($encryptedId) {
            return Helpers::decrypt($encryptedId);
        })->toArray();

        $postTemplates = PostTemplate::select('id','status')->whereIn('id',$decryptedIds)->get();
        
        if ($postTemplates->isNotEmpty()) {

            foreach ($postTemplates as $key => $value) {
                if ($type == "single") {
                    $value->status = !$value->status;
                } else if ($type == "bulk" && $value->status == 1) {
                    $value->status = !$value->status;
                }

                $value->save();
            }

            $message = "Design Template status has been updated successfully.";
            if ($type == "bulk") {
                $message = "Design Template has been disabled successfully.";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Design Template not found.'
            ]);
        }
    }

    public function duplicate(Request $request)
    {
        $decryptedId = Helpers::decrypt($request->post_template_id);
        $postTemplate = PostTemplate::find($decryptedId);
        
        if (!empty($postTemplate)) {
            $templateImage = $postTemplate->template_image;
            $newUrl = null;
            
            // Check if the image is from Digital Ocean or local storage
            if ($this->isDigitalOceanUrl($templateImage)) {
                // Handle Digital Ocean URL - download and re-upload
                $newUrl = $this->duplicateDigitalOceanImage($templateImage);
            } else {
                // Handle local file - existing logic
                $newUrl = $this->duplicateLocalImage($templateImage);
            }
            
            if ($newUrl) {
                // Create the duplicate record
                $newPostTemplate = $postTemplate->replicate();
                $newPostTemplate->template_image = $newUrl;
                $newPostTemplate->save();

                /** Activity Log */
                Helpers::activityLog([
                    'title' => "Duplicate Design Template",
                    'description' => "Admin Panel: Design Template is ".$postTemplate->template_name.". Design Template Content: ".(isset($postTemplate->postContent) ? $postTemplate->postContent->title : '-').". Design Template Category: ".(isset($postTemplate->category) ? $postTemplate->category->name : '-').". Design Template Sub-Category: ".(isset($postTemplate->subCategory) ? $postTemplate->subCategory->name : '-'),
                    'url' => route('post-template.create-duplicate')
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate Design Template created successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to duplicate image.'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Design Template not found.'
            ]);
        }
    }

    /**
     * Check if the URL is from Digital Ocean Spaces
     */
    private function isDigitalOceanUrl($url)
    {
        // Adjust this condition based on your Digital Ocean URL pattern
        // Common patterns: contains 'digitaloceanspaces.com' or your specific endpoint
        return strpos($url, 'digitaloceanspaces.com') !== false || 
            strpos($url, 'cdn.digitaloceanspaces.com') !== false ||
            // Add your specific Digital Ocean endpoint here
            strpos($url, config('filesystems.disks.spaces.endpoint')) !== false;
    }

    /**
     * Duplicate image from Digital Ocean by downloading and re-uploading
     */
    private function duplicateDigitalOceanImage($imageUrl)
    {
        try {
            // Download the image content
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent === false) {
                return null;
            }
            
            // Get the original filename and extension
            $originalFilename = basename(parse_url($imageUrl, PHP_URL_PATH));
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            
            // Generate new unique filename
            $newFilename = 'admin_template_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            
            // Create a temporary file
            $tempPath = sys_get_temp_dir() . '/' . $newFilename;
            file_put_contents($tempPath, $imageContent);
            
            // Create UploadedFile object from the temporary file
            $uploadedFile = new UploadedFile(
                $tempPath,
                $newFilename,
                mime_content_type($tempPath),
                null,
                true // test parameter - set to true for temporary files
            );
            
            $prefix = 'admin_template_' . rand(1000, 9999);
            $newUrl = Helpers::uploadImage($prefix, $uploadedFile, 'images/admin-post-templates');
            
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            return $newUrl;
            
        } catch (\Exception $e) {
            Log::error('Error duplicating Digital Ocean image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Duplicate local image - existing logic
     */
    private function duplicateLocalImage($templateImage)
    {
        try {
            // Check if the local file exists
            $fullPath = public_path($templateImage);
            if (!file_exists($fullPath)) {
                return null;
            }
            
            // Generate new unique filename
            $extension = pathinfo($templateImage, PATHINFO_EXTENSION);
            $newFilename = 'admin_template_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $prefix = 'admin_template_' . rand(1000, 9999);
            
            $uploadNewFile = new UploadedFile($fullPath, $newFilename, null, null, true);
            return Helpers::uploadImage($prefix, $uploadNewFile, 'images/admin-post-templates');
            
        } catch (\Exception $e) {
            Log::error('Error duplicating local image: ' . $e->getMessage());
            return null;
        }
    }

    /** Get Design Template Sub-Category */
    public function getSubCategory(Request $request)
    {
        $categories = Categories::whereNotNull(columns: 'parent_id')
            ->where(function ($query) use ($request) {
                $query->where('parent_id', $request->category_id);
            })
            ->orderBy('name', 'asc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Subcategory Found',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
