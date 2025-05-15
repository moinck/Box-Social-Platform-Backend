<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageStockManagement extends Model
{
    protected $table = 'image_stock_management';

    protected $fillable = [
        'image_url',
        'tag_name',
        'user_id',
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
}   
