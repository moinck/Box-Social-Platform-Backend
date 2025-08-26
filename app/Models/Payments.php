<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'plan_name',
        'status',
        'amount',
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
        $subscription = UserSubscription::where('stripe_payment_method_id', $this->stripe_payment_intent_id)->latest()->first();
        return $subscription;
    }
}
