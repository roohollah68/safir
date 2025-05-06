<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'amount',
        'account_owner',
        'desc',
        'iban',
        'due_day',
    ];
}
