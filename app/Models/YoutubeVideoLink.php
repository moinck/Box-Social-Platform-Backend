<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeVideoLink extends Model
{
    protected $fillable = [
        'title',
        'link',
        'is_active',
    ];
}
