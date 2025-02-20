<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order_item extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'product_id',
        'order_id',
        'qte',
        'price'
    ];
}
