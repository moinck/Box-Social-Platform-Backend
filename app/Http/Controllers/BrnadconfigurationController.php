<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\BrandKit;
use App\Models\PostTemplate;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BrnadconfigurationController extends Controller
{
    public function index()
    {
        return view('content.pages.brand-configuration.index');
    }

    public function dataTable()
    {
        $data = BrandKit::with('user:id,first_name,last_name,company_name,email')
            ->whereHas('user', function ($query) {
                $query->where('role', 'customer');
            })
            ->latest()->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('logo', function ($data) {
                return '<img src="' . asset($data->logo) . '" alt="' . $data->name . '" class="br-1" width="100" height="100">';
            })
            ->addColumn('user', function ($data) {
                $fullName = ($data->user->first_name ?? "") . " " . ($data->user->last_name ?? "");
                return $fullName;
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
                    <a href="' . $showRoute . '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Show">
                        <i class="ri-eye-line"></i>
                    </a>
                    <a href="javascript:void(0);" data-brand-kit-id="' . $id . '" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-brand-kit-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </a>
                ';
            })
            ->rawColumns(['logo', 'user', 'company_name', 'email', 'created_date', 'updated_date', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'brand_company_name' => 'required',
            'brand_email' => 'required',
            'brand_phone' => 'nullable',
            'brand_address' => 'nullable',
            'brand_state' => 'nullable',
            'brand_country' => 'nullable',
            'brand_postal_code' => 'nullable',
            'brand_website' => 'nullable',
            'show_email_on_post' => 'nullable|in:on',
            'show_phone_number_on_post' => 'nullable|in:on',
            'show_website_on_post' => 'nullable|in:on',
            'show_address_on_post' => 'nullable|in:on',
        ]);

        $brandKit = new BrandKit();
        $brandKit->user_id = $request->user_id;
        $brandKit->company_name = $request->brand_company_name;
        $brandKit->email = $request->brand_email;
        $brandKit->phone = $request->brand_phone;
        $brandKit->address = $request->brand_address;
        $brandKit->state = $request->brand_state;
        $brandKit->country = $request->brand_country;
        $brandKit->postal_code = $request->brand_postal_code;
        $brandKit->website = $request->brand_website;
        $brandKit->show_email_on_post = $request->show_email_on_post ? 1 : 0;
        $brandKit->show_phone_number_on_post = $request->show_phone_number_on_post ? 1 : 0;
        $brandKit->show_website_on_post = $request->show_website_on_post ? 1 : 0;
        $brandKit->show_address_on_post = $request->show_address_on_post ? 1 : 0;
        $brandKit->save();
        
        return redirect()->back()->with('success', 'Brand Kit created successfully');
    }

    public function show($id)
    {
        $id = Helpers::decrypt($id);
        $brandKit = BrandKit::with('user:id,first_name,last_name,role,company_name,email')->find($id);
        $socialMediaObj = SocialMedia::where('brand_kits_id', $brandKit->id)->first();
        $socialMedia = [];
        if (!empty($socialMediaObj)) {
            $socialMedia = json_decode($socialMediaObj->social_media_icon);
        }
        $fontsData = json_decode($brandKit->font, true);
        return view('content.pages.brand-configuration.show', compact('brandKit', 'socialMedia', 'fontsData'));
    }

    public function edit($id)
    {
        $id = Helpers::decrypt($id);
        $brandKit = BrandKit::find($id);
        // dd(json_decode($brandKit->color));
        $socialMediaObj = SocialMedia::where('brand_kits_id', $brandKit->id)->first();

        $socialMedia = [];
        if (!empty($socialMediaObj)) {
            $socialMedia = json_decode($socialMediaObj->social_media_icon);
        }
        return view('content.pages.brand-configuration.edit', compact('brandKit', 'socialMedia'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'brand_company_name' => 'required',
            'brand_email' => 'required',
            'brand_phone' => 'nullable',
            'brand_address' => 'nullable',
            'brand_state' => 'nullable',
            'brand_country' => 'nullable',
            'brand_postal_code' => 'nullable',
            'brand_website' => 'nullable',
            'show_email_on_post' => 'nullable|in:on',
            'show_phone_number_on_post' => 'nullable|in:on',
            'show_website_on_post' => 'nullable|in:on',
            'show_address_on_post' => 'nullable|in:on',
        ]);
        $brandKit = BrandKit::find($request->id);
        if (!empty($brandKit)) {
            $brandKit->company_name = $request->brand_company_name;
            $brandKit->email = $request->brand_email;
            $brandKit->phone = $request->brand_phone;
            $brandKit->address = $request->brand_address;
            $brandKit->state = $request->brand_state;
            $brandKit->country = $request->brand_country;
            $brandKit->postal_code = $request->brand_postal_code;
            $brandKit->website = $request->brand_website;
            $brandKit->show_email_on_post = $request->show_email_on_post ? 1 : 0;
            $brandKit->show_phone_number_on_post = $request->show_phone_number_on_post ? 1 : 0;
            $brandKit->show_website_on_post = $request->show_website_on_post ? 1 : 0;
            $brandKit->show_address_on_post = $request->show_address_on_post ? 1 : 0;
            $brandKit->update();

            return redirect()->back()->with('success', 'Brand Kit updated successfully');
        } else {
            return redirect()->back()->with('error', 'Brand Kit not found');
        }
    }

    public function destroy(Request $request)
    {
        $id = Helpers::decrypt($request->brand_kit_id);
        $brandKit = BrandKit::find($id);
        if (!empty($brandKit)) {
            // delete it's social media icons
            $socialMedia = SocialMedia::where('brand_kits_id', $brandKit->id)->first();
            if (!empty($socialMedia)) {
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


    public function updateJsonData()
    {
        DB::connection()->enableQueryLog();
        $start = microtime(true);
        $postTemplate = PostTemplate::find(1);

        $brandkit = BrandKit::find(7);

        $brandkitData = [
            'name' => $brandkit->user->first_name . ' ' . $brandkit->user->last_name,
            'email' => $brandkit->user->email,
            'phone' => $brandkit->phone,
            'company' => $brandkit->company_name,
            'address' => $brandkit->address,
            'website' => $brandkit->website,
            'brandkit_logo' => $brandkit->logo,
        ];

        try {
            $processedTemplate = Helpers::replaceFabricTemplateData($postTemplate->template_data, $brandkitData);
        } catch (\Exception $e) {
            // Helpers::sendErrorMailToDeveloper($e);
            dd($e);
        }
        $queryLog = DB::getQueryLog();
        $executionTime = $queryLog[0]['time'];
        $end = microtime(true);
        $TotalExecutionTime = round($end - $start, 4) . " seconds";

        return response()->json([
            'queryLog' => $queryLog,
            'executionTime' => $executionTime,
            'TotalExecutionTime' => $TotalExecutionTime,
            'test_time' => "Time: " . round(microtime(true) - $start, 4) . "s",
            'template' => $processedTemplate,
            'decode_template' => json_decode($processedTemplate)
        ]);
    }
}
