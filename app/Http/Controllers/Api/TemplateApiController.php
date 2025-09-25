<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\Categories;
use App\Models\PostContent;
use App\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PostTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TemplateApiController extends Controller
{
    use ResponseTrait;

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'sub_category_id' => 'nullable|string',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required', // or 'array' if JSON
            'design_style_id' => 'nullable|string',
            'post_content_id' => 'nullable|string',
        ],[
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $postContentId = $request->post_content_id ? Helpers::decrypt($request->post_content_id) : null;
        $categoryId = $request->category_id ? Helpers::decrypt($request->category_id) : null;
        $subCategoryId = $request->sub_category_id ? Helpers::decrypt($request->sub_category_id) : null;
        $designStyleId = $request->design_style_id ? Helpers::decrypt($request->design_style_id) : null;

        try {
            DB::beginTransaction();
            $imagePath = null;
            if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
                $prefix = 'admin_template_'. rand(1000, 9999);
                $imagePath = Helpers::handleBase64Image($request->template_image, $prefix, 'images/admin-post-templates');
            }
            $tempObj = new PostTemplate();
            $tempObj->category_id = $categoryId;
            $tempObj->template_image = $imagePath;
            $tempObj->template_data = $request->template_data;
    
            if ($request->has('sub_category_id') && $subCategoryId !== null) {
                $tempObj->sub_category_id = $subCategoryId;
            }
    
            if ($request->has('design_style_id') && $designStyleId) {
                $tempObj->design_style_id = $designStyleId;
            }
    
            if ($request->has('post_content_id') && $postContentId) {
                $tempObj->post_content_id = $postContentId;
            }
            $tempObj->save();
    
            $data = [
                "id" => Helpers::encrypt($tempObj->id),
            ];
            DB::commit();
            return $this->success($data, 'Template create successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Helpers::sendErrorMailToDeveloper($e);
            return $this->error('Something went wrong', 500);
        }
    }

    /** Store Template New API */
    public function newStoreTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'sub_category_id' => 'nullable|string',
            'template_image' => 'required',
            'template_data' => 'required', // or 'array' if JSON
            'design_style_id' => 'nullable|string',
            'post_content_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $postContentId = $request->post_content_id ? Helpers::decrypt($request->post_content_id) : null;
        $categoryId = $request->category_id ? Helpers::decrypt($request->category_id) : null;
        $subCategoryId = $request->sub_category_id ? Helpers::decrypt($request->sub_category_id) : null;
        $designStyleId = $request->design_style_id ? Helpers::decrypt($request->design_style_id) : null;

        try {
            DB::beginTransaction();
            // $imagePath = null;
            // if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
            //     $prefix = 'admin_template_'. rand(1000, 9999);
            //     $imagePath = Helpers::handleBase64Image($request->template_image, $prefix, 'images/admin-post-templates');
            // }

            $imagePath = null;
            if ($request->has('template_image')) {
                $prefix = 'admin_template_'. rand(1000, 9999);
                $imagePath = Helpers::uploadImage($prefix, $request->template_image, 'images/admin-post-templates');
            }

            $tempObj = new PostTemplate();
            $tempObj->category_id = $categoryId;
            $tempObj->template_image = $imagePath;
            // $tempObj->template_data = $request->template_data;
            
            if ($request->has('sub_category_id') && $subCategoryId !== null) {
                $tempObj->sub_category_id = $subCategoryId;
            }
            
            if ($request->has('design_style_id') && $designStyleId) {
                $tempObj->design_style_id = $designStyleId;
            }
            
            if ($request->has('post_content_id') && $postContentId) {
                $tempObj->post_content_id = $postContentId;
            }
            $tempObj->save();

            $templateJsonUrl = null;
            if ($request->has('template_data')) {
                $prefix = 'admin_template_json_' . $tempObj->id;
                $templateJsonUrl = Helpers::uploadImage($prefix, $request->template_data, 'json/admin-template-data');   
            }

            $tempObj->template_url = $templateJsonUrl;
            $tempObj->save();
    
            $data = [
                "id" => Helpers::encrypt($tempObj->id),
            ];
            DB::commit();
            return $this->success($data, 'Template create successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Helpers::sendErrorMailToDeveloper($e);
            return $this->error('Something went wrong', 500);
        }
    }

    public function getTemplate(Request $request, $id)
    {

        $tempObj = PostTemplate::with('postContent')
            ->where('id', Helpers::decrypt($id))->first();

        if (!$tempObj) {
            return $this->error('Template not found', 404);
        }

        // post content data
        $postTemplateData = $tempObj->postContent ?? null;
        if (!empty($postTemplateData)) {
            $postContentData = [
                "id" => Helpers::encrypt($postTemplateData->id),
                "title" => $postTemplateData->title,
                "warning_message" => $postTemplateData->warning_message ? $postTemplateData->warning_message : '',
            ];
        }
        // $brandkitData = BrandKit::where('user_id', Auth::user()->id)->first();

        // $brandkitData = [
        //     'name' => $brandkitData->user->first_name . ' ' . $brandkitData->user->last_name,
        //     'email' => $brandkitData->user->email,
        //     'phone' => $brandkitData->phone ?? '',
        //     'company' => $brandkitData->company_name ?? '',
        //     'address' => $brandkitData->address ?? '',
        //     'website' => $brandkitData->website ?? '',
        //     'brandkit_logo' => $brandkitData->logo ?? '',
        // ];

        // $processedTemplateData = Helpers::replaceFabricTemplateData($tempObj->template_data, $brandkitData);

        $adminTemplateData = [
            'category_id' => Helpers::encrypt($tempObj->category_id),
            'sub_category_id' => $tempObj->sub_category_id ? Helpers::encrypt($tempObj->sub_category_id) : null,
            'post_content_id' => $tempObj->post_content_id ? Helpers::encrypt($tempObj->post_content_id) : null,
            'design_style_id' => $tempObj->design_style_id ? Helpers::encrypt($tempObj->design_style_id) : null,
        ];

        $data = [
            'id' => Helpers::encrypt($tempObj->id),
            'category_id' => Helpers::encrypt($tempObj->category_id),
            'template_image' => isset($tempObj->template_image) ? asset($tempObj->template_image) : '',
            'post_content_data' => isset($postContentData) ? $postContentData : null,
            'admin_template_data' => isset($adminTemplateData) ? $adminTemplateData : [],
            'template_data' => isset($tempObj->template_data) ? $tempObj->template_data : [],
            'template_json_url' => $tempObj->template_url ?? null,
        ];

        if (!empty($tempObj)) {
            return $this->success($data, 'Template Fetch successfully');
        }
    }

    public function getTemplateNew(Request $request, $id)
    {

        $tempObj = PostTemplate::with('postContent')
            ->where('id', Helpers::decrypt($id))->first();

        if (!$tempObj) {
            return $this->error('Template not found', 404);
        }

        // post content data
        $postTemplateData = $tempObj->postContent ?? null;
        if (!empty($postTemplateData)) {
            $postContentData = [
                "id" => Helpers::encrypt($postTemplateData->id),
                "title" => $postTemplateData->title,
                "warning_message" => $postTemplateData->warning_message ? $postTemplateData->warning_message : '',
            ];
        }

        if(!empty($tempObj->template_url)){
            // Remove the domain part to get the relative path
            $relativePath = str_replace('https://boxsocialplatform.lon1.digitaloceanspaces.com/', '', $tempObj->template_url);
            $updatedTemplateData = Storage::disk('digitalocean')->get($relativePath);
        }else{
            $updatedTemplateData = $tempObj->template_data;
        }

        $adminTemplateData = [
            'category_id' => Helpers::encrypt($tempObj->category_id),
            'sub_category_id' => $tempObj->sub_category_id ? Helpers::encrypt($tempObj->sub_category_id) : null,
            'post_content_id' => $tempObj->post_content_id ? Helpers::encrypt($tempObj->post_content_id) : null,
            'design_style_id' => $tempObj->design_style_id ? Helpers::encrypt($tempObj->design_style_id) : null,
        ];

        $data = [
            'id' => Helpers::encrypt($tempObj->id),
            'category_id' => Helpers::encrypt($tempObj->category_id),
            'template_image' => isset($tempObj->template_image) ? asset($tempObj->template_image) : '',
            'post_content_data' => isset($postContentData) ? $postContentData : null,
            'admin_template_data' => isset($adminTemplateData) ? $adminTemplateData : [],
            'template_data' => $updatedTemplateData,
            'template_json_url' => $tempObj->template_url ?? null,
        ];

        if (!empty($tempObj)) {
            return $this->success($data, 'Template Fetch successfully');
        }
    }

    public function getTemplateList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'nullable|array',
            'sub_category_ids' => 'nullable|array',
            'template_ids' => 'nullable|array',
            'post_content_ids' => 'nullable|array',
        ]);
    
        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }
    
        // Decrypt category IDs
        $decryptedCategoryIds = [];
        if ($request->has('category_ids') && !empty($request->category_ids)) {
            $decryptedCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->category_ids);
        }
    
        // Fetch the categories from DB
        $categories = Categories::select('id','name')->whereIn('id', $decryptedCategoryIds)->get();
    
        // Decrypt sub category and post content IDs
        $decryptedSubCategoryIds = $request->has('sub_category_ids') && !empty($request->sub_category_ids)
            ? array_map([Helpers::class, 'decrypt'], $request->sub_category_ids) : [];
        $decryptedPostContentIds = $request->has('post_content_ids') && !empty($request->post_content_ids)
            ? array_map([Helpers::class, 'decrypt'], $request->post_content_ids) : [];
        $decryptedTemplateIds = $request->has('template_ids') && !empty($request->template_ids)
            ? array_map([Helpers::class, 'decrypt'], $request->template_ids) : [];
    
        $tempData = [];
        $postContentData = [];

        // get all post content data of post_content_ids
        if (!empty($decryptedPostContentIds) && !empty($decryptedTemplateIds)) {
            $postContents = PostContent::select('id','title','warning_message','category_id')->whereIn('id', $decryptedPostContentIds)->get();
            $postContentData['post_content_data'] = $postContents->map(function ($postContent) {
                return [
                    'id' => Helpers::encrypt($postContent->id),
                    'category_id' => Helpers::encrypt($postContent->category_id),
                    'title' => $postContent->title,
                    'warning_message' => $postContent->warning_message ? $postContent->warning_message : '',
                ];
            })->toArray();
        }
    
        foreach ($categories as $category) {
            $query = PostTemplate::with('category:id,name')
                ->where('status', 1)
                ->where('category_id', $category->id);
    
            // Apply sub_category filter if needed
            if (!empty($decryptedSubCategoryIds) && empty($decryptedTemplateIds)) {
                $query->where(function ($q) use ($decryptedSubCategoryIds) {
                    $q->whereIn('sub_category_id', $decryptedSubCategoryIds)
                        ->orWhereNull('sub_category_id');
                });
            }
    
            // Apply post_content filter if needed
            if (!empty($decryptedPostContentIds) && empty($decryptedTemplateIds)) {
                $query->where(function ($q) use ($decryptedPostContentIds) {
                    $q->whereIn('post_content_id', $decryptedPostContentIds)
                        ->orWhereNull('post_content_id');
                });
            }
    
            // Apply template_ids filter (if provided)
            if (!empty($decryptedTemplateIds)) {
                $query->whereIn('id', $decryptedTemplateIds);
            }
    
            $templates = $query->latest()->get();
    
            $categoryName = $category->name ?? 'Uncategorized';
    
            $tempData[$categoryName] = $templates->map(function ($t) use ($decryptedTemplateIds) {

                if (!empty($decryptedTemplateIds)) {
                    $postContentId = $t->post_content_id ? Helpers::encrypt($t->post_content_id) : null;
                    return [
                        'id' => Helpers::encrypt($t->id),
                        'category_id' => Helpers::encrypt($t->category_id),
                        'post_content_id' => $postContentId,
                        'template_image' => isset($t->template_image) ? asset($t->template_image) : '',
                        'base64_template_image' => isset($t->template_image) ? Helpers::imageUrlToBase64($t->template_image) : '',
                        'template_data' => isset($t->template_data) ? $t->template_data : '',
                        'template_json_url' => $t->template_url ?? null,
                    ];
                } else {
                    return [
                        'id' => Helpers::encrypt($t->id),
                        'category_id' => Helpers::encrypt($t->category_id),
                        'post_content_id' => $t->post_content_id ? Helpers::encrypt($t->post_content_id) : null,
                        'template_image' => isset($t->template_image) ? asset($t->template_image) : '',
                        'template_json_url' => $t->template_url ?? null,
                    ];
                }
            })->toArray();
        }

        $returnData = array_merge($tempData, $postContentData);
    
        return $this->success($returnData, 'Template fetched successfully');
    }
    

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required',
            'category_id' => 'required|string',
            'sub_category_id' => 'nullable|string',
            'design_style_id' => 'nullable|string',
            'post_content_id' => 'nullable|string',
        ],[
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $decyptedId = Helpers::decrypt($request->template_id);

        
        $adminTemplate = PostTemplate::find($decyptedId);
        
        if (!$adminTemplate) {
            return $this->error('Template not found', 404);
        }
        $oldTemplateImage = $adminTemplate->template_image;
        
        // upload image
        $updateImageUrl = null;
        if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
            $updateImageUrl = Helpers::handleBase64Image($request->template_image, 'admin_template', 'images/admin-post-templates');

            if ($oldTemplateImage && $oldTemplateImage != null) {
                Helpers::deleteImage($oldTemplateImage);
            }
        }

        // nullable
        if ($request->has('sub_category_id') && $request->sub_category_id !== null) {
            $adminTemplate->sub_category_id = Helpers::decrypt($request->sub_category_id);
        } else {
            $adminTemplate->sub_category_id = null;
        }

        if ($request->has('category_id') && $request->category_id !== null) {
            $adminTemplate->category_id = Helpers::decrypt($request->category_id);
        }

        if ($request->has('design_style_id') && $request->design_style_id) {
            $decryptedDesignStyleId = Helpers::decrypt($request->design_style_id);
            $adminTemplate->design_style_id = $decryptedDesignStyleId;
        }

        if ($request->has('post_content_id') && $request->post_content_id) {
            $decryptedPostContentId = Helpers::decrypt($request->post_content_id);
            $adminTemplate->post_content_id = $decryptedPostContentId;
        } else {
            $adminTemplate->post_content_id = null;
        }

        $adminTemplate->template_image = $updateImageUrl ?? null;
        $adminTemplate->template_data = $request->template_data;
        $adminTemplate->save();


        return $this->success([], 'Template updated successfully');
    }

    /** Update Template New API */
    public function newUpdateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'template_image' => 'required',
            'template_data' => 'required',
            'category_id' => 'required|string',
            'sub_category_id' => 'nullable|string',
            'design_style_id' => 'nullable|string',
            'post_content_id' => 'nullable|string',
        ],[
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $decyptedId = Helpers::decrypt($request->template_id);

        
        $adminTemplate = PostTemplate::find($decyptedId);
        
        if (!$adminTemplate) {
            return $this->error('Template not found', 404);
        }
        $oldTemplateImage = $adminTemplate->template_image;
        $oldTemplateJsonUrl = $adminTemplate->template_url;
        
        // upload image
        // $updateImageUrl = null;
        // if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
        //     $updateImageUrl = Helpers::handleBase64Image($request->template_image, 'admin_template', 'images/admin-post-templates');

        //     if ($oldTemplateImage && $oldTemplateImage != null) {
        //         Helpers::deleteImage($oldTemplateImage);
        //     }
        // }

        $updateImageUrl = $oldTemplateImage;
        if ($request->has('template_image')) {
            $prefix = 'admin_template_'. rand(1000, 9999);
            $updateImageUrl = Helpers::uploadImage($prefix, $request->template_image, 'images/admin-post-templates');
            if ($oldTemplateImage && $oldTemplateImage != null && $updateImageUrl) {
                Helpers::deleteImage($oldTemplateImage);
            }
        }

        $templateJsonUrl = $oldTemplateJsonUrl;
        if ($request->has('template_data')) {
            $prefix = 'admin_template_json_' . $adminTemplate->id;
            $templateJsonUrl = Helpers::uploadImage($prefix, $request->template_data, 'json/admin-template-data');   

            if ($oldTemplateJsonUrl && $oldTemplateJsonUrl != null && $templateJsonUrl) {
                Helpers::deleteImage($oldTemplateJsonUrl);
            }
        }

        // nullable
        if ($request->has('sub_category_id') && $request->sub_category_id !== null) {
            $adminTemplate->sub_category_id = Helpers::decrypt($request->sub_category_id);
        } else {
            $adminTemplate->sub_category_id = null;
        }

        if ($request->has('category_id') && $request->category_id !== null) {
            $adminTemplate->category_id = Helpers::decrypt($request->category_id);
        }

        if ($request->has('design_style_id') && $request->design_style_id) {
            $decryptedDesignStyleId = Helpers::decrypt($request->design_style_id);
            $adminTemplate->design_style_id = $decryptedDesignStyleId;
        }

        if ($request->has('post_content_id') && $request->post_content_id) {
            $decryptedPostContentId = Helpers::decrypt($request->post_content_id);
            $adminTemplate->post_content_id = $decryptedPostContentId;
        } else {
            $adminTemplate->post_content_id = null;
        }

        $adminTemplate->template_image = $updateImageUrl ?? null;
        // $adminTemplate->template_data = $request->template_data;
        $adminTemplate->template_url = $templateJsonUrl;
        $adminTemplate->save();


        return $this->success([], 'Template updated successfully');
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $decyptedId = Helpers::decrypt($request->template_id);

        $template = PostTemplate::find($decyptedId);

        if (!$template) {
            return $this->error('Template not found', 404);
        }

        if ($template->template_image && $template->template_image != null) {
            Helpers::deleteImage($template->template_image);
        }

        if ($template->template_url && $template->template_url != null) {
            Helpers::deleteImage($template->template_url);
        }

        $template->delete();

        return $this->success([], 'Template deleted successfully');
    }

    /** Old Get Text Content Template */
    public function getTextContentTemplateListOld(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'category_ids' => 'nullable|array',
                'sub_category_ids' => 'nullable|array',
                'post_content_ids' => 'nullable|array',
                'template_ids' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationError('Validation failed', $validator->errors());
            }

            // Decrypt category IDs
            $decryptedCategoryIds = [];
            if ($request->has('category_ids') && !empty($request->category_ids)) {
                $decryptedCategoryIds = array_map(function ($id) {
                    return Helpers::decrypt($id);
                }, $request->category_ids);
            }
            
            // Decrypt sub category and post content IDs
            $decryptedSubCategoryIds = $request->has('sub_category_ids') && !empty($request->sub_category_ids)
            ? array_map([Helpers::class, 'decrypt'], $request->sub_category_ids) : [];
            $decryptedPostContentIds = $request->has('post_content_ids') && !empty($request->post_content_ids)
            ? array_map([Helpers::class, 'decrypt'], $request->post_content_ids) : [];
            $decryptedTemplateIds = $request->has('template_ids') && !empty($request->template_ids)
            ? array_map([Helpers::class, 'decrypt'], $request->template_ids) : [];

            // Fetch the categories from DB
            $categories = Categories::select('id','name')->whereIn('id', $decryptedCategoryIds)->get();
            $postContents = PostContent::select('id','title','category_id','warning_message')->whereIn('id',$decryptedPostContentIds)->get();
            $tempData = [];

            foreach ($categories as $category) {

                $query = PostTemplate::with('category:id,name')
                    ->where('status', 1)
                    ->where('category_id', $category->id);

                // Apply sub_category filter if needed
                if (!empty($decryptedSubCategoryIds) && empty($decryptedTemplateIds)) {
                    $query->where(function ($q) use ($decryptedSubCategoryIds) {
                        $q->whereIn('sub_category_id', $decryptedSubCategoryIds)
                            ->orWhereNull('sub_category_id');
                    });
                }
        
                // Apply post_content filter if needed
                if (!empty($decryptedPostContentIds) && empty($decryptedTemplateIds)) {
                    $query->where(function ($q) use ($decryptedPostContentIds) {
                        $q->whereIn('post_content_id', $decryptedPostContentIds)
                            ->orWhereNull('post_content_id');
                    });
                }

                // Apply template_ids filter (if provided)
                if (!empty($decryptedTemplateIds)) {
                    $query->whereIn('id', $decryptedTemplateIds);
                }

                $templates = $query->latest()->get();
                
                $categoryName = $category->name ?? 'Uncategorized';
                $tempContentData = [];

                foreach ($templates as $template) {

                    $postContent = $postContents->where('id',$template->post_content_id)->where('category_id',$category->id)->first();
                    
                    $templateData = [];
                    if (!empty($decryptedTemplateIds)) {
                        $templateData = [
                            'base64_template_image' => isset($template->template_image) ? Helpers::imageUrlToBase64($template->template_image) : '',
                            'template_data' => isset($template->template_data) ? $template->template_data : '',
                        ];
                    }
                    
                    $tempContentData[] = array_merge([
                        'id' => Helpers::encrypt($template->id),
                        'category_id' => Helpers::encrypt($template->category_id),
                        'post_content_id' => $template->post_content_id ? Helpers::encrypt($template->post_content_id) : null,
                        'template_image' => isset($template->template_image) ? asset($template->template_image) : '',
                        'title' => $postContent && $postContent['title'] ? $postContent['title'] : null,
                        'warning_message' => $postContent && $postContent['warning_message'] ? $postContent['warning_message'] : ''
                    ],$templateData);

                }

                $tempData[] = [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $categoryName,
                    'post_content_data' => $tempContentData
                ];
            }

            return $this->success($tempData, 'Template fetched successfully');

        } catch (Exception $e) {
            Log::error($e);
            return $this->error('Something went wrong', 500);
        }
    }

    /** Optimize Get Test Content Template Optimize */
    public function getTextContentTemplateList(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'category_ids' => 'nullable|array',
                'sub_category_ids' => 'nullable|array',
                'post_content_ids' => 'nullable|array',
                'template_ids' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationError('Validation failed', $validator->errors());
            }

            // Decrypt all IDs at once where applicable
            $decryptedCategoryIds = collect($request->input('category_ids', []))
                ->map(fn($id) => Helpers::decrypt($id))
                ->all();

            $decryptedSubCategoryIds = collect($request->input('sub_category_ids', []))
                ->map(fn($id) => Helpers::decrypt($id))
                ->all();

            $decryptedPostContentIds = collect($request->input('post_content_ids', []))
                ->map(fn($id) => Helpers::decrypt($id))
                ->all();

            $decryptedTemplateIds = collect($request->input('template_ids', []))
                ->map(fn($id) => Helpers::decrypt($id))
                ->all();

                
                // Fetch categories in one query
            $categories = Categories::select('id', 'name')
                ->whereIn('id', $decryptedCategoryIds)
                ->get();
                
            // Fetch post contents in one query
            $postContents = PostContent::select('id', 'title', 'category_id', 'warning_message')
                ->whereIn('id', $decryptedPostContentIds)
                ->get()
                ->groupBy('id');

            // Extract category IDs once
            $categoryIds = $categories->pluck('id')->all();

            // Fetch templates once with necessary filters
            

            if (!empty($decryptedTemplateIds)) {
                $postTemplatesQuery = PostTemplate::with('category:id,name')
                ->where('status', 1)
                ->whereIn('category_id', $categoryIds);
                
                $postTemplatesQuery->whereIn('id', $decryptedTemplateIds);
            } else {
                $postTemplatesQuery = PostTemplate::select('id','category_id','sub_category_id','design_style_id','post_content_id','template_image','template_name')->with('category:id,name')
                    ->where('status', 1)
                    ->whereIn('category_id', $categoryIds);

                if (!empty($decryptedSubCategoryIds)) {
                    $postTemplatesQuery->where(function ($query) use ($decryptedSubCategoryIds) {
                        $query->whereIn('sub_category_id', $decryptedSubCategoryIds)
                            ->orWhereNull('sub_category_id');
                    });
                }
                if (!empty($decryptedPostContentIds)) {
                    $postTemplatesQuery->where(function ($query) use ($decryptedPostContentIds) {
                        $query->whereIn('post_content_id', $decryptedPostContentIds)
                            ->orWhereNull('post_content_id');
                    });
                }

            }

            $postTemplates = $postTemplatesQuery->latest()
                ->get()
                ->groupBy('category_id');

            $tempData = [];

            foreach ($categories as $category) {

                $templates = $postTemplates->get($category->id, collect());

                $tempContentData = $templates->map(function ($template) use ($postContents, $decryptedTemplateIds) {
                    $postContent = $postContents->get($template->post_content_id)?->first();

                    $templateData = [];
                    if (!empty($decryptedTemplateIds)) {
                        $templateData = [
                            'base64_template_image' => $template->template_image ? Helpers::imageUrlToBase64($template->template_image) : '',
                            'template_data' => $template->template_data ?? '',
                            'template_json_url' => $template->template_url ?? '',
                        ];
                    }

                    return array_merge([
                        'id' => Helpers::encrypt($template->id),
                        'category_id' => Helpers::encrypt($template->category_id),
                        'post_content_id' => $template->post_content_id ? Helpers::encrypt($template->post_content_id) : null,
                        'template_image' => $template->template_image ? asset($template->template_image) : '',
                        'title' => $postContent->title ?? null,
                        'warning_message' => $postContent->warning_message ?? '',
                    ], $templateData);
                })->all();

                $tempData[] = [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $category->name ?? 'Uncategorized',
                    'post_content_data' => $tempContentData
                ];

            }

            return $this->success($tempData, 'Template fetched successfully');

        } catch (Exception $e) {
            Log::error($e);
            return $this->error('Something went wrong.',500);    
        }
    }
}
