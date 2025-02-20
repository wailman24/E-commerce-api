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
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
