<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\City;
use App\Models\CouponLink;
use App\Models\Customer;
use App\Models\CustomerMeta;
use App\Models\GoodMeta;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductChange;
use App\Models\Setting;
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
        $users = User::withTrashed()->get()->keyBy("id");
        $orders = Helper::Order(false)->orderBy('id', 'desc')->with(['website', 'orderProducts', 'warehouse'])
            ->limit($user->meta('NuRecords'))->get()->keyBy('id');
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

    public function showOrder($id)
    {
        $order = Helper::Order(false)->findOrFail($id);
        return view('orders.order', [
            'order' => $order,
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
        })->with('good')->get()->keyBy('id');
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
            $discount = +$product->coupon;
            if (auth()->user()->meta('changeDiscount'))
                $discount = +$request['discount_' . $id];
            $price = round((100 - $discount) * $product->good->price / 100);
            $editPrice = false;
            if (auth()->user()->meta('changePrice')) {
                $price = +str_replace(",", "", $request['price_' . $id]);
                $editPrice = $price != $product->good->price;
                $price = $price = round((100 - $discount) * $price / 100);
            }
            $order->total += $price * (+$number);
            $Total += $product->good->price * (+$number);
            $order->save();
            $order->orderProducts()->create([
                'name' => $product->good->name,
                'price' => $price,
                'product_id' => $id,
                'number' => $number,
                'discount' => $discount,
                'editPrice' => $editPrice
            ]);
        }
        if ($order->total < 0) {
            $order->desc .= ' (فاکتور برگشت به انبار)';
        }
        if ($this->safir()) {
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
        $order = Helper::Order(true)->with(['customer.city', 'user'])->findOrFail($id);
        $user = $order->user;
        if (($order->state && $user->safir()) || ($order->confirm && $user->admin()))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);
        if ($order->total < 0)
            return view('error')->with(['message' => 'فاکتور بازگشت به انبار قابل ویرایش نیست، لطفا حذف کنید و مجدد ثبت کنید.']);
        $products = Product::withTrashed()->where('warehouse_id', $order->warehouse_id)->where('available', true)->
        whereHas('good', function (Builder $query) {
            if (auth()->user()->meta('sellRawProduct'))
                $query->where('category', '<>', 'pack');
            else
                $query->where('category', 'final');
        })->get()->keyBy('id');
        $cart = [];

        $products = $this->calculateDis($products, $user);

        $selectedProducts = $order->orderProducts->keyBy('product_id');
        foreach ($selectedProducts as $id => $orderProduct) {
            if (isset($products[$id])) {
                $cart[$id] = (int)$orderProduct->number;
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
            'creatorIsAdmin' => $order->user->admin() || $order->user->superAdmin(),
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
        $order = Helper::Order(true)->with('user')->with('orderProducts')->findOrFail($id);
        $user = $order->user;
        if (($order->state && $user->safir()) || ($order->confirm && $user->admin()))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);
        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|string|min:11,max:11',
        ]);

        $request->phone = Helper::number_Fa_En($request->phone);
        $request->zip_code = Helper::number_Fa_En($request->zip_code);

        if (!$order->user->safir()) {
            $products = Product::withTrashed()->where('available', true)->where('warehouse_id', $order->warehouse_id)->get()->keyBy('id');
            $products = $this->calculateDis($products, $user);
            $order->orderProducts()->delete();
            if (count($request->cart) == 0) {
                return $this->errorBack('محصولی انتخاب نشده است!');
            }
            $order->total = 0;
            foreach ($request->cart as $id => $number) {
                $product = $products[$id];
                $discount = $product->coupon;
                $editPrice = false;
                if ($user->meta('changeDiscount'))
                    $discount = +$request['discount_' . $id];
                $price = round((100 - $discount) * $product->good->price / 100);
                if ($user->meta('changePrice')) {
                    $price = +str_replace(",", "", $request['price_' . $id]);
                    $editPrice = $price != $product->good->price;
                    $price = round((100 - $discount) * $price / 100);
                }
                $order->total += $price * (+$number);
                $order->orderProducts()->create([
                    'name' => $product->good->name,
                    'price' => $price,
                    'product_id' => $id,
                    'number' => $number,
                    'discount' => $discount,
                    'editPrice' => $editPrice,
                ]);
            }
        }

        $order->save();
        $order->update([
            'name' => $order->user->safir() ? $request->name : $order->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'desc' => $request->desc,
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
        Helper::access('changeOrderState');
        DB::beginTransaction();
        $order = Helper::Order(true)->findOrFail($id);
        $user = $order->user;
        // جلوگیری از ارسال سفارشات نقدی و چکی بدون تایید پرداخت
        if (+$state == 1 && $order->payPercentApproved() < 100 && ($order->paymentMethod == 'cash' || $order->paymentMethod == 'cheque')) {
            return [$order->state, 'ابتدا پرداخت فاکتور باید تایید شود.'];
        }
        //if($order->state == 10 && $order->total < 0 )
        //return [+$order->state, 'خطا'];
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
                $product = $productChange->product()->withTrashed()->first();
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
            foreach ($order->orderProducts as $orderProduct) {
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
        if ($order->state == 10) {
            // ثبت واریزی بازگشت به انبار
            if ($order->total < 0) {
                $order->customer->transactions()->create([
                    'amount' => -$order->total,
                    'verified' => 'waiting',
                    'description' => 'بازگشت به انبار، سفارش: ' . $order->id,
                ]);
            }
            $text = 'سفارش ارسال شد';
        }
        if ($text)
            (new CommentController)->create($order, auth()->user(), $text);
        $order->save();
        $user->save();
        DB::commit();
        return [+$order->state, $text];
    }

    public function pdfs($ids)
    {
        $idArray = explode(",", $ids);
        $fonts = array();
        $orders = array();
        foreach ($idArray as $id) {
            $order = Helper::Order(false)->with('warehouse')->findOrFail($id);
            if ($order->state == 1) {
                $order->update([
                    'state' => 2
                ]);
            }
            $order->desc .= '(انبار ' . $order->warehouse->name . ')';
            $order->orders = $order->orders();
            $font = 32;
            $order = $this->addCityToAddress($order);
            do {
                if ($font < 18 && !$order->user->safir()) {
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
        $order = Helper::Order(false)->findOrFail($id);
        $total_no_dis = 0;
        $total_dis = 0;
        $totalProducts = 0;
        foreach ($order->orderProducts as $id => $orderProduct) {
            if ($orderProduct->discount != 100)
                $orderProduct->price_no_dis = round((100 / (100 - $orderProduct->discount)) * $orderProduct->price);
            else
                $orderProduct->price_no_dis = $orderProduct->product()->withTrashed()->first()->good->price;
            $orderProduct->sub_total_no_dis = $orderProduct->price_no_dis * $orderProduct->number;
            $total_no_dis = $total_no_dis + $orderProduct->sub_total_no_dis;
            $total_dis = $total_dis + ($orderProduct->price * $orderProduct->number);
            $totalProducts += $orderProduct->number;
        }
        $number = $order->orderProducts->count();
        if ($request->pageContent == 'all')
            $pageContents = [$number];
        else {
            $pageContents = [20, $number - 20];
            if ($number > 40)
                $pageContents = [20, 35, $number - 55];
            if ($number > 75)
                $pageContents = [20, 35, 35, $number - 90];
            if ($number > 110)
                $pageContents = [20, 35, 35, 35, $number - 125];
        }
        $res = [];
        $start = 0;
        foreach ($pageContents as $id => $pageContent) {
            $page = view('orders.invoice', [
                'firstPage' => ($id == 0) ? '' : 'd-none',
                'lastPage' => ($id == count($pageContents) - 1) ? '' : 'd-none',
                'page' => $id + 1,
                'pages' => count($pageContents),
                'order' => $order,
                'start' => $start,
                'end' => $start + $pageContent,
                'total_no_dis' => $total_no_dis,
                'total_dis' => $total_dis,
                'totalProducts' => $totalProducts,
                'setting' => $this->settings(),
            ])->render();
            $start += $pageContent;
            array_push($res, $page);
        }
        return $res;
    }

    public function cancelInvoice($id, Request $req)
    {
        DB::beginTransaction();
        $order = Helper::Order(!Helper::meta('counter'))->findOrFail($id);
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
        $order = Helper::Order(true)->with('user')->findOrFail($id);
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
            $discounts = [];
            foreach ($user->couponLinks as $couponLink) {
                if (isset($discounts[$couponLink->good_id]))
                    $discounts[$couponLink->good_id] = max($discounts[$couponLink->good_id], $couponLink->coupon->percent);
                else
                    $discounts[$couponLink->good_id] = $couponLink->coupon->percent;
            }
            foreach ($products as $id => $product) {
                $products[$id]->coupon = $discounts[$product->good_id] ?? 0;
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

        $orders = Helper::Order(false)->with(['website', 'orderProducts', 'warehouse'])
            ->whereBetween('created_at', [$from, $to])
            ->limit($limit)
            ->get()->keyBy('id');

        return $orders;
    }

    public function viewOrder($id)
    {
        $order = Helper::Order(false)->findOrFail($id);
        $order = $this->addCityToAddress($order);
        return view('orders.view', ['order' => $order]);
    }

    public function setSendMethod($id, Request $req)
    {
        Helper::access('changeOrderState');
        DB::beginTransaction();
        $order = Helper::Order(true)->findOrFail($id);
        if ($order->total < 0)
            return $order;
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
        $order = Helper::Order(true)->with('customer')->findOrFail($id);
        if ($order->confirm)
            return ['error', 'قبلا تایید شده.'];
        // بازگشت به انبار
        if ($order->total < 0) {
            $order->update([
                'confirm' => true,
                'counter' => 'waiting',
                'paymentNote' => 'بازگشت به انبار، سفارش: ' . $order->id,
            ]);
            return ['ok', $order];
        }
        if ($order->customer->block)
            return ['error', 'حساب مشتری مسدود شده است.'];
        request()->validate([
            'paymentMethod' => 'required',
        ]);
        DB::beginTransaction();
        $paymentMethod = $req->paymentMethod;
        $payInDate = '';
        if ($paymentMethod == 'payInDate') {
            if (strlen($req->payInDate) != 10)
                return ['error', 'تاریخ باید مشخص شود.'];
            $payInDate = $req->payInDatePersian;
        }
        $order->update([
            'confirm' => true,
            'paymentMethod' => $paymentMethod,
            'payInDate' => $req->payInDate,
            'paymentNote' => $req->note,
            'counter' => 'waiting',
        ]);
        $order->customer->update([
            'balance' => $order->customer->balance - $order->total,
        ]);
        (new CommentController)->create($order, auth()->user(), 'سفارش تایید شد. ' . $req->note . '/ ' . $order->payMethod() . '/ ' . $payInDate);

        DB::commit();
        return ['ok', $order];
    }

//    public function orderExcel($id)
//    {
//        $order = Helper::Order(false)->findOrFail($id);
//        $customer = $order->customer;
//        $customerMeta = $customer->customerMetas->first();
//        $orderProducts = $order->orderProducts->keyBy('id');
//        foreach ($orderProducts as $orderProduct) {
//            if ($orderProduct->discount == 100) {
//                if (isset($orderProduct->product))
//                    $orderProduct->original_price = $orderProduct->product->good->price;
//                else
//                    $orderProduct->original_price = 0;
//            } else
//                $orderProduct->original_price = +round($orderProduct->price * 100 / (100 - $orderProduct->discount));
//            $orderProduct->add_value = $orderProduct->price * $orderProduct->number * 0.1;
//        }
//        return view('orders.orderExcel', [
//            'order' => $order,
//            'customer' => $customer,
//            'customerMeta' => $customerMeta,
//            'orderProducts' => $orderProducts,
//        ]);
//    }
//
//    public function saveExcelData($id, Request $req)
//    {
//        $order = Helper::Order(false)->findOrFail($id);
//        if ($req->customer_code)
//            CustomerMeta::updateOrCreate(
//                ['customer_id' => $order->customer_id],
//                [
//                    'customer_code' => $req->customer_code
//                ]
//            );
//        foreach ($order->orderProducts as $orderProduct) {
//            GoodMeta::updateOrCreate(
//                ['good_id' => $orderProduct->product->good_id],
//                [
//                    'warehouse_code' => $req->{'warehouse_code_' . $orderProduct->id},
//                    'stuff_code' => $req->{'stuff_code_' . $orderProduct->id},
////                    'added_value' => $req->{'added_value_' . $orderProduct->id},
//                ]
//            );
//        }
//        return 'با موفقیت ذخیره شد.';
//    }

    public function changeWarehose($orderId, $warehouseId)
    {
        DB::beginTransaction();
        $order = Helper::Order(true)->findOrFail($orderId);
        $productOrders = $order->orderProducts;
        foreach ($productOrders as $productOrder) {
            $product = $productOrder->product;
            $product2 = Product::withTrashed()->firstOrCreate([
                'warehouse_id' => $warehouseId,
                'good_id' => $product->good_id,
            ], []);
            $product2->available = true;
            $product2->save();
            $productOrder->product_id = $product2->id;
            $productOrder->save();
        }
        $order->warehouse_id = $warehouseId;
        $order->save();
        $order->warehouse = $order->warehouse;
        DB::commit();
        return $order;
    }

    public function viewPaymentMethods($id)
    {
        return view('orders.paymentMethods', [
            'order' => Order::find($id)
        ]);
    }
}

