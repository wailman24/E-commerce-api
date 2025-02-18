<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory, Notifiable,HasApiTokens;
    
    protected $fillable = [
        'image_url',
        'is_main',
        'product_id',
    ];

    public function Product(){
        return $this->hasOne(Product::class);

    }
}
