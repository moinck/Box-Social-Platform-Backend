<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use App\Models\UserSubscription;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'fca_number',
        'company_name',
        'website',
        'password',
        'status',
        'role',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Custom method to mark email as verified
    public function markEmailAsVerified()
    {
        $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'is_verified' => true,
        ])->save();

        $this->fireModelEvent('verified');
    }

    // Check if email is verified
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at) && $this->is_verified;
    }

    // Override getEmailForVerification if needed
    public function getEmailForVerification()
    {
        return $this->email;
    }

    // check if user have brand-kit
    public function hasBrandKit()
    {
        return $this->hasOne(BrandKit::class,'user_id','id')->exists();
    }

    /**
     * User Brandkit relation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<BrandKit, User>
     */
    public function brandKit()
    {
        return $this->hasOne(BrandKit::class,'user_id','id');
    }

    public function subscription()
    {
        return $this->hasOne(UserSubscription::class,'user_id','id')->where('status','active');
    }
}