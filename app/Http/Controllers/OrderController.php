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
            $orders = Order::withTrashed()->orderBy('id', 'desc')->with('website')
                ->limit($user->meta('NuRecords'))->get()->keyBy('id');
        } else {
            $users = array(auth()->user()->id => auth()->user());
            $orders = auth()->user()->orders()->withTrashed()
                ->orderBy('id', 'desc')->limit($user->meta('NuRecords'))->get()->keyBy('id');
        }
        return view('orders.orders', [
            'users' => $users,
            'orders' => $orders,
            'user' => auth()->user(),
            'limit' => $user->meta('NuRecords'),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function newForm(Request $req)
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
        $products = $this->calculateDis($products);

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

        $request->phone = Helper::number_Fa_En($request->phone);
        $request->zip_code = Helper::number_Fa_En($request->zip_code);
        $user = auth()->user();
        $products = Product::where('available', true)->where('warehouse_id', +$request->warehouseId)->get()->keyBy('id');
        $products = $this->calculateDis($products);
        $request->orders = '';
        $Total = 0;     //جمع بدون احتساب تخفیف
        $total = 0;  //جمع با احتساب تخفیف
        $request->customerCost = 0;
        $request->orderList = [];
        foreach ($request->cart as $id => $number) {
            $product = $products[$id];
            $request->orders .= ' ' . $product->name . ' ' . +$number . 'عدد' . '،';
            $discount = +$product->coupon;
            if (auth()->user()->meta('changeDiscount'))
                $discount = +$request['discount_' . $id];
            $price = round((100 - $discount) * $product->price / 100);
            if (auth()->user()->meta('changePrice') && $discount == 0)
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

        if ($request->orders == '') {
            return $this->errorBack('محصولی انتخاب نشده است!');
        }

        if ($this->safir()) {
            $deliveryCost = $this->deliveryCost($request->deliveryMethod);
            if ($Total < Helper::settings()->freeDelivery || $user->id == 10) // استثنا خانوم موسوی
                $total += $deliveryCost;
            if ($request->paymentMethod == 'credit') {
                if ($total > ($user->balance + Helper::settings()->negative)) {
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
                $quantity = $products[$id]->quantity - $product['number'];
                Product::find($id)->update([
                    'quantity' => $quantity,

                ]);
                $order->productChange()->create([
                    'product_id' => $product['product_id'],
                    'change' => -$product['number'],
                    'quantity' => $quantity,
                    'desc' => ' خرید سفیر ' . $user->name
                ]);
            }
            $response = app('Telegram')->sendOrderToBale($order, env('GroupId'));
            if (isset($response->result)) {
                $order->bale_id = $response->result->message_id;
            }
            $order->save();
        }

        (new CommentController)->create($order, $user, 'سفارش ایجاد شد');

        DB::commit();

        return redirect()->route('listOrders');
    }

    public function editForm($id)
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
        $products = $this->calculateDis($products);

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

    public function update($id, Request $request)
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

        (new CommentController)->create($order, auth()->user(), 'سفارش ویرایش شد');

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
        $text = null;
        if ($order->state == 0)
            $text = 'سفارش به انبار بازگشت';
        if ($order->state == 1)
            $text = 'سفارش در حال پردازش برای ارسال';
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
        if (auth()->user()->meta('showAllOrders'))
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
        $this->removeFromCustomerTransactions($order);
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
        if ($this->superAdmin())
            $order = Order::with('user')->findOrFail($id);
        else
            $order = auth()->user()->orders()->with('user')->findOrFail($id);

        if ($order->state || ($order->confirm && $order->user->role !== 'user'))
            return 'سفارش نمی تواند حذف شود، چون پردازش شده است!';

        if ($order->delete()) {
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

    public function calculateDis($products)
    {
        $couponLinks = CouponLink::where('user_id', auth()->user()->id)->with('coupon')->get();
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
        return $products;
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
            'warehouse_id' => $request->warehouseId,
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
        $trans1 = $this->addToCustomerTransactions($order);
        if ($photo) {
            $trans2 = $order->customer->transactions()->create([
                'amount' => $order->total,
                'type' => true,
                'verified' => 'waiting',
                'description' => $req->note . '/ ' . $order->payMethod(),
                'photo' => $photo->store("", 'deposit'),
                'paymentLink' => $trans1->id,
            ]);
            $trans1->update([
                'paymentLink' => $trans2->id,
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

    public function refund($id)
    {
        $orders = Order::where('customer_id', $id)->with('orderProducts')->get();
        dd($orders);
    }
}

