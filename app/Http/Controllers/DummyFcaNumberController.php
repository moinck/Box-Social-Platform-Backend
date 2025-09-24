<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\DummyFcaNumber;
use App\Models\FcaNumbers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DummyFcaNumberController extends Controller
{
    /** Dummy FCA Number List */
    public function index(Request $request) 
    {
        try {
            
            if ($request->ajax()) {

                $fcaNumber = DummyFcaNumber::get();

                return DataTables::of($fcaNumber)
                    ->addIndexColumn()
                    ->addColumn('fca_number', function ($fcaNumber) {
                        return $fcaNumber->fca_number;
                    })
                    ->addColumn('company_name', function ($fcaNumber) {
                        return $fcaNumber->company_name;
                    })
                    ->addColumn('user', function ($fcaNumber) {
                        return isset($fcaNumber->user) ? $fcaNumber->user->first_name . " " . $fcaNumber->user->last_name ." (". $fcaNumber->user->email .")" : '-';
                    })
                    ->addColumn('action', function ($fcaNumber) {
                        $fcaNumberId = Helpers::encrypt($fcaNumber->id);
                        return '
                            <a href="javascript:;" title="edit fca number" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-fca-number-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-fca-number-id="' . $fcaNumberId . '"><i class="ri-edit-box-line"></i></a>
                            <a href="javascript:;" title="delete fca number" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-fca-number-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-fca-number-id="' . $fcaNumberId . '"><i class="ri-delete-bin-line"></i></a>
                        ';
                    })
                    ->rawColumns(['fca_number', 'company_name', 'user', 'action'])
                    ->make(true);

            }

            return view("content.pages.dummy-fca-number.index");

        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error','Something went wrong.');
        }
    }

    /** Store Dummy FCA Number */
    public function store(Request $request)
    {

        $request->validate([
            'fca_number' => [
                'required',
                'numeric',
                'digits_between:6,7', // ensures at least 6 digits, up to 20
                Rule::unique('users', 'fca_number'),
                Rule::unique('fca_numbers', 'fca_number'),
                Rule::unique('dummy_fca_numbers', 'fca_number'),
            ],
            'company_name' => 'required|string|max:255',
        ]);

        $headers = [
            'x-auth-email' => config('app.FCA_Auth_EMAIL'),
            'x-auth-key' => config('app.FCA_Auth_KEY'),
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->get('https://register.fca.org.uk/services/V0.1/Firm/' . $request->fca_number);
        $data = $response->json();

        if (!empty($data['Data'][0]["Name"])) {
            return response()->json([
                'success' => false,
                'message' => 'Already company registered with this FCA number.'
            ]);
        }

        $fca_number = new DummyFcaNumber();
        $fca_number->fca_number = $request->fca_number;
        $fca_number->company_name = $request->company_name;
        $fca_number->save();

        return response()->json([
            'success' => true,
            'message' => 'Dummy FCA number created successfully.'
        ]);
    }

    /** Edit Dummy FCA Number */
    public function edit($id)
    {
        $fcaNumberId = Helpers::decrypt($id);
        $fcaNumber = DummyFcaNumber::find($fcaNumberId);
        if ($fcaNumber) {
            return response()->json([
                'success' => true,
                'data' => $fcaNumber
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'FCA number not found.'
            ]);
        }
    }

    /** Update Dummy FCA Number */
    public function update(Request $request)
    {

        $fcaNumberId = Helpers::decrypt($request->edit_fca_number_id);

        $request->validate([
            'edit_fca_number' => [
                'required',
                'numeric',
                'digits_between:6,7', // ensures 6–7 digits only
                Rule::unique('users', 'fca_number'),
                Rule::unique('fca_numbers', 'fca_number'),
                Rule::unique('dummy_fca_numbers', 'fca_number')->ignore($fcaNumberId),
            ],
            'edit_company_name' => 'required|string|max:255',
        ]);
        $fca_number = $request->edit_fca_number;
        $company_name = $request->edit_company_name;

        $headers = [
            'x-auth-email' => config('app.FCA_Auth_EMAIL'),
            'x-auth-key' => config('app.FCA_Auth_KEY'),
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->get('https://register.fca.org.uk/services/V0.1/Firm/' . $request->edit_fca_number);
        $data = $response->json();

        if (!empty($data['Data'][0]["Name"])) {
            return response()->json([
                'success' => false,
                'message' => 'Already company registered with this FCA number.'
            ]);
        }

        $fcaNumber = DummyFcaNumber::find($fcaNumberId);
        $fcaNumber->fca_number = $fca_number;
        $fcaNumber->company_name = $company_name;
        $fcaNumber->save();

        return response()->json([
            'success' => true,
            'message' => $fcaNumber
        ]);
    }

    /** Delete Dummy FCA Number */
    public function destroy(Request $request)
    {
        $fca_number_id = Helpers::decrypt($request->fca_number_id);
        $dummyFcaNumber = DummyFcaNumber::find($fca_number_id);
        if ($dummyFcaNumber) {
            
            $dummyFcaNumber->delete();

            $fcaNumber = FcaNumbers::where('fca_number',$dummyFcaNumber->fca_number)->first();
            if ($fcaNumber) {
                $fcaNumber->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'FCA number deleted successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'FCA number not found.'
            ]);
        }
    }

}
