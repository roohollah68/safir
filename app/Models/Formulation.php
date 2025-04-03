<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded =[
        'id'
    ];

    public function good()
    {
        return $this->belongsTo(Good::class , 'good_id');
    }

    public function rawGood()
    {
        return $this->belongsTo(Good::class ,'rawGood_id');
    }
}
