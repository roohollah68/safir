<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keysun extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction()
    {
        return $this->belongsTo(CustomerTransaction::class);
    }

    public function keysunMetas()
    {
        return $this->hasMany(KeysunMeta::class);
    }

    public function conversion()
    {
        if($this->conv<0.4){
            return "<span class='btn btn-danger' onclick='changeKeysun(". $this->id .")'>$this->conv</span>";
        }elseif ($this->conv>1.5){
            return "<span class='btn btn-warning' onclick='changeKeysun(". $this->id .")'>$this->conv</span>";
        }else{
            return "<span class='btn btn-success' onclick='changeKeysun(". $this->id .")'>$this->conv</span>";
        }
    }
}
