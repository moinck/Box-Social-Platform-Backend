<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    protected $fillable = [
        'brand_kits_id',
        'social_media_icon'
    ];

    protected $table = 'social_media';
}
