<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoStock extends Model
{
    protected $table = 'video_stocks';
    
    protected $fillable = [
        'user_id',
        'tag_name',
        'thumbnail_url',
        'video_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
