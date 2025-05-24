<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Good;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\Bank;
use App\Models\Withdrawal;
use App\Helper\Helper;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatisticController extends Controller
{
    public function showStatistic(Request $request)
    {
        $user = auth()->user();

        $users = User::withTrashed()->with('customers');
        if (!$user->meta('statistic'))
            $users = $users->where('id', $user->id);
        $users = $users->get()->keyBy("id");

        if (isset($request->city)) {
            $request->base = 'customerBase';
        }
        if (!isset($request->base)) {
            return view('statistic', [
                'users' => $users,
                'request' => (object)[
                    'from' => verta()->addMonths(-1)->toCarbon(),
                    'to' => verta()->toCarbon(),
                    'user' => $user->meta('statistic') ? '' : $user->id,
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
            ['state', '>', 0],
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
        $totalDiscount = 0;
        $totalOriginal = 0;

        if ($request->base == 'productBase') {
            $orders = $orders->with('orderProducts', 'website')->get();
            $goods = Good::withTrashed()->where('category', 'final')->get()->keyBy('id');
            $products = Product::withTrashed()->get()->keyBy('id');
            foreach ($goods as $id => $good) {
                $good->number = 0;
                $good->total = 0;
                $good->profit = 0;
                $good->discount = 0;
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
                        $good->discount += $orderProduct->number * $orderProduct->discount;
                        $productNumber += $orderProduct->number;
                        $good->total += $orderProduct->number * $orderProduct->price;
                        $good->profit += $orderProduct->number * ($orderProduct->price - $good->productPrice);
                        $totalSale += $orderProduct->number * $orderProduct->price;
                        $totalProfit += $orderProduct->number * ($orderProduct->price - $good->productPrice);
                        $totalDiscount += $orderProduct->originalPrice() * ($orderProduct->discount / 100);
                        $totalOriginal += $orderProduct->originalPrice();
                    }
                }
            }
            $goods->each(function ($good) {
                $good->discount = round($good->number ? $good->discount / $good->number : 0);
            });
            return view('statistic', [
                'goods' => $goods,
                'totalSale' => $totalSale,
                'totalProfit' => $totalProfit,
                'request' => $request,
                'users' => $users,
                'orderNumber' => $orderNumber,
                'productNumber' => $productNumber,
                'avgDiscount' => round($totalOriginal ? $totalDiscount / $totalOriginal * 100 : 0),
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
            if (!$user->meta('statistic'))
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
            fn($productsInMonth) => (int)round($productsInMonth->avg('price') ?? 0)
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

    public function chequeChart(Request $request)
    {
        Helper::access('statistic');

        foreach ([CustomerTransaction::class, Withdrawal::class] as $model) {
            $model::where('pay_method', 'cheque')
                ->where('cheque_status', 2)
                ->whereDate('cheque_date', '<', now())
                ->update(['cheque_status' => 1]);
        }

        $receivedQuery = CustomerTransaction::where('pay_method', 'cheque')->where('verified', 'approved');
        $givenQuery = Withdrawal::where('pay_method', 'cheque')->where('payment_confirm', 1);

        if ($request->filled('state') && $request->state !== 'all') {
            $receivedQuery->where('cheque_status', $request->state);
            $givenQuery->where('cheque_status', $request->state);
        }

        if ($request->filled('type')) {
            $isOfficial = $request->type === 'official' ? 1 : 0;
            $receivedQuery->where('official', $isOfficial);
            $givenQuery->where('official', $isOfficial);
        }

        foreach (['from' => '>=', 'to' => '<='] as $key => $op) {
            if ($request->filled($key)) {
                $date = Verta::parse($request->$key)->DateTime();
                if ($date) {
                    $receivedQuery->whereDate('cheque_date', $op, $date);
                    $givenQuery->whereDate('cheque_date', $op, $date);
                }
            }
        }

        if ($request->filled('bank')) {
            $givenQuery->where('bank_id', $request->bank);
        }

        $receivedTotal = $receivedQuery->sum('amount');
        $givenTotal = $givenQuery->sum('amount');
        $receivedCheques = $receivedQuery->get(['cheque_date', 'amount']);
        $givenCheques = $givenQuery->get(['cheque_date', 'amount']);

        $dates = collect();
        $receivedByDate = [];
        $givenByDate = [];

        foreach ($receivedCheques as $item) {
            $date = (new Verta($item->cheque_date))->format('Y/m/d');
            $dates->push($date);
            $receivedByDate[$date] = ($receivedByDate[$date] ?? 0) + $item->amount;
        }
        foreach ($givenCheques as $item) {
            $date = (new Verta($item->cheque_date))->format('Y/m/d');
            $dates->push($date);
            $givenByDate[$date] = ($givenByDate[$date] ?? 0) + $item->amount;
        }

        $chartLabels = $dates->unique()->sort()->values()->all();
        $receivedData = array_map(fn($d) => $receivedByDate[$d] ?? 0, $chartLabels);
        $givenData = array_map(fn($d) => $givenByDate[$d] ?? 0, $chartLabels);

        return view('cheque.chart', [
            'receivedTotal' => $receivedTotal,
            'givenTotal' => $givenTotal,
            'banks' => Bank::all(),
            'filters' => $request->all(),
            'chartLabels' => $chartLabels,
            'receivedData' => $receivedData,
            'givenData' => $givenData,
            'from' => $request->from,
            'to' => $request->to
        ]);
    }

    public function deliveryReport(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $ordersQuery = Order::where('state', 10)
            ->whereNotNull('deliveryMethod')
            ->where('deliveryMethod', '!=', 'admin')
            ->where('deliveryMethod', '!=', ' - ')
            ->when($request->from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', Verta::parse($request->from)->DateTime());
            })
            ->when($request->to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', Verta::parse($request->to)->DateTime());
            });

        $sendMethods = [
            'پیک شهری' => ['peykeShahri', 6, 'پیک شهری'],
            'تیپاکس' => ['peyk', 'peykCost', 4, 'پیک'],
            'پست' => ['post', 'postCost', 3, 'پست'],
            'اسنپ' => [2],
            'ماشین شرکت' => [1],
            'پس کرایه' => ['paskerayeh', 'پس کرایه'],
            'باربری' => [5],
            'حضوری' => [7],
            'نفیس اکسپرس' => [8],
            'اتوبوس' => [9],
        ];

        $orders = $ordersQuery->get();
        $totalOrders = $orders->count();
        $methodCount = array_fill_keys(array_merge(array_keys($sendMethods), ['نامشخص', 'ارسال رایگان']), 0);
        $config = config('sendMethods');

        foreach ($orders as $order) {
            $found = false;
            $clean = Str::lower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $order->deliveryMethod)));

            if (mb_strpos($clean, 'ارسال رایگان') !== false) {
                $methodCount['ارسال رایگان']++;
                $found = true;
            }

            if (!$found) {
                $paskerayeh = ['paskerayeh', 'پس کرایه', Str::lower($config['paskerayeh'] ?? '')];
                foreach ($paskerayeh as $keyword) {
                    $cleanKeyword = Str::lower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $keyword)));
                    if (!empty($cleanKeyword) && mb_strpos($clean, $cleanKeyword) !== false) {
                        $methodCount['پس کرایه']++;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                foreach ($sendMethods as $groupLabel => $groupKeys) {
                    foreach ($groupKeys as $groupKey) {
                        $needle = is_numeric($groupKey)
                            ? ($config[$groupKey] ?? '')
                            : ($config[$groupKey] ?? $groupKey);

                        $keywords = [];
                        if ($groupLabel === 'پیک شهری') {
                            $keywords = ['peykeShahri', 'پیک شهری', $config['peykeShahri'] ?? '', $config[6] ?? ''];
                        } elseif ($groupLabel === 'پست') {
                            $keywords = ['post', 'postCost', 'پست', $config['post'] ?? '', $config['postCost'] ?? '', $config[3] ?? ''];
                        } elseif ($groupLabel === 'تیپاکس') {
                            $keywords = ['peyk', 'peykCost', 'پیک', $config['peyk'] ?? '', $config['peykCost'] ?? '', $config[4] ?? ''];
                        } else {
                            $keywords = [$needle];
                        }

                        foreach ($keywords as $keyword) {
                            $cleanKeyword = Str::lower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $keyword)));
                            if (!empty($cleanKeyword) && mb_strpos($clean, $cleanKeyword) !== false) {
                                $methodCount[$groupLabel]++;
                                $found = true;
                                break 3;
                            }
                        }
                    }
                }
            }

            if (!$found) {
                $methodCount['نامشخص']++;
            }
        }

        $chartLabels = [];
        $chartData = [];
        $colorPalette = ['#FF1744','#1976D2','#FFD600','#43A047','#8E24AA',
                        '#FF6F00','#00B8D4','#6D4C41','#C51162','#00C853','#9E9E9E', '#D79422'];

        foreach ($methodCount as $group => $count) {
            if ($count > 0) {
                $chartLabels[] = $group;
                $chartData[] = $count;
            }
        }

        return view('orders.deliveryReport', [
            'chartData' => [
                'labels' => $chartLabels,
                'data' => $chartData,
                'colors' => array_slice($colorPalette, 0, count($chartData)),
                'total' => $totalOrders
            ],
            'from' => $request->from,
            'to' => $request->to,
        ]);
    }
}
