<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'enable',
        'total',
        'official'
    ];

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
    public function deposit()
    {
        return $this->hasMany(CustomerTransaction::class);
    }
}
