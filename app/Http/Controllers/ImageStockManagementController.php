<?php

namespace App\Http\Controllers;

use App\Models\ImageStockManagement;
use Illuminate\Http\Request;

class ImageStockManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = config('image_topics');
       
        return view('content.pages.pages-image-stock-management', compact('topics'));
    }

    public function imagesStore(Request $request){
        foreach ($request->selectImages as $key => $value) {
            $imge = ImageStockManagement::updateOrCreate(
                ['image_url' => $value], 
                ['tag_name' => $request->select2Icons]
            );
        }
    }

    public function GetImages(Request $request){
        
        $url = "https://pixabay.com/api/?key=".env('PIXABAY')."&q=".urlencode($request->type)."&image_type=photo&pretty=true&page=$request->page&per_page=100";
        
        dd(env('PEXELS'));
        if($request->api_type == "pexels"){
            $url = "https://api.pexels.com/v1/search?query=".urlencode($request->type)."&per_page=100&page=".$request->page;
            
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization:  ".env('PEXELS')
            ]);

            $response = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpStatus == 200) {
                return json_decode($response, true);
            } else {
                return [
                    'error' => 'Request failed',
                    'status' => $httpStatus
                ];
            }
        }else{
            // Initialize cURL
            $ch = curl_init($url);

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);

            // Execute cURL request
            $response = curl_exec($ch);
            dd($response);

            // Check for errors
            if (curl_errno($ch)) {
                echo "cURL Error: " . curl_error($ch);
            } else {
                // Decode JSON response
                $data = json_decode($response, true);
               
               return $data;
            }

            // Close cURL session
            curl_close($ch);
    }
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
