<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keysungood extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = [];

    public function good()
    {
        return $this->belongsTo(Good::class,'id' , 'id');
    }
}
