<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Categories extends Model
{
    protected $table = 'categories';
    
    protected $fillable = [
        'name',
        'image',
        'description',
        'status',
        'is_comming_soon',
        'custom_label',
    ];

    /** Boot method on clear cache */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::created(function ($user) {
    //         Cache::forget('categories_list');
    //     });

    //     static::updated(function ($user) {
    //         Cache::forget('categories_list');
    //     });

    //     static::deleted(function ($user) {
    //         Cache::forget('categories_list');
    //     });
    // }

    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    /**
     * get List Of Active Categoey
     * @return \Illuminate\Database\Eloquent\Collection<int, Categories>
     */
    public static function getActiveCategoeyList()
    {
        return Categories::select(['id', 'name', 'is_comming_soon'])->where(function ($query) {
            $query->where('status', true)
                ->where('parent_id', null);
                // ->where('is_comming_soon', false);
        })->orderBy('name', 'asc')->get();
    }
}
