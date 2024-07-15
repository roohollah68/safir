<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
        ]);

        if ($request->user != 'all')
            $orders = $orders->where('user_id', $request->user);
        $totalSale = 0;
        $totalProfit = 0;
        $orderNumber = 0;
        if ($request->base == 'productBase') {
            $orders = $orders->with('orderProducts', 'website')->get();
            $products = Product::where('category', 'final')->get()->keyBy('id');
            foreach ($products as $id => $product) {
                $products[$id]->number = 0;
                $products[$id]->total = 0;
                $products[$id]->profit = 0;
            }

            foreach ($orders as $order) {
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
                    continue;
                $orderNumber++;
                foreach ($order->orderProducts as $orderProduct) {
                    $id = $orderProduct->product_id;
                    if (isset($products[$id])) {
                        $products[$id]->number += $orderProduct->number;
                        $products[$id]->total += $orderProduct->number * $orderProduct->price;
                        $products[$id]->profit += $orderProduct->number * ($orderProduct->price - $products[$id]->productPrice);
                        $totalSale += $orderProduct->number * $orderProduct->price;
                        $totalProfit += $orderProduct->number * ($orderProduct->price - $products[$id]->productPrice);
                    }
                }
            }

            return view('statistic', [
                'products' => $products,
                'totalSale' => $totalSale,
                'totalProfit' => $totalProfit,
                'request' => $request,
                'users' => $users,
                'orderNumber' => $orderNumber,
            ]);

        } elseif ($request->base == 'safirBase') {
            foreach ($users as $id => $user) {
                $users[$id]->orderNumber = 0;
                $users[$id]->totalSale = 0;
            }
            $orders = $orders->with('website')->get();
            foreach ($orders as $order) {
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
                    continue;
                $id = $order->user_id;
                $users[$id]->orderNumber++;
                $users[$id]->totalSale += $order->total;
                $orderNumber++;
                $totalSale += $order->total;
            }
            return view('statistic', [
                'totalSale' => $totalSale,
                'request' => $request,
                'users' => $users,
                'orderNumber' => $orderNumber,
            ]);

        } elseif ($request->base == 'customerBase') {
            $customers = Customer::all()->keyBy('id');
            foreach ($customers as $id => $customer) {
                $customers[$id]->orderNumber = 0;
                $customers[$id]->totalSale = 0;
            }
            $orders = $orders->with('website', 'customer')->get();
            foreach ($orders as $order) {
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
                    continue;
                if (!$order->customer)
                    continue;
                $id = $order->customer_id;
                $customers[$id]->orderNumber++;
                $customers[$id]->totalSale += $order->total;
                $orderNumber++;
                $totalSale += $order->total;
            }
            return view('statistic', [
                'totalSale' => $totalSale,
                'request' => $request,
                'users' => $users,
                'customers' => $customers,
                'orderNumber' => $orderNumber,
            ]);
        } elseif ($request->base == 'paymentBase') {
            $paymentMethods = [];
            $orders = $orders->with('website')->get();
            foreach ($orders as $order) {
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
                    continue;
                if (!$order->paymentMethod)
                    continue;
                $index = $order->payMethod();
                if(!isset($paymentMethods[$index])){
                    $paymentMethods[$index] = (object)['orderNumber'=>0,'totalSale'=>0];
                }
                $paymentMethods[$index]->orderNumber++;
                $paymentMethods[$index]->totalSale += $order->total;
                $orderNumber++;
                $totalSale += $order->total;
            }
            return view('statistic', [
                'totalSale' => $totalSale,
                'request' => $request,
                'users' => $users,
                'paymentMethods' => $paymentMethods,
                'orderNumber' => $orderNumber,
            ]);
        }
    }


}
