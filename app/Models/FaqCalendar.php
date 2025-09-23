<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqCalendar extends Model
{
    protected $fillable = [
        'year',
        'month',
        'image_url'
    ];
}
