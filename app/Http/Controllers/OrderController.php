<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CouponLink;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class OrderController extends Controller
{
    public function showOrders()
    {
        if ($this->superAdmin() || $this->print()) {
            $users = User::withTrashed()->get()->keyBy("id");
            $orders = Order::withTrashed()->orderBy('id', 'desc')
                ->limit($this->settings()->loadOrders)->get()->keyBy('id');
        } else {
            $users = array();
            $users[auth()->user()->id] = auth()->user();
            $orders = auth()->user()->orders()->withTrashed()
                ->orderBy('id', 'desc')->limit($this->settings()->loadOrders)->get()->keyBy('id');
        }
        return view('orders.orders', [
            'users' => $users,
            'orders' => $orders,
            'userId' => auth()->user()->id,
            'limit' => $this->settings()->loadOrders,
//            'sendMethods' => $orders->first()->sendMethods(),
        ]);
    }

    public function newForm()
    {
        $user = auth()->user();
        if (($this->superAdmin() || $this->admin()) && $user->id != 57) {
            $products = Product::where('category', '<>', 'pack')->get()->keyBy('id');
            $customersData = Customer::all();
        } else {
            $products = Product::where('category', 'final')->where('price', '>', '1')->get()->keyBy('id');
            $customersData = $user->customers()->get();
        }
        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
        }
        $order = new Order();
        $customers = $customersData->keyBy('name');
        $customersId = $customersData->keyBy('id');
        $customer = new Customer();
        $customer->city_id = 301;
        $cities = City::all()->keyBy('name');
        $citiesId = $cities->keyBy('id');
        $province = Province::all()->keyBy('id');
        return view('addEditOrder.addEditOrder', [
            'edit' => false,
            'customers' => $customers,
            'customersId' => $customersId,
            'products' => $products,
            'settings' => $this->settings(),
            'id' => $user->id,
            'cart' => (object)[],
            'creator' => ($this->superAdmin() || $this->admin()),
            'order' => $order,
            'customer' => $customer,
            'cities' => $cities,
            'citiesId' => $citiesId,
            'province' => $province,
        ]);
    }

    public function insertOrder(Request $request)
    {
        DB::beginTransaction();
        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp,pdf|max:3048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|string|min:11,max:11',
        ]);
        $request->phone = $this->number_Fa_En($request->phone); //تبدیل اعداد فارسی به انگلیسی
        $request->zip_code = $this->number_Fa_En($request->zip_code); //تبدیل اعداد فارسی به انگلیسی

        $user = auth()->user();
        $products = Product::where('available', true)->get()->keyBy('id');
        $request->orders = '';
        $Total = 0;     //جمع بدون احتساب تخفیف
        $total = 0;  //جمع با احتساب تخفیف
        $request->customerCost = 0;
        $admin = $this->superAdmin() || $this->admin();
        $request->orderList = [];
        $counter = 0;
        foreach ($products as $id => $product) {
            $number = $request['product_' . $id];
            if ($number > 0) {
                $request->orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';
                $counter++;
                $coupon = $this->calculateDis($id);
                if ($admin)
                    $coupon = $request['discount_' . $id];
                $price = round((100 - $coupon) * $product->price / 100);
                $total += $price * $number;
                $Total += $product->price * $number;
                $request->orderList[$id] = [
                    'name' => $product->name,
                    'price' => $price,
                    'photo' => $product->photo,
                    'product_id' => $product->id,
                    'number' => $number,
                    'discount' => $coupon,
                    'verified' => !$admin,
                ];
            }
        }
        if (!count($request->orderList)) {
            return $this->errorBack('محصولی انتخاب نشده است!');
        }

        if ($this->safir()) {
            $deliveryCost = $this->deliveryCost($request->deliveryMethod);
            if ($Total < $this->settings()->freeDelivery || $user->id == 10) // استثنا خانوم موسوی
                $total += $deliveryCost;
            if ($request->paymentMethod == 'credit') {
                if ($total > ($user->balance + $this->settings()->negative)) {
                    return $this->errorBack('اعتبار شما کافی نیست!');
                } else {
                    $user->update([
                        'balance' => $user->balance - $total
                    ]);
                }
            } elseif ($request->paymentMethod == 'receipt') {
                if ($request->file("receipt"))
                    $request->receipt = $request->file("receipt")->store("", 'receipt');
                else
                    return $this->errorBack('باید عکس رسید بانکی بارگذاری شود!');

            } elseif ($request->paymentMethod == 'onDelivery') {
                $request->desc .= '- پرداخت در محل';
                $request->customerCost = round($Total * (100 - $request->customerDiscount) / 100 + $deliveryCost);
            } else
                return $this->errorBack('روش پرداخت به درستی انتخاب نشده است!');
        }

        $request->total = $total;

        $request->customerId = $this->addToCustomers($request);
        if ($request->customerId == 'not match')
            return $this->errorBack('نام مشتری مطابقت ندارد!');

        $order = $this->addToOrders($request);

        $this->addToOrderProducts($request, $order);

        $this->addToTransactions($request, $order);

        if ($this->safir()) {
            foreach ($request->orderList as $id => $product) {
                $products[$id]->update([
                    'quantity' => $products[$id]->quantity - $product['number'],
                ]);
                $order->productChange()->create([
                    'product_id' => $product['product_id'],
                    'change' => -$product['number'],
                    'quantity' => $products[$id]->quantity,
                    'desc' => ' خرید سفیر ' . $user->name
                ]);
            }
            $order->bale_id = app('Telegram')->sendOrderToBale($order, env('GroupId'))->result->message_id;
            $order->save();
        }

        DB::commit();

        return redirect()->route('listOrders');
    }

    public function editForm($id)
    {
        $user = auth()->user();
        if ($this->superAdmin())
            $order = Order::findOrFail($id);
        else
            $order = $user->orders()->findOrFail($id);
        $creator = !$order->user()->first()->safir();

        if ($order->state || ($order->confirm && $creator))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);

        if (($this->superAdmin() || $this->admin()) && $user->id != 57) {
            $products = Product::where('category', '<>', 'pack')->get()->keyBy('id');
            $customersData = Customer::all();
        } else {
            $products = Product::where('category', 'final')->where('price', '>', '1')->get()->keyBy('id');
            $customersData = $user->customers()->get();
        }
        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
        }
        $customers = $customersData->keyBy('name');
        $customersId = $customersData->keyBy('id');
        $selectedProducts = $order->orderProducts()->get();
        $cart = [];
        foreach ($selectedProducts as $product) {
            $cart[$product->product_id] = +$product->number;
            $products[$product->product_id]->coupon = +$product->discount;
            $products[$product->product_id]->priceWithDiscount = round((100 - +$product->discount) * $product->price / 100);
        }
//        $customer = null;
//        if($creator)
        $customer = $order->customer()->first();
        if (!$customer) {
            $customer = new Customer();
            $customer->city_id = 0;
        }
        $cities = City::all()->keyBy('name');
        $citiesId = $cities->keyBy('id');
        $province = Province::all()->keyBy('id');

        return view('addEditOrder.addEditOrder')->with([
            'edit' => true,
            'order' => $order,
            'customers' => $customers,
            'customersId' => $customersId,
            'products' => $products,
            'settings' => $this->settings(),
            'id' => $user->id,
            'cart' => $cart,
            'creator' => $creator,
            'customer' => $customer,
            'cities' => $cities,
            'citiesId' => $citiesId,
            'province' => $province,
        ]);
    }

    public function editOrder($id, Request $request)
    {
        DB::beginTransaction();

        if ($this->superAdmin())
            $order = Order::findOrFail($id);
        else
            $order = auth()->user()->orders()->findOrFail($id);

        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|digits:11',
        ]);

        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);

        if (!$order->user()->first()->safir()) {
            $orders = '';
            $products = Product::where('available', true)->get()->keyBy('id');
            $productOrders = $order->orderProducts()->get()->keyBy('product_id');
            $total = 0;
            $counter = 0;
            foreach ($products as $id => $product) {
                $number = $request['product_' . $id];
                if ($number > 0) {
                    $coupon = $request['discount_' . $id];
                    $total += round((100 - $coupon) * $product->price * $number / 100);
                    $counter++;
                    $orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';
                    if (isset($productOrders[$id]))
                        $productOrders[$id]->update([
                            'discount' => $request['discount_' . $id],
                            'number' => $number,
                            'price' => round((100 - $request['discount_' . $id]) * $product->price / 100),
                        ]);
                    else
                        $order->orderProducts()->create([
                            'discount' => $request['discount_' . $id],
                            'number' => $number,
                            'price' => round((100 - $request['discount_' . $id]) * $product->price / 100),
                            'name' => $product->name,
                            'product_id' => $id,
                        ]);
                }
            }
            foreach ($productOrders as $product_id => $productOrder) {
                $number = $request['product_' . $product_id];
                if ($number < 1 && $productOrder->number > 0)
                    $productOrder->delete();
            }
        } else {
            $orders = $order->orders;
            $total = $order->total;
        }

        $order->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'desc' => $request->desc,
            'orders' => $orders,
            'total' => $total,
        ]);

        $this->addToCustomers($request);
        app('Telegram')->editOrderInBale($order, env('GroupId'));
        DB::commit();

        return redirect()->route('listOrders');
    }

    public function changeState($id, Request $req)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        $user = $order->user()->first();
        $order->state = +$req->state;

        if ($order->paymentMethod == 'onDelivery') {
            if ($order->state == 1) {
                $user->balance += $order->customerCost - $order->total;
                $order->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $order->customerCost - $order->total,
                    'balance' => $user->balance,
                    'type' => true,
                    'description' => 'سهم سفیر(پرداخت در محل)',
                ]);
            } elseif ($order->state == 0) {
                $user->balance -= $order->customerCost - $order->total;
                $order->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $order->customerCost - $order->total,
                    'balance' => $user->balance,
                    'type' => false,
                    'description' => 'حذف سهم سفیر(پرداخت در محل)',
                ]);
            }
        }
        $order->save();
        $user->save();
        DB::commit();
        return +$order->state;
    }

    public function pdfs($ids): string
    {
        $idArray = explode(",", $ids);
        $fonts = array();
        $orders = array();
        foreach ($idArray as $id) {
            $order = Order::findOrFail($id);
            if ($order->state == 1)
                $order->update([
                    'state' => 2
                ]);

            $font = 32;
            $order = $this->addCityToAddress($order);
            do {
                if ($font < 19 && !$order->user()->first()->safir()) {
                    $order->orders = 'طبق فاکتور';
                    $font = 32;
                }
                $font = $font - 1;

                $pdf = PDF::loadView('pdfs', ['orders' => [$order], 'fonts' => [$font]], []);
                $mpdf = $pdf->getMpdf();
            } while ($mpdf->page > 1 || $font < 5);
            $fonts[] = $font;
            $orders[] = $order;

        }

        $pdfs = PDF::loadView('pdfs', ['orders' => $orders, 'fonts' => $fonts], []);
        $fileName = $ids . '(' . sizeof($idArray) . ').pdf';
        $pdfs->getMpdf()->OutputFile('pdf/' . $fileName);
        return 'pdf/' . $fileName;
    }

    public function invoice($id, Request $request)
    {
        if ($this->superAdmin() || $this->print())
            $order = Order::findOrFail($id);
        else
            $order = auth()->user()->orders()->findOrFail($id);
        $order = $this->addCityToAddress($order);
        if ($request->onlyOrderData) {
            return $order;
        }

        $firstPageItems = $request->firstPageItems;
        $totalPages = $request->totalPages;
        $orderProducts = OrderProduct::where('order_id', $id)->get();
        $number = $orderProducts->count();
        if ($totalPages > 1) {
            $v1 = view('orders.invoice', [
                'firstPage' => '',
                'lastPage' => 'd-none',
                'page' => '1',
                'pages' => '2',
                'order' => $order,
                'orderProducts' => $orderProducts,
                'firstPageItems' => $firstPageItems,
            ])->render();
            $v2 = view('orders.invoice', [
                'firstPage' => 'd-none',
                'lastPage' => '',
                'page' => '2',
                'pages' => '2',
                'order' => $order,
                'orderProducts' => $orderProducts,
                'firstPageItems' => $firstPageItems,
            ])->render();
            return [[$v1, $order->id . '(page1)'], [$v2, $order->id . '(page2)']];
        } else {
            return [[view('orders.invoice', [
                'firstPage' => '',
                'lastPage' => '',
                'page' => '1',
                'pages' => '1',
                'order' => $order,
                'orderProducts' => $orderProducts,
                'firstPageItems' => $firstPageItems,
            ])->render(), $order->id]];
        }
    }

    public function confirmInvoice($id, Request $request)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($order->confirm || $order->state)
            return $order;
        $order->paymentMethod = $request->pay;
        $order->confirm = +$request->confirm;
        $order->save();
        $orderProducts = $order->orderProducts();
        $orderProducts->update(['verified' => true]);
        foreach ($orderProducts->get() as $orderProduct) {
            $product = $orderProduct->product()->first();
            $product->update([
                'quantity' => $product->quantity - $orderProduct->number,
            ]);
            $order->productChange()->create([
                'product_id' => $product->id,
                'change' => -$orderProduct->number,
                'quantity' => $product->quantity,
                'desc' => ' خرید مشتری ' . $order->name,
            ]);
        }
        $this->addToCustomerTransactions($order);
        $order->bale_id = app('Telegram')->sendOrderToBale($order, env('GroupId'))->result->message_id;
        $order->save();
        DB::commit();
        return $order;
    }

    public function cancelInvoice($id)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if (!$order->confirm || $order->state)
            return $order;
        $order->confirm = false;
        $order->save();
        $order->orderProducts()->update(['verified' => false]);
        foreach ($order->productChange()->get() as $productChange) {
            $product = $productChange->product()->first();
            $product->update([
                'quantity' => $product->quantity - $productChange->change,
            ]);
        }
        $order->productChange()->delete();
        $this->removeFromCustomerTransactions($order);
        $this->deleteFromBale(env('GroupId'), $order->bale_id);
        DB::commit();
        return $order;
    }

    public function deleteOrder($id, Request $request)
    {
        DB::beginTransaction();
        if ($this->superAdmin())
            $order = Order::findOrFail($id);
        else
            $order = auth()->user()->orders()->findOrFail($id);

        if ($order->state || ($order->confirm && $order->user()->first()->role !== 'user'))
            return 'سفارش نمی تواند حذف شود، چون پردازش شده است!';

        if ($order->delete()) {
            $orderProducts = $order->orderProducts()->delete();
            if ($order->user()->first()->safir()) {
                foreach ($order->productChange()->get() as $productChange) {
                    $product = $productChange->product()->first();
                    if ($product)
                        $product->update([
                            'quantity' => $product->quantity - $productChange->change,
                        ]);
                }
                $order->productChange()->delete();
            }

            if ($order->paymentMethod == 'credit' && $order->user()->first()->safir()) {
                $user = $order->user()->first();
                $user->update([
                    'balance' => $user->balance + $order->total,
                ]);
                $order->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $order->total,
                    'balance' => $user->balance,
                    'type' => true,
                    'description' => 'حذف سفارش',
                ]);

            }
            $this->deleteFromBale(env('GroupId'), $order->bale_id);
            DB::commit();
            return ['با موفقیت حذف شد', $order];
        };
        return 'مشکلی به وجود آمده!';
    }

    public function calculateDis($product_id)
    {
        $dis = 0;
        $user_id = auth()->user()->id;
        $couponLinks = CouponLink::where('product_id', $product_id)->where('user_id', $user_id)->get();
        foreach ($couponLinks as $couponLink) {
            $dis = max($dis, $couponLink->coupon()->first()->percent);
        }
        return $dis + $this->settings()->minCoupon;
    }

    public function addToCustomers($request)
    {
        if ($this->safir()) {
            $request->customerId = false;
        }
        if ($this->safir() && !$request->addToCustomers) {
            return null;
        }
        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'category' => $request->category?:0,
            'city_id' => $request->city_id,
        ];
        if (!$request->addToCustomers && ($this->superAdmin() || $this->admin())) {
            if ($request->customerId) {
                $customer = Customer::findOrFail($request->customerId);
                if ($customer->name != $request->name) {
                    return 'not match';
                }
            } else {
                $customer = auth()->user()->customers()->Create($data);
            }
        }

        if ($request->addToCustomers) {
            if ($request->customerId) {
                $customer = Customer::findOrFail($request->customerId);
                $customer->update($data);
            } else {
                $customer = auth()->user()->customers()->Create($data);
            }
        }
        return $customer->id;
    }

    public function addToTransactions($request, $order)
    {
        if ($this->safir() && $request->paymentMethod == 'credit')
            $order->transactions()->create([
                'user_id' => auth()->user()->id,
                'amount' => $request->total,
                'balance' => auth()->user()->balance,
                'type' => false,
                'description' => 'ثبت سفارش',
            ]);
    }

    public function addToCustomerTransactions($order)
    {
        DB::beginTransaction();
        $customer = $order->customer()->first();
        $customer->update([
            'balance' => $customer->balance - $order->total,
        ]);
        $customer->transactions()->create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'balance' => $customer->balance,
            'type' => false,
            'description' => 'تایید سفارش ' . $order->id . ' - ' . auth()->user()->name,
        ]);
        DB::commit();
    }

    public function removeFromCustomerTransactions($order)
    {
        DB::beginTransaction();
        $customerTransaction = $order->customerTransactions()->latest()->first();
        $customer = $customerTransaction->customer()->first();
        $order->customerTransactions()->create([
            'amount' => $customerTransaction->amount,
            'description' => 'ابطال سفارش  ' . $order->id . ' - ' . auth()->user()->name,
            'type' => true,
            'balance' => $customer->balance + $customerTransaction->amount,
            'customer_id' => $customer->id,
            'deleted' => true,
        ]);
        $customerTransaction->update([
            'deleted' => true,
            'description' => $customerTransaction->description . '* باطل شد'
        ]);
        $customer->update([
            'balance' => $customer->balance + $customerTransaction->amount,
        ]);
        DB::commit();
    }

    public function addToOrderProducts($request, $order)
    {
        foreach ($request->orderList as $id => $product) {
            $order->orderProducts()->create($product);
        }
    }

    public function addToOrders($request)
    {
        return auth()->user()->orders()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'orders' => $request->orders,
            'desc' => $request->desc,
            'receipt' => $request->receipt,
            'total' => $request->total,
            'customerCost' => $request->customerCost,
            'paymentMethod' => $request->paymentMethod,
            'deliveryMethod' => $request->deliveryMethod,
            'customer_id' => $request->customerId,
            'confirm' => $this->safir(),
        ]);
    }

    public function dateFilter(Request $request)
    {
        $from = date($request->date1 . ' 00:00:00');
        $to = date($request->date2 . ' 23:59:59');
        $limit = $request->limit;

        if ($this->superAdmin() || $this->print()) {
            $orders = Order::withTrashed()
                ->whereBetween('created_at', [$from, $to])
                ->limit($limit)
                ->get()->keyBy('id');
        } else {
            $orders = auth()->user()->orders()->withTrashed()
                ->whereBetween('created_at', [$from, $to])
                ->limit($limit)
                ->get()->keyBy('id');
        }
        return $orders;
    }

    public function viewOrder($id)
    {
        if ($this->superAdmin())
            $order = Order::withTrashed()->findOrFail($id);
        else
            $order = auth()->user()->orders()->withTrashed()->findOrFail($id);
        $order = $this->addCityToAddress($order);
        return view('orders.view', ['order' => $order]);
    }

    public function setSendMethod($id, Request $req)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if (!$order->deliveryMethod)
            $order->deliveryMethod = '';
        $order->deliveryMethod .= ' - ' . $req->sendMethod;
        $order->save();
        DB::commit();
        return $order;
    }
}
