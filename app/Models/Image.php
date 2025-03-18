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
        'product_id',
        'is_main',
    ];

    public function product(){
        return $this->hasOne(Product::class);
    }
}
