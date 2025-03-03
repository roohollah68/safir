<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'account',
        'phone',
        'code',
        'description',
    ];

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
}
