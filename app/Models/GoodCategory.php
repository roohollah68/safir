<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodCategory extends Model
{
    protected $table = 'good_categories';
    public $timestamps = false;

    protected $fillable = [
        'good_id',
        'sweetener',
        'packaging',
        'type',
        'brand',
    ];

    public function good()
    {
        return $this->belongsTo(Good::class, 'good_id');
    }
}
