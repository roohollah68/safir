<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'website',
        'phone',
        'verified',
        'password',
        'telegram_id',
        'telegram_code',
        'balance',
        'role',
        'credit',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function userMetas()
    {
        return $this->hasMany(UserMeta::class);
    }

    public function admin()
    {
        return $this->role == "admin";
    }

    public function safir()
    {
        return $this->role == 'user';
    }

    public function meta($name)
    {
        if (is_string($name)) {
            $Meta = $this->userMetas->where('name', $name)->first();
            if ($Meta)
                return $Meta->value;
            else
                return config('userMeta.' . $name);
        } elseif (is_array($name)) {
            foreach ($name as $key){
                $Meta = $this->userMetas->where('name', $key)->first();
                if ($Meta)
                    if($Meta->value)
                        return true;
            }
            return false;
        }else
            return false;
    }

    public function couponLinks()
    {
        return $this->hasMany(CouponLink::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function totalDepth()
    {
        $total = 0;
        foreach ($this->customers as $customer){
            $total += $customer->balance();
        }
        return -$total;
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

}
