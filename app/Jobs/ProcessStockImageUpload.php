<?php

namespace App\Jobs;

use App\Models\ImageStockManagement;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessStockImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $imageId;

    public function __construct($imageId)
    {
        $this->imageId = $imageId;
    }

    public function handle()
    {
        $image = ImageStockManagement::find($this->imageId);
        $appType = config('app.env') ?? env('APP_ENV','local');
        
        if (!$image || $image->is_expired == 1) {
            return;
        }

        try {
            // Download image from external URL
            $response = Http::timeout(30)->get($image->image_url);
            
            if ($response->successful()) {
                // Get image extension from URL or response headers
                $extension = $this->getImageExtension($image->image_url, $response);
                $filename = 'stock_images_' . rand(1000, 9999) . '_' . time() . '.' . $extension;
                
                // Store to cloud storage
                $path =  $appType.'/images/stock_images/' . $filename;
                Storage::disk('digitalocean')->put($path, $response->body(),'public'); // or whatever cloud disk you use
                $uploadedUrl = Storage::disk('digitalocean')->url($path);
                
                if($uploadedUrl){                    
                    // Update database with new cloud URL
                    $image->update([
                        'old_url' => $image->image_url,
                        'image_url' => $uploadedUrl,
                        'is_expired' => 1,
                        'uploaded_at' => now()
                    ]);
                } else {
                    $image->update([
                        'is_expired' => 3,
                        'uploaded_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Log error and mark as failed
            Log::error('Image upload failed for ID: ' . $this->imageId . ' - ' . $e->getMessage());
            
            $image->update([
                'is_expired' => 2,
                'uploaded_at' => now()
            ]);
        }
    }

    private function getImageExtension($url, $response)
    {
        // Try to get from Content-Type header
        $contentType = $response->header('Content-Type');
        if ($contentType) {
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];
            
            if (isset($extensions[$contentType])) {
                return $extensions[$contentType];
            }
        }
        
        // Fallback to URL extension
        return pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    }
}
