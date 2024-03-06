<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'zip_code',
        'balance',
        'category'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(CustomerTransactions::class);
    }

    public function categoryText($cat)
    {
        switch ($cat) {
            case 1:
                return 'فروشگاه قهوه';
            case 2:
                return 'عطاری';
            case 3:
                return 'هایپرمارکت';
            case 4:
                return 'کافی شاپ';
            case 5:
                return 'آجیل و شیرینی فروشی';
            case 6:
                return 'پخش';
            case 7:
                return 'نمایندگی پپتینا';
            case 8:
                return 'مردمی';
            case 9:
                return 'نمونه رایگان';
            case 10:
                return 'سایر';
        }
        return 'انتخاب نشده';
    }
}
