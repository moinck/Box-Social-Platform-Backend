<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandKit extends Model
{
    protected $table = 'brand_kits';

    protected $fillable = [
        'brand_kits_id',
        'social_media_icon',
        'show_address_on_post',
        'design_style_id',
        'base64_logo',
        'warning_title',
        'warning_message',
        'logo',
    ];

    public function designStyle()
    {
        return $this->belongsTo(DesignStyles::class);
    }

    public function socialMedia()
    {
        return $this->belongsTo(SocialMedia::class, 'id', 'brand_kits_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
