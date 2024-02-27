<?php

namespace App\Http\Controllers;

use App\Models\CouponLink;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

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
        foreach ($orders as $id => $order) {
            $order->created_at_p = verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime();
            $order->updated_at_p = verta($order->updated_at)->timezone('Asia/tehran')->formatJalaliDatetime();
            if ($order->deleted_at)
                $order->deleted_at_p = verta($order->deleted_at)->timezone('Asia/tehran')->formatJalaliDatetime();
            else
                $order->deleted_at_p = null;
            $orders[$id] = $order;
        }
        return view('orders', [
            'users' => $users,
            'orders' => $orders,
            'userId' => $this->userId(),
        ]);
    }

    public function newForm()
    {
        $user = auth()->user();
        if ($this->superAdmin() || $this->admin())
            $products = Product::where('category', '<>', 'pack')->get()->keyBy('id');
        else
            $products = Product::where('category', 'final')->where('price', '>', '1')->get()->keyBy('id');
        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
        }

        $customersData = auth()->user()->customers()->get();

        $customers = $customersData->keyBy('name');
        $customersId = $customersData->keyBy('id');
        return view('addEditOrder', [
            'edit' => false,
            'customers' => $customers,
            'customersId' => $customersId,
            'products' => $products,
            'settings' => $this->settings(),
            'id' => $user->id,
            'cart' => (object)[],
            'creator' => ($this->superAdmin() || $this->admin()),
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
        if ($admin)
            $request->orders = 'طبق فاکتور';

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
                return $this->errorBack('مشکلی پیش آمده!');
        } else {
            $request->paymentMethod = 'admin';
            $request->deliveryMethod = 'admin';
        }
        $request->total = $total;

        $request->customerId = $this->addToCustomers($request);

        $order = $this->addToOrders($request);

        $this->addToOrderProducts($request, $order);

        $this->addToTransactions($request, $order);

        if ($this->safir())
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

        app('Telegram')->sendOrderToBale($order, env('GroupId'));

        DB::commit();

        return redirect()->route('listOrders');
    }

    public function newOrderTelegram($id, $pass)
    {
        $user = User::findOrFail($id);
        if ($user->telegram_code == $pass) {
            auth()->login($user);
            return redirect()->route('newOrder');
        }
        return abort(404);
    }

    public function newOrderWithPhotoTelegram($id, $pass, $file_id)
    {
        $user = User::findOrFail($id);
        if ($user->telegram_code == $pass) {
            auth()->login($user);
            $order = $user->orders->where('receipt', $file_id . '.jpg')->first();
            if ($order)
                return redirect("edit_order/{$order->id}");
            elseif (TelegramController::savePhoto($file_id))
                return redirect("add_order?file=$file_id");
        }
        return abort(404);
    }

    public function editForm($id)
    {
        $user = auth()->user();
        if ($this->superAdmin())
            $order = Order::findOrFail($id);
        else
            $order = $user->orders()->findOrFail($id);

        $creator = $order->user()->first()->role !== 'user';

        if ($order->state || ($order->confirm && $creator))
            return view('error')->with(['message' => 'سفارش قابل ویرایش نیست چون پردازش شده است.']);

        if (!$this->safir())
            $products = Product::where('category', '<>', 'pack')->get()->keyBy('id');
        else
            $products = Product::where('category', 'final')->where('price', '>', '1')->get()->keyBy('id');

        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
        }
        $customersData = $user->customers()->get();
        $customers = $customersData->keyBy('name');
        $customersId = $customersData->keyBy('id');
        $selectedProducts = $order->orderProducts()->get();
        $cart = [];
        foreach ($selectedProducts as $product) {
            $cart[$product->product_id] = +$product->number;
            $products[$product->product_id]->coupon = +$product->discount;
            $products[$product->product_id]->priceWithDiscount = round((100 - +$product->discount) * $product->price / 100);
        }
        return view('addEditOrder')->with([
            'edit' => true,
            'order' => $order,
            'customers' => $customers,
            'customersId' => $customersId,
            'products' => $products,
            'settings' => $this->settings(),
            'id' => $user->id,
            'cart' => $cart,
            'creator' => $creator,
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
//            $orders = '';
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
//                    $orders .= ' ' . $product->name . ' ' . $number . 'عدد' . '،';
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
//            if ($counter > 10)
            $orders = 'طبق فاکتور';
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

        DB::commit();

        return redirect()->route('listOrders');
    }

    public function changeState($id)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->admin != $this->userId() && $order->admin && $order->paymentMethod == 'admin')
            return [$order->state, $order->admin];
        $user = $order->user()->first();
        $order->state = !$order->state;
        if (!$order->state)
            $order->admin = null;
        else
            $order->admin = $this->userId();
        if ($order->paymentMethod == 'onDelivery') {
            if ($order->state) {
                $user->balance += $order->customerCost - $order->total;
                $order->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $order->customerCost - $order->total,
                    'balance' => $user->balance,
                    'type' => true,
                    'description' => 'سهم سفیر(پرداخت در محل)',
                ]);
            } else {
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
        return [$order->state, $order->admin];

    }

    public function pdf($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user()->first()->role == 'admin')
            $order->orders = 'طبق فاکتور';
        $font = 28;
        do {
            $pdf = PDF::loadView('pdf', ['order' => $order], [], [
                'format' => [200, 100],
                'default_font' => 'iransans',
                'default_font_size' => $font,
                'margin_left' => 2,
                'margin_right' => 2,
                'margin_top' => 2,
                'margin_bottom' => 2,
            ]);
            $font = $font - 1;
        } while ($pdf->getMpdf()->page > 1);
        return $pdf->stream($order->name . '.pdf');
    }

    public function pdfs($ids)
    {
        $ids = explode(",", $ids);
        $fonts = array();
        $orders = array();
        foreach ($ids as $id) {
            $order = Order::findOrFail($id);
            if ($order->admin != $this->userId() && $order->admin)
                abort(405);
            if ($order->user()->first()->role == 'admin')
                $order->orders = 'طبق فاکتور';
            $font = 28;
            do {
                $font = $font - 1;
                $pdf = PDF::loadView('pdf', ['order' => $order], [], [
                    'format' => [200, 100],
                    'default_font' => 'iransans',
                    'default_font_size' => $font,
                    'margin_left' => 2,
                    'margin_right' => 2,
                    'margin_top' => 2,
                    'margin_bottom' => 2,
                ]);
                $mpdf = $pdf->getMpdf();

            } while ($mpdf->page > 1);
            array_push($fonts, $font);
            array_push($orders, $order);
        }

        $pdfs = PDF::loadView('pdfs', ['orders' => $orders, 'fonts' => $fonts], [], [
            'format' => [200, 100],
            'default_font' => 'iransans',
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
        ]);
//        $fileName =  sizeof($ids) . "_" . $order->name . '.pdf';
        $fileName = $order->id . '(' . sizeof($ids) . ').pdf';
        $pdfs->getMpdf()->OutputFile('pdf/' . $fileName);
//        return $pdfs->stream(sizeof($ids) . "_" . $order->name . '.pdf');
//        return response()->download(public_path($fileName));
        return 'pdf/' . $fileName;
    }

    public function invoice($id)
    {
        $order = Order::findOrFail($id);
        $order->created_at_p = verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime();
        $orderProducts = OrderProduct::where('order_id', $id)->get();
        $number = $orderProducts->count();
        if ($number > 33) {
            $v1 = view('orders.invoice', [
                'firstPage' => '',
                'lastPage' => 'd-none',
                'page' => '1',
                'pages' => '2',
                'order' => $order,
                'orderProducts' => $orderProducts,
            ])->render();
            $v2 = view('orders.invoice', [
                'firstPage' => 'd-none',
                'lastPage' => '',
                'page' => '2',
                'pages' => '2',
                'order' => $order,
                'orderProducts' => $orderProducts,
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
            ])->render(), $order->id]];
        }
    }

    public function confirmInvoice($id, Request $request)
    {
        DB::beginTransaction();
        $pay = +$request->pay;
        $order = Order::findOrFail($id);
        if ($order->confirm || $order->state)
            return $order;
        $order->confirm = $pay;
        $desc = 'شیوه پرداخت نامغلوم';
        switch ($pay) {
            case 1:
                $desc = 'پرداخت نقدی';
                break;
            case 2:
                $desc = 'پرداخت چکی';
                break;
            case 3:
                $desc = 'پرداخت در محل';
                break;
            case 4:
                $desc = 'امانی';
                break;
            case 5:
                $desc = ' پرداخت در تاریخ ' .$request->date;
                break;
            case 6:
                $desc = 'فاکتور به فاکتور';
                break;
        }
        $order->desc .= '***' . $desc;
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
                'desc' => ' خرید مشتری ' . $order->name
            ]);
        }
        $this->addToCustomerTransactions($order);
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
        $order->desc = substr( $order->desc, 0, strpos( $order->desc, '***' ) );
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
            $orderProducts = $order->orderProducts();
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
            $orderProducts->delete();
//            OrderProduct::where('order_id', $id)->delete();
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
            $order->created_at_p = verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime();
            $order->updated_at_p = verta($order->updated_at)->timezone('Asia/tehran')->formatJalaliDatetime();
            $order->deleted_at_p = verta($order->deleted_at)->timezone('Asia/tehran')->formatJalaliDatetime();
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
        if (!$request->addToCustomers && ($this->superAdmin() || $this->admin())) {
            if ($request->customerId) {
                $customer = auth()->user()->customers()->findOrFail($request->customerId);
                if ($customer->name != $request->name) {
                    return $this->errorBack('نام مشتری مطابقت ندارد!');
                }
            } else {
                $customer = auth()->user()->customers()->Create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                ]);
            }
        }

        if ($request->addToCustomers) {
            if ($request->customerId) {
                $customer = auth()->user()->customers()->findOrFail($request->customerId);
                $customer->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                ]);
            } else {
                $customer = auth()->user()->customers()->Create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                ]);
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
            'description' => 'تایید سفارش ' . $order->id,
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
            'description' => 'ابطال سفارش  ' . $order->id,
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

        if ($this->superAdmin() || $this->print()) {
            $orders = Order::withTrashed()
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('id', 'desc')
                ->limit($this->settings()->loadOrders)->get()->keyBy('id');
        } else {
            $orders = auth()->user()->orders()->withTrashed()
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('id', 'desc')->limit($this->settings()->loadOrders)->get()->keyBy('id');
        }
        return $orders;
    }
}
