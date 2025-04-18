<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
//    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;
    protected $fillable = [
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
//   protected $appends = ['name', 'price', 'productPrice', 'photo', 'category'];

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

    public function good()
    {
        return $this->belongsTo(Good::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function discount($user)
    {
        $discount = 0;
        if($user->safir()){
            // $couponLinks = $this->good->couponLinks->where('user_id' , $user->id)->all();
            // foreach ($couponLinks as $couponLink)
            //     $discount = max(+$couponLink->coupon->percent, $discount);
           $discount = $user->meta('discount');
        }
        return $discount;
    }
}
