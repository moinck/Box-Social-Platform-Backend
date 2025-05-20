<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'categories';
    
    protected $fillable = [
        'name',
        'image',
        'description',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }
}
