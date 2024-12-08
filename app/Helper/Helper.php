<?php

namespace App\Helper;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Warehouse;

class Helper
{
    public static function meta($key)
    {
        return auth()->user()->meta($key);
    }

    public static function access($key): void
    {
        if (is_string($key))
            if (!auth()->user()->meta($key))
                abort(401);
        if(is_array($key)) {
            foreach ($key as $k)
                if (auth()->user()->meta($k))
                    return;
            abort(401);
        }

    }

    public static function number_Fa_En($Number): string  //تبدیل اعداد فارسی به انگلیسی
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($fa, '' . $en, $Number);
        }
        return $Number;
    }

    public static function number_En_Fa($Number): string
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($en, $fa, $Number);
        }
        return $Number;
    }

    public static function settings(): object
    {
        $sets = Setting::all();
        $res = [];
        foreach ($sets as $set) {
            $res[$set->name] = $set->value;
        }
        return (object)$res;
    }

    public static function condition($state): string
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

    public static function Order()
    {
        $user = auth()->user();
        $orders = Order::withTrashed();
        if (!$user->meta('showAllOrders') && !$user->meta('counter')) {
            $orders = $orders->where(function ($query) {
                $user = auth()->user();
                $warehouses = Warehouse::where('user_id', $user->id)->get()->keyBy('id')->keys();
                $query->orWhere('user_id', $user->id)->orWhereIn('warehouse_id', $warehouses);
            });
        }
        return $orders;
    }
}


