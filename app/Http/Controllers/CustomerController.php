<?php

namespace App\Http\Controllers;

use App\BaleAPIv2;
use App\Helper\Helper;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerTransactions;
use App\Models\Order;
use App\Models\Province;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

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
        $request->phone = Helper::number_Fa_En($request->phone);
        $request->zip_code = Helper::number_Fa_En($request->zip_code);

        auth()->user()->customers()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
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

        $request->phone = Helper::number_Fa_En($request->phone);
        $request->zip_code = Helper::number_Fa_En($request->zip_code);

        if ($this->superAdmin())
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);
        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
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
        if (!auth()->user()->meta('counter'))
            abort(401);
        return view('customersDepositList', [
            'transactions' => CustomerTransactions::with('customer.user')->limit(2000)->orderBy('id', 'desc')->get()->keyBy('id'),
            'users' => User::where('role', 'admin')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,
        ]);
    }

    public function approveDeposit($id)
    {
        if (!auth()->user()->meta('counter'))
            abort(401);
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

    public function rejectDeposit($id, Request $req)
    {
        if (!auth()->user()->meta('counter'))
            abort(401);
        $trans = CustomerTransactions::with('customer')->findOrFail($id);
        if ($trans->verified == 'rejected')
            return;
        if ($trans->verified == 'approved')
            $trans->customer->update([
                'balance' => $trans->customer->balance - $trans->amount,
            ]);
        $trans->update([
            'verified' => 'rejected',
            'description' => $trans->description . ' _ ' . $req->reason,
        ]);
    }

    public function customersOrderList(Request $req)
    {
        if (!auth()->user()->meta('counter'))
            abort(401);
        return view('customersOrderList', [
            'orders' => Order::where('confirm', true)->where('customer_id', '>', '0')
                ->where('state', false)->with('user')->get()->keyBy('id'),
            'users' => User::where('role', 'admin')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,

        ]);
    }

    public function approveOrder($id)
    {
        if (!auth()->user()->meta('counter'))
            abort(401);
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'approved')
            return;
        $order->counter = 'approved';
        $orderProducts = $order->orderProducts()->with('product');
        $orderProducts->update(['verified' => true]);
        foreach ($orderProducts->get() as $orderProduct) {
            $product = $orderProduct->product()->withTrashed()->first();
            if ($product) {
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
        }
        $response = app('Telegram')->sendOrderToBale($order, env('GroupId'));
        if (isset($response->result)) {
            $order->bale_id = $response->result->message_id;
        }
        (new CommentController)->create($order, auth()->user(), 'تایید حسابداری');
        $order->save();
        DB::commit();
    }

    public function rejectOrder($id, Request $req)
    {
        if (!auth()->user()->meta('counter'))
            abort(401);
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'rejected')
            return;
        if ($order->counter == 'approved') {
            $order->orderProducts()->update(['verified' => false]);
            foreach ($order->productChange()->get() as $productChange) {
                $product = $productChange->product()->first();
                $productChange->update(['isDeleted' => true]);
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
            $this->deleteFromBale(env('GroupId'), $order->bale_id);
        }
        $order->counter = 'rejected';
        if (isset($req->reason)) {
            (new CommentController)->create($order, auth()->user(), 'عدم تایید حسابداری: ' . $req->reason);
        }
        $order->save();
        DB::commit();
    }

    public function customerSOA($id, Request $request)
    {

        $transactions = CustomerTransactions::where('customer_id', $id);
        $timeDescription = 'همه تراکنش ها';
        if ($request->timeFilter == 'specifiedTime') {
            $timeDescription = 'از ' . $request->from . ' تا ' . $request->to;
            $request->from = Verta::parse($request->from)->toCarbon();
            $request->to = Verta::parse($request->to)->addDay()->addSeconds(-1)->toCarbon();
            $transactions = $transactions->where([
                ['created_at', '>', $request->from],
                ['created_at', '<', $request->to]
            ]);
        }

        $customer = Customer::find($id);
        $pdf = PDF::loadView('customerSOA', [
                'customer' => $customer,
                'transactions' => $transactions->get(),
                'timeDescription' => $timeDescription,
                'withInvoice' => !!$request->allInvoice,
                'orders' => [],
                'total' => 0,
                'total1' => 0,
                'total2' => 0,
            ]
            , []
            , [
                'format' => 'A4',
                'title' => 'گردش حساب',
                'margin_left' => 4,
                'margin_right' => 4,
                'margin_top' => 4,
                'margin_bottom' => 4,
            ]);
        $pdf->getMpdf()->OutputFile('pdf/' . $id . '.pdf');
        return env('APP_URL') . 'pdf/' . $id . '.pdf';
//        return $pdf->stream($id . '.pdf');
//        return $pdf->download($id . '.pdf');
    }

}
