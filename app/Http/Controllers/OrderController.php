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
        ]);
    }

    public function newForm(Request $req)
    {
        if ($this->superAdmin())
            return redirect()->back();
        $city = $req->city ?: 't';
        if ($this->safir())
            $city = 't';
        $user = auth()->user();
        $order = new Order();

        $customersData = $user->customers()->get();

        //استثنا آقای عبدی
        if ($this->safir() || $user->id != 57) {
            $products = Product::where('category', 'final')->where('location', 't')->where('price', '>', '1')->where('available', true)->get()->keyBy('id');
        } else {
            $products = Product::where('category', '<>', 'pack')->where('location', 't')->where('available', true)->get()->keyBy('id');
        }
        $cart = [];
        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
            $cart[$id] = '';
        }

        $customers = $customersData->keyBy('name');
        $customersId = $customersData->keyBy('id');
        $customer = new Customer();
        $customer->city_id = 301;
        $cities = City::all()->keyBy('name');
        $citiesId = $cities->keyBy('id');
        $province = Province::all()->keyBy('id');
        return view('addEditOrder.addEditOrder', [
            'customers' => $customers,
            'customersId' => $customersId,
            'products' => $products,
            'settings' => $this->settings(),
            'user' => $user,
            'cart' => $cart,
            'creatorIsAdmin' => ($this->superAdmin() || $this->admin()),
            'order' => $order,
            'customer' => $customer,
            'cities' => $cities,
            'citiesId' => $citiesId,
            'location' => $city,
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
        $request->orderList = [];

        foreach ($products as $id => $product) {
            $number = $request['product_' . $id];
            if ($number > 0) {
                $request->orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';
                $discount = +$this->calculateDis($id);
                if ($this->superAdmin() || $this->admin())
                    $discount = +$request['discount_' . $id];
                $price = round((100 - $discount) * $product->price / 100);
                if (($this->superAdmin() || $this->admin()) && $discount == 0)
//                    $price = max(+str_replace(",", "", $request['price_' . $id]), $product->price);
                    $price = +str_replace(",", "", $request['price_' . $id]);
                $total += $price * $number;
                $Total += $product->price * $number;
                $request->orderList[$id] = [
                    'name' => $product->name,
                    'price' => $price,
                    'photo' => $product->photo,
                    'product_id' => $product->id,
                    'number' => $number,
                    'discount' => $discount,
                    'verified' => $this->safir(),
                ];
            }
        }
        if ($request->orders == '') {
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

        if ($this->superAdmin()) {
            $order = Order::with('customer.city.province')->with('user')->findOrFail($id);
            $customersData = Customer::all();
        } else {
            $order = $user->orders()->with('customer.city.province')->with('user')->findOrFail($id);
            $customersData = $user->customers()->get();
        }

        if (($order->state && $order->user->safir()) || ($order->confirm && $order->user->admin()))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);

        //استثنا آقای عبدی
        if ($this->safir() || $user->id != 57) {
            $products = Product::where('category', 'final')->where('location', $order->location)->where('available', true)->where('price', '>', '1')->get()->keyBy('id');
        } else {
            $products = Product::where('category', '<>', 'pack')->where('location', $order->location)->where('available', true)->get()->keyBy('id');
        }
        $cart = [];
        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
            $cart[$id] = '';
        }
        $customers = $customersData->keyBy('name');
        $customersId = $customersData->keyBy('id');
        $selectedProducts = $order->orderProducts()->get();
        foreach ($selectedProducts as $product) {
            if (isset($products[$product->product_id])) {
                $cart[$product->product_id] = +$product->number;
                $products[$product->product_id]->coupon = +$product->discount;
                $products[$product->product_id]->priceWithDiscount = round((100 - +$product->discount) * $products[$product->product_id]->price / 100);
            }
        }
        $customer = $order->customer;
        if (!$customer) {
            $customer = new Customer();
            $customer->city_id = 0;
        }
        $cities = City::all()->keyBy('name');
        $citiesId = $cities->keyBy('id');

        return view('addEditOrder.addEditOrder')->with([
            'cart' => $cart,
            'cities' => $cities,
            'citiesId' => $citiesId,
            'creatorIsAdmin' => $order->user->admin(),
            'customer' => $customer,
            'customers' => $customers,
            'customersId' => $customersId,
            'edit' => true,
            'id' => $user->id,
            'location' => $order->location,
            'order' => $order,
            'products' => $products,
            'settings' => $this->settings(),
            'user' => $user,
        ]);
    }

    public function update($id, Request $request)
    {
        DB::beginTransaction();

        if ($this->superAdmin())
            $order = Order::with('user')->with('orderProducts')->findOrFail($id);
        else
            $order = auth()->user()->orders()->with('orderProducts')->with('user')->findOrFail($id);

        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|digits:11',
        ]);

        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);

        if (!$order->user->safir()) {
            $orders = '';
            $products = Product::where('available', true)->where('location', $request->location)->get()->keyBy('id');
            $productOrders = $order->orderProducts->keyBy('product_id');
            $total = 0;
            foreach ($products as $id => $product) {
                $number = $request['product_' . $id];
                if ($number > 0) {
                    $coupon = +$request['discount_' . $id];
                    if ($coupon == 0)
                        $price = +str_replace(",", "", $request['price_' . $id]);
                    else
                        $price = round((100 - $coupon) * $product->price / 100);
                    $total += $price * $number;
                    $orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';

                    if (isset($productOrders[$id]))
                        $productOrders[$id]->update([
                            'discount' => $request['discount_' . $id],
                            'number' => $number,
                            'price' => $price,
                        ]);
                    else
                        $order->orderProducts()->create([
                            'discount' => $request['discount_' . $id],
                            'number' => $number,
                            'price' => $price,
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
            'name' => $order->user->safir() ? $request->name : $order->name,
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

    public function pdfs($ids)
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
            if ($order->location != 't')
                $order->desc .= '(انبار ' . $this->city[$order->location][0] . ')';

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
            } while ($mpdf->page > 1 || $font < 6);
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
        if ($order->location != 't')
            $order->desc .= '(انبار ' . $this->city[$order->location][0] . ')';
        if ($request->onlyOrderData) {
            return $order;
        }
        $order->city = $this->city($order->user()->first())[0];
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

    public function cancelInvoice($id)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if (!$order->confirm)
            return $order;
        if ($order->state)
            $order->state = 4;
        $order->confirm = false;
        if ($order->counter == 'approved') {
            $customerController = new CustomerController();
            $customerController->rejectOrder($id);
        }
        $this->removeFromCustomerTransactions($order);
        $order->counter = 'waiting';
        $order->paymentMethod = null;
        $order->payInDate = null;
        $order->paymentNote = null;
        $order->save();

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
            app('Telegram')->deleteOrderFromBale($order, env('GroupId'));
//            $this->deleteFromBale(env('GroupId'), $order->bale_id);
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
            'category' => $request->category ?: 0,
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
        $trans = $customer->transactions()->create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'type' => false,
            'description' => 'تایید سفارش ' . $order->id . ' - ' . auth()->user()->name,
        ]);
        DB::commit();
        return $trans;
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
            'counter' => $this->safir() ? 'approved' : 'waiting',
            'location' => $request->location,
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

    public function paymentMethod($id, Request $req)
    {
        if ($this->superAdmin())
            $order = Order::with('customer')->findOrFail($id);
        else
            $order = auth()->user()->orders()->with('customer')->findOrFail($id);
        request()->validate([
            'paymentMethod' => 'required',
            'cashPhoto' => 'mimes:jpeg,jpg,png,bmp,pdf|max:3048',
            'chequePhoto' => 'mimes:jpeg,jpg,png,bmp,pdf|max:3048',
        ]);
        DB::beginTransaction();
        $paymentMethod = $req->paymentMethod;
        $photo = null;
        $date = null;
        if ($paymentMethod == 'cash') {
            if (!$req->file("cashPhoto"))
                return ['error', 'باید عکس رسید بانکی بارگذاری شود.'];
            $photo = $req->file("cashPhoto")->store("", 'deposit');
            $photo2 = $req->file("cashPhoto")->store("", 'receipt');

        }
        if ($paymentMethod == 'cheque') {
            if ($req->file("chequePhoto"))
                $photo = $req->file("chequePhoto")->store("", 'deposit');
            $photo2 = $req->file("chequePhoto")->store("", 'receipt');
        }
        if ($paymentMethod == 'payInDate') {
            if (strlen($req->payInDate) != 10)
                return ['error', 'تاریخ باید مشخص شود.'];
        }
        $order->update([
            'confirm' => 1,
            'paymentMethod' => $paymentMethod,
            'payInDate' => $req->payInDate,
            'paymentNote' => $req->note,
            'counter' => 'waiting',
        ]);
        $trans1 = $this->addToCustomerTransactions($order);
        if ($photo) {
            $trans2 = $order->customer->transactions()->create([
                'amount' => $order->total,
                'type' => true,
                'verified' => 'waiting',
                'description' => $req->note . '/ ' . $order->payMethod(),
                'photo' => $photo,
                'paymentLink' => $trans1->id,
            ]);
            $trans1->update([
                'paymentLink' => $trans2->id,
            ]);
            $order->update([
                'receipt' => $photo2,
            ]);
        }

        DB::commit();
        return ['ok', $order];
    }
}

