<?php

namespace App\Http\Controllers;

use App\BaleAPIv2;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerTransactions;
use App\Models\Order;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function customersList(Request $req)
    {

        if ($this->superAdmin()) {
            if (!$req->user || $req->user == 'all')
                $customers = Customer::with('user')->get()->keyBy("id");
            else
                $customers = Customer::where('user_id', $req->user)->with('user')->get();
        } else
            $customers = auth()->user()->customers()->get()->keyBy("id");
        $total = 0;
        foreach ($customers as $customer) {
            $total += $customer->balance;
        }
        return view('customerList', [
            'customers' => $customers,
            'total' => $total,
            'users' => User::where('role', 'admin')->where('verified', true)->get(),
        ]);
    }

    public function customersTransactionList($id)
    {
        if ($this->superAdmin())
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);
        $transactions = $customer->transactions()->get()->keyBy('id');
        $orders = $customer->orders()->get();

        return view('customerTransactionList',
            ['customer' => $customer, 'transactions' => $transactions, 'orders' => $orders]);

    }

    public function addForm()
    {
        $customer = new Customer;
        $customer->city_id = 301;

        return view('addEditCustomer', [
            'customer' => $customer,
            'cities' => City::all()->keyBy('name'),
            'citiesId' => City::all()->keyBy('id'),
            'province' => Province::all()->keyBy('id'),
        ]);
    }

    public function storeNewCustomer(Request $request)
    {
        request()->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|string|max:11|min:11',
            'address' => 'required|string',
        ]);
        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);

        auth()->user()->customers()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'category' => $request->category,
            'city_id' => $request->city_id,
        ]);
        return redirect()->route('CustomerList');
    }

    public function showEditForm($id)
    {
        if ($this->superAdmin())
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);

        return view('addEditCustomer', [
            'customer' => $customer,
            'cities' => City::all()->keyBy('name'),
            'citiesId' => City::all()->keyBy('id'),
            'province' => Province::all()->keyBy('id'),
            'users' => User::where('verified', true)->get(),
        ]);
    }

    public function updateCustomer($id, Request $request)
    {
        request()->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|string|max:11|min:11',
            'address' => 'required|string',
        ]);

        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);

        if ($this->superAdmin())
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'category' => $request->category,
            'city_id' => $request->city_id,
            'user_id' => $request->user,
        ]);
        return redirect()->route('CustomerList');
    }

    public function newForm($id, $linkId = false)
    {
        if ($this->superAdmin())
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);

        if ($linkId)
            $link = $customer->transactions()->findOrFail($linkId);
        else
            $link = false;

        return view('addEditCustomerDeposit', ['customer' => $customer, 'deposit' => false, 'link' => $link]);
    }

    public function storeNew(Request $req)
    {
        $req->amount = +str_replace(",", "", $req->amount);
        request()->validate([
            'photo' => 'required|mimes:jpeg,jpg,png,bmp|max:2048',
            'amount' => 'required',
        ], [
            'photo.required' => 'ارائه رسید بانکی الزامی است!'
        ]);

        DB::beginTransaction();

        $photo = '';
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'deposit');
        }
        $order_id = '';
        if ($req->link)
            $order_id = CustomerTransactions::find($req->link)->order()->first()->id;

        if ($this->superAdmin())
            $customer = Customer::findOrFail($req->id);
        else
            $customer = auth()->user()->customers()->findOrFail($req->id);

        $newTransaction = $customer->transactions()->create([
            'amount' => $req->amount,
            'description' => 'واریزی ' . $order_id . ' * ' . $req->desc . ' - ' . auth()->user()->name,
            'type' => true,
            'photo' => $photo,
            'paymentLink' => $req->link,
            'verified' => 'waiting',
        ]);

        $customer->update([
            'balance' => $customer->balance + $req->amount,
        ]);
        if ($req->link) {
            $transaction = $customer->transactions()->find($req->link);
            $transaction->update([
                'paymentLink' => $newTransaction->id,
            ]);
            Order::find($transaction->order_id)->update([
                'receipt' => $photo,
            ]);
        }
        $req->amount = number_format($req->amount);

        $message = "ثبت سند واریزی مشتری
        نام:{$customer->name}
        مبلغ: {$req->amount} ریال
         توضیحات:{$newTransaction->description}
        ";

        $array = array("caption" => $message, "photo" => env('APP_URL') . "deposit/{$photo}");
        $this->sendPhotoToBale($array, '4538199149');

        DB::commit();

        return redirect('/customer/transaction/' . $req->id);
    }

    public function deleteDeposit($id)
    {
        DB::beginTransaction();
        $transaction = CustomerTransactions::findOrFail($id);
        $customer = $transaction->customer()->first();
        if (!$this->superAdmin())
            $customer = auth()->user()->customers()->findOrFail($customer->id);

        if ($transaction->deleted)
            return;

        $customer->transactions()->create([
            'amount' => $transaction->amount,
            'description' => 'ابطال ثبت واریزی - ' . $transaction->desc . ' - ' . auth()->user()->name,
            'type' => false,
            'verified' => 'rejected',
            'photo' => $transaction->photo,
            'deleted' => true,
        ]);
        if ($transaction->paymentLink) {
            CustomerTransactions::find($transaction->paymentLink)->update([
                'paymentLink' => null,
            ]);
        }
        $transaction->update([
            'paymentLink' => null,
            'deleted' => true,
            'verified' => 'rejected',
            'description' => $transaction->description . '* باطل شد',
        ]);

        DB::commit();
    }

    public function customersDepositList(Request $req)
    {

        return view('customersDepositList', [
            'transactions' => CustomerTransactions::with('customer.user')->get()->keyBy('id'),
            'users' => User::where('role', 'admin')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,
        ]);
    }

    public function approveDeposit($id)
    {
        $trans = CustomerTransactions::with('customer')->findOrFail($id);
        if ($trans->verified == 'approved')
            return;
        $trans->customer->update([
            'balance' => $trans->customer->balance + $trans->amount,
        ]);
        $trans->update([
            'verified' => 'approved',
        ]);
    }

    public function rejectDeposit($id)
    {
        $trans = CustomerTransactions::with('customer')->findOrFail($id);

        if ($trans->verified == 'rejected')
            return;
        if ($trans->verified == 'approved')
            $trans->customer->update([
                'balance' => $trans->customer->balance - $trans->amount,
            ]);
        $trans->update([
            'verified' => 'rejected',
        ]);
    }

    public function customersOrderList(Request $req)
    {

        return view('customersOrderList', [
            'orders' => Order::where('confirm',true)->where('customer_id','>','0')
                ->where('state',false)->with('user')->get()->keyBy('id'),
            'users' => User::where('role', 'admin')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,

        ]);
    }

    public function approveOrder($id)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'approved')
            return;
        $order->counter = 'approved';
        $orderProducts = $order->orderProducts()->with('product');
        $orderProducts->update(['verified' => true]);
        foreach ($orderProducts->get() as $orderProduct) {
            $product = $orderProduct->product;

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
        $order->bale_id = app('Telegram')->sendOrderToBale($order, env('GroupId'))->result->message_id;
        $order->save();
        DB::commit();
    }

    public function rejectOrder($id)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'rejected')
            return;
        if($order->counter == 'approved'){
            $order->orderProducts()->update(['verified' => false]);
            foreach ($order->productChange()->get() as $productChange) {
                $product = $productChange->product()->first();
                $product->update([
                    'quantity' => $product->quantity - $productChange->change,
                ]);
                $productChange->update(['isDeleted' => true]);
                $order->productChange()->create([
                    'product_id' => $product->id,
                    'change' => -$productChange->change,
                    'quantity' => $product->quantity,
                    'desc' => 'لغو خرید مشتری ' . $order->name,
                    'isDeleted' => true,
                ]);
            }
            $this->deleteFromBale(env('GroupId'), $order->bale_id);
        }
        $order->counter = 'rejected';
        $order->save();
        DB::commit();
    }

}
