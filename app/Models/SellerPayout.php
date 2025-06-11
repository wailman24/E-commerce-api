<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPayout extends Model
{
    protected $fillable = [
        'seller_id',
        'amount_paid',
        'batch_id',
        'paid_at',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
