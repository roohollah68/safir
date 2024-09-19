<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
//    use HasFactory;

    protected $fillable =[
        'good_id',
        'name',
        'price',
        'photo',
        'available',
        'quantity',
        'alarm',
        'category',
        'high_alarm',
        'productPrice',
        'location',
        'warehouse_id',
    ];

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function productChange()
    {
        return $this->hasMany(ProductChange::class);
    }

    public function couponLinks()
    {
        return $this->hasMany(CouponLink::class);
    }

    public function good()
    {
        return $this->belongsTo(Good::class);
    }
}
