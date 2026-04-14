<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    // relationship with users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // relationship with products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // relationship with stocks
    public function stocks()
    {
        return $this->hasMany(Stock::class);  
    }      
}