<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTransactions;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductData;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
//        DB::beginTransaction();

//        $products = Product::all();
//        foreach ($products as $product) {
//            if ($product->location == 'm') {
//                if ($product->quantity != 0) {
//                    $product2 = Product::where('name', $product->name)->where('location', 't')->get()->first();
//                    $product2->update(['quantity_m' => $product->quantity]);
//                }
//                $product->delete();
//            }
//            if ($product->location == 'f') {
//                if ($product->category != 'final') {
//                    $product->update([
//                        'location' => 't',
//                        'quantity_f' => $product->quantity,
//                        'quantity' => 0,
//                    ]);
//                } else {
//                    if ($product->quantity != 0) {
//                        $product2 = Product::where('name', $product->name)->where('location', 't')->get()->first();
//                        $product2->update(['quantity_f' => $product->quantity]);
//                    }
//                    $product->delete();
//                }
//            }
//        }

//        $users = User::with('customers')->get();
//        foreach ($users as $user) {
////        $customers = Customer::where('user_id','<>' , 61)->get()->keyBy('id');
////        $customers = Customer::where('user_id',$user->id)->get()->keyBy('id');
//            $customers = $user->customers->keyBy('id');
//            foreach ($customers as $customer) {
//                foreach ($customers as $id => $customer2) {
//                    if ($customer->id < $customer2->id &&
//                        $customer->name == $customer2->name) {
//                        CustomerTransactions::where('customer_id', $customer2->id)->update([
//                            'customer_id' => $customer->id
//                        ]);
//                        Order::where('customer_id', $customer2->id)->update([
//                            'customer_id' => $customer->id,
//                        ]);
//                        $customer->update([
//                            'balance' => $customer->balance + $customer2->balance,
//                        ]);
//                        $customer2->delete();
//                        $customers[$id]->delete();
//                    }
//                }
//            }
//        }

//        $products = Product::all();
//        foreach ($products as $product){
//            $productData = new ProductData();
//            $productData->id = $product->id;
//            $productData->product_id = $product->id;
//            $productData->available = $product->available;
//            $productData->quantity = $product->quantity;
//            $productData->location = $product->location;
//            $productData->save();
//        }

//        $products = Product::where('location', 't')->get();
//        foreach ($products as $product) {
//            $product2 = Product::where('name', $product->name)->where('location', 'm')->get()->first();
//            if ($product2) {
//                $product2->update([
//                    'price' => $product->price,
//                    'productPrice' => $product->productPrice,
//                    'photo'=>$product->photo,
//                ]);
//            } else {
//                $product2 = $product->replicate();
//                $product2->created_at = Carbon::now();
//                $product2->location = 'm';
//                $product2->save();
//            }
//        }

//        $products = Product::where('location', 'm')->get();
//        foreach ($products as $product) {
//            $product2 = Product::where('name', $product->name)->where('location', 't')->get()->first();
//            if (!$product2) {
//                echo $product->name . '<br>';
//            }
//        }

        $customers = Customer::all();
        foreach ($customers as $customer){
            $trans = CustomerTransactions::where('customer_id' , $customer->id)->get();
            $balance = 0;
            foreach ($trans as $tran){
                if(($tran->type && $tran->verified != 'approved')||$tran->deleted)
                    continue;
                if(!$tran->type) {
                    $amount = -$tran->amount;
                }else {
                    $amount = +$tran->amount;
                }
                $balance += $amount;
                $tran->save();
            }
            $customer->balance = $balance;
            $customer->save();
        }


//        DB::commit();
        return 'ok';
    }
}


