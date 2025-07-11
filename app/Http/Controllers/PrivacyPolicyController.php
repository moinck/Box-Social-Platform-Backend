<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            ->addColumn('created_date', function ($row) {
                return Helpers::dateFormate($row->created_at);
            })
            ->addColumn('updated_date', function ($row) {
                return Helpers::dateFormate($row->updated_at);
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
            ->rawColumns(['title', 'created_date', 'updated_date', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'privacy_policy_description' => 'required',
        ]);

        PrivacyPolicy::create([
            'title' => $request->title,
            'description' => $request->privacy_policy_description,
        ]);

        return redirect()->route('privacy-policy')->with('success', 'Privacy Policy created successfully');
    }

    public function edit($id)
    {
        $encryptedEditId = $id;
        $id = Helpers::decrypt($id);
        $privacyPolicy = PrivacyPolicy::find($id);

        return view('content.pages.privacy-policy.edit', compact('privacyPolicy', 'encryptedEditId'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'privacy_policy_edit_description' => 'required',
        ]);

        $id = Helpers::decrypt($request->privacy_policy_id);

        PrivacyPolicy::find($id)->update([
            'title' => $request->title,
            'description' => $request->privacy_policy_edit_description,
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
