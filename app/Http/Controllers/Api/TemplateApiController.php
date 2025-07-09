<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PostTemplate;
use Illuminate\Support\Facades\Auth;

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
            'design_style_id' => 'required|string',
            'post_content_id' => 'required|string',
        ],[
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $imagePath = null;
        if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
            $imagePath = Helpers::handleBase64Image($request->template_image, 'admin_template', 'images/admin-post-templates');
        }

        $tempObj = new PostTemplate();
        $tempObj->category_id = Helpers::decrypt($request->category_id);
        $tempObj->template_image = $imagePath;
        $tempObj->template_data = json_encode($request->template_data);

        if ($request->has('sub_category_id') && $request->sub_category_id !== null) {
            $tempObj->sub_category_id = Helpers::decrypt($request->sub_category_id);
        }

        if ($request->has('design_style_id') && $request->design_style_id) {
            $decryptedDesignStyleId = Helpers::decrypt($request->design_style_id);
            $tempObj->design_style_id = $decryptedDesignStyleId;
        }

        if ($request->has('post_content_id') && $request->post_content_id) {
            $decryptedPostContentId = Helpers::decrypt($request->post_content_id);
            $tempObj->post_content_id = $decryptedPostContentId;
        }
        $tempObj->save();

        $data = [
            "id" => Helpers::encrypt($tempObj->id),
        ];
        return $this->success($data, 'Template create successfully');

    }

    public function getTemplate(Request $request, $id)
    {

        $tempObj = PostTemplate::with('postContent')
            ->where('id', Helpers::decrypt($id))->first();

        if (!$tempObj) {
            return $this->error('Template not found', 404);
        }

        // post content data
        $postTemplateData = $tempObj->postContent;
        $postContentData = [
            "id" => Helpers::encrypt($postTemplateData->id),
            "title" => $postTemplateData->title,
            "warning_message" => $postTemplateData->warning_message ? $postTemplateData->warning_message : '',
        ];
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

        $data = [
            'id' => Helpers::encrypt($tempObj->id),
            'category_id' => Helpers::encrypt($tempObj->category_id),
            'template_image' => isset($tempObj->template_image) ? asset($tempObj->template_image) : '',
            'post_content_data' => isset($postContentData) ? $postContentData : [],
            'template_data' => isset($tempObj->template_data) ? $tempObj->template_data : [],
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

        $tempObj = PostTemplate::with('category:id,name')->where('status', 1);

        if (!$tempObj) {
            return $this->error('Template not found', 404);
        }

        // filter by categories
        if ($request->has('category_ids') && $request->category_ids != []) {
            $decryptedCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->category_ids);
            $tempObj->whereIn('category_id', $decryptedCategoryIds);
        }

        // filter by sub categories (include records with matching sub_category_id OR null sub_category_id)
        if ($request->has('sub_category_ids') && $request->sub_category_ids != [] && $request->template_ids == []) {
            $decryptedSubCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->sub_category_ids);
            
            $tempObj->orWhere(function ($query) use ($decryptedSubCategoryIds) {
                $query->whereIn('sub_category_id', $decryptedSubCategoryIds);
            });
        }

        // filter by selected post contents (include records with matching post_content_id OR null post_content_id)
        if ($request->has('post_content_ids') && $request->post_content_ids != []) {
            $decryptedPostContentIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->post_content_ids);
            $tempObj->whereIn('post_content_id', $decryptedPostContentIds);
        }

        // filter by selected templates
        if ($request->has('template_ids') && $request->template_ids != []) {
            $decryptedTemplateIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->template_ids);
            $tempObj->whereIn('id', $decryptedTemplateIds);
        }

        $tempObj = $tempObj->get();

        $tempData = [];
        foreach ($tempObj as $key => $t) {
            $categoryName = $t->category->name ?? 'Uncategorized';

            $tempData[$categoryName][] = [
                'id' => Helpers::encrypt($t->id),
                'category_id' => Helpers::encrypt($t->category_id),
                'post_content_id' => Helpers::encrypt($t->post_content_id),
                'template_image' => isset($t->template_image) ? asset($t->template_image) : '',
            ];
        }
        $data = $tempData;

        if (!empty($tempObj)) {
            return $this->success($data, 'Template Fetch successfully');
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required',
        ],[
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $decyptedId = Helpers::decrypt($request->template_id);

        
        $userTemplate = PostTemplate::find($decyptedId);
        
        if (!$userTemplate) {
            return $this->error('Template not found', 404);
        }
        $oldTemplateImage = $userTemplate->template_image;
        
        // upload image
        $updateImageUrl = null;
        if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
            $updateImageUrl = Helpers::handleBase64Image($request->template_image, 'admin_template', 'images/admin-post-templates');

            if ($oldTemplateImage && $oldTemplateImage != null) {
                Helpers::deleteImage($oldTemplateImage);
            }
        }

        $userTemplate->template_image = $updateImageUrl ?? null;
        $userTemplate->template_data = json_encode($request->template_data);
        $userTemplate->save();


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

        $template->delete();

        return $this->success([], 'Template deleted successfully');
    }
}
