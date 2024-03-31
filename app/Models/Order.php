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
        'bale_id'
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
        return $this->belongsTo(User::class,'admin');
    }

    public function productChange()
    {
        return $this->hasMany(ProductChange::class);
    }

    public function sendMethod()
    {
        $sendMethods = $this->sendMethods();
        switch ($this->state%10) {
            case 1:
                return $sendMethods[1];
            case 2:
                return $sendMethods[2];
            case 3:
                return $sendMethods[3];
            case 4:
                return $sendMethods[4];
            case 5:
                return $sendMethods[5];
            case 6:
                return $sendMethods[6];
            case 0:
                return $sendMethods[0];
        }
        return $sendMethods[0];
    }

    public function sendMethods()
    {
        return ['ارسال نشده','ماشین شر‌کت','اسنپ','پست','تیپاکس','باربری','اتوبوس'];
    }
}
