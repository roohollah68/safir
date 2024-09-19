<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Good extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable =[
        'name',
        'price',
        'photo',
        'category',
        'productPrice',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
