<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandKit extends Model
{
    protected $fillable = [
        'brand_kits_id',
        'social_media_icon'
    ];

    public function socialMedia()
{
    return $this->hasOne(SocialMedia::class);
}



}
