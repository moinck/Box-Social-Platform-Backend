<?php

namespace App\Http\Controllers;

use App\Models\VideoStock;
use Illuminate\Http\Request;

class VideoStockController extends Controller
{
    public function index()
    {
        $searchTopics = ['Nature', 'Places', 'Things', 'Activities','People','Rain','Sky','Money'];

        $data = [];
        $data['searchTopics'] = $searchTopics;
        $data['savedVideoCount'] = VideoStock::where('user_id', auth()->user()->id)->count() ?? 0;
        return view('content.pages.video-stocks.index', compact('data'));
    }

    public function GetVideos(Request $request)
    {
        $pixabeyAPI = env('PIXABAY');
        $pexelsAPI = env('PEXELS');

        $requestType = $request->api_type;

        $returnData = [];

        if ($requestType == 'pixabay') {
            // make curl request
            // https://pixabay.com/api/videos/?key=49984251-e73d8b5431d7a754a00b81205&q=rain&pretty=true&videos=medium

            $url = "https://pixabay.com/api/videos/?key=".$pixabeyAPI."&q=".urlencode($request->search_query)."&per_page=60&page=".$request->page."&pretty=true&videos=medium";

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo "cURL Error: " . curl_error($ch);
            } else {
                $data = json_decode($response, true);
                $returnData = [
                    'success' => true,
                    'data' => $data
                ];
            }

            curl_close($ch);

        }else{
            // make curl request
            // https://api.pexels.com/videos/search?query=rain&per_page=100&page=1
            
            $url = "https://api.pexels.com/videos/search?query=".urlencode($request->search_query)."&per_page=50&page=".$request->page."";
            
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization:  ".$pexelsAPI
            ]);

            $response = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpStatus == 200) {
                $returnData = [
                    'success' => true,
                    'data' => json_decode($response, true)
                ];
            } else {
                $returnData = [
                    'success' => false,
                    'status' => $httpStatus
                ];
            }
        }
        
        return response()->json($returnData);
    }

    public function getSavedVideos()
    {
        $videos = VideoStock::where('user_id', auth()->user()->id)->get();
        $returnData = [
            'success' => true,
            'savedVideosCount' => count($videos) ?? 0,
            'data' => $videos
        ];
        return response()->json($returnData);
    }

    public function store(Request $request)
    {
        $selectedVideos = json_decode($request->selectedVideos, true);
        if (!empty($selectedVideos)) {
            foreach ($selectedVideos as $value) {
                $imge = VideoStock::updateOrCreate(
                    [
                        'video_url' => $value['video_url']
                    ], 
                    [
                        'tag_name' => $request->video_tag_name,
                        'thumbnail_url' => $value['thumbnail_url'],
                        'user_id' => auth()->user()->id
                    ]
                );
            }

            // update saved images count
            $savedVideosCount = VideoStock::where('user_id', auth()->user()->id)->count() ?? 0;
            return response()->json([
                'success' => true,
                'message' => 'Videos saved successfully',
                'savedVideosCount' => $savedVideosCount
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No videos selected'
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $selectedVideos = $request->selectedVideos;
        if (!empty($selectedVideos)) {
            $videos = VideoStock::whereIn('id', $selectedVideos)->delete();

            // update saved images count
            $savedVideosCount = VideoStock::where('user_id', auth()->user()->id)->count() ?? 0;
            return response()->json([
                'success' => true,
                'message' => 'Videos deleted successfully',
                'savedVideosCount' => $savedVideosCount
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No videos selected'
            ]);
        }
    }
}
