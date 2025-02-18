<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\City;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;


class OrderController extends Controller
{
    public function showOrders()
    {
        $users = User::withTrashed()->get()->keyBy("id");
        return view('orders.orders', [
            'users' => $users,
            'orders' => $this->getOrders(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function getOrders()
    {
        $orders = Helper::Order(false)
            ->with(['user', 'website', 'orderProducts', 'warehouse'])
            ->limit(auth()->user()->meta('NuRecords'));

        if (request('fromDate')) {
            $orders = $orders->where('created_at', ">=", verta()
                ->parse(request('fromDate') . ' 20:30')->subDay()->toCarbon())
                ->orderBy('id', 'asc');
        }
        if (request('toDate')) {
            $orders = $orders->where('created_at', "<=", verta()
                ->parse(request('toDate') . ' 20:30')->toCarbon());
        }
        if (request('fromId')) {
            $orders = $orders->where('id', '>=', request('fromId'))
                ->orderBy('id', 'asc');
        }
        if (request('toId')) {
            $orders = $orders->where('id', '<=', request('toId'));
        }
        $orders = $orders->orderBy('id', 'desc')->get()->keyBy('id');
        foreach ($orders as $order) {
            $order->orders = $order->orders();
        }
        return $orders;
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
                $query->whereIn('category', ['final', 'other', 'raw']);
            else
                $query->whereIn('category', ['final', 'other']);
        })->with('good.couponLinks.coupon')->get()->keyBy('id');
        $products = $this->calculateDiscount($products, $user);
        $order->customer = new Customer();
        return view('addEditOrder.addEditOrder', [
            'products' => $products,
            'settings' => Helper::settings(),
            'user' => $user,
            'cart' => [],
            'creatorIsAdmin' => $this->admin(),
            'order' => $order,
            'customers' => $user->customers()->get()->keyBy('id'),
            'warehouses' => Warehouse::all(),
            'warehouseId' => $warehouseId,
            'edit' => false,
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
            'cart' => 'required|array|min:1'
        ]);
        $user = auth()->user();
        $order = $user->orders()->create([
            'name' => $request->name,
            'address' => $request->address,
            'warehouse_id' => $request->warehouse_id,
            'paymentMethod' => $request->paymentMethod,
            'deliveryMethod' => $request->deliveryMethod,
            'desc' => $request->desc,
            'customer_id' => $request->customer_id,
            'phone' => Helper::number_Fa_En($request->phone),
            'zip_code' => Helper::number_Fa_En($request->zip_code),
            'total' => 0, // جمع با احتساب تخفیف
            'confirm' => $this->safir(),
            'counter' => $this->safir() ? 'approved' : 'waiting',
            'orders' => ''
        ]);
        $Total = 0;     //جمع بدون احتساب تخفیف
        $products = Product::with('good')->find(array_keys($request->cart))->keyBy('id');
        $products = $this->calculateDiscount($products, $user);
        foreach ($request->cart as $id => $item) {
            $product = $products[$id];
            $discount = +$product->discount;
            if ($user->meta('changeDiscount'))
                $discount = +$item['discount'];
            $price = round((100 - $discount) * $product->good->price / 100);
            $editPrice = false;
            if ($user->meta('changePrice')) {
                $price = +str_replace(",", "", $item['price']);
                $editPrice = $price != $product->good->price;
                $price = round((100 - $discount) * $price / 100);
            }
            $order->total += $price * (+$item['number']);
            $Total += $product->good->price * (+$item['number']);
            $order->orderProducts()->create([
                'name' => $product->good->name,
                'price' => $price,
                'product_id' => $id,
                'number' => $item['number'],
                'discount' => $discount,
                'editPrice' => $editPrice
            ]);
        }
        if ($order->total < 0) {
            $order->desc .= ' (فاکتور برگشت به انبار)';
        }
        if ($user->safir()) {
            $deliveryCost = Helper::settings()->{$request->deliveryMethod};
            if ($Total < Helper::settings()->freeDelivery || $user->id == 10) // استثنا خانوم موسوی
                $order->total += $deliveryCost;
            if ($request->paymentMethod == 'credit') {
                if ($user->credit > 0) {
                    if ($order->total > ($user->balance + $user->credit))
                        return $this->errorBack('اعتبار شما کافی نیست!');
                } else {
                    if ($order->total > ($user->balance + Helper::settings()->negative))
                        return $this->errorBack('اعتبار شما کافی نیست!');
                }
                $user->update([
                    'balance' => $user->balance - $order->total
                ]);
                $order->transactions()->create([
                    'user_id' => auth()->user()->id,
                    'amount' => $order->total,
                    'balance' => auth()->user()->balance,
                    'type' => false,
                    'description' => 'ثبت سفارش',
                ]);

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
            app('Telegram')->sendOrderToBale($order, env('GroupId'));
        }

        $order->save();
        (new CommentController)->create($order, $user, 'سفارش ایجاد شد');

        DB::commit();

        return redirect()->route('listOrders');
    }

    public function editOrder($id)
    {
        $order = Helper::Order(true)->with(['customer', 'user'])->findOrFail($id);
        $user = $order->user;
        if (($order->state && $user->safir()) || ($order->confirm && $user->admin()))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);
        if ($order->total < 0)
            return view('error')->with(['message' => 'فاکتور بازگشت به انبار قابل ویرایش نیست، لطفا حذف کنید و مجدد ثبت کنید.']);
        $products = Product::where('warehouse_id', $order->warehouse_id)->where('available', true)->
        whereHas('good', function (Builder $query) {
            if (auth()->user()->meta('sellRawProduct'))
                $query->whereIn('category', ['final', 'other', 'raw']);
            else
                $query->whereIn('category', ['final', 'other']);
        })->get()->keyBy('id');
        $cart = [];

        $products = $this->calculateDiscount($products, $user);

        $selectedProducts = $order->orderProducts->keyBy('product_id');
        foreach ($selectedProducts as $id => $orderProduct) {
            if (isset($products[$id])) {
                $cart[$id] = (int)$orderProduct->number;
                $products[$id]->discount = +$orderProduct->discount;
                $products[$id]->priceWithDiscount = +$orderProduct->price;
            } else
                echo $orderProduct->name . ' از محصولات حذف شده است';
        }

        $cities = City::with('province')->get()->keyBy('id');
        return view('addEditOrder.addEditOrder')->with([
            'cart' => $cart,
            'creatorIsAdmin' => !$user->safir(),
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
            'cart' => 'required|array|min:1',
        ]);

        if (!$user->safir()) {
            $products = Product::with('good')->find(array_keys($request->cart))->keyBy('id');
            $products = $this->calculateDiscount($products, $user);
            $order->orderProducts()->delete();
            $order->total = 0;
            foreach ($request->cart as $id => $item) {
                $product = $products[$id];
                $discount = +$product->discount;
                if ($user->meta('changeDiscount'))
                    $discount = +$item['discount'];
                $price = round((100 - $discount) * $product->good->price / 100);
                $editPrice = false;
                if ($user->meta('changePrice')) {
                    $price = +str_replace(",", "", $item['price']);
                    $editPrice = $price != $product->good->price;
                    $price = round((100 - $discount) * $price / 100);
                }
                $order->total += $price * (+$item['number']);
                $order->orderProducts()->create([
                    'name' => $product->good->name,
                    'price' => $price,
                    'product_id' => $id,
                    'number' => $item['number'],
                    'discount' => $discount,
                    'editPrice' => $editPrice
                ]);
            }
        }

        $order->save();
        $order->update([
            'name' => $order->user->safir() ? $request->name : $order->name,
            'phone' => Helper::number_Fa_En($request->phone),
            'address' => $request->address,
            'zip_code' => Helper::number_Fa_En($request->zip_code),
            'desc' => $request->desc,
        ]);

        $order->paymentLinks()->delete();
        (new CommentController)->create($order, auth()->user(), 'سفارش ویرایش شد');

//        $this->addToCustomers($request, $order);
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
            return abort(405, 'ابتدا پرداخت فاکتور باید تایید شود.');
        }
        if (+$state == 1 && !$order->confirm) {
            return abort(405, 'ابتدا فاکتور باید تایید شود.');
        }
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
//                    'desc' => 'لغو خرید مشتری ' . $order->name,
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
                    $order->productChange()->create([
                        'product_id' => $product->id,
                        'change' => -$orderProduct->number,
                        'quantity' => $product->quantity,
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
        foreach ($pageContents as $pageNumber => $pageContent) {
            $page = view('orders.invoice', [
                'firstPage' => ($pageNumber == 0) ? '' : 'd-none',
                'lastPage' => ($pageNumber == count($pageContents) - 1) ? '' : 'd-none',
                'page' => $pageNumber + 1,
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
            return abort(405, 'سفارش قبلا لغو شده است');
        if ($order->state) {
            $order->state = 4;
            (new CommentController)->create($order, auth()->user(), 'سفارش بعد از تایید ویرایش شد');
        }
        $order->confirm = false;
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

    public function calculateDiscount($products, $user)
    {
        foreach ($products as $id => $product) {
            $product->discount = $product->discount($user);
            $product->priceWithDiscount = round((100 - $product->discount) * $product->good->price / 100);
        }
        return $products;
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
        $this->confirmAuthorize($id);
        request()->validate([
            'paymentMethod' => 'required',
        ]);
        DB::beginTransaction();
        $paymentMethod = $req->paymentMethod;
        $payInDate = '';
        if ($paymentMethod == 'payInDate') {
            if (strlen($req->payInDate) != 10)
                return abort(405, 'تاریخ باید مشخص شود.');
            $payInDate = $req->payInDatePersian;
        }
        $order->paymentLinks()->delete();
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
        return $order;
    }

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

    public function confirmAuthorize($id)
    {
        $order = Order::find($id);

        if ($order->customer->block)
            return abort(405, 'حساب مشتری مسدود شده است.');
        if ($order->confirm)
            return abort(405, 'سفارش قبلا تائید شده.');
//        if ($order->customer->credit_limit - $order->total < -$order->customer->balance())
//            return abort(405, 'بدهی مشتری بیش از سقف اعتبار است.');
        if (!$order->customer->agreement)
            return abort(405, 'لطفا قسمت تفاهم با مشتری را در ویرایش مشتری کامل کنید.');
        if ($order->user->credit > 0 && $order->user->credit < ($order->user->totalDepth() + $order->total))
            return abort(405, 'مجموع بدهی مشتریان از اعتبار کاربر بیشتر است.');
        if ($order->total < 0)
            $order->update([
                'confirm' => true,
                'counter' => 'waiting',
                'paymentNote' => 'بازگشت به انبار، ',
            ]);
        if ($order->total == 0)
            $order->update([
                'confirm' => true,
                'counter' => 'waiting',
            ]);
        return $order;
    }

    public function excelData(Request $request)
    {
        $orders = Helper::Order(false)
            ->whereIn('id', $request->ids)
            ->with('orderProducts')
            ->get()->keyBy('id');
        return [
            view('keysun.invoice1', compact('orders'))->render(),
            view('keysun.invoice2', compact('orders'))->render()
        ];
    }
}

