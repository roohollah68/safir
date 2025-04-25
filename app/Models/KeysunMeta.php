<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeysunMeta extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = [];

    public function keysun()
    {
        return $this->belongsTo(Keysun::class);
    }

    public function keysungood()
    {
        return $this->belongsTo(Keysungood::class);
    }
}
