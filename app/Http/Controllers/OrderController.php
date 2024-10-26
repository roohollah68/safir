<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\City;
use App\Models\CouponLink;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Illuminate\Database\Eloquent\Builder;


class OrderController extends Controller
{
    public function showOrders()
    {
        $user = auth()->user();
        if (auth()->user()->meta('showAllOrders')) {
            $users = User::withTrashed()->get()->keyBy("id");
            $orders = Order::withTrashed()->orderBy('id', 'desc')->with(['website', 'orderProducts'])
                ->limit($user->meta('NuRecords'))->get()->keyBy('id');
        } else {
            $users = array(auth()->user()->id => auth()->user());
            $orders = auth()->user()->orders()->withTrashed()
                ->orderBy('id', 'desc')->with(['orderProducts'])->limit($user->meta('NuRecords'))->get()->keyBy('id');
        }

        foreach ($orders as $order) {
            $order->orders = $order->orders();
        }

        return view('orders.orders', [
            'users' => $users,
            'orders' => $orders,
            'user' => auth()->user(),
            'limit' => $user->meta('NuRecords'),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function newOrder(Request $req)
    {
        Helper::access('addOrder');
        $user = auth()->user();
        $order = new Order();
        $warehouseId = $req->warehouseId ?: $user->meta('warehouseId');
        $products = Product::where('warehouse_id', $warehouseId)->where('available', true)->
        whereHas('good', function (Builder $query) {
            if (auth()->user()->meta('sellRawProduct'))
                $query->where('category', '<>', 'pack');
            else
                $query->where('category', 'final');
        })->get()->keyBy('id');
        $products = $this->calculateDis($products, $user);

        $order->customer = new Customer();
        $order->customer->city_id = 301;
        $cities = City::with('province')->get()->keyBy('id');

        return view('addEditOrder.addEditOrder', [
            'products' => $products,
            'settings' => Helper::settings(),
            'user' => $user,
            'cart' => [],
            'creatorIsAdmin' => ($this->superAdmin() || $this->admin()),
            'order' => $order,
            'customers' => $user->customers()->get()->keyBy('id'),
            'cities' => $cities,
            'warehouses' => Warehouse::all(),
            'warehouseId' => $warehouseId,
        ]);
    }

    public function insertOrder(Request $request)
    {
        Helper::access('addOrder');
        DB::beginTransaction();
        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp,pdf|max:3048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|string|min:11,max:11',
        ]);
        $user = auth()->user();

        $order = new Order;
        $order->name = $request->name;
        $order->phone = Helper::number_Fa_En($request->phone);
        $order->zip_code = Helper::number_Fa_En($request->zip_code);
        $order->orders = '';
        $order->desc = $request->desc;
        $order->total = 0;  //جمع با احتساب تخفیف
        $Total = 0;     //جمع بدون احتساب تخفیف
        $order->paymentMethod = $request->paymentMethod;
        $order->deliveryMethod = $request->deliveryMethod;
        $order->confirm = $this->safir();
        $order->counter = $this->safir() ? 'approved' : 'waiting';
        $order->user_id = $user->id;
        $order->address = $request->address;
        $order->warehouse_id = $request->warehouseId;
        $products = Product::find(array_keys($request->cart))->keyBy('id');
        $products = $this->calculateDis($products, $user);
        $request->orderList = [];
        if (count($request->cart) == 0) {
            return $this->errorBack('محصولی انتخاب نشده است!');
        }
        foreach ($request->cart as $id => $number) {
            $product = $products[$id];
//            $order->orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';
            $discount = +$product->coupon;
            if (auth()->user()->meta('changeDiscount'))
                $discount = +$request['discount_' . $id];
            $price = round((100 - $discount) * $product->price / 100);
            if (auth()->user()->meta('changePrice') && $discount == 0)
                $price = +str_replace(",", "", $request['price_' . $id]);
            $order->total += $price * $number;
            $Total += $product->price * $number;
            $order->save();
            $order->orderProducts()->create([
                'name' => $product->name,
                'price' => $price,
                'product_id' => $product->id,
                'number' => $number,
                'discount' => $discount,
                'verified' => $this->safir(),
            ]);
        }
        if ($order->total < 0) {
            $order->desc .= ' (فاکتور برگشت به انبار)';
        }
        if ($this->safir()) {
//            $order->payPercent = 100;
            $deliveryCost = Helper::settings()->{$request->deliveryMethod};
            if ($Total < Helper::settings()->freeDelivery || $user->id == 10) // استثنا خانوم موسوی
                $order->total += $deliveryCost;
            if ($request->paymentMethod == 'credit') {
                if ($order->total > ($user->balance + Helper::settings()->negative)) {
                    return $this->errorBack('اعتبار شما کافی نیست!');
                } else {
                    $user->update([
                        'balance' => $user->balance - $order->total
                    ]);
                }
            } elseif ($request->paymentMethod == 'receipt') {
                if ($request->file("receipt"))
                    $order->receipt = $request->file("receipt")->store("", 'receipt');
                else
                    return $this->errorBack('باید عکس رسید بانکی بارگذاری شود!');

            } elseif ($request->paymentMethod == 'onDelivery') {
                $request->desc .= '- پرداخت در محل';
                $order->customerCost = round($Total * (100 - $request->customerDiscount) / 100 + $deliveryCost);
            } else
                return $this->errorBack('روش پرداخت به درستی انتخاب نشده است!');
        }
        $order->customer_id = $this->addToCustomers($request, $order);
        if ($order->customer_id == 'not match')
            return $this->errorBack('نام مشتری مطابقت ندارد!');

        $this->addToTransactions($request, $order);

        if ($this->safir()) {
            app('Telegram')->sendOrderToBale($order, env('GroupId'));
        }
        $order->save();
        (new CommentController)->create($order, $user, 'سفارش ایجاد شد');

        DB::commit();

        return redirect()->route('listOrders');
    }

    public function editOrder($id)
    {
        if (auth()->user()->meta('showAllOrders')) {
            $order = Order::with('customer.city')->with('user')->findOrFail($id);
        } else {
            $order = auth()->user()->orders()->with('customer.city')->with('user')->findOrFail($id);
        }
        $user = $order->user()->first();
        if (($order->state && $order->user->safir()) || ($order->confirm && $order->user->admin()))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);
        $products = Product::where('warehouse_id', $order->warehouse_id)->where('available', true)->
        whereHas('good', function (Builder $query) {
            if (auth()->user()->meta('sellRawProduct'))
                $query->where('category', '<>', 'pack');
            else
                $query->where('category', 'final');
        })->get()->keyBy('id');
        $cart = [];

        $products = $this->calculateDis($products, $user);

        $selectedProducts = $order->orderProducts()->get()->keyBy('product_id');
        foreach ($selectedProducts as $id => $orderProduct) {
            if (isset($products[$id])) {
                $cart[$id] = +$orderProduct->number;
                $products[$id]->coupon = +$orderProduct->discount;
                $products[$id]->priceWithDiscount = +$orderProduct->price;
            }
        }
        if (!$order->customer) {
            $order->customer = new Customer();
            $order->customer->city_id = 0;
        }
        $cities = City::with('province')->get()->keyBy('id');
        return view('addEditOrder.addEditOrder')->with([
            'cart' => $cart,
            'cities' => $cities,
            'creatorIsAdmin' => $order->user->admin(),
            'customers' => $user->customers()->get()->keyBy('id'),
            'edit' => true,
            'order' => $order,
            'products' => $products,
            'settings' => Helper::settings(),
            'user' => $user,
            'warehouseId' => $order->warehouse_id
        ]);
    }

    public function updateOrder($id, Request $request)
    {
        DB::beginTransaction();
        if (auth()->user()->meta('showAllOrders'))
            $order = Order::with('user')->with('orderProducts')->findOrFail($id);
        else
            $order = auth()->user()->orders()->with('orderProducts')->with('user')->findOrFail($id);

        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|string|min:11,max:11',
        ]);

        $request->phone = Helper::number_Fa_En($request->phone);
        $request->zip_code = Helper::number_Fa_En($request->zip_code);

        if (!$order->user->safir()) {
            $orders = '';
            $products = Product::where('available', true)->where('warehouse_id', $order->warehouse_id)->get()->keyBy('id');
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
//                    $orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';

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
        $order->paymentLinks()->delete();
        (new CommentController)->create($order, auth()->user(), 'سفارش ویرایش شد');

        $this->addToCustomers($request, $order);
        app('Telegram')->editOrderInBale($order, env('GroupId'));
        DB::commit();

        return redirect()->route('listOrders');
    }

    public function changeState($id, $state)
    {
        Helper::access('showAllOrders');
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        $user = $order->user()->first();
        $order->state = +$state;

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
        $text = null;
        if ($order->state == 0) {
            $text = 'سفارش به انبار بازگشت';
            foreach ($order->productChange()->get() as $productChange) {
                $productChange->update(['isDeleted' => true]);
                $product = $productChange->product;
                if (!$product)
                    continue;
                $product->update([
                    'quantity' => $product->quantity - $productChange->change,
                ]);
                $order->productChange()->create([
                    'product_id' => $productChange->product_id,
                    'change' => -$productChange->change,
                    'quantity' => $product->quantity,
                    'desc' => 'لغو خرید مشتری ' . $order->name,
                    'isDeleted' => true,
                ]);
            }
        }
        if ($order->state == 1) {
            $text = 'سفارش در حال پردازش برای ارسال';
            foreach ($order->orderProducts()->get() as $orderProduct) {
                $product = $orderProduct->product()->withTrashed()->first();
                if ($product) {
                    $product->update([
                        'quantity' => $product->quantity - $orderProduct->number,
                    ]);
                    if ($order->total < 0)
                        $desc = 'بازگشت به انبار ';
                    else
                        $desc = ' خرید مشتری ';
                    $order->productChange()->create([
                        'product_id' => $product->id,
                        'change' => -$orderProduct->number,
                        'quantity' => $product->quantity,
                        'desc' => $desc . $order->name,
                    ]);
                }
            }
        }
        if ($order->state == 10)
            $text = 'سفارش ارسال شد';
        if ($text)
            (new CommentController)->create($order, auth()->user(), $text);
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
            $order = Order::with('warehouse')->findOrFail($id);
            if ($order->state == 1) {
                $order->update([
                    'state' => 2
                ]);
//                (new CommentController)->create($order, auth()->user(), 'لیبل سفارش پرینت شد.');
            }
            if ($order->warehouse_id != 1)
                $order->desc .= '(انبار ' . $order->warehouse->name . ')';

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
        if (auth()->user()->meta('showAllOrders') || auth()->user()->meta('counter'))
            $order = Order::with('warehouse')->findOrFail($id);
        else
            $order = auth()->user()->orders()->with('warehouse')->findOrFail($id);
        $order = $this->addCityToAddress($order);

        if ($order->warehouse_id != 1)
            $order->desc .= '(انبار ' . $order->warehouse->name . ')';
        if ($request->onlyOrderData) {
            return $order;
        }
        $order->city = $this->city($order->user()->first())[0];
        $firstPageItems = $request->firstPageItems;
        $totalPages = $request->totalPages;
        $orderProducts = OrderProduct::where('order_id', $id)->with('product')->get();
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

    public function cancelInvoice($id, Request $req)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if (!$order->confirm)
            return $order;
        if ($order->state) {
            $order->state = 4;
            (new CommentController)->create($order, auth()->user(), 'سفارش بعد از تایید ویرایش شد');
        }
        $order->confirm = false;
        if ($order->counter == 'approved')
            (new CustomerController)->rejectOrder($id, $req);
        $order->customer->update([
            'balance' => $order->customer->balance + $order->total,
        ]);
        $order->counter = 'waiting';
        $order->paymentMethod = null;
        $order->payInDate = null;
        $order->paymentNote = null;
        (new CommentController)->create($order, auth()->user(), 'سفارش به حالت پیش فاکتور بازگشت ');
        $order->save();

        DB::commit();
        return $order;
    }

    public function deleteOrder($id, Request $request)
    {
        DB::beginTransaction();
        if (auth()->user()->meta('showAllOrders'))
            $order = Order::with('user')->findOrFail($id);
        else
            $order = auth()->user()->orders()->with('user')->findOrFail($id);

        if ($order->state || ($order->confirm && $order->user->role !== 'user'))
            return 'سفارش نمی تواند حذف شود، چون پردازش شده است!';

        if ($order->delete()) {
            $order->orders = $order->orders();
            $order->paymentLinks()->delete();
            $order->save();
            $order->orderProducts()->delete();
            if ($order->user->safir()) {
                foreach ($order->productChange()->with('product')->get() as $productChange) {
                    $product = $productChange->product;
                    if ($product)
                        $product->update([
                            'quantity' => $product->quantity - $productChange->change,
                        ]);
                }
                $order->productChange()->delete();
            }

            if ($order->paymentMethod == 'credit' && $order->user->safir()) {
                $user = $order->user;
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
            (new CommentController)->create($order, auth()->user(), 'سفارش حذف شد');
            DB::commit();
            return ['با موفقیت حذف شد', $order];
        };
        return 'مشکلی به وجود آمده!';
    }

    public function calculateDis($products, $user)
    {
        if ($user->safir()) {
            $couponLinks = CouponLink::where('user_id', $user->id)->with('coupon')->get();
            $dis = [];
            foreach ($couponLinks as $couponLink) {
                $pid = $couponLink->product_id;
                if (isset($dis[$pid]))
                    $dis[$pid] = max($dis[$pid], $couponLink->coupon->percent);
                else
                    $dis[$pid] = $couponLink->coupon->percent;
            }
            foreach ($products as $id => $product) {
                if (isset($dis[$id]))
                    $products[$id]->coupon = $dis[$id];
                else
                    $products[$id]->coupon = 0;
                $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->good->price / 100);
            }
        } else {
            foreach ($products as $id => $product) {
                $products[$id]->coupon = 0;
                $products[$id]->priceWithDiscount = $product->good->price;
            }
        }
        return $products;
    }

    public function addToCustomers($request, $order)
    {
        if ($this->safir()) {
            $request->customerId = false;
        }
        if ($this->safir() && !$request->addToCustomers) {
            return null;
        }
        $data = [
            'name' => $order->name,
            'phone' => $order->phone,
            'address' => $order->address,
            'zip_code' => $order->zip_code,
            'city_id' => $request->city_id,
        ];
        if ($request->customerId) {
            $customer = Customer::findOrFail($request->customerId);
            if ($request->addToCustomers)
                $customer->update($data);
            else
                if ($customer->name != $request->name)
                    return 'not match';
        } else
            $customer = auth()->user()->customers()->Create($data);

        return $customer->id;
    }

    public function addToTransactions($request, $order)
    {
        if ($this->safir() && $request->paymentMethod == 'credit')
            $order->transactions()->create([
                'user_id' => auth()->user()->id,
                'amount' => $order->total,
                'balance' => auth()->user()->balance,
                'type' => false,
                'description' => 'ثبت سفارش',
            ]);
    }

    public function dateFilter(Request $request)
    {
        $from = date($request->date1 . ' 00:00:00');
        $to = date($request->date2 . ' 23:59:59');
        $limit = $request->limit;

        if ($this->superAdmin() || $this->print()) {
            $orders = Order::withTrashed()->with('website')
                ->whereBetween('created_at', [$from, $to])
                ->limit($limit)
                ->get()->keyBy('id');
        } else {
            $orders = auth()->user()->orders()->withTrashed()->with('website')
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
        Helper::access('showAllOrders');
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if (!$order->deliveryMethod || $order->isCreatorAdmin())
            $order->deliveryMethod = '';
        if ($req->note)
            $req->note = ' - کد مرسوله: ' . $req->note;
        $order->deliveryMethod .= ' - ' . $req->sendMethod . $req->note;
        (new CommentController)->create($order, auth()->user(), $req->sendMethod . $req->note);
        $order->save();
        DB::commit();
        return $order;
    }

    public function paymentMethod($id, Request $req)
    {
        if (auth()->user()->meta('showAllOrders'))
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
        $payInDate = '';
        if ($order->confirm) {
            return ['error', 'قبلا تایید شده.'];
        }
        if ($paymentMethod == 'cash') {
            if (!$req->file("cashPhoto"))
                return ['error', 'باید عکس رسید بانکی بارگذاری شود.'];
            $photo = $req->file("cashPhoto");
        }
        if ($paymentMethod == 'cheque') {
            $photo = $req->file("chequePhoto");
        }
        if ($paymentMethod == 'payInDate') {
            if (strlen($req->payInDate) != 10)
                return ['error', 'تاریخ باید مشخص شود.'];
            $payInDate = $req->payInDatePersian;
        }
        $order->update([
            'confirm' => 1,
            'paymentMethod' => $paymentMethod,
            'payInDate' => $req->payInDate,
            'paymentNote' => $req->note,
            'counter' => 'waiting',
        ]);
        $order->customer->update([
            'balance' => $order->customer->balance - $order->total,
        ]);
        if ($photo) {
            $trans = $order->customer->transactions()->create([
                'amount' => $order->total,
                'verified' => 'waiting',
                'description' => $req->note . '/ ' . $order->payMethod(),
                'photo' => $photo->store("", 'deposit'),
            ]);
            $order->paymentLinks()->create([
                'customer_transaction_id' => $trans->id,
                'amount' => $order->total,
            ]);
            $order->update([
                'receipt' => $photo->store("", 'receipt'),
            ]);
            (new CommentController)->create($order, auth()->user(), 'سفارش تایید شد. ' . $req->note . '/ ' . $order->payMethod() . '/ ' . $payInDate, $photo->store("", 'comment'));
        } else {
            (new CommentController)->create($order, auth()->user(), 'سفارش تایید شد. ' . $req->note . '/ ' . $order->payMethod() . '/ ' . $payInDate);
        }

        DB::commit();
        return ['ok', $order];
    }

}

