<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'user_id',
        'store',
        'logo',
        'phone',
        'adress',
        'status',
        'paypal'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function seller_earning()
    {
        return $this->belongsTo(Seller_earning::class);
    }
}
