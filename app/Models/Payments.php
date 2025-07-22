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
}
