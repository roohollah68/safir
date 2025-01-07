<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodMeta extends Model
{
    protected $fillable = [
        'good_id',
        'warehouse_code',
        'stuff_code',
        'added_value',
        'supplier_inf',
    ];
    public $timestamps = false;

    public function good()
    {
        return $this->belongsTo(Good::class);
    }
}
