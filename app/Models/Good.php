<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Good extends Model
{
//    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable =[
        'name',
        'price',
        'photo',
        'category',
        'productPrice',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function goodMeta()
    {
        return $this->hasone(GoodMeta::class);
    }

    public function couponLinks()
    {
        return $this->hasMany(CouponLink::class);
    }

    public function getSupplier_infAttribute()
    {
        return $this->goodMeta()->first()->supplier_inf;
    }
}
