<?php

namespace App\Http\Controllers;

use App\BaleAPIv2;
use App\Helper\Helper;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Order;
use App\Models\PaymentLink;
use App\Models\Province;
use App\Models\Transaction;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class CustomerController extends Controller
{
    public function customersList(Request $req)
    {
        if (auth()->user()->meta('allCustomers')) {
            if (!$req->user || $req->user == 'all')
                $customers = Customer::with('user')->get()->keyBy("id");
            else
                $customers = Customer::where('user_id', $req->user)->with('user')->get();
        } else
            $customers = auth()->user()->customers->keyBy("id");
        $total = 0;
        foreach ($customers as $customer) {
            $total += $customer->balance;
        }
        return view('customer.customerList', [
            'customers' => $customers,
            'total' => $total,
            'users' => User::where('role', 'admin')->where('verified', true)->get()->keyBy('id'),
        ]);
    }

    public function customersTransactionList($id)
    {
        if (auth()->user()->meta('allCustomers'))
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);
        $transactions = $customer->transactions()->get()->keyBy('id');
        $orders = $customer->orders()->get()->keyBy('id');

        return view('customer.customerTransactionList', [
            'customer' => $customer,
            'transactions' => $transactions,
            'orders' => $orders
        ]);

    }

    public function addForm()
    {
        $customer = new Customer;
        $customer->city_id = 301;

        return view('customer.addEditCustomer', [
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
        if (auth()->user()->meta('allCustomers'))
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);

        return view('customer.addEditCustomer', [
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

        if (auth()->user()->meta('allCustomers'))
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
        if (auth()->user()->meta('allCustomers'))
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);

        if ($linkId)
            $link = $customer->transactions()->findOrFail($linkId);
        else
            $link = false;

        return view('customer.addEditCustomerDeposit', [
            'customer' => $customer,
            'deposit' => false,
            'link' => $link
        ]);
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

        if (auth()->user()->meta('allCustomers'))
            $customer = Customer::findOrFail($req->id);
        else
            $customer = auth()->user()->customers()->findOrFail($req->id);

        $newTransaction = $customer->transactions()->create([
            'amount' => $req->amount,
            'description' => $req->desc,
            'photo' => $photo,
            'verified' => 'waiting',
        ]);
        $req->amount = number_format($req->amount);

        $message = "ثبت سند واریزی مشتری
نام: {$customer->name}
مبلغ: {$req->amount} ریال
توضیحات: {$newTransaction->description}
        ";

        $array = array("caption" => $message, "photo" => env('APP_URL') . "deposit/{$photo}");
        $this->sendPhotoToBale($array, '4538199149');

        DB::commit();

        return redirect('/customer/transaction/' . $req->id);
    }

    public function deleteDeposit($id)
    {
        $transaction = CustomerTransaction::findOrFail($id);
        $customer = $transaction->customer()->first();
        if (!auth()->user()->meta('allCustomers'))
            if ($customer->user_id != auth()->user()->id)
                abort(403);
        $transaction->paymentLinks()->delete();
        $transaction->delete();
    }

    public function customersDepositList(Request $req)
    {
        Helper::meta('counter');
        return view('customer.customersDepositList', [
            'transactions' => CustomerTransaction::with('customer.user')->limit(2000)->orderBy('id', 'desc')->get()->keyBy('id'),
            'users' => User::where('role', 'admin')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,
        ]);
    }

    public function approveDeposit($id)
    {
        Helper::meta('counter');
        $trans = CustomerTransaction::with('customer')->findOrFail($id);
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
        Helper::meta('counter');
        $trans = CustomerTransaction::with('customer')->findOrFail($id);
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

        $payLinks = $trans->paymentLinks()->delete();
        foreach ($payLinks as $payLink) {
            $order = $payLink->order;
            $payLink->delete();
            $order->payPercent = $order->payPercent();
            $order->save();
        }
    }

    public function customersOrderList(Request $req)
    {
        Helper::meta('counter');
        return view('customer.customersOrderList', [
            'orders' => Order::where('confirm', true)->where('customer_id', '>', '0')
                ->where('state', false)->with('user')->get()->keyBy('id'),
            'users' => User::where('role', 'admin')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,

        ]);
    }

    public function approveOrder($id)
    {
        Helper::meta('counter');
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'approved')
            return;
        $order->counter = 'approved';
        $orderProducts = $order->orderProducts()->with('product');
        $orderProducts->update(['verified' => true]);

        app('Telegram')->sendOrderToBale($order, env('GroupId'));
        (new CommentController)->create($order, auth()->user(), 'تایید حسابداری');
        $order->save();
        DB::commit();
    }

    public function rejectOrder($id, Request $req)
    {
        Helper::meta('counter');
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'rejected' || $order->state)
            return;
        if ($order->counter == 'approved') {
            $order->orderProducts()->update(['verified' => false]);
            $this->deleteFromBale(env('GroupId'), $order->bale_id);
        }
        $order->counter = 'rejected';
        (new CommentController)->create($order, auth()->user(), 'عدم تایید حسابداری: ' . ($req->reason ?? ''));
        $order->save();
        DB::commit();
    }

    public function customerSOA($id, Request $request)
    {
        $transactions = CustomerTransaction::where('customer_id', $id);
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
        $pdf = PDF::loadView('customer.customerSOA', [
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
    }

    public function depositLink($id)
    {
        $transaction = CustomerTransaction::findOrFail($id);
        $customer = $transaction->customer;
        $payLinks = $transaction->paymentLinks;
        $payLinkTotal = 0;
        foreach ($payLinks as $payLink) {
            $payLinkTotal += $payLink->amount;
        }
        $orders = $customer->orders->keyBy('id')->reverse();
        return view('customer.depositLink', [
            'transaction' => $transaction,
            'payLinks' => $payLinks,
            'orders' => $orders,
            'payLinkTotal' => $payLinkTotal,
        ]);
    }

    public function orderLink($id)
    {
        $order = Order::findOrFail($id);
        $customer = $order->customer;
        $payLinks = $order->paymentLinks;
        $payLinkTotal = 0;
        foreach ($payLinks as $payLink) {
            $payLinkTotal += $payLink->amount;
        }
        $transactions = $customer->transactions->reverse();
        return view('customer.orderLink', [
            'transactions' => $transactions,
            'payLinks' => $payLinks,
            'order' => $order,
            'payLinkTotal' => $payLinkTotal,
        ]);
    }

    public function deletePayLink($id)
    {
        $payLink = PaymentLink::findOrfail($id);
        $payLink->delete();
    }

    public function addPayLink($transaction_id, $order_id)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($order_id);
        $payLinks = $order->paymentLinks;
        $total = 0;
        foreach ($payLinks as $payLink) {
            $total += $payLink->amount;
        }
        $orderRemain = max(0, $order->total - $total);
        $transaction = CustomerTransaction::findOrFail($transaction_id);
        $payLinks = $transaction->paymentLinks;
        $total = 0;
        foreach ($payLinks as $payLink) {
            $total += $payLink->amount;
        }
        $transRemain = max(0, $transaction->amount - $total);
        if ($transaction->verified != 'rejected' && $order->customer_id == $transaction->customer_id)
            $order->paymentLinks()->create([
                'customer_transaction_id' => $transaction_id,
                'amount' => min($orderRemain, $transRemain),
            ]);
        DB::commit();
    }

    public function paymentTracking()
    {
        $orders = [];
        $Orders = Order::with('paymentLinks')->get()->keyBy('id')->reverse();
        foreach ($Orders as $id => $Order) {
            if (count($orders) >= 100)
                break;
            if($Order->counter == 'rejected')
                continue;
            if (!auth()->user()->meta('allCustomers') && auth()->user()->id != $Order->user_id)
                continue;
            if ($Order->payPercent() < 100 && time() > strtotime($Order->payInDate) && $Order->total > 0)
                $orders[$id] = $Order;
        }
        return view('customer.paymentTracking', [
            'orders' => $orders,
        ]);
    }

    function postpondDay($id, $days)
    {
        if (auth()->user()->meta('allCustomers'))
            $order = Order::findOrFail($id);
        else {
            $order = auth()->user()->orders->find($id);
            $days = max($days, 7);
        }
        $date = Carbon::now();
        $date->addDays(+$days);
//        $date = new DateTime('now');
//        $date->modify('+ ' . $days . 'days');
        $order->payInDate = $date;
        $order->save();
    }

}
