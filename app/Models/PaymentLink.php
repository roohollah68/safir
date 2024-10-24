<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLink extends Model
{

    public $timestamps = false;
    protected $fillable = [
        'customer_transaction_id',
        'order_id',
        'amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customerTransaction()
    {
        return $this->belongsTo(CustomerTransaction::class);
    }
}
