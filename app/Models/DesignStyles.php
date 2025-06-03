<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignStyles extends Model
{
    protected $table = 'design_styles';

    protected $fillable = [
        'name',
        'image',
    ];
}
