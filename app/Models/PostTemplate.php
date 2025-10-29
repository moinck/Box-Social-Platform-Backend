<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTemplate extends Model
{
    protected $table = 'post_templates3';

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'design_style_id',
        'template_image',
        'template_name',
        'post_content_id',
        'template_data',
        'status',
        'template_url',
        'uploaded_at',
        'is_uploaded',
        'category_id_json',
        'sub_category_id_json',
    ];
    
    protected $appends = ['related_posts'];

    public function category()
    {
        return $this->belongsTo(Categories::class,'category_id','id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Categories::class,'sub_category_id','id');
    }

    public function designStyle()
    {
        return $this->belongsTo(DesignStyles::class,'design_style_id','id');
    }

    public function postContent()
    {
        return $this->belongsTo(PostContent::class,'post_content_id','id');
    }

    public function getRelatedPostsAttribute()
    {
        $categoryIds = json_decode($this->category_id_json, true) ?? [];
        $subCategoryIds = json_decode($this->sub_category_id_json, true) ?? [];

        $posts = \App\Models\PostContent::whereIn('category_id', $categoryIds)
                ->whereIn('sub_category_id', $subCategoryIds)
                ->get();

        return $posts;
    }
}
