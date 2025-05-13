<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageStockManagement extends Model
{
    protected $table = 'image_stock_management';

    protected $fillable = [
        'image_url',
        'tag_name',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
