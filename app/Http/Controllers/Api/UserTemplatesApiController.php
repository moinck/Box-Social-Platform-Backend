<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\UserTemplateSendMail;
use App\Models\PostTemplate;
use App\Models\User;
use App\Models\UserTemplates;
use App\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserTemplatesApiController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        $user = Auth::user();

        $search = $request->search ?? '';
        $category = $request->category_id ?? '';

        $userTemplates = UserTemplates::with('category','template.category','template.postContent')
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
        $userTemplate = UserTemplates::with('category','template.category')->find($decyptedId);

        if (!$userTemplate) {
            return $this->error('User template not found', 404);
        }

        $categoryName = $userTemplate->category->name ?? null;
        if (empty($categoryName)) {
            $categoryName = $userTemplate->template->category->name ?? null;
        }

        $updatedTemplateData = helpers::replaceFabricTemplateData($userTemplate->template_data,[]);

        $returnData = [
            'id' => Helpers::encrypt($userTemplate->id),
            'category' => $categoryName,
            'template_name' => $userTemplate->template_name ?? null,
            'template_image' => $userTemplate->template_image ? asset($userTemplate->template_image) : null,
            'template_data' => $updatedTemplateData,
        ];

        return $this->success($returnData, 'User template');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'category_id' => 'nullable',
            'template_name' => 'required|string',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required',
            "send_mail" => 'nullable|string',
        ],[
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
            'template_data' => json_encode($request->template_data),
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

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'template_name' => 'required|string',
            'template_image' => 'required|string|regex:/^data:image\/[^;]+;base64,/',
            'template_data' => 'required',
            "send_mail" => 'nullable|string',
        ],[
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
        $userTemplate->template_data = json_encode($request->template_data);
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
            Helpers::deleteImage($userTemplate->template_image);
        }

        $userTemplate->delete();

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
