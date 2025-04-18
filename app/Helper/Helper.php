<?php

namespace App\Helper;

use App\Models\Order;
use App\Models\Setting;

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
                abort(403, 'این دسترسی را ندارید!');
        if (is_array($key)) {
            foreach ($key as $k)
                if (auth()->user()->meta($k))
                    return;
            abort(403, 'این دسترسی را ندارید!');
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

    public static function Order($edit = false)
    {
        $user = auth()->user();
        $orders = Order::withTrashed();
        if ((!$edit && $user->meta('showAllOrders')) || $user->meta(['editAllOrders', 'counter']))
            return $orders;
        else
            return $orders->where(function ($query) use ($user) {
                $warehouses = $user->warehouses->pluck('id');
                $query->orWhere('user_id', $user->id)->orWhereIn('warehouse_id', $warehouses);
            });
    }

    public static function ِdate($datetime): string
    {
        return $datetime ? verta($datetime)->formatJalaliDate() : '';
    }
}


