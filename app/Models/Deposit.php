<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'photo',
        'desc',
        'confirmed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
