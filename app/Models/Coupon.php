<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable=[
        'percent'
    ];

    public function couponLinks()
    {
        return $this->hasMany(CouponLink::class);
    }
}
