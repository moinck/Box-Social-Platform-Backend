<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessJsonFileUpload;
use App\Mail\UserTemplateSendMail;
use App\Models\PostTemplate;
use App\Models\User;
use App\Models\UserTemplates;
use App\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserTemplatesApiController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        $user = Auth::user();

        $search = $request->search ?? '';
        $category = $request->category_id ?? '';

             $userTemplates = UserTemplates::select( 'id','user_id','template_id','category_id','template_name','template_image','updated_at')->with('category', 'template.category', 'template.postContent')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('category', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                        ->orWhereHas('template.postContent', function ($query) use ($search) {
                            $query->where('title', 'like', "%{$search}%");
                        })
                        ->orWhere('template_name', 'like', "%{$search}%");
                });
            })
            ->when($category && $category != "", function ($query) use ($category) {
                $query->where('category_id', Helpers::decrypt($category));
            })
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($userTemplates->isEmpty()) {
            return $this->error('User templates not found', 404);
        }

        $returnData = [];
        foreach ($userTemplates as $key => $value) {
            $categoryName = $value->category->name ?? null;
            $postContentTitle = $value->template->postContent->title ?? "-";
            if (empty($categoryName)) {
                $categoryName = $value->template->category->name ?? null;
            }
            $returnData[] = [
                'id' => Helpers::encrypt($value->id),
                'category' => $categoryName,
                'post_title' => $postContentTitle,
                'template_name' => $value->template_name ?? null,
                'template_image' => $value->template_image ? asset($value->template_image) : null,
                'edited' => Carbon::parse($value->updated_at)->diffForHumans(),
            ];
        }

        return $this->success($returnData, 'User templates list');
    }

    public function get($id)
    {
        $decyptedId = Helpers::decrypt($id);
        $userTemplate = UserTemplates::with('category', 'template.category')->find($decyptedId);

        if (!$userTemplate) {
            return $this->error('User template not found', 404);
        }

        $categoryName = $userTemplate->category->name ?? null;
        if (empty($categoryName)) {
            $categoryName = $userTemplate->template->category->name ?? null;
        }

        $updatedTemplateData = helpers::replaceFabricTemplateData($userTemplate->template_data, []);

        $returnData = [
            'id' => Helpers::encrypt($userTemplate->id),
            'category' => $categoryName,
            'template_name' => $userTemplate->template_name ?? null,
            'template_image' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
            'template_data' => $updatedTemplateData,
            'template_json_url' => $userTemplate->template_url ?? null
        ];

        return $this->success($returnData, 'User template');
    }

    public function getTemplateNew($id)
    {
        $decyptedId = Helpers::decrypt($id);
        $userTemplate = UserTemplates::with('category', 'template.category')->find($decyptedId);

        if (!$userTemplate) {
            return $this->error('User template not found', 404);
        }

        $categoryName = $userTemplate->category->name ?? null;
        if (empty($categoryName)) {
            $categoryName = $userTemplate->template->category->name ?? null;
        }

        if(!empty($userTemplate->template_url)){
            // Remove the domain part to get the relative path
            $relativePath = str_replace('https://boxsocialplatform.lon1.digitaloceanspaces.com/', '', $userTemplate->template_url);
            $updatedTemplateData = Storage::disk('digitalocean')->get($relativePath);
        }else{
            $updatedTemplateData = helpers::replaceFabricTemplateData($userTemplate->template_data, []);
        }

        $returnData = [
            'id' => Helpers::encrypt($userTemplate->id),
            'category' => $categoryName,
            'template_name' => $userTemplate->template_name ?? null,
            'template_image' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
            'template_data' => $updatedTemplateData,
            'template_json_url' => $userTemplate->template_url ?? null
        ];

        return $this->success($returnData, 'User template');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'category_id' => 'required',
            'template_name' => 'required|string',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required',
            "send_mail" => 'nullable|string',
        ], [
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $user = Auth::user();
        
        $decyptedId = Helpers::decrypt($request->template_id);

        // upload image
        $imageUrl = null;
        if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
            $imageUrl = Helpers::handleBase64Image($request->template_image, 'user_template', 'images/user-template-images');
        }

        $categoryId = $request->category_id ?? null;
        if (!empty($categoryId)) {
            $categoryId = Helpers::decrypt($categoryId);
        } else {
            $categoryId = PostTemplate::find($decyptedId)->category_id;
        }

        $userTemplate = UserTemplates::create([
            'user_id' => $user->id,
            'template_id' => $decyptedId,
            'category_id' => $categoryId ?? null,
            'template_name' => $request->template_name,
            'template_image' => $imageUrl ?? null,
            'template_data' => $request->template_data,
        ]);

        // send mail
        if ($request->send_mail && $request->send_mail == "1") {
            $this->sendTemplateMail($userTemplate, 'store');
        }

        $postContentData = $userTemplate->template->postContent ?? null;
        $postContentArray = [
            'title' => $postContentData->title ?? null,
            'description' => $postContentData->description ?? null,
        ];

        $returnData = [
            'id' => Helpers::encrypt($userTemplate->id),
            'template_url' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
            'post_content_data' => $postContentArray,
        ];

        return $this->success($returnData, 'User template saved successfully');
    }


    /**
     * FOr saving multiple template at a time
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function MultiStore(Request $request)
    {
        // Check if templates data is an array (multiple templates) or single template
        $templatesData = $request->has('templates') ? $request->templates : [$request->all()];

        $user = Auth::user();
        $savedTemplates = [];
        $errors = [];

        foreach ($templatesData as $index => $templateData) {
            // Validate each template
            $validator = Validator::make($templateData, [
                'template_id' => 'required',
                'category_id' => 'required',
                'template_name' => 'required|string',
                'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
                'template_data' => 'required',
                "send_mail" => 'nullable|string',
            ], [
                'template_image.regex' => 'Invalid image format',
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors();
                continue; // Skip this template and continue with next
            }

            $templateName = [];
            DB::beginTransaction();
            try {
                $decyptedId = Helpers::decrypt($templateData['template_id']);

                // Upload image
                $imageUrl = null;
                if (isset($templateData['template_image']) && strpos($templateData['template_image'], 'data:image/') === 0) {
                    $prefix = 'user_template_' . rand(1000, 9999);
                    $imageUrl = Helpers::handleBase64Image($templateData['template_image'], $prefix, 'images/user-template-images');
                }

                $categoryId = $templateData['category_id'];
                if (!empty($categoryId)) {
                    $categoryId = Helpers::decrypt($categoryId);
                } else {
                    $categoryId = PostTemplate::find($decyptedId)->category_id;
                }

                // Log::info('Template data type: ' . gettype($templateData['template_data']));

                $templateDataString = is_array($templateData['template_data']) 
                    ? json_encode($templateData['template_data']) 
                    : $templateData['template_data'];
                
                // Log::info('Template data type: ' . gettype($templateDataString));

                $userTemplate = UserTemplates::create([
                    'user_id' => $user->id,
                    'template_id' => $decyptedId,
                    'category_id' => $categoryId,
                    'template_name' => $templateData['template_name'],
                    'template_image' => $imageUrl ?? null,
                    'template_data' => $templateDataString,
                ]);

                $templateName[] = $templateData['template_name'];

                // Send mail
                if (isset($templateData['send_mail']) && $templateData['send_mail'] == "1") {
                    $this->sendTemplateMail($userTemplate, 'store');
                }

                $postContentData = $userTemplate->template->postContent ?? null;
                $postContentArray = [
                    'title' => $postContentData->title ?? null,
                    'description' => $postContentData->description ?? null,
                ];

                $savedTemplates[] = [
                    'id' => Helpers::encrypt($userTemplate->id),
                    'template_url' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
                    'post_content_data' => $postContentArray,
                ];
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $errors[$index] = ['error' => 'Failed to save template: ' . $e->getMessage()];
            }
        }

        // Prepare response
        if (empty($savedTemplates) && !empty($errors)) {
            return $this->validationError('All templates failed to save', $errors);
        }

        $response = [
            'saved_templates' => $savedTemplates,
            'total_saved' => count($savedTemplates),
            'total_attempted' => count($templatesData),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['failed_count'] = count($errors);
        }

        $message = count($savedTemplates) > 1
            ? count($savedTemplates) . ' templates saved successfully'
            : 'Template saved successfully';

        if (!empty($errors)) {
            $message .= ', ' . count($errors) . ' failed';
        }

        /** User Activity Log */
        Helpers::activityLog([
            'title' => "User Template",
            'description' => "User template saved successfully. User: ".$user->email.". Template Name: (".implode(', ',$templateName).").",
            'url' => "api/user-template/multiple/store"
        ]);

        return $this->success($response, $message);
    }

    /** Multi Store New API */
    public function multiStoreNew(Request $request)
    {
        // Check if templates data is an array (multiple templates) or single template
        $templatesData = $request->has('templates') ? $request->templates : [$request->all()];

        $user = Auth::user();
        $savedTemplates = [];
        $errors = [];

        foreach ($templatesData as $index => $templateData) {
            // Validate each template
            $validator = Validator::make($templateData, [
                'template_id' => 'required',
                'category_id' => 'required',
                'template_name' => 'required|string',
                'template_image' => 'required',
                'template_data' => 'required',
                "send_mail" => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors();
                continue; // Skip this template and continue with next
            }

            $templateName = [];
            DB::beginTransaction();
            try {
                $decyptedId = Helpers::decrypt($templateData['template_id']);

                // Upload image
                // $imageUrl = null;
                // if (isset($templateData['template_image']) && strpos($templateData['template_image'], 'data:image/') === 0) {
                //     $prefix = 'user_template_' . rand(1000, 9999);
                //     $imageUrl = Helpers::handleBase64Image($templateData['template_image'], $prefix, 'images/user-template-images');
                // }

                $imageUrl = null;
                if (isset($templateData['template_image']) && $templateData['template_image']) {
                    $prefix = 'user_template_' . rand(1000, 9999);
                    $imageUrl = Helpers::uploadImage($prefix, $templateData['template_image'], 'images/user-template-images');
                }

                $categoryId = $templateData['category_id'];
                if (!empty($categoryId)) {
                    $categoryId = Helpers::decrypt($categoryId);
                } else {
                    $categoryId = PostTemplate::find($decyptedId)->category_id;
                }

                // Log::info('Template data type: ' . gettype($templateData['template_data']));

                // $templateDataString = is_array($templateData['template_data']) 
                //     ? json_encode($templateData['template_data']) 
                //     : $templateData['template_data'];
                
                // Log::info('Template data type: ' . gettype($templateDataString));

                $userTemplate = UserTemplates::create([
                    'user_id' => $user->id,
                    'template_id' => $decyptedId,
                    'category_id' => $categoryId,
                    'template_name' => $templateData['template_name'],
                    'template_image' => $imageUrl ?? null,
                    'template_data' => null
                ]);

                $templateJsonUrl = null;
                if ($templateData['template_data']) {
                    $prefix = 'template_json_' . $userTemplate->id;
                    $templateJsonUrl = Helpers::uploadImage($prefix, $templateData['template_data'], 'json/template-data');   
                }

                $userTemplate->update([
                    'template_url' => $templateJsonUrl,
                    'template_data' => null
                ]);

                $userTemplate = $userTemplate->refresh();

                $templateName[] = $templateData['template_name'];

                // Send mail
                if (isset($templateData['send_mail']) && $templateData['send_mail'] == "1") {
                    $this->sendTemplateMail($userTemplate, 'store');
                }

                $postContentData = $userTemplate->template->postContent ?? null;
                $postContentArray = [
                    'title' => $postContentData->title ?? null,
                    'description' => $postContentData->description ?? null,
                ];

                $savedTemplates[] = [
                    'id' => Helpers::encrypt($userTemplate->id),
                    'template_url' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
                    'post_content_data' => $postContentArray,
                    'template_json_url' => $templateJsonUrl
                ];
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $errors[$index] = ['error' => 'Failed to save template: ' . $e->getMessage()];
            }
        }

        // Prepare response
        if (empty($savedTemplates) && !empty($errors)) {
            return $this->validationError('All templates failed to save', $errors);
        }

        $response = [
            'saved_templates' => $savedTemplates,
            'total_saved' => count($savedTemplates),
            'total_attempted' => count($templatesData),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['failed_count'] = count($errors);
        }

        $message = count($savedTemplates) > 1
            ? count($savedTemplates) . ' templates saved successfully'
            : 'Template saved successfully';

        if (!empty($errors)) {
            $message .= ', ' . count($errors) . ' failed';
        }

        /** User Activity Log */
        Helpers::activityLog([
            'title' => "User Template",
            'description' => "User template saved successfully. User: ".$user->email.". Template Name: (".implode(', ',$templateName).").",
            'url' => "api/user-template/multiple/store"
        ]);

        return $this->success($response, $message);
    }

    /**
     * update user template
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'template_name' => 'required|string',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required',
            "send_mail" => 'nullable|string',
        ], [
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $decyptedId = Helpers::decrypt($request->template_id);


        $userTemplate = UserTemplates::with('template.postContent')->find($decyptedId);

        if (!$userTemplate) {
            return $this->error('User template not found', 404);
        }
        $oldTemplateImage = $userTemplate->template_image;

        // upload image
        $imageUrl = null;
        if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
            $imageUrl = Helpers::handleBase64Image($request->template_image, 'user_template', 'images/user-template-images');

            if ($oldTemplateImage && $oldTemplateImage != null) {
                Helpers::deleteImage($oldTemplateImage);
            }
        }

        $userTemplate->template_name = $request->template_name;
        $userTemplate->template_image = $imageUrl ?? null;
        $userTemplate->template_data = $request->template_data;
        $userTemplate->save();


        // send mail
        if ($request->send_mail && $request->send_mail == "1") {
            $this->sendTemplateMail($userTemplate, 'update');
        }

        $postContentData = $userTemplate->template->postContent ?? null;
        $postContentArray = [
            'title' => $postContentData->title ?? null,
            'description' => $postContentData->description ?? null,
        ];

        $returnData = [
            'id' => Helpers::encrypt($userTemplate->id),
            'template_url' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
            'post_content_data' => $postContentArray,
        ];

        $user = User::select('id','email')->where('id', $userTemplate->user_id)->first();
        /** User Activity Log */
        Helpers::activityLog([
            'title' => "User Template",
            'description' => "User template update successfully. Template Name: ".$userTemplate->template_name.". User: ".$user->email,
            'url' => "api/user-template/update"
        ]);

        return $this->success($returnData, 'User template updated successfully');
    }

    /** Update New API */
    public function templateUpdateNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'template_name' => 'required|string',
            'template_image' => 'required',
            'template_data' => 'required',
            "send_mail" => 'nullable|string',
        ], [
            'template_image.regex' => 'Invalid image format',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $decyptedId = Helpers::decrypt($request->template_id);


        $userTemplate = UserTemplates::with('template.postContent')->find($decyptedId);

        if (!$userTemplate) {
            return $this->error('User template not found', 404);
        }
        $oldTemplateImage = $userTemplate->template_image;
        $oldTemplateJsonUrl = $userTemplate->template_url;

        // upload image
        // $imageUrl = null;
        // if ($request->has('template_image') && strpos($request->template_image, 'data:image/') === 0) {
        //     $imageUrl = Helpers::handleBase64Image($request->template_image, 'user_template', 'images/user-template-images');

        //     if ($oldTemplateImage && $oldTemplateImage != null) {
        //         Helpers::deleteImage($oldTemplateImage);
        //     }
        // }


        $imageUrl = $oldTemplateImage;
        if ($request->has('template_image')) {
            $prefix = 'user_template_' . rand(1000, 9999);
            $imageUrl = Helpers::uploadImage($prefix, $request->template_image, 'images/user-template-images');

            if ($oldTemplateImage && $oldTemplateImage != null && $imageUrl) {
                Helpers::deleteImage($oldTemplateImage,'isDelete');
            }
        }

        $templateJsonUrl = $oldTemplateJsonUrl;
        if ($request->has('template_data')) {
            $prefix = 'template_json_' . $userTemplate->id;
            $templateJsonUrl = Helpers::uploadImage($prefix, $request->template_data, 'json/template-data');   

            if ($oldTemplateJsonUrl && $oldTemplateJsonUrl != null && $templateJsonUrl) {
                Helpers::deleteImage($oldTemplateJsonUrl,'isDelete');
            }
        }

        $userTemplate->template_name = $request->template_name;
        $userTemplate->template_image = $imageUrl ?? null;
        // $userTemplate->template_data = $request->template_data;
        $userTemplate->template_url = $templateJsonUrl;
        $userTemplate->save();

        // send mail
        if ($request->send_mail && $request->send_mail == "1") {
            $this->sendTemplateMail($userTemplate, 'update');
        }

        $postContentData = $userTemplate->template->postContent ?? null;
        $postContentArray = [
            'title' => $postContentData->title ?? null,
            'description' => $postContentData->description ?? null,
        ];

        $returnData = [
            'id' => Helpers::encrypt($userTemplate->id),
            'template_url' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
            'post_content_data' => $postContentArray,
            'template_json_url' => $templateJsonUrl
        ];

        $user = User::select('id','email')->where('id', $userTemplate->user_id)->first();
        /** User Activity Log */
        Helpers::activityLog([
            'title' => "User Template",
            'description' => "User template update successfully. Template Name: ".$userTemplate->template_name.". User: ".$user->email,
            'url' => "api/user-template/update"
        ]);

        return $this->success($returnData, 'User template updated successfully');
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
        $userTemplate = UserTemplates::find($decyptedId);

        if (!$userTemplate) {
            return $this->error('User template not found', 404);
        }

        if ($userTemplate->template_image && $userTemplate->template_image != null) {
            Helpers::deleteImage($userTemplate->template_image,'isDelete');
        }

        if ($userTemplate->template_url && $userTemplate->template_url != null) {
            Helpers::deleteImage($userTemplate->template_url,'isDelete');
        }

        $userTemplate->delete();

        $user = User::select('id','email')->where('id', $userTemplate->user_id)->first();
        /** User Activity Log */
        Helpers::activityLog([
            'title' => "User Template",
            'description' => "User template delete successfully. Template Name: ".$userTemplate->template_name.". Category Name: ".$userTemplate->category->name.". User: ".$user->email,
            'url' => "api/user-template/delete"
        ]);

        return $this->success([], 'User template deleted successfully');
    }

    /**
     * Function to send template mail
     * @param mixed $userTemplate
     * @return bool
     */
    public function sendTemplateMail($userTemplate, $type)
    {
        $user = User::select('id', 'email', 'first_name', 'last_name')->find($userTemplate->user_id);
        if (!$user) {
            return false;
        }
        $mailData = [];
        $mailData['user'] = $user;
        $mailData['template'] = $userTemplate;
        Mail::to($user->email)->send(new UserTemplateSendMail($mailData, $type));

        return true;
    }
}
