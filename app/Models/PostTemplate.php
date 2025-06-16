<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTemplate extends Model
{
    protected $table = 'post_templates';

    protected $fillable = [
        'category_id',
        'design_style_id',
        'template_image',
        'template_name',
        'template_data',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class,'category_id','id');
    }

    public function designStyle()
    {
        return $this->belongsTo(DesignStyles::class,'design_style_id','id');
    }
}
