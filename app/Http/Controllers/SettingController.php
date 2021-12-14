<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function showSettings()
    {
        return view('settings',['loadOrders'=> $this->loadOrders(), 'minCoupon'=>$this->minCoupon() , 'negative'=>$this->negative()]);
    }

    public function editSettings(Request $req)
    {
        request()->validate([
            'loadOrders' => 'required|numeric|min:1',
            'minCoupon' => 'required|numeric|min:0|max:99',
        ]);

        Setting::where('name', 'minCoupon')->update([
            'value' => $req->minCoupon
        ]);

        Setting::where('name', 'loadOrders')->update([
            'value' => $req->loadOrders
        ]);

        Setting::where('name', 'negative')->update([
            'value' => $req->negative
        ]);
        return redirect(route('settings'));
    }
}


