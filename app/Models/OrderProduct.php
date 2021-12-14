<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'photo',
        'product_id',
        'number',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
