<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductChange extends Model
{
    use HasFactory;

    protected $fillable =[
        'product_id',
        'order_id',
        'change',
        'quantity',
        'isDeleted',
        'desc',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }
}
