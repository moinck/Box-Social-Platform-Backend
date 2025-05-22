<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandKit extends Model
{
    protected $fillable = [
        'logo',
        'user_id',
        'company_name',
        'email',
        'address',
        'state',
        'phone',
        'country',
        'website',
        'postal_code',
        'show_email_on_post',
        'show_phone_number_on_post',
        'show_website_on_post',
        'design_style',

    ];

    public function socialMedia()
{
    return $this->hasOne(SocialMedia::class);
}



}
