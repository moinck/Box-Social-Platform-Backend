<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TermsAndConditionController extends Controller
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
        $TermsAndCondition = TermsAndCondition::all();

        return DataTables::of($TermsAndCondition)
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

                $deleteBtn = '<a href="javascript:;" title="delete privacy policy" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-privacy-policy-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-privacy-policy-id="' . $id . '"><i class="ri-delete-bin-line"></i></a>';

                return '
                    <a href="' . $editUrl . '" title="edit privacy policy" class="btn btn-sm btn-text-secondary rounded-pill btn-icon"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"><i class="ri-edit-box-line"></i></a>
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

        TermsAndCondition::create([
            'title' => $request->title,
            'description' => $request->privacy_policy_description,
        ]);

        return redirect()->route('privacy-policy')->with('success', 'Privacy Policy created successfully');
    }

    public function edit($id)
    {
        $encryptedEditId = $id;
        $id = Helpers::decrypt($id);
        $TermsAndCondition = TermsAndCondition::find($id);

        return view('content.pages.privacy-policy.edit', compact('TermsAndCondition', 'encryptedEditId'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'privacy_policy_edit_description' => 'required',
        ]);

        $id = Helpers::decrypt($request->privacy_policy_id);

        TermsAndCondition::find($id)->update([
            'title' => $request->title,
            'description' => $request->privacy_policy_edit_description,
        ]);

        return redirect()->route('privacy-policy')->with('success', 'Privacy Policy updated successfully');
    }

    public function destroy(Request $request)
    {
        $id = Helpers::decrypt($request->privacy_policy_id);
        $TermsAndCondition = TermsAndCondition::find($id);
        $TermsAndCondition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Privacy Policy deleted successfully',
        ]);
    }
}
