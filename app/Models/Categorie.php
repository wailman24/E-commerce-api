<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categorie extends Model
{
    use HasFactory, Notifiable,HasApiTokens;
    
    protected $fillable = [
        'name',
        'category_id',
    ];

    public function Categorie(){
        return $this->hasMany(Categorie::class);

    }

    public function Product(){
        return $this->hasMany(Product::class);

    }
}
