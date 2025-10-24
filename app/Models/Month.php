<?php

namespace App\Models;

use App\Models\PostContent;
use Illuminate\Database\Eloquent\Model;

class Month extends Model
{
   protected $table = 'months';
   
   public function postContents()
   {
      return $this->belongsToMany(PostContent::class, 'post_content_months', 'month_id', 'post_content_id')->withTimestamps();
   }
}
