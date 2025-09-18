<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
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

        /** Activity Log */
        Helpers::activityLog([
            'title' => "Icon Saved",
            'description' => "Admin Panel: Icon Saved. Tag Name: ". $customTagName,
            'url' => route('icon-management.store')
        ]);

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
            ->latest()
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

    public function deleteSavedIcon(Request $request)
    {
        $request->validate([
            'deleteIconIds' => 'required',
        ]);
        $iconManagement = IconManagement::whereIn('id',$request->deleteIconIds)->get();
        $iconUrls = collect($iconManagement)->pluck('icon_url')->toArray();
        IconManagement::whereIn('id', $request->deleteIconIds)->delete();

        /** Activity Log */
        Helpers::activityLog([
            'title' => "Delete Icons",
            'description' => "Admin Panel: Delete Icons. Icons URL: (". implode(', ',$iconUrls) . ")",
            'url' => route('icon-management.delete.saved-icon')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Icons deleted successfully',
            'savedIconsCount' => IconManagement::count()
        ]);
    }
}
