<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        return view('content.pages.privacy-policy.index');
    }

    public function create()
    {
        return view('content.pages.privacy-policy.create');
    }

    public function dataTable()
    {
        $privacyPolicy = PrivacyPolicy::all();

        return DataTables::of($privacyPolicy)
            ->addIndexColumn()
            ->addColumn('title', function ($row) {
                return $row->title;
            })
            ->addColumn('description', function ($row) {
                return $row->description;
            })
            ->addColumn('created_date', function ($row) {
                return $row->created_at->format('Y-m-d');
            })
            ->addColumn('updated_date', function ($row) {
                return $row->updated_at->format('Y-m-d');
            })
            ->addColumn('action', function ($row) {
                $id = Helpers::encrypt($row->id);
                $editUrl = route('privacy-policy.edit', $id);

                return '
                    <a href="' . $editUrl . '" title="edit privacy policy" class="btn btn-sm btn-text-secondary rounded-pill btn-icon"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" title="delete privacy policy" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-privacy-policy-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-privacy-policy-id="' . $id . '"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['title', 'description', 'created_date', 'updated_date', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        PrivacyPolicy::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('privacy-policy')->with('success', 'Privacy Policy created successfully');
    }

    public function edit($id)
    {
        $id = Helpers::decrypt($id);
        $privacyPolicy = PrivacyPolicy::find($id);

        return view('content.pages.privacy-policy.edit', compact('privacyPolicy'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        PrivacyPolicy::find($request->privacy_policy_id)->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('privacy-policy')->with('success', 'Privacy Policy updated successfully');
    }

    public function destroy(Request $request)
    {
        $id = Helpers::decrypt($request->privacy_policy_id);
        $privacyPolicy = PrivacyPolicy::find($id);
        $privacyPolicy->delete();

        return redirect()->route('privacy-policy')->with('success', 'Privacy Policy deleted successfully');
    }
}
