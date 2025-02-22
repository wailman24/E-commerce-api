<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categorie extends Model
{
    use HasFactory, Notifiable,HasApiTokens;
    
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'category_id',
    ];

    public function subcategories(){
        return $this->hasMany(Categorie::class, 'category_id');
    }
    
    public function parentCategory(){
        return $this->belongsTo(Categorie::class, 'category_id');
    }

    public function Product(){
        return $this->hasMany(Product::class);

    }
}
