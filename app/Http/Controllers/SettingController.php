<?php

namespace App\Http\Controllers;

//use App\Models\Customer;
//use App\Models\CustomerTransaction;
//use App\Models\Good;
use App\Helper\Helper;
use App\Models\CustomerTransaction;
use App\Models\Order;
//use App\Models\Product;
//use App\Models\ProductData;
use App\Models\Setting;
//use App\Models\User;
//use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
//use Illuminate\Support\Carbon;
//use Illuminate\Support\Facades\DB;

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
        \Artisan::call('route:cache');
        \Artisan::call('config:clear');
    }

    public function command()
    {
//        $this->orders();
        $this->customerTransactions();
//        $this->orderSafir100();
        return 'ok';
    }


    public function customerTransactions()
    {
        $transactions = CustomerTransaction::all();
        foreach ($transactions as $transaction){
            if($transaction->deleted) {
                $transaction->delete();
                continue;
            }
            if (!$transaction->order_id)
                continue;
            if(!$transaction->paymentLink) {
                $transaction->delete();
                continue;
            }
            $order = $transaction->order;
            $order->paymentLinks()->create([
                'customer_transaction_id'=>$transaction->paymentLink,
                'amount'=>$order->total,
            ]);
            $transaction->delete();
        }
    }

    public function orders()
    {
        $orders = Order::withTrashed()->get();
        foreach ($orders as $order) {
            if($order->orders == '')
                continue;
            if ($order->orders == 'طبق فاکتور') {
                $order->orders = '';
            } else
                foreach ($order->orderProducts()->get() as $orderProduct) {
                    $text = ' ' . $orderProduct->name . ' ' . +$orderProduct->number . 'عدد' . '،';
                    $order->orders = str_replace($text, '', $order->orders);
                }
            $order->save();
        }
    }


}


