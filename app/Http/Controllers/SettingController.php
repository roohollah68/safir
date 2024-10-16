<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTransactions;
use App\Models\Good;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductData;
use App\Models\Setting;
use App\Models\User;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function showSettings()
    {
        $setting = $this->settings();
        return view('settings', ['setting' => $setting,]);
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


//        $products = Product::where('location', 't')->get();
//        foreach ($products as $product) {
//            $product2 = $product->replicate();
//            $product2->created_at = Carbon::now();
//            $product2->location = 'e';
//            $product2->quantity = 0;
//            $product2->available = false;
//            $product2->save();
//        }


//        $customers = Customer::with('transactions')->get();
//        foreach ($customers as $customer) {
////            $trans = CustomerTransactions::where('customer_id' , $customer->id)->get();
//            $balance = 0;
//            foreach ($customer->transactions as $tran) {
//                if (($tran->type && $tran->verified != 'approved') || $tran->deleted)
//                    continue;
//                if (!$tran->type) {
//                    $amount = -$tran->amount;
//                } else {
//                    $amount = +$tran->amount;
//                }
//                $balance += $amount;
//      //          $tran->save();
//            }
//            if ($customer->balance != $balance) {
//                echo $customer->name . ' / ' . $customer->id . '<br>';
//            }

//            $customer->save();
//    }


        //$this->createGoodTable();

        //$this->setWarehouseId();

        //$this->setWarehouseId2();

//        $this->repairShiraz();

//        DB::commit();

//        $this->sendTextToBale('hi ```[ljk]lkuvlkwnv```' , env('GroupId'));

        $this->addAllTowarehouse();

        return 'ok';
    }

    public function createGoodTable()
    {
        //$nameList = [];
        $products = Product::where('id', '>', 6054)->get();
        foreach ($products as $product) {
            $good = Good::where('name', $product->name)->first();
            if ($good) {
                $product->update([
                    'good_id' => $good->id,
                ]);
            } else {
                $good = Good::create([
                    'name' => $product->name,
                    'price' => $product->price,
                    'productPrice' => $product->productPrice,
                    'photo' => $product->photo,
                    'category' => $product->category,
                ]);
                $product->update([
                    'good_id' => $good->id,
                ]);
            }

        }
    }

    public function setWarehouseId()
    {
        $products = Product::all();
        $warehouseId = [
            't' => 1,
            'f' => 3,
            'm' => 2,
            's' => 4,
            'e' => 5
        ];
        foreach ($products as $product) {
            $product->update([
                'warehouse_id' => $warehouseId[$product->location]
            ]);
        }

    }

    public function setWarehouseId2()
    {
        $orders = Order::all();
        $warehouseId = [
            't' => 1,
            'f' => 3,
            'm' => 2,
            's' => 4,
            'e' => 5
        ];

        foreach ($orders as $order) {
            $order->update([
                'warehouse_id' => $warehouseId[$order->location]
            ]);
        }
    }

    public function repairShiraz()
    {
        $duplicates = DB::select('select   `warehouse_id`,
         `good_id`
from     products
group by `warehouse_id`,
         `good_id`
having   count(*) > 1
');
        foreach ($duplicates as $duplicate){
            $products = Product::where('warehouse_id',$duplicate->warehouse_id)->where('good_id',$duplicate->good_id)->get();
            $product = $products[0];
            if(!$product->avalaible || !$product->quantity)
                $product->forceDelete();
            else
                $products[1]->forceDelete();
        }

    }

    public function addAllTowarehouse()
    {
        $products = Product::where('warehouse_id' , 1)->get();
        $products2 = Product::withTrashed()->where('warehouse_id' , 7)->get()->keyBy('good_id');
        foreach ($products as $product){
            if(isset($products2[$product->good_id]))
                continue;
            Product::create([
                'good_id' => $product->good_id,
                'available' => $product->available,
                'warehouse_id' => 7,
            ]);
        }
    }
}


