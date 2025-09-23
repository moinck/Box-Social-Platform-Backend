<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DummyFcaNumber extends Model
{
    protected $fillable = [
        'user_id',
        'fca_number',
        'company_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
