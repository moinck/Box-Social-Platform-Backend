<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\IconManagement;

class IconManagementAPiController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        $searchQuery = $request->search;
        $limit = $request->limit ?? 25; // default to 25 if not provided
        $page = $request->offset ?? 1; // treat 'offset' as page number
        $realOffset = $request->offset;
        $totalIconCount = IconManagement::count();

        $icons = IconManagement::select('icon_url', 'tag_name')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('tag_name','LIKE',"%".$searchQuery."%");
            })
            ->latest()
            ->offset($realOffset)
            ->limit($limit)
            ->get();

        $iconTagNameList = IconManagement::select('tag_name')
            ->distinct()
            ->get()
            ->pluck('tag_name')
            ->toArray();

        $returnData = [];
        $returnData['limit'] = $limit;
        $returnData['total_icons'] = $totalIconCount;
        $returnData['page'] = $page;
        $returnData['icons'] = $icons;
        $returnData['icon_tag_name_list'] = $iconTagNameList;

        return $this->success($returnData, 'Icons list fetched successfully');
    }
}
