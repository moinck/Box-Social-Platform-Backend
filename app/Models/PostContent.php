<?php

namespace App\Models;

use App\Models\Month;
use Illuminate\Database\Eloquent\Model;

class PostContent extends Model
{
    protected $table = 'post_contents';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'warning_message',
        'sub_category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class,'category_id','id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Categories::class,'sub_category_id','id');
    }
    
    public function months()
    {
        return $this->belongsToMany(Month::class, 'post_content_months', 'post_content_id', 'month_id')->withTimestamps();
    }
}
