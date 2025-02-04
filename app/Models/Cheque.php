<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    // protected $fillable = [
    //     'pay_method', 'cheque_date', 'cheque_name', 'cheque_code', 'customer_id', 'amount', 'cheque_pass', 'description', 'created_at', 'updated_at'
    // ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
