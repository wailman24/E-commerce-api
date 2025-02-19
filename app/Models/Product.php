<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, Notifiable,HasApiTokens;
    
    protected $fillable = [
        'name',
        'category_id',
        'about',
        'prix',
        'seller_id',
    ];

    public function Categorie(){
        return $this->hasOne(Categorie::class);

    }

    public function Seller(){
        //return $this->hasOne(Seller::class);

    }


}
