<?php

namespace App\Http\Controllers;

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
            ->addColumn('address', function ($data) {
                return $data->address;
            })
            ->addColumn('website', function ($data) {
                return $data->website;
            })
            ->addColumn('action', function ($data) {
                return '<div class="btn-group" role="group" aria-label="Basic mixed styles example">
                            <button type="button" class="btn btn-primary">Primary</button>
                            <button type="button" class="btn btn-warning">Warning</button>
                            <button type="button" class="btn btn-danger">Danger</button>
                        </div>';
            })
            ->make(true);
    }
}
