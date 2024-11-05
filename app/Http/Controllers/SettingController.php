<?php

namespace App\Http\Controllers;

//use App\Models\Customer;
//use App\Models\CustomerTransaction;
//use App\Models\Good;
use App\Helper\Helper;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Order;
//use App\Models\Product;
//use App\Models\ProductData;
use App\Models\Setting;
//use App\Models\User;
//use GuzzleHttp\Psr7\Query;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
//use Illuminate\Support\Carbon;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class SettingController extends Controller
{
    public function showSettings()
    {
        Helper::meta('manageSafir');
        $setting = $this->settings();
        return view('settings', ['setting' => $setting,]);
    }

    public function editSettings(Request $req)
    {
        Helper::meta('manageSafir');
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
        Artisan::call('route:cache');
        Artisan::call('config:clear');
    }

    public function command()
    {
        $costomers = Customer::all();
        foreach ($costomers as $customer){
            $orders = $customer->orders()->where('confirm' , true)->get();
            $transactions = $customer->transactions()->where('verified', 'approved')->get();
            $total1 = 0;
            foreach ($orders as $order){
                $total1 += $order->total;
            }
            $total2 = 0;
            foreach ($transactions as $transaction) {
                $total2 += $transaction->amount;
            }
            if($customer->balance != $total2-$total1) {
                echo $customer->name . ' / ' . $customer->id . '<br>';
                dd([$customer->balance , $total2 ,$total1, $total2-$total1]);
            }
        }

    }

}


