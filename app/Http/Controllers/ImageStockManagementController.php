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
        $savedImagesCount = ImageStockManagement::myImageCount();

        $savedImageTopics = ImageStockManagement::where('user_id', auth()->user()->id)
            ->latest()
            ->pluck('tag_name')
            ->unique()
            ->toArray();
       
        return view('content.pages.pages-image-stock-management', compact('topics', 'savedImagesCount', 'savedImageTopics'));
    }

    public function getSavedTopics()
    {
        $savedImageTopics = ImageStockManagement::where('user_id', auth()->user()->id)
            ->latest()
            ->pluck('tag_name')
            ->unique()
            ->toArray();
        
        return response()->json([
            'success' => true,
            'data' => $savedImageTopics
        ]);
    }

    public function imagesStore(Request $request)
    {
        $request->validate([
            'selectImages' => 'required|unique:image_stock_management,image_url',
            'custom_tag_name' => 'required',
        ],[
            'selectImages.required' => 'Please select images',
            'selectImages.unique' => 'Some of Selected image already exists. please select different images',
            'custom_tag_name.required' => 'Please select tag name',
        ]);

        $selectImages = $request->selectImages;
        if (!empty($selectImages)) {
            foreach ($selectImages as $key => $value) {
                $imge = ImageStockManagement::updateOrCreate(
                    [
                        'image_url' => $value
                    ], 
                    [
                        'tag_name' => $request->custom_tag_name,
                        'user_id' => auth()->user()->id
                    ]
                );
            }

            // update saved images count
            $savedImagesCount = ImageStockManagement::where('user_id', auth()->user()->id)->count() ?? 0;
            return response()->json([
                'success' => true,
                'message' => 'Images saved successfully',
                'savedImagesCount' => $savedImagesCount
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No images selected'
            ]);
        }

    }

    public function GetImages(Request $request)
    {
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
    
            // check the status code
            if ($httpStatus == 200) {
                return [
                    'success' => true,
                    'message' => 'Request successful',
                    'data' => json_decode($response, true)
                ];
            } else {
                $returndata =   [
                    "page" => 0,
                    "per_page" => 0,
                    "photos" => [],
                    "total_results" => 0,
                ];
                return [
                    'success' => false,
                    'message' => 'Request failed, Please try different keyword.',
                    'data' => $returndata
                ];
            }
        } else {
            $url = "https://pixabay.com/api/?key=".env('PIXABAY')."&q=".urlencode($request->type)."&image_type=photo&pretty=true&page=$request->page&per_page=100";

            // Initialize cURL
            $ch = curl_init($url);
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            // Execute cURL request
            $response = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // Close cURL session
            curl_close($ch);
    
            // check the status code
            if ($httpStatus != 200) {
                $returndata =   [
                    "total" => 0,
                    "totalHits" => 0,
                    "hits" => []
                ];
                return [
                    'success' => false,
                    'message' => 'Request failed, Please try different keyword.',
                    'data' => $returndata
                ];
            } else {
                // Decode JSON response
                $data = json_decode($response, true);
                return [
                    'success' => true,
                    'message' => 'Request successful',
                    'data' => $data
                ];
            }

        }
    }
    

    public function OldsavedImages(Request $request)
    {
        $images = ImageStockManagement::where('user_id', auth()->user()->id)
        ->when($request->selectedTopic != null && $request->selectedTopic != 0, function ($query) use ($request) {
            return $query->where('tag_name', $request->selectedTopic);
        })
        ->latest()->get()
        ->map(function ($image) {
            return [
                'id' => $image->id,
                'tag_name' => $image->tag_name,
                'image_url' => $image->image_url,
                'image_exists' => pathinfo($image->image_url, PATHINFO_EXTENSION) ? true : false,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }

    public function savedImages(Request $request)
    {
        $limit = $request->limit ?? 24; // default to 24 if not provided
        $page = $request->offset ?? 1; // treat 'offset' as page number
        $page = $page == 0 ? 1 : $page;
        $realOffset = ($page - 1) * $limit;

        $query = ImageStockManagement::where('user_id', auth()->user()->id)
            ->when($request->selectedTopic != null && $request->selectedTopic != 0, function ($query) use ($request) {
                return $query->where('tag_name', $request->selectedTopic);
            })
            ->latest();

        // Get total count for pagination
        $totalImages = $query->count();
        
        $images = $query->offset($realOffset)
            ->limit($limit)
            ->get()
            ->map(function ($image) {
                return [
                    'id' => $image->id,
                    'tag_name' => $image->tag_name,
                    'image_url' => $image->image_url,
                    'image_exists' => pathinfo($image->image_url, PATHINFO_EXTENSION) ? true : false,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $images,
            'pagination' => [
                'current_page' => $page,
                'total_images' => $totalImages,
                'per_page' => $limit,
                'has_more' => ($realOffset + $limit) < $totalImages
            ]
        ]);
    }

    // delete saved images
    public function deleteSavedImages(Request $request)
    {
        $imageIds = $request->image_ids;

        if (!empty($imageIds)) {
            ImageStockManagement::whereIn('id', $imageIds)->delete();
            $savedImagesCount = ImageStockManagement::where('user_id', auth()->user()->id)->count() ?? 0;
            return response()->json([
                'success' => true,
                'message' => 'Images deleted successfully',
                'savedImagesCount' => $savedImagesCount
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No images selected'
            ]);
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
