<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageStockManagement extends Model
{
    protected $fillable = [
        'image_url',
        'tag_name',
    ];
}
