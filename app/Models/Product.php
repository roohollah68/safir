<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable =[
        'name',
        'price',
        'photo',
        'available'
    ];

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }
}
