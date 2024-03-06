<?php

namespace App\Http\Controllers;

use App\BaleAPIv2;
use App\Models\Customer;
use App\Models\CustomerTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function customersList()
    {
        if ($this->superAdmin() || $this->admin())
            $customers = Customer::all()->keyBy("id");
        else
            $customers = auth()->user()->customers()->get()->keyBy("id");
        $total = 0;
        foreach ($customers as $customer) {
            $total += $customer->balance;
        }
        return view('customerList', ['customers' => $customers, 'total' => $total]);
    }

    public function customersTransactionList($id)
    {
        $customer = Customer::find($id);
        $transactions = $customer->transactions()->get();
        $orders = $customer->orders()->get();

        return view('customerTransactionList',
            ['customer' => $customer, 'transactions' => $transactions, 'orders' => $orders]);

    }

    public function addForm()
    {
        $customer = new Customer;
        return view('addEditCustomer', [ 'customer' => $customer]);
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
        ]);
        return redirect()->route('CustomerList');
    }

    public function showEditForm($id)
    {
        $customer = Customer::find($id);
        return view('addEditCustomer', ['customer' => $customer]);
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

        auth()->user()->customers()->find($id)->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'category' => $request->category,
        ]);
        return redirect()->route('CustomerList');
    }

    public function newForm($id, $linkId = false)
    {
        $customer = Customer::find($id);
        if ($linkId) {
            $link = CustomerTransactions::find($linkId);
        } else {
            $link = false;
        }
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

        $customer = Customer::find($req->id);
        $newTransaction = $customer->transactions()->create([
            'amount' => $req->amount,
            'description' => 'واریزی ' . $order_id . ' * ' . $req->desc.' - '.auth()->user()->name,
            'type' => true,
            'photo' => $photo,
            'balance' => $customer->balance + $req->amount,
            'paymentLink' => $req->link,
        ]);

        $customer->update([
            'balance' => $customer->balance + $req->amount,
        ]);
        if ($req->link)
            $customer->transactions()->find($req->link)->update([
                'paymentLink' => $newTransaction->id,
            ]);
        $req->amount = number_format($req->amount);
        $message = "ثبت سند واریزی مشتری
        نام:{$customer->name}
        مبلغ: {$req->amount} ریال
        ";
        $array = array("caption" => $message, "photo" => env('APP_URL') . "deposit/{$photo}");
        $this->sendPhotoToBale($array, '4538199149');

        DB::commit();

        return redirect('/customer/transaction/' . $req->id);
    }

    public function deleteDeposit($id)
    {
        DB::beginTransaction();
        $transaction = CustomerTransactions::find($id);
        if ($transaction->deleted)
            return;
        $customer = $transaction->customer()->first();
        $customer->transactions()->create([
            'amount' => $transaction->amount,
            'description' => 'ابطال ثبت واریزی - ' . $transaction->desc .' - '.auth()->user()->name,
            'type' => false,
            'photo' => $transaction->photo,
            'balance' => $customer->balance - $transaction->amount,
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
            'description' => $transaction->description . '* باطل شد',
        ]);
        $customer->update([
            'balance' => $customer->balance - $transaction->amount,
        ]);
        DB::commit();
    }
}
