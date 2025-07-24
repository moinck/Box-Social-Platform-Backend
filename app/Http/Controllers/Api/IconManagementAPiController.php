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
        $icons = IconManagement::select('icon_url', 'tag_name')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('tag_name','LIKE',"%".$searchQuery."%");
            })
            ->get();

        return $this->success($icons, 'Icons list fetched successfully');
    }
}
