<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\BrandKit;
use App\Models\SocialMedia;
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
                return '<img src="'.asset($data->logo).'" alt="'.$data->name.'" class="br-1" width="100" height="100">';
            })
            ->addColumn('user', function ($data) {
                return $data->user->first_name.' '.$data->user->last_name;
            })
            ->addColumn('company_name', function ($data) {
                return $data->company_name;
            })
            ->addColumn('email', function ($data) {
                return $data->email;
            })
            ->addColumn('created_date', function ($data) {
                $date = Helpers::dateFormate($data->created_at);
                return $date;
            })
            ->addColumn('updated_date', function ($data) {
                $date = Helpers::dateFormate($data->updated_at);
                return $date;
            })
            ->addColumn('action', function ($data) {
                $id = Helpers::encrypt($data->id);
                $editRoute = route('brand-configuration.edit', $id);
                $showRoute = route('brand-configuration.show', $id);

                return '
                    <a href="'.$showRoute.'" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Show">
                        <i class="ri-eye-line"></i>
                    </a>
                    <a href="javascript:void(0);" data-brand-kit-id="'.$id.'" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-brand-kit-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </a>
                ';
            })
            ->rawColumns(['logo','user','company_name','email','created_date','updated_date','action'])
            ->make(true);
    }

    public function show($id)
    {
        $id = Helpers::decrypt($id);
        $brandKit = BrandKit::with('user:id,first_name,last_name,role,company_name,email')->find($id);
        $socialMediaObj = SocialMedia::where('brand_kits_id',$brandKit->id)->first();
        $socialMedia = [];
        if(!empty($socialMediaObj)){
            $socialMedia= json_decode($socialMediaObj->social_media_icon);
        }
        $fontsData = json_decode($brandKit->font,true);
        return view('content.pages.brand-configuration.show', compact('brandKit','socialMedia','fontsData'));
    }

    public function edit($id)
    {
        $id = Helpers::decrypt($id);
        $brandKit = BrandKit::find($id);
        // dd(json_decode($brandKit->color));
        $socialMediaObj = SocialMedia::where('brand_kits_id',$brandKit->id)->first();

        $socialMedia = [];
        if(!empty($socialMediaObj)){
            $socialMedia= json_decode($socialMediaObj->social_media_icon);
            // dd($socialMedia);
        }
        return view('content.pages.brand-configuration.edit', compact('brandKit','socialMedia'));
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

    public function destroy(Request $request)
    {
        $id = Helpers::decrypt($request->brand_kit_id);
        $brandKit = BrandKit::find($id);
        if (!empty($brandKit)) {
            // delete it's social media icons
            $socialMedia = SocialMedia::where('brand_kits_id',$brandKit->id)->first();
            if(!empty($socialMedia)){
                $socialMedia->delete();
            }
            // delete logo with data
            Helpers::deleteImage($brandKit->logo);
            $brandKit->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Brand Kit deleted successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Brand Kit not found'
            ]);
        }
    }
}
