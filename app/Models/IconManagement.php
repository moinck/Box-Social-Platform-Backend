<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IconManagement extends Model
{
    protected $table = 'icon_management';
    protected $fillable = [
        'icon_name',
        'icon_url',
        'tag_name',
        'category',
    ];
}
