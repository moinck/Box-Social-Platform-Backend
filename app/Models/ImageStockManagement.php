<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ImageStockManagement extends Model
{
    protected $table = 'image_stock_management';

    protected $fillable = [
        'image_url',
        'tag_name',
        'user_id',
        'is_expired',
        'old_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get image count of current login user
     * @return int
     */
    public static function myImageCount()
    {
        return ImageStockManagement::where('user_id', auth()->user()->id)->count() ?? 0;
    }

    public static function checkImageExists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $statusCode == 200;
    }

    /**
     * get image exists attribute
     * @return bool
     */
    public function getImageExistsAttribute()
    {
        return $this->checkImageExists($this->image_url);
    }

    /**
     * The "booted" method of the model.
     */
    // protected static function booted()
    // {
    //     // This runs BEFORE the record is deleted from database
    //     static::deleting(function ($image) {
    //         // Delete from cloud storage if cloud URL exists
    //         if (!empty($image->image_url)) {
    //             try {
    //                 Helpers::deleteImage($image->image_url);
    //             } catch (\Exception $e) {
    //                 Log::error("Failed to delete cloud image for ID {$image->id}: " . $e->getMessage());
    //                 return false;
    //             }
    //         }
    //     });
    // }
}   
