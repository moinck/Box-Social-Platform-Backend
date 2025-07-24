<?php

namespace App\Http\Controllers;

use App\Models\IconManagement;
use Illuminate\Http\Request;

class IconManagementController extends Controller
{
    public function index()
    {
        $savedTagNames = IconManagement::select('tag_name')->pluck('tag_name')->unique()->toArray();
        $savedIconsCount = IconManagement::count();
        return view('content.pages.icon-management.index', compact('savedTagNames', 'savedIconsCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'selectIcons' => 'required',
            'custom_tag_name' => 'required',
        ]);
        $allIcons = $request->selectIcons;
        $customTagName = $request->custom_tag_name;
        foreach ($allIcons as $key => $value) {
            IconManagement::updateOrCreate(
                [
                    'icon_url' => $value
                ],
                [
                    'tag_name' => $customTagName,
                    'icon_search_input' => $request->icon_search_input ?? null
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Icons saved successfully',
            'savedIconsCount' => IconManagement::count()
        ]);
    }

    public function getSavedIcon(Request $request)
    {
        $icons = IconManagement::select('id','icon_url', 'tag_name')
            ->when($request->filterTag, function ($query) use ($request) {
                $query->where('tag_name','LIKE',"%".$request->filterTag."%");
            })
            ->get();

        $savedTagNames = $icons->pluck('tag_name')->unique()->toArray();

        return response()->json([
            'success' => true,
            'data' => $icons,
            'savedIconsCount' => $icons->count(),
            'savedTagNames' => $savedTagNames
        ]);
    }

    public function getSavedTag(Request $request)
    {
        $savedTagNames = IconManagement::select('id','tag_name')
            ->pluck('tag_name')
            ->unique()
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $savedTagNames,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'icon_url' => 'required',
        ]);
        IconManagement::where('icon_url', $request->icon_url)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Icon deleted successfully',
            'savedIconsCount' => IconManagement::count()
        ]);
    }
}
