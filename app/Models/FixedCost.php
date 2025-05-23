<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_id',
        'category',
        'amount',
        'account_owner',
        'desc',
        'iban',
        'due_day',
        'official',
        'vat',
        'supplier_id',
        'location',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
