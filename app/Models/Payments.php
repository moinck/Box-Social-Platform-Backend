<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'plan_name',
        'status',
        'amount',
        'coupon_discounted_amt',
        'currency',
        'payment_type',
        'payment_method',
        'stripe_payment_intent_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id', 'id');
    }
}
