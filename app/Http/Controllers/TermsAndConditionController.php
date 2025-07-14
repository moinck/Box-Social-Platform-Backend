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
        return view('content.pages.terms-condition.index');
    }

    public function create()
    {
        return view('content.pages.terms-condition.create');
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
                $editUrl = route('terms-and-condition.edit', $id);

                $deleteBtn = '<a href="javascript:;" title="delete privacy policy" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-terms-and-condition-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-terms-and-condition-id="' . $id . '"><i class="ri-delete-bin-line"></i></a>';

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
            'terms_condition_description' => 'required',
        ]);

        TermsAndCondition::create([
            'title' => $request->title,
            'description' => $request->terms_condition_description,
        ]);

        return redirect()->route('terms-and-condition')->with('success', 'Terms and Condition created successfully');
    }

    public function edit($id)
    {
        $encryptedEditId = $id;
        $id = Helpers::decrypt($id);
        $termsAndCondition = TermsAndCondition::find($id);

        return view('content.pages.terms-condition.edit', compact('termsAndCondition', 'encryptedEditId'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'terms_condition_edit_description' => 'required',
        ]);

        $id = Helpers::decrypt($request->terms_condition_id);

        TermsAndCondition::find($id)->update([
            'title' => $request->title,
            'description' => $request->terms_condition_edit_description,
        ]);

        return redirect()->route('terms-and-condition')->with('success', 'Terms and Condition updated successfully');
    }

    public function destroy(Request $request)
    {
        $id = Helpers::decrypt($request->terms_condition_id);
        $termsAndCondition = TermsAndCondition::find($id);
        $termsAndCondition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Terms and Condition deleted successfully',
        ]);
    }
}
