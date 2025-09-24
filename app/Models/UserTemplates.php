<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTemplates extends Model
{
    protected $table = 'user_templates';

    protected $fillable = [
        'user_id',
        'template_id',
        'category_id',
        'template_name',
        'template_image',
        'template_data',
        'template_url',
        'uploaded_at',
        'is_uploaded',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(PostTemplate::class);
    }

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }
}
