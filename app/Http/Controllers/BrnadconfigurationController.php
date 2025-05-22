<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\BrandKit;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BrnadconfigurationController extends Controller
{
    public function index()
    {
        return view('content.pages.brand-configuration.index');
    }

    public function dataTable()
    {
        $data = BrandKit::get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('logo', function ($data) {
                return '<img src="'.asset($data->logo).'" alt="'.$data->name.'" class="img-fluid br-1" width="100" height="100">';
            })
            ->addColumn('company_name', function ($data) {
                return $data->company_name;
            })
            ->addColumn('email', function ($data) {
                return $data->email;
            })
            ->addColumn('phone', function ($data) {
                return $data->phone;
            })
            ->addColumn('created_date', function ($data) {
                return $data->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('action', function ($data) {
                $id = Helpers::encrypt($data->id);
                $editRoute = route('brand-configuration.edit', $id);

                return '
                    <a href="'.$editRoute.'" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:void(0);" class="btn btn-sm btn-text-danger rounded-pill btn-icon" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['logo','company_name','email','phone','created_date','action'])
            ->make(true);
    }

    public function edit($id)
    {
        $id = Helpers::decrypt($id);
        $brandKit = BrandKit::find($id);
        return view('content.pages.brand-configuration.edit', compact('brandKit'));
    }

    public function update(Request $request)
    {
        $id = Helpers::decrypt($request->id);
        $data = BrandKit::find($id);
        $data->update($request->except('_token','id'));
        return response()->json([
            'success' => true,
            'message' => 'Brand Kit updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $id = Helpers::decrypt($id);
        $brandKit = BrandKit::find($id);
        $brandKit->delete();
        return response()->json([
            'success' => true,
            'message' => 'Brand Kit deleted successfully'
        ]);
    }
}
