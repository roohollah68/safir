<?php

namespace App\Helper;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;

class Helper
{
    public static function meta($key)
    {
        return auth()->user()->meta($key);
    }

    public static function access($key)
    {
        if (!auth()->user()->meta($key))
            abort(401);
    }

    public static function number_Fa_En($Number)  //تبدیل اعداد فارسی به انگلیسی
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($fa, '' . $en, $Number);
        }
        return $Number;
    }

    public static function number_En_Fa($Number)
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($en, '' . $fa, $Number);
        }
        return $Number;
    }

    public static function settings()
    {
        $sets = Setting::all();
        $res = [];
        foreach ($sets as $set) {
            $res[$set->name] = $set->value;
        }
        return (object)$res;
    }

    public static function condition($state)
    {
        if ($state == 'waiting')
            return 'در انتظار بررسی';
        elseif ($state == 'approved')
            return 'تایید شده';
        elseif ($state == 'rejected')
            return 'رد شده';
        else
            return 'نامشخص';
    }

    public static function orderAccess($order , $userId = null)
    {
        if(!$userId)
            $userId = auth()->user()->id;
        $user = User::findOrFail($userId);
        $warehouses = Warehouse::where('user_id' , $userId)->get()->keyBy('id');
        if($order->user_id == $userId || $user->meta('showAllOrders') ||
            $user->meta('counter') || $warehouses[$order->warehouse_id])
            return true;
        return false;
    }

    public static function Order()
    {
        $user = auth()->user();
        $orders = Order::withTrashed();
        if(!$user->meta('showAllOrders') && !$user->meta('counter')){
            $orders = $orders->where(function ($query) {
                $user = auth()->user();
                $warehouses = Warehouse::where('user_id' , $user->id)->get()->keyBy('id')->keys();
                $query->orWhere('user_id',$user->id)->orWhereIn('warehouse_id',$warehouses);
            });
        }
        return $orders;
    }
}


