<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImageStockManagement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function uploadMissingImages()
    {
        $getImages = ImageStockManagement::where('is_expired',0)->get();
        
        foreach ($getImages as $image) {
            $url = $image->image_url; // assuming you store the original URL in DB
            $response = Http::get($url);

            if ($response->successful()) {

                $image->old_url = $image->image_url;
                $mime = $response->header('Content-Type'); // e.g. image/png

                // Map MIME to extension
                $map = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp',
                ];

                $extension = $map[$mime];
                $fileName  = $image->id.'.'.$extension;

                $path = Storage::disk('digitalocean')->put("local/stock_images/".$fileName, $response->body(), 'public');
                if($path){
                    $image->image_url = Storage::disk('digitalocean')->url("local/stock_images/".$fileName);
                    $image->is_expired = 1;
                    $image->save();
                }else{
                    $image->is_expired = 3;
                    $image->save();
                }
                
               
            }else{
                $image->is_expired = 2;
                $image->save();
            }

            return $image->image_url;
        }

        return response()->json(['message' => 'All images processed successfully!']);

    }
}
