<?php

namespace App\Http\Controllers;

use App\Models\CouponLink;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function showOrders()
    {
        return view('orders');
    }

    public function getOrders()
    {
        if ($this->isAdmin()) {
            $users = User::withTrashed()->get();
            $orders = Order::withTrashed()->orderBy('id', 'desc')->limit($this->settings()->loadOrders)->get();
        } else {
            $users = [auth()->user()];
            $orders = auth()->user()->orders()->withTrashed()->orderBy('id', 'desc')->limit($this->settings()->loadOrders)->get();
        }

        include('../app/jdf.php');
        $dates = [];
        foreach ($orders as $key => $order) {
            $dates[$key][0] = $order->created_at->getTimestamp();
            $dates[$key][1] = $order->updated_at->getTimestamp();
            $order->deleted_at ? ($dates[$key][2] = $order->deleted_at->getTimestamp()) : ($dates[$key][2] = null);
        }
        foreach ($orders as $key => $order) {
            $order->created_at_p = jdate('Y/m/d H:i', $dates[$key][0]);
            $order->updated_at_p = jdate('Y/m/d H:i', $dates[$key][1]);
            if ($dates[$key][2])
                $order->deleted_at_p = jdate('Y/m/d H:i', $dates[$key][2]);
            else
                $order->deleted_at_p = null;
            $orders[$key] = $order;
        }
        return [$orders, $users, $this->isAdmin()];
    }

    public function listOrderTelegram($id, $pass)
    {
        $user = User::findOrFail($id);
        if ($user->telegram_code == $pass) {
            auth()->login($user);
            return redirect()->route('listOrders');
        }
        return abort(404);
    }

    public function newForm(Request $request)
    {
        $user = auth()->user();
        if ($this->isAdmin() || $user->id == 10)
            $products = Product::all()->keyBy('id');
        else
            $products = Product::where('price', '>', '0')->get()->keyBy('id');
        foreach ($products as $id => $product) {
            $products[$id]->coupon = $this->calculateDis($id);
            $products[$id]->priceWithDiscount = round((100 - $products[$id]->coupon) * $product->price / 100);
        }
        $customers = auth()->user()->customers()->get()->keyBy('name');
        return view('addEditOrder', [
            'req' => $request->all(),
            'order' => false,
            'customers' => $customers,
            'products' => $products,
            'settings' => $this->settings()
        ]);
    }

    public function insertOrder(Request $request)
    {
        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|string|min:11,max:11',
        ]);
        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);

        $user = auth()->user();
        $products = Product::where('available', true)->get()->keyBy('id');
        $request->orders = '';
        $Total = 0;
        $total = 0;
        $customerCost = 0;
        if ($this->isAdmin() && $request->factor)
            $request->orders = 'طبق فاکتور';
        else {
            $deliveryCost = $this->deliveryCost($request->deliveryMethod);
            foreach ($products as $id => $product) {
                $number = $request['product_' . $id];
                if ($number > 0) {
                    $request->orders = $request->orders . '*' . $product->name . ' ' . $number . 'عدد' . '*';
                    $coupon = $this->calculateDis($id);
                    $total += round((100 - $coupon) * $product->price * $number / 100);
                    $Total += $product->price * $number;
                }
            }
            if ($Total < $this->settings()->freeDelivery)
                $total += $deliveryCost;
            if ($Total == 0) {
                return $this->errorBack('محصولی انتخاب نشده است!');
            }
            if (!$this->isAdmin())
                if ($request->paymentMethod == 'credit') {
                    if ($total > ($user->balance + $this->settings()->negative)) {
                        return $this->errorBack('اعتبار شما کافی نیست!');
                    } else {
                        $user->update([
                            'balance' => $user->balance - $total
                        ]);
                    }
                } elseif ($request->paymentMethod == 'receipt') {
                    if ($request->file("receipt")) {
                        $request->receipt = $request->file("receipt")->store("", 'receipt');
                    } elseif ($request->file) {
                        $request->receipt = $request->file;
                    } else {
                        return $this->errorBack('باید عکس رسید بانکی بارگذاری شود!');
                    }
                } elseif ($request->paymentMethod == 'onDelivery') {
                    $request->desc = $request->desc . '- پرداخت در محل';
                    $customerCost = round($Total * (100 - $request->customerDiscount) / 100 + $deliveryCost);
                } else
                    return $this->errorBack('مشکلی پیش آمده!');
        }

        $order = $user->orders()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'orders' => $request->orders,
            'desc' => $request->desc,
            'receipt' => $request->receipt,
            'total' => $total,
            'customerCost' => $customerCost,
            'paymentMethod' => $request->paymentMethod,
            'deliveryMethod' => $request->deliveryMethod,
        ]);

        foreach ($products as $name => $product) {
            if (($request[$name]) > 0) {
                $coupon = $this->calculateDis($product->id);
                $price = round((100 - $coupon) * $product->price / 100);
                $order->orderProducts()->create([
                    'name' => $name,
                    'price' => $price,
                    'photo' => $product->photo,
                    'product_id' => $product->id,
                    'number' => $request[$name],
                ]);
            }
        }
        TelegramController::sendOrderToTelegram($order);
        TelegramController::sendOrderToTelegramAdmins($order);

        $this->addToCustomers($request);

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
        if ($this->isAdmin())
            $order = Order::findOrFail($id);
        else
            $order = auth()->user()->orders()->findOrFail($id);

        if ($order->state > 0)
            return 'سفارش قابل ویرایش نیست چون پردازش شده است.';
//        $products = Product::all()->keyBy('id');
        $customers = auth()->user()->customers()->get()->keyBy('name');
        return view('addEditOrder')->with(['order' => $order, 'customers' => $customers]);
    }

    public function editOrder($id, Request $request)
    {
        if ($this->isAdmin())
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

        $order->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'desc' => $request->desc,
        ]);

        $this->addToCustomers($request);

        return redirect()->route('listOrders');
    }

    public function increaseState($id)
    {
        $order = Order::findOrFail($id);
        $user = User::find($order->user_id);
        $order->state = '' . (($order->state + 1) % 4);
        if ($order->paymentMethod == 'onDelivery') {
            if ($order->state == '1') {
                $user->balance += $order->customerCost - $order->total;
            } elseif ($order->state == '0') {
                $user->balance -= $order->customerCost - $order->total;
            }
        }
        $order->save();
        $user->save();
        return $order->state;

    }

    public function deleteOrder($id, Request $request)
    {
        if ($this->isAdmin())
            $order = Order::findOrFail($id);
        else
            $order = auth()->user()->orders()->findOrFail($id);

        if ($order->state > 0)
            return 'سفارش نمی تواند حذف شود، چون پردازش شده است!';

        if ($order->delete()) {
            if ($order->paymentMethod == 'credit' && !$this->isAdmin()) {
                $user = $order->user()->first();
                $user->update([
                    'balance' => $user->balance + $order->total,
                ]);
            }
            return 'با موفقیت حذف شد';
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

    private function addToCustomers($request)
    {
        if ($request->addToCustomers) {
            $customer = auth()->user()->customers()->where('name', $request->name);
            if ($customer->count()) {
                $customer->update([
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                ]);
            } else {
                auth()->user()->customers()->create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                ]);
            }
        }
    }


}
