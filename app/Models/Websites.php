<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Websites extends Model
{
    protected $fillable = [
        'order_id',
        'website',
        'website_id',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
