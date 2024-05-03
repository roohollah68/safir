<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponLink extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'product_id',
        'coupon_id',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->first();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->first();
    }
}
