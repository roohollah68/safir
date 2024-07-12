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
        if (!isset($request->base)) {
            return view('statistic', [
                'users' => User::all()->keyBy("id"),
                'request' => (object)[
                    'from' => verta()->addMonths(-1)->toCarbon(),
                    'to' => verta()->toCarbon(),
                    'user' => 'all',
                    'base' => 'productBase',
                    'safirOrders' => true,
                    'siteOrders' => true,
                    'adminOrders' => true,
                ],
            ]);
        }

        $request->from = Verta::parse($request->from)->toCarbon();
        $request->to = Verta::parse($request->to)->toCarbon();
        $users = User::all()->keyBy("id");
        $orders = Order::where([
            ['state', 10],
            ['created_at', '>', $request->from],
            ['created_at', '<', $request->to]
        ])->with('orderProducts');

        if ($request->user != 'all')
            $orders = $orders->where('user_id', $request->user);

        if ($request->base == 'productBase') {
            $orders = $orders->get();
            $products = Product::where('category', 'final')->get()->keyBy('id');
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
                'request' => $request,
                'users' => User::all()->keyBy("id"),
            ]);

        } elseif ($request->base == 'safirBase') {

        } elseif ($request->base == 'customerBase') {

        }
    }
}
