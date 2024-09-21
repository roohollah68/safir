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
        'warehouse_id',
    ];
    protected $appends = ['name','price','productPrice','photo','category'];

    public function getNameAttribute()
    {
        return $this->good()->first()->name;
    }

    public function getPriceAttribute()
    {
        return $this->good()->first()->price;
    }

    public function getProductPriceAttribute()
    {
        return $this->good()->first()->productPrice;
    }

    public function getPhotoAttribute()
    {
        return $this->good()->first()->photo;
    }

    public function getCategoryAttribute()
    {
        return $this->good()->first()->category;
    }

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
