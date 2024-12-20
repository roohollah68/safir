<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'address',
        'phone',
        'zip_code',
        'balance',
        'category',
        'city_id',
        'user_id',
        'discount',
        'trust',
        'agreement',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function categoryText($cat)
    {
        if ($cat > 0 && $cat < 11)
            return $this->categories()[$cat];
        return 'انتخاب نشده';
    }

    public function categories()
    {
        return [
            'انتخاب نشده',
            'فروشگاه قهوه',
            'عطاری',
            'هایپرمارکت',
            'کافی شاپ',
            'آجیل و شیرینی فروشی',
            'پخش',
            'نمایندگی پپتینا',
            'مردمی',
            'نمونه رایگان',
            'سایر'
        ];
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function cityName()
    {
        return $this->city()->first()->name;
    }

    public function customerMetas()
    {
        return $this->hasMany(CustomerMeta::class);
    }

    public function balance()
    {
        $total = 0;
        foreach ($this->transactions as $trans)
            if ($trans->verified == 'approved')
                $total += $trans->amount;
        foreach ($this->orders as $order)
            if ($order->confirm)
                $total -= $order->total;
        return $total;
    }
}
