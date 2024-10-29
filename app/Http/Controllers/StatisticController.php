<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function showStatistic(Request $request)
    {
        if (!auth()->user()->meta('statistic'))
            abort(401);
        $users = User::with('customers')->get()->keyBy("id");
        foreach ($users as $id => $user) {
            $users[$id]->customer = $user->customers->keyby('name');
        }
        if (!isset($request->base)) {
            return view('statistic', [
                'users' => $users,
                'request' => (object)[
                    'from' => verta()->addMonths(-1)->toCarbon(),
                    'to' => verta()->toCarbon(),
                    'user' => 'all',
                    'base' => 'productBase',
                    'safirOrders' => true,
                    'siteOrders' => true,
                    'adminOrders' => true,
                    'customer' => 'همه',
                ],
            ]);
        }
        $request->from = Verta::parse($request->from)->toCarbon();
        $request->to = Verta::parse($request->to)->addDay()->addSeconds(-1)->toCarbon();
//        dd([$request->from , $request->to]);
        $orders = Order::where([
            ['state', 10],
            ['created_at', '>', $request->from],
            ['created_at', '<', $request->to]
        ]);

        if ($request->user != 'all') {
            $orders = $orders->where('user_id', $request->user);
            $customer = $users[$request->user]->customer;
            if(isset($customer[$request->customer]))
                $orders = $orders->where('customer_id',$customer[$request->customer]->id);
            else
                $request->customer = 'همه';
        }
        $totalSale = 0;
        $totalProfit = 0;
        $orderNumber = 0;
        $productNumber = 0;
        if ($request->base == 'productBase') {
            $orders = $orders->with('orderProducts', 'website')->get();
            $products = Product::whereHas('good', function (Builder $query) {
                $query->where('category', 'final');
            })->get()->keyBy('id');
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
                    if (isset($products[$id]) && $orderProduct->price > 0) {
                        $products[$id]->number += $orderProduct->number;
                        $productNumber += $orderProduct->number;
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
                'productNumber' => $productNumber,
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
                if (!isset($paymentMethods[$index])) {
                    $paymentMethods[$index] = (object)['orderNumber' => 0, 'totalSale' => 0];
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
        } elseif ($request->base == 'depositBase') {
            $deposits = CustomerTransaction::where([
                ['deleted', false],
                ['verified', 'approved'],
                ['created_at', '>', $request->from],
                ['created_at', '<', $request->to]
            ]);

            if ($request->user == 'all') {
                $customers = Customer::all()->keyBy('id');
            }else{
                $customers = Customer::where('user_id' , $request->user);
                if(isset($customer[$request->customer]))
                    $customers = $customers->where('name' , $request->customer);
                $customers = $customers->get()->keyBy('id');
            }

            foreach ($customers as $id => $customer){
                $customers[$id]->total = 0;
                $customers[$id]->number = 0;
            }

            $deposits = $deposits->get();
            foreach ($deposits as $deposit) {
                if(isset($customers[$deposit->customer_id])) {
                    $customers[$deposit->customer_id]->total += $deposit->amount;
                    $customers[$deposit->customer_id]->number++;
                    $totalSale += $deposit->amount;
                    $orderNumber++;
                }
            }
            return view('statistic', [
                'totalSale' => $totalSale,
                'request' => $request,
                'users' => $users,
                'orderNumber' => $orderNumber,
                'customers' => $customers,
            ]);
        }
    }


}
