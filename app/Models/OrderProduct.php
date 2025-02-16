<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{

    public $timestamps = false;
    protected $fillable = [
        'name',
        'price',
        'product_id',
        'order_id',
        'number',
        'discount',
        'verify',
        'editPrice',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function originalPrice()
    {
        if($this->price == 0 || $this->discount == 100)
            return $this->product->good->price;
        else
            return $this->price * (100/(100-$this->discount));
    }
}
