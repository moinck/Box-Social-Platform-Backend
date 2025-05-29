<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostContent extends Model
{
    protected $table = 'post_contents';

    protected $fillable = [
        'category_id',
        'title',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class,'category_id','id');
    }
}
