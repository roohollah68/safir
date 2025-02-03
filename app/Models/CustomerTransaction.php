<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTransaction extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
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

    public function linkedAmount()
    {
        $payLinks = $this->paymentLinks()->get();
        $Total = 0;
        foreach ($payLinks as $payLink) {
            $Total += $payLink->amount;
        }
        return $Total;
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function verified()
    {
        if ($this->verified == 'waiting')
            return '<i class="btn btn-info">بررسی</i>';
        if ($this->verified == 'approved')
            return '<i class="btn btn-success">تایید</i>';
        if ($this->verified == 'rejected')
            return '<i class="btn btn-danger">شده</i>';
    }

}
