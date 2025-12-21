<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
 
    protected $casts = [
        'amount' => 'decimal:6',
        'usd_amount' => 'decimal:6',
        'usd_rate' => 'decimal:10',
        'payment_at' => 'datetime',
    ];
}
