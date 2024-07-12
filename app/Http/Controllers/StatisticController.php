<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function showStatistic(Request $request)
    {
        if (!isset($request->Base)) {
            return view('statistic', [
                'users' => User::all()->keyBy("id"),
            ]);
        }
        $from = Verta::parse($request->from)->toCarbon();
        $to = Verta::parse($request->to)->toCarbon();
        $users = User::all()->keyBy("id");

        $orders = Order::where([
            ['state', 10],
            ['created_at', '>', $from],
            ['created_at', '<', $to]
        ])->with('orderProducts');

        if ($request->user != 'all')
            $orders = $orders->where('user_id', $request->user);

        if ($request->Base == 'productBase') {
            $orders = $orders->get();
            $products = Product::all()->keyBy('id');
            foreach ($products as $id => $product) {
                $products[$id]->number = 0;
                $products[$id]->total = 0;
            }
            foreach ($orders as $order) {

                foreach ($order->orderProducts as $orderProduct) {
                    if (isset($products[$orderProduct->product_id])) {
                        $products[$orderProduct->product_id]->number += $orderProduct->number;
                        $products[$orderProduct->product_id]->total += $orderProduct->number * $orderProduct->price;
                    }
                }
            }
            $totalSale = 0;
            foreach ($products as $id => $product) {
                $totalSale += $products[$id]->total;
            }
            return view('statistic', [
                'products' => $products,
                'totalSale' => $totalSale,
                'users' => User::all()->keyBy("id"),
            ]);

        } elseif ($request->Base == 'safirBase') {

        } elseif ($request->Base == 'customerBase') {

        }
//        $products = Product::all()->keyBy('id');
//        $totalSale = 0;
//
//        foreach ($products as $id => $product) {
//            $orderProducts = OrderProduct::where('created_at', '>', $from)->
//            where('created_at', '<', $to)->where('product_id', $id)->get();
//            $products[$id]->number = 0;
//            $products[$id]->total = 0;
//            foreach ($orderProducts as $orderProduct) {
//                $products[$id]->number += $orderProduct->number;
//                $products[$id]->total += $orderProduct->number * $orderProduct->price;
//            }
//            $totalSale += $products[$id]->total;
//        }

    }
}
