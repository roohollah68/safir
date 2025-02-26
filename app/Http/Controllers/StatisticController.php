<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Good;
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
        $user = auth()->user();
        $statistic = $user->meta('statistic');
        $users = User::withTrashed()->with('customers');
        if (!$statistic)
            $users = $users->where('id', $user->id);
        $users = $users->get()->keyBy("id");

        foreach ($users as $id => $user) {
            $users[$id]->customer = $user->customers->keyby('name');
        }
        if (isset($request->city)) {
            $request->base = 'customerBase';
        }
        if (!isset($request->base)) {
            return view('statistic', [
                'users' => $users,
                'request' => (object)[
                    'from' => verta()->addMonths(-1)->toCarbon(),
                    'to' => verta()->toCarbon(),
                    'user' => $statistic ? '' : $user->id,
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
        $orders = Order::where([
            ['state', 10],
            ['created_at', '>', $request->from],
            ['created_at', '<', $request->to],
        ])->where('total', '>', 0);

        if ($request->user) {
            $orders = $orders->where('user_id', $request->user);
            $customer = $users[$request->user]->customer;
            if (isset($customer[$request->customer]))
                $orders = $orders->where('customer_id', $customer[$request->customer]->id);
        }
        $totalSale = 0;
        $totalProfit = 0;
        $orderNumber = 0;
        $productNumber = 0;

        if ($request->base == 'productBase') {
            $orders = $orders->with('orderProducts', 'website')->get();
            $goods = Good::withTrashed()->where('category', 'final')->get()->keyBy('id');
            $products = Product::withTrashed()->get()->keyBy('id');
            foreach ($goods as $id => $good) {
                $good->number = 0;
                $good->total = 0;
                $good->profit = 0;
            }

            foreach ($orders as $order) {
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!isset($users[$order->user_id]))
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
                    continue;
                $orderNumber++;
                foreach ($order->orderProducts as $orderProduct) {
                    $id = $orderProduct->product_id;
                    if (isset($products[$id]) && $orderProduct->price > 0) {
                        if (isset($products[$orderProduct->product_id]))
                            $product = $products[$orderProduct->product_id];
                        else
                            continue;
                        if (isset($goods[$product->good_id]))
                            $good = $goods[$product->good_id];
                        else
                            continue;
                        $good->number += $orderProduct->number;
                        $productNumber += $orderProduct->number;
                        $good->total += $orderProduct->number * $orderProduct->price;
                        $good->profit += $orderProduct->number * ($orderProduct->price - $good->productPrice);
                        $totalSale += $orderProduct->number * $orderProduct->price;
                        $totalProfit += $orderProduct->number * ($orderProduct->price - $good->productPrice);
                    }
                }
            }

            return view('statistic', [
                'goods' => $goods,
                'totalSale' => $totalSale,
                'totalProfit' => $totalProfit,
                'request' => $request,
                'users' => $users,
                'orderNumber' => $orderNumber,
                'productNumber' => $productNumber,
            ]);

        }
        if ($request->base == 'safirBase') {
            foreach ($users as $id => $user) {
                $users[$id]->orderNumber = 0;
                $users[$id]->totalSale = 0;
            }
            $orders = $orders->with('website')->get();
            foreach ($orders as $order) {
//                if (!isset($users[$order->user_id]))
//                    continue;
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

        }
        if ($request->base == 'customerBase') {
            $customers = Customer::query();
            if (!$statistic)
                $customers = $customers->where('user_id', $user->id);
            if ($request->city)
                $customers = $customers->where('city_id', $request->city);
            $customers = $customers->get()->keyBy('id');
            foreach ($customers as $id => $customer) {
                $customers[$id]->orderNumber = 0;
                $customers[$id]->totalSale = 0;
            }
            $orders = $orders->with('website', 'customer')->get();
            foreach ($orders as $order) {
                if (!$order->customer || (isset($request->city) && $order->customer->city_id != $request->city))
                    continue;
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
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
        }
        if ($request->base == 'paymentBase') {
            $paymentMethods = [];
            $orders = $orders->with(['website', 'user'])->get();
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
        }
        if ($request->base == 'depositBase') {
            $deposits = CustomerTransaction::where([
                ['verified', 'approved'],
                ['created_at', '>', $request->from],
                ['created_at', '<', $request->to],
            ]);

            if (!$request->user) {
                $customers = Customer::all()->keyBy('id');
            } else {
                $customers = Customer::where('user_id', $request->user);
                if (isset($customer[$request->customer]))
                    $customers = $customers->where('name', $request->customer);
                $customers = $customers->get()->keyBy('id');
            }

            foreach ($customers as $id => $customer) {
                $customers[$id]->total = 0;
                $customers[$id]->number = 0;
            }

            $deposits = $deposits->get();
            foreach ($deposits as $deposit) {
                if (isset($customers[$deposit->customer_id])) {
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
        if ($request->base == 'cityBase') {
            $cities = City::all()->keyBy('id');
            foreach ($cities as $id => $city) {
                $city->orderNumber = 0;
                $city->totalSale = 0;
            }
            $orders = $orders->with('website', 'customer')->get();
            foreach ($orders as $order) {
                if (!$order->customer)
                    continue;
                if ($order->website && !$request->siteOrders)
                    continue;
                if (!$order->website && $users[$order->user_id]->safir() && !$request->safirOrders)
                    continue;
                if ($users[$order->user_id]->admin() && !$request->adminOrders)
                    continue;
                $id = $order->customer->city_id;
                $cities[$id]->orderNumber++;
                $cities[$id]->totalSale += $order->total;
                $orderNumber++;
                $totalSale += $order->total;
            }
            return view('statistic', [
                'totalSale' => $totalSale,
                'request' => $request,
                'users' => $users,
                'cities' => $cities,
                'orderNumber' => $orderNumber,
            ]);
        }
    }
    public function productChart($id)
    {
        $good = Good::findOrFail($id);
        $productIds = $good->products()->pluck('id');

        $endDate = Verta::now()->endDay()->toCarbon();
        $startDate = Verta::now()->subMonths(11)->startDay()->toCarbon();

        $orderProducts = OrderProduct::whereIn('product_id', $productIds)
            ->whereHas('order', fn($query) => $query
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('total', '>', 0)
            )
            ->with('order')
            ->get();

        $salesData = $orderProducts->groupBy(
            fn($op) => Verta::instance($op->order->created_at)->format('Y-m')
        )->map(fn($productsInMonth) => (int)$productsInMonth->sum('number'));

        $pricesData = $orderProducts->groupBy(
            fn($op) => Verta::instance($op->order->created_at)->format('Y-m')
        )->map(
            fn($productsInMonth) => (int) round($productsInMonth->avg('price') ?? 0)
        );

        $monthData = collect(range(11, 0, -1))->map(function ($monthsAgo) {
            $monthDate = Verta::now()->subMonths($monthsAgo);
            return [
                'key' => $monthDate->format('Y-m'),
                'label' => $monthDate->formatWord('F')
            ];
        });

        $month = $monthData->pluck('key');
        $labels = $monthData->pluck('label');

        $data = $month->map(fn($key) => $salesData->get($key, 0));
        $priceValues = $month->map(fn($key) => $pricesData->get($key, 0));

        return view('productChart', compact('labels', 'data', 'priceValues'));
}
}
