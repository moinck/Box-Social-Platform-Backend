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

    public function store(Request $request){

       
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
            'template_image' => 'nullable|string',
            'template_data' => 'required', // or 'array' if JSON
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }
        

        $tempObj = new PostTemplate();
        $tempObj->category_id = $request->category_id;
        $tempObj->template_image =  $request->template_image;
        $tempObj->template_data = json_encode($request->template_data) ;
        $tempObj->save();

        $data =  [
            "id" => Helpers::encrypt($tempObj->id),
        ];
        return $this->success($data, 'Template create successfully');

    }

    public function getTemplate(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }
        

        $tempObj = PostTemplate::find(Helpers::decrypt($request->id))->first();

        $data =  [
            "template_data" => json_decode($tempObj->template_data),
            'id'=>$request->id
        ];
        
        if(!empty($tempObj)){
            return $this->success($data, 'Template Fetch successfully');
        }
    }

    public function getTemplateList(Request $request){
        
        $tempObj = PostTemplate::where('status',1)->get();

        $tempData = [];
        foreach ($tempObj as $key => $t) {
            $tempData[] = [
                'id'=>Helpers::encrypt( $t->id),
                'template_data'=>json_decode($t->template_data),
            ];
        }
        $data = $tempData;
        
        if(!empty($tempObj)){
            return $this->success($data, 'Template Fetch successfully');
        }
    }
}
