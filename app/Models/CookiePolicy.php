<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookiePolicy extends Model
{
    protected $table = 'cookie_policies';
    
    protected $fillable = [
        'title',
        'description'
    ];
}
