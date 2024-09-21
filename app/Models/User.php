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

    public function superAdmin()
    {
        return $this->role == 'superAdmin';
    }

    public function print()
    {
        return $this->role == "print";
    }

    public function warehouse()
    {
        return $this->role == 'warehouse';
    }

    public function safir()
    {
        return $this->role == 'user';
    }

    public function counter()
    {
        return $this->role == 'counter';
    }

    public function account()
    {
        return $this->role == 'account';
    }

    public function meta($name)
    {
        $Meta = $this->userMetas()->where('name', $name)->first();
        if ($Meta)
            return $Meta->value;
        else
            return config('userMeta.' . $name);
    }

}
