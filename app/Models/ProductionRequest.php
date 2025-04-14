<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'good_id',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function good()
    {
        return $this->belongsTo(Good::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class , 'request_id');
    }
    
}