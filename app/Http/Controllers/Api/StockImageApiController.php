<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ImageStockManagement;
use App\ResponseTrait;
use Illuminate\Http\Request;

class StockImageApiController extends Controller
{
    use ResponseTrait;

    public function get(Request $request)
    {
        $imageData = ImageStockManagement::get();

        $returnData = [];
        foreach ($imageData as $key => $value) {
            $returnData[] = [
                'id' => Helpers::encrypt($value->id),
                'image_url' => $value->image_url,
                'tag_name' => $value->tag_name,
            ];
        }

        return $this->success($returnData, 'Stock Image Fetch successfully');
    }
}
