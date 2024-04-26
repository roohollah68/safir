<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $value = str_replace(",", "", $value);
            Setting::where('name', $name)->update([
                'value' => $value
            ]);
        }
        return redirect(route('settings'));
    }

    public function clearRoute()
    {
        \Artisan::call('route:cache');
        \Artisan::call('config:clear');
    }

    public function command()
    {
        DB::beginTransaction();
        $products = Product::all();
        foreach ($products as $product) {
            if ($product->location == 'm') {
                if ($product->quantity != 0) {
                    $product2 = Product::where('name', $product->name)->where('location', 't')->get()->first();
                    $product2->update(['quantity_m' => $product->quantity]);
                }
                $product->delete();
            }
            if ($product->location == 'f') {
                if ($product->category != 'final') {
                    $product->update([
                        'location' => 't',
                        'quantity_f' => $product->quantity,
                        'quantity' => 0,
                    ]);
                } else {
                    if ($product->quantity != 0) {
                        $product2 = Product::where('name', $product->name)->where('location', 't')->get()->first();
                        $product2->update(['quantity_f' => $product->quantity]);
                    }
                    $product->delete();
                }
            }

        }
        DB::commit();
        return 'ok';
    }
}


