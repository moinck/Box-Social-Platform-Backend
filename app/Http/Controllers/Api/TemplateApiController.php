<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PostTemplate;
use PHPUnit\TextUI\Help;

class TemplateApiController extends Controller
{
    use ResponseTrait;

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'template_image' => 'nullable|string',
            'template_data' => 'required', // or 'array' if JSON
            'design_style_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }


        $tempObj = new PostTemplate();
        $tempObj->category_id = Helpers::decrypt($request->category_id);
        $tempObj->template_image = $request->template_image;
        $tempObj->template_data = json_encode($request->template_data);
        if ($request->has('design_style_id') && $request->design_style_id) {
            $decryptedDesignStyleId = Helpers::decrypt($request->design_style_id);
            $tempObj->design_style_id = $decryptedDesignStyleId;
        }
        $tempObj->save();

        $data = [
            "id" => Helpers::encrypt($tempObj->id),
        ];
        return $this->success($data, 'Template create successfully');

    }

    public function getTemplate(Request $request, $id)
    {

        $tempObj = PostTemplate::where('id', Helpers::decrypt($id))->first();

        if (!$tempObj) {
            return $this->error('Template not found', 404);
        }

        $data = [
            'id' => Helpers::encrypt($tempObj->id),
            'template_image' => isset($tempObj->template_image) ? asset($tempObj->template_image) : '',
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
            'template_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation failed', $validator->errors());
        }

        $tempObj = PostTemplate::where('status', 1);

        if (!$tempObj) {
            return $this->error('Template not found', 404);
        }

        // filter bt categories
        if ($request->has('category_ids') && $request->category_ids != []) {
            $decryptedCategoryIds = array_map(function ($id) {
                return Helpers::decrypt($id);
            }, $request->category_ids);
            $tempObj->whereIn('category_id', $decryptedCategoryIds);
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
            $categoryName = $t->category->name;

            $tempData[$categoryName][] = [
                'id' => Helpers::encrypt($t->id),
                'template_image' => isset($t->template_image) ? asset($t->template_image) : '',
                'category_id' => Helpers::encrypt($t->category_id),
                'template_data' => isset($t->template_data) ? $t->template_data : [],
            ];
        }
        $data = $tempData;

        if (!empty($tempObj)) {
            return $this->success($data, 'Template Fetch successfully');
        }
    }
}
