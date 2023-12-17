<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function customersList()
    {
        $customers = auth()->user()->customers()->get();
        return view('customerList', ['customers' => $customers]);
    }

    public function customersTransactionList($id)
    {
        $customer = auth()->user()->customers()->find($id);
        $transactions = $customer->transactions()->get();
        $orders = $customer->orders()->get();

        return view('customerTransactionList',
            ['customer' => $customer, 'transactions' => $transactions, 'orders' => $orders]);

    }

    public function addForm()
    {
        return view('addEditCustomer', ['customer' => false]);
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


//      check duplicate name
        $customer = auth()->user()->customers()->where('name', $request->name);
        if ($customer->count()) {
            $customer->first()->update([
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
        return redirect()->route('CustomerList');
    }

    public function deleteCustomer($id)
    {
        auth()->user()->customers()->find($id)->delete();
    }

    public function showEditForm($id)
    {
        $customer = auth()->user()->customers()->find($id);
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
        ]);
        return redirect()->route('CustomerList');
    }

    public function newForm($id)
    {
        $customer = auth()->user()->customers()->find($id);
        return view('addEditCustomerDeposit', ['customer' => $customer, 'deposit' => false]);
    }

    public function storeNew(Request $req)
    {
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'amount' => 'required|numeric',
        ]);

        DB::beginTransaction();

        $photo = '';
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'deposit');
        }

        $customer = Customer::find($req->id);
        $customer->transactions()->create([
            'amount' => $req->amount,
            'description' => 'ثبت واریزی - ' . $req->desc,
            'type' => true,
            'photo' => $photo,
            'balance' => $customer->balance + $req->amount,
        ]);

        $customer->update([
            'balance' => $customer->balance + $req->amount,
        ]);

        DB::commit();

        return redirect('/customer/transaction/' . $req->id);
    }

    public function deleteDeposit($id)
    {
        DB::beginTransaction();
        $transaction = CustomerTransactions::find($id);
        $customer = Customer::find($transaction->customer_id);
        CustomerTransactions::create([
            'amount' => $transaction->amount,
            'description' => 'ابطال ثبت واریزی - ' . $transaction->desc,
            'type' => false,
            'photo' => $transaction->photo,
            'balance' => $customer->balance - $transaction->amount,
            'customer_id' => $transaction->customer_id
        ]);
        $transaction->update([
            'deleted'=>true,
            'description' => $transaction->description . '- باطل شد',
        ]);
        $customer->update([
            'balance' => $customer->balance - $transaction->amount,
        ]);
        DB::commit();
    }
}
