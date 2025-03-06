<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;
//    use SoftDeletes;
    protected $fillable = [
        'phone',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
