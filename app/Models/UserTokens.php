<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserTokens extends Model
{
    protected $table = 'user_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'type',
        'is_used',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isTokenExpired()
    {
        return $this->expires_at < now() ? true : false;
    }

    public function isTokenUsed()
    {
        return $this->is_used == true ? true : false;
    }
}
