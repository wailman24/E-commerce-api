<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller_earning extends Model
{
    protected $fillable = [
        'seller_id',
        'order_id',
        'is_paid',
        'unpaid_amount',
        'paid_amount'
    ];

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }
}
