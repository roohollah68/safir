<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
//    use HasFactory;

    protected $fillable =[
        'name',
        'price',
        'photo',
        'available',
        'quantity',
        'quantity_m',
        'quantity_f',
        'alarm',
        'category',
        'high_alarm',
        'productPrice',
        'location'
    ];

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function productChange()
    {
        return $this->hasMany(ProductChange::class);
    }
}
