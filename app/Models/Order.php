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
        return $this->hasMany(CustomerTransactions::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin');
    }

    public function productChange()
    {
        return $this->hasMany(ProductChange::class);
    }

    public function sendMethod()
    {
        if ($this->user()->first()->safir() || $this->deliveryMethod != 'admin')
            if (isset(config('sendMethods')[$this->deliveryMethod])) {
                return config('sendMethods')[$this->deliveryMethod];
            } elseif($this->deliveryMethod) {
                return $this->deliveryMethod;
            }else{
                return '';
            }
        else {
            return config('sendMethods')[$this->state % 10];
        }
    }


    public function payMethod(): string
    {
        if ($this->user()->first()->safir() || $this->paymentMethod != 'admin')
            if (isset(config('payMethods')[$this->paymentMethod])) {
                return config('payMethods')[$this->paymentMethod];
            } elseif($this->paymentMethod) {
                return $this->paymentMethod;
            }else{
                return '';
            }
        else
            return config('payMethods')[$this->confirm];
    }

    public function website()
    {
        return $this->hasOne(Websites::class);
    }

}
