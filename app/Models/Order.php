<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
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
        'confirmed_at',
        'processed_at',
        'sent_at',
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
        'user_id',
        'official',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($order) {
            // ... code here
        });

        self::created(function ($order) {
            // ... code here
        });

        self::updating(function ($order) {
            // ... code here
        });

        self::updated(function ($order) {
            // ... code here
        });

        self::deleting(function ($order) {
            // ... code here
        });

        self::deleted(function ($order) {
            // ... code here
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
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
        return config('payMethods')[$this->paymentMethod]??$this->paymentMethod??'';
    }

    public function website()
    {
        return $this->hasOne(Websites::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function isCreatorAdmin()
    {
        return $this->user()->first()->role == 'admin';
    }

    public function orders()
    {
        $orderProducts = $this->orderProducts;
        $text = $this->orders ?? '';
        foreach ($orderProducts as $orderProduct) {
            $text .= ' ' . $orderProduct->name . ' ' . +$orderProduct->number . 'عدد' . '،';
        }
        return $text;
    }

    public function payPercent()
    {
        if ($this->total == 0)
            return 0;
        if ($this->total < 0)
            return 100;
        if ($this->user->safir())
            return 100;
        $payLinks = $this->paymentLinks;
        $Total = 0;
        foreach ($payLinks as $payLink) {
            $Total += $payLink->amount;
        }

        return round($Total / $this->total * 100);
    }

    public function unpaid()
    {
        if ($this->total <= 0 || $this->user->safir())
            return 0;
        $payLinks = $this->paymentLinks;
        $Total = 0;
        foreach ($payLinks as $payLink) {
            $Total += $payLink->amount;
        }
        return round($this->total - $Total);
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

    public function orderCondition()
    {
        if ($this->deleted_at)
            return '<span class="btn btn-secondary">حذف شده</span>';
        if ($this->state === 11)
            return '<span class="btn btn-delivered">تحویل داده شده</span>';
        if ($this->state === 10)
            return '<span class="btn btn-success">ارسال شده</span>';
        if ($this->counter === 'rejected')
            return '<span class="btn btn-danger">رد شده در حسابداری</span>';
        if (!$this->confirm)
            return '<span class="btn btn-info">منتظر تایید کاربر</span>';
        if ($this->confirm && $this->counter === 'waiting') {
            return '<span class="btn btn-info">منتظر تایید حسابدار</span>';
        }
        if ($this->confirm && $this->counter === 'approved' && $this->state === 0)
            return '<span class="btn btn-secondary">در انتظار پرینت</span>';
        if ($this->state === 1 || $this->state === 2)
            return '<span class="btn btn-warning">در حال پردازش برای ارسال</span>';
        if ($this->state === 4)
            return '<span class="btn btn-danger">سفارش در مرحله در حال پردازش ادیت شده.</span>';
    }


}
