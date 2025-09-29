<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact_us';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'email_subject',
        'feedback_reply',
        'is_replied',
        'ip_address',
        'user_agent',
    ];
}
