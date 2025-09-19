<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcaNumbers extends Model
{
    protected $table = 'fca_numbers';

    protected $fillable = [
        'fca_number',
        'fca_name',
    ];
}
