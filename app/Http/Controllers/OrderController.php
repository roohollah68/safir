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
            $orders = Order::withTrashed()->orderBy('id', 'desc')->limit($this->loadOrders())->get();
        } else {
            $users = [auth()->user()];
            $orders = auth()->user()->orders()->withTrashed()->orderBy('id', 'desc')->limit($this->loadOrders())->get();
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
        $products = Product::all()->keyBy('name');
        foreach ($products as $ii => $product) {
            $products[$ii]->coupon = $this->calculateDis($product->id);
            $products[$ii]->price2 = round((100 - $products[$ii]->coupon) * $product->price / 100);
        }
        $customers = auth()->user()->customers()->get()->keyBy('name');
        return view('addEditOrder', ['req' => $request->all(), 'order' => false, 'customers' => $customers, 'products' => $products]);
    }

    public function insertOrder(Request $request)
    {
        request()->validate([
            'receipt' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|digits:11',
        ]);
        $user = auth()->user();
        $products = Product::where('available', true)->get()->keyBy('name');
        $request->orders = '';
        $total = 0;
        foreach ($products as $name2 => $product) {
            $name = str_replace(' ', '_', $name2);
            if (($request[$name]) > 0) {
                $request->orders = $request->orders . $name2 . ' ' . $request[$name] . ' عدد|';
                $coupon = $this->calculateDis($product->id);
                $total += round((100 - $coupon) * $product->price * $request[$name] / 100);
            }
        }
        if ($total == 0) {
            return redirect()->back()->withInput()->withErrors(['محصولی انتخاب نشده است!']);
        }
        $request->orders = substr($request->orders, 0, -1);
        if ($request->credit) {
            if ($total > ($user->balance + $this->negative())) {
                return redirect()->back()->withInput()->withErrors(['اعتبار شما کافی نیست!']);
            } else {
                $user->update([
                    'balance' => $user->balance - $total
                ]);
            }
        } else {
            if ($request->file("receipt")) {
                $request->receipt = $request->file("receipt")->store("", 'public');
            } elseif ($request->file) {
                $request->receipt = $request->file;
            } else {
                return redirect()->back()->withInput()->withErrors(['باید عکس رسید بانکی بارگذاری شود!']);
            }
        }


        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);
        $order = $user->orders()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'orders' => $request->orders,
            'desc' => $request->desc,
            'receipt' => $request->receipt,
            'total' => $total,
            'fromCredit' => true && $request->credit,
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
        if ($this->isAdmin()) {
            $order = Order::findOrFail($id);
            $order->state = '' . (($order->state + 1) % 7);
            $order->save();
            return $order->state;
        }
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
            if ($order->fromCredit) {
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
        $dis = $this->minCoupon();
        $user_id = auth()->user()->id;
        $couponLinks = CouponLink::where('product_id', $product_id)->where('user_id', $user_id)->get();
        foreach ($couponLinks as $couponLink) {
            $dis = max($dis, $couponLink->coupon()->first()->percent);
        }
        return $dis;
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
