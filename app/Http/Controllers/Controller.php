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

    public function settings()
    {
        $sets = Setting::all();
        $res = [];
        foreach ($sets as $set){
            $res[$set->name] = $set->value;
        }
        return (object)$res;
    }

}
