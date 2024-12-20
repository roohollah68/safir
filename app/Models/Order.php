<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
//    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'zip_code',
        'orders',
        'desc',
        'receipt',
        'state',
        'confirm',
        'total',
        'paymentMethod',
        'customerCost',
        'deliveryMethod',
        'admin',
        'customer_id',
        'bale_id',
        'location',
        'warehouse_id',
        'payInDate',
        'postponeDate',
        'paymentNote',
        'counter',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function customerTransactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function paymentLinks()
    {
        return $this->hasMany(PaymentLink::class);
    }

    public function productChange()
    {
        return $this->hasMany(ProductChange::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function sendMethod()
    {
        if ($this->user()->first()->safir() || $this->deliveryMethod != 'admin')
            if (isset(config('sendMethods')[$this->deliveryMethod])) {
                return config('sendMethods')[$this->deliveryMethod];
            } elseif ($this->deliveryMethod) {
                return $this->deliveryMethod;
            } else {
                return '';
            }
        else {
            return config('sendMethods')[$this->state % 10];
        }
    }

    public function payMethod(): string
    {
        if ($this->user->safir() || $this->paymentMethod != 'admin')
            if (isset(config('payMethods')[$this->paymentMethod])) {
                return config('payMethods')[$this->paymentMethod];
            } elseif ($this->paymentMethod) {
                return $this->paymentMethod;
            } else {
                return '';
            }
        else
            return config('payMethods')[$this->confirm];
    }

    public function website()
    {
        return $this->hasOne(Websites::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function isCreatorAdmin()
    {
        return $this->user()->first()->role == 'admin';
    }

    public function orders()
    {
        $orderProducts = $this->orderProducts;
        $text = $this->orders;
        foreach ($orderProducts as $orderProduct) {
            $text .= ' ' . $orderProduct->name . ' ' . +$orderProduct->number . 'عدد' . '،';
        }
        return $text;
    }

    public function payPercent()
    {
        if ($this->user->safir())
            return 100;
        $payLinks = $this->paymentLinks;
        $Total = 0;
        foreach ($payLinks as $payLink) {
            $Total += $payLink->amount;
        }
        if ($this->total == 0)
            return 0;
        return round($Total / $this->total * 100);
    }

    public function payPercentApproved()
    {
        if ($this->user->safir())
            return 100;
        $payLinks = $this->paymentLinks;
        $Total = 0;
        foreach ($payLinks as $payLink) {
            if ($payLink->customerTransaction->verified == 'approved')
                $Total += $payLink->amount;
        }
        if ($this->total == 0)
            return 100;
        return round($Total / $this->total * 100);
    }
}
