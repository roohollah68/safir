<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount',
        'balance',
        'type',
        'description',
        'photo',
        'deleted',
        'paymentLink',
        'verified',
        'date',
        'paymentMethod',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentLinks()
    {
        return $this->hasMany(PaymentLink::class);
    }

}
