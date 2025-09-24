<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\YoutubeVideoLink;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class YoutubeVideoController extends Controller
{
    /** List of Youtube Video Link */
    public function index(Request $request)
    {
        try {

            if ($request->ajax()) {
                $videoLinks = YoutubeVideoLink::get();

                return DataTables::of($videoLinks)
                    ->addIndexColumn()
                    ->addColumn('title', function ($videoLinks) {
                        return $videoLinks->title;
                    })
                    ->addColumn('image_url', function ($videoLinks) {
                        return '<a href="' . $videoLinks->link . '" target="_blank">
                            <img src="' . $videoLinks->image_url . '" alt="Image" width="120">
                        </a><br><br><a href="'.$videoLinks->link.'" style="color:black;">'.$videoLinks->link.'</a>';
                    })
                    ->addColumn('video_link', function ($videoLinks) {
                        $link = $videoLinks->link; // direct YouTube link from DB
                        $videoId = null;

                        if (strpos($link, 'youtube.com/watch?v=') !== false) {
                            // normal YouTube link
                            parse_str(parse_url($link, PHP_URL_QUERY), $params);
                            $videoId = $params['v'] ?? null;
                        } elseif (strpos($link, 'youtu.be/') !== false) {
                            // short YouTube link
                            $videoId = basename(parse_url($link, PHP_URL_PATH));
                        }

                        if ($videoId) {
                            return '<iframe width="120" height="100" 
                                        src="https://www.youtube.com/embed/' . $videoId . '" 
                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                    </iframe></br>';
                        }

                        return 'Invalid YouTube Link';
                    })
                    ->addColumn('status', function ($videoLinks) {
                        $status = $videoLinks->is_active == true ? 'checked' : '';

                        $title = '';
                        if ($videoLinks->is_active == true) {
                            $title = 'Click To Disable Video';
                        } else {
                            $title = 'Click To Enable Video';
                        }

                        $videoLinkId = Helpers::encrypt($videoLinks->id);
                        return '<label class="switch">
                                    <input type="checkbox" class="switch-input" ' . $status . ' data-id="' . $videoLinkId . '" id="youtube-video-status">
                                    <span class="switch-toggle-slider" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $title . '">
                                        <span class="switch-on"></span>
                                        <span class="switch-off"></span>
                                    </span>
                                </label>';
                    })
                    ->addColumn('action', function ($videoLinks) {
                        $videoLinkId = Helpers::encrypt($videoLinks->id);
                        return '
                            <a href="javascript:;" title="edit video link" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-youtube-video-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-youtube-video-id="' . $videoLinkId . '"><i class="ri-edit-box-line"></i></a>
                            <a href="javascript:;" title="delete video link" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-youtube-video-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-youtube-video-id="' . $videoLinkId . '"><i class="ri-delete-bin-line"></i></a>
                        ';
                    })
                    ->rawColumns(['video_link', 'image_url', 'status', 'action'])
                    ->make(true);
            }

            return view('content.pages.youtube-video-link.index');

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with("error","Something went wrong.");
        }

    }

    /** Save Youtube Link */
    public function saveYoutubeVideo(Request $request)
    {
        DB::beginTransaction();
        try {
    
            $validator = Validator::make($request->all(),[
                'title' => 'required|string|max:255',
                'link' => 'required|string',
                'video_link_status' => 'required|string|in:active,inactive',
                'image' => 'required_if:is_image_exists,0|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $video_link_id = Helpers::decrypt($request->video_link_id);
            $title = $request->title;
            $link = $request->link;

            $videoData = "";
            $video_link_id = "";
            if ($request->video_link_id) {
                $video_link_id = Helpers::decrypt($request->video_link_id);
                $videoData = YoutubeVideoLink::find($video_link_id);
            }

            $imageUrl = $videoData ? $videoData->image_url : null;
            if (isset($request->image) && $request->file('image') ) {
                $prefix = 'youtube';
                $imageUrl = Helpers::uploadImage($prefix,$request->file('image'),'images/youtube');

                if($videoData) {
                    if($videoData->image_url) {
                        Helpers::deleteImage($videoData->image_url);
                    }
                }
            }

            YoutubeVideoLink::updateOrCreate(
                [
                    'id' => $video_link_id
                ],[
                    'title' => $title,
                    'link' => $link,
                    'image_url' => $imageUrl,
                    'is_active' => $request->video_link_status == 'active' ? 1 : 0,
                ]
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Video link save successfully.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ]);
        }
    }

    /** Edit Youtube Video Link */
    public function editYoutubeVideo(Request $request,$id)
    {
        try {
            $videoLinkId = Helpers::decrypt($id);
            $videoLink = YoutubeVideoLink::find($videoLinkId);

            if ($videoLink) {
                return response()->json([
                    'success' => true,
                    'message' => 'Video link fetched successfully.',
                    'data'    => $videoLink
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Video link not found.'
            ], 404);

        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    /** Delete Youtube Video Link */
    public function deleteYoutubeVideo(Request $request)
    {
        DB::beginTransaction();
        try {

            $videoLinkId = Helpers::decrypt($request->video_link_id);
            $videoLink = YoutubeVideoLink::where('id', $videoLinkId)->first();

            if($videoLink) {

                if($videoLink->image_url) {
                    Helpers::deleteImage($videoLink->image_url);
                }

                $videoLink->delete();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Video link deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Video link not found.'
                ]);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Somehting went wrong.'
            ]);
        }
    }

    /** Change status Youtube Video Link */
    public function changeStatus(Request $request)
    {
        DB::beginTransaction();
        try {

            $videoLinkId = Helpers::decrypt($request->id);
            $videoLink = YoutubeVideoLink::where('id', $videoLinkId)->first();

            if($videoLink) {

                $videoLink->is_active = $videoLink->is_active == 1 ? 0 : 1;
                $videoLink->save();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Video link status updated successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Video link not found.'
                ]);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ]);
        }
    }
}
