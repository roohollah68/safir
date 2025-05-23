<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Good extends Model
{
    use SoftDeletes;
    public $timestamps = false;
    protected $fillable =[
        'name',  //نام کالا
        'price',  //قیمت فروش
        'photo', //تصویر محصول
        'category', //دسته بندی محصول نهایی، مواد اولیه، ملزومات بسته بندی
        'unit_id', //واحد
        'productPrice', //قیمت تولید
        'isic', //اینتا کد
        'vat', //ارزش افزوده دارد یا خیر
        'tag', //شناسه کالا
        'replace_id', //کالا جایگزین برای سامانه مودیان
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function goodMeta()
    {
        return $this->hasone(GoodMeta::class);
    }

    public function couponLinks()
    {
        return $this->hasMany(CouponLink::class);
    }

    public function Supplier_inf()
    {
        if($this->goodMeta)
            return $this->goodMeta->supplier_inf;
        else
            return null;
    }

    public function formulations()
    {
        return $this->hasMany(Formulation::class);
    }

    public function keysungood()
    {
        return $this->hasone(Keysungood::class , 'id' , 'id');
    }

    public function replace()
    {
        return Good::find($this->replace_id);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function productionRequests()
    {
        return $this->hasMany(ProductionRequest::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function remainingRequests()
    {
        $requestedAmount = $this->productionRequests()->sum('amount');
        $producedAmount = $this->productions()->sum('amount');
        return $requestedAmount - $producedAmount;
    }

    public function isKeysun()
    {
        return $this->keysungood ?? $this->replace()->keysungood ?? null;
    }

    public function goodCategory()
    {
        return $this->hasOne(GoodCategory::class);
    }
}
