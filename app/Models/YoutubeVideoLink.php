<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeVideoLink extends Model
{
    protected $fillable = [
        'title',
        'link',
        'image_url',
        'is_active',
    ];
}
