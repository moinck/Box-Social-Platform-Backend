<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTemplate extends Model
{
    protected $table = 'post_templates';

    protected $fillable = [
        'category_id',
        'template_image',
        'template_name',
        'template_data',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }
}
