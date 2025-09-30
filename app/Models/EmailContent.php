<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailContent extends Model
{
    protected $table = 'email_contents';
    
    protected $fillable = [
        'title',
        'subject',
        'slug',
        'content'
    ];
}
