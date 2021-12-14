<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function number_Fa_En($Number)
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($fa, '' . $en, $Number);
        }
        return $Number;
    }

    public function isAdmin()
    {
        if (auth()->user()->role == 'admin') {
            return true;
        } else {
            return false;
        }
    }

    public function minCoupon()
    {
        $minCoupon = Setting::where('name', 'minCoupon')->get();
        return $minCoupon[0]->value;
    }

    public function loadOrders()
    {
        $loadOrders = Setting::where('name', 'loadOrders')->get();
        return $loadOrders[0]->value;
    }

    public function negative()
    {
        $negative = Setting::where('name', 'negative')->get();
        return $negative[0]->value;
    }

}
