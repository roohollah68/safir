<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function showSettings()
    {
        $setting = $this->settings();
        return view('settings',
            [
                'loadOrders' => $setting->loadOrders,
                'minCoupon' => $setting->minCoupon,
                'negative' => $setting->negative,
                'peykCost' => $setting->peykCost,
                'postCost' => $setting->postCost,
                'freeDelivery' => $setting->freeDelivery,
            ]);
    }

    public function editSettings(Request $req)
    {
        foreach ($req->all() as $name => $value) {
            $value = str_replace(",","",$value);
            Setting::where('name', $name)->update([
                'value' => $value
            ]);
        }
        return redirect(route('settings'));
    }

    public function clearRoute()
    {
        \Artisan::call('route:cache');
    }


}


