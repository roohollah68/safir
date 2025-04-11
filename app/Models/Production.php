<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use HasFactory;
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'good_id',
        'requested_quantity',
        'produced_quantity',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'requested_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function good()
    {
        return $this->belongsTo(Good::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->requested_quantity - $this->produced_quantity;
    }

    public function getStatusInPersianAttribute()
    {
        $statusColors = [
            'pending' => 'btn-secondary',
            'in_production' => 'btn-warning',
            'completed' => 'btn-success',
            'default' => 'btn-dark',
        ];

        $statusLabels = [
            'pending' => 'در انتظار',
            'in_production' => 'در حال تولید',
            'completed' => 'تکمیل شده',
            'default' => 'نامشخص',
        ];

        $color = $statusColors[$this->status] ?? $statusColors['default'];
        $label = $statusLabels[$this->status] ?? $statusLabels['default'];

        return new \Illuminate\Support\HtmlString("<span class='btn $color'>$label</span>");
    }
}