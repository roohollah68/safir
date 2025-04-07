<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Bank;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Order;
use App\Models\PaymentLink;
use App\Models\Province;
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
        $user = auth()->user();
        $viewAllAuth = $user->meta(['allCustomers', 'editAllCustomers', 'counter']);
        $customers = Customer::with(['user', 'orders', 'transactions']);
        if ($viewAllAuth) {
            if ($req->user)
                $customers = $customers->where('user_id', $req->user);
        } else
            $customers = $customers->where('user_id', $user->id);
        if (isset($req->trust))
            $customers = $customers->where('trust', +$req->trust);
        $customers = $customers->get()->keyBy("id");
        $total = 0;
        foreach ($customers as $customer) {
            //$customer->balance = $customer->balance();
            $total += $customer->balance;
        }
        return view('customer.customerList', [
            'customers' => $customers,
            'total' => $total,
            'users' => User::where('role', 'admin')->where('verified', true)->get()->keyBy('id'),
            'viewAllAuth' => $viewAllAuth,
        ]);
    }

    public function customersTransactionList($id)
    {
        $user = auth()->user();
        $customer = Customer::with(['orders', 'transactions']);
        if (!$user->meta(['allCustomers', 'editAllCustomers']))
            $customer = $customer->where('user_id', $user->id);
        $customer = $customer->findOrFail($id);
        $deposits = $customer->transactions->keyBy('id');
        $orders = $customer->orders->keyBy('id')->where('confirm', true)->where('total', '<>', 0);

        return view('customer.customerTransactionList', [
            'customer' => $customer,
            'deposits' => $deposits,
            'orders' => $orders
        ]);

    }

    public function addForm()
    {
        $customer = new Customer;
        $customer->city_id = 301;
        $customer->user_id = auth()->user()->id;
        return view('customer.addEditCustomer', [
            'customer' => $customer,
            'cities' => City::all()->keyBy('name'),
            'citiesId' => City::all()->keyBy('id'),
            'province' => Province::all()->keyBy('id'),
            'users' => User::where('verified', true)->get(),
            'edit' => false,
        ]);
    }

    public function storeCustomer(Request $request, $id = null)
    {
        request()->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|string|max:11|min:11',
            'address' => 'required|string',
            'agreement' => 'required',
            'national_code' => 'nullable|string|max:10|min:10',
            'national_id' => 'nullable|string|max:11|min:11',
            'economic_code' => 'nullable|string|max:14|min:11',
            'customer_type' => 'required|in:Individual,LegalEntity',
        ], [
            'agreement.required' => 'پر کردن فیلد تفاهم اجباری است.',
            'name.required' => 'پر کردن فیلد نام اجباری است.',
        ]);

        if ($id) {
            if (auth()->user()->meta('editAllCustomers'))
                $customer = Customer::findOrFail($id);
            else {
                $customer = auth()->user()->customers()->findOrFail($id);
                $request->credit_limit = '' . $customer->credit_limit;
            }
        } else {
            $customer = new Customer();
            $request->credit_limit = $request->credit_limit ?? '0';
        }

        $customer->fill([
            'name' => $request->name,
            'phone' => Helper::number_Fa_En($request->phone),
            'address' => $request->address,
            'zip_code' => Helper::number_Fa_En($request->zip_code),
            'city_id' => $request->city_id,
            'user_id' => $request->user,
            'credit_limit' => +str_replace(",", "", $request->credit_limit),
            'discount' => $request->discount,
            'agreement' => $request->agreement,
            'customer_type' => $request->customer_type,
            'national_code' => Helper::number_Fa_En($request->national_code),
            'national_id' => Helper::number_Fa_En($request->national_id),
            'economic_code' => Helper::number_Fa_En($request->economic_code),
        ])->save();
        $customer->orders()->update([
            'user_id' => $request->user,
        ]);
        return redirect()->route('CustomerList');
    }

    public function showEditForm($id)
    {
        if (auth()->user()->meta('editAllCustomers'))
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);

        return view('customer.addEditCustomer', [
            'customer' => $customer,
            'cities' => City::all()->keyBy('name'),
            'citiesId' => City::all()->keyBy('id'),
            'province' => Province::all()->keyBy('id'),
            'users' => User::where('verified', true)->get(),
            'edit' => true,
        ]);
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::with(['orders.orderProducts', 'transactions'])->find($id);
        foreach ($customer->orders as $order) {
            foreach ($order->orderProducts as $orderProduct)
                $orderProduct->delete();
            $order->delete();
        }
        foreach ($customer->transactions as $transaction) {
            $transaction->delete();
        }
        $customer->delete();
    }

    public function changeTrust($id)
    {
        if (auth()->user()->meta('editAllCustomers'))
            $customer = Customer::findOrFail($id);
        else
            $customer = auth()->user()->customers()->findOrFail($id);
        $customer->trust = !$customer->trust;
        $customer->save();
        return $customer->trust;
    }

    public function newForm($customerId, $orderId = null)
    {
        if (auth()->user()->meta('editAllCustomers'))
            $customer = Customer::findOrFail($customerId);
        else
            $customer = auth()->user()->customers()->findOrFail($customerId);

        if ($orderId)
            $order = Order::findOrFail($orderId);
        else
            $order = new Order();

        return view('customer.addEditCustomerDeposit', [
            'customer' => $customer,
            'deposit' => new CustomerTransaction(),
            'order' => $order,
            'banks' => Bank::where('enable', true)->get()->keyBy('id'),
            'edit' => false,
        ]);
    }

    public function editForm($customerId, $depositId)
    {
        if (auth()->user()->meta('editAllCustomers'))
            $customer = Customer::findOrFail($customerId);
        else
            $customer = auth()->user()->customers()->findOrFail($customerId);

        return view('customer.addEditCustomerDeposit', [
            'customer' => $customer,
            'deposit' => $customer->transactions()->find($depositId),
            'order' => new Order(),
            'banks' => Bank::where('enable', true)->get()->keyBy('id'),
            'edit' => true,
        ]);
    }

    public function store(Request $req, $customerId, $orderId = null, $depositId = null)
    {
        DB::beginTransaction();
        $user = auth()->user();
        request()->validate([
            'photo' => 'required_without:old_Photo|mimes:jpeg,jpg,png,bmp|max:2048',
            'cheque_registration' => 'required_if:pay_method,cheque|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:2048',
            'old_Photo' => 'required_without:photo',
        ], [
            'photo.required_without' => 'ارائه تصویر الزامی است!',
            'old_Photo.required_without' => '',
            'photo.max' => 'حجم فایل نباید از 2mb بیشتر باشد.',
            'cheque_registration.max' => 'حجم فایل نباید از 2mb بیشتر باشد.',
            'cheque_registration.required_if' => 'ارائه تصویر الزامی است!'
        ]);

        if ($user->meta('editAllCustomers'))
            $customer = Customer::findOrFail($customerId);
        else
            $customer = auth()->user()->customers()->findOrFail($customerId);

        $photo = $req->old_Photo;
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'deposit');
        }
        if ($req->file('cheque_registration')) {
            $cheque_registration = $req->file('cheque_registration')->store("", 'deposit');
        }

        $transaction = $customer->transactions()->findOrNew($depositId)->fill([
            'bank_id' => $req->bank_id,
            'pay_method' => $req->pay_method,
            'cheque_date' => $req->cheque_date,
            'cheque_name' => $req->cheque_name,
            'cheque_code' => $req->cheque_code,
            'description' => $req->description,
            'amount' => +str_replace(",", "", $req->amount),
            'photo' => $photo,
            'cheque_registration' => $req->pay_method == 'cheque' ? $cheque_registration : null,
        ]);
        $transaction->save();
        if (+$orderId) {
            $transaction->paymentLinks()->create([
                'order_id' => $orderId,
                'amount' => $transaction->amount,
            ]);
            $order = Order::findOrFail($orderId);
            if (!$order->confirm)
                $customer->update([
                    'balance' => $customer->balance - $order->total,
                ]);
            $order->update([
                'confirm' => true,
                'confirmed_at' => Carbon::now(),
                'paymentMethod' => $req->pay_method,
            ]);
            (new CommentController)->create($order, auth()->user(), 'سفارش تایید شد. ' . $req->description . '/ ' . $order->payMethod());
        } else {
            $transaction->paymentLinks()->delete();
        }
        $message = "*ثبت سند واریزی مشتری*
نام: {$customer->name}
مبلغ: {$req->amount} ریال
توسط: {$user->name}
توضیحات: {$transaction->description}
        ";

        $array = array("caption" => $message, "photo" => env('APP_URL') . "deposit/{$photo}");
        $this->sendPhotoToBale($array, env('Deposit'));

        DB::commit();

        return redirect(route('customersTransactionList', ['id' => $customerId]));
    }

    public function viewDeposit($id)
    {
        $deposit = CustomerTransaction::findOrFail($id);
        return view('customer.view', ['deposit' => $deposit]);
    }

    public function deleteDeposit($id)
    {
        $transaction = CustomerTransaction::findOrFail($id);
        if (auth()->user()->meta('editAllCustomers'))
            $customer = Customer::findOrFail($transaction->customer_id);
        else
            $customer = auth()->user()->customers()->findOrFail($transaction->customer_id);
        if (!auth()->user()->meta('allCustomers'))
            if ($customer->user_id != auth()->user()->id)
                abort(403);
        $transaction->paymentLinks()->delete();
        $transaction->delete();
    }

    public function customersDepositList(Request $req)
    {
        Helper::access('counter');
        $req->verified = $req->verified ?: 'waiting';
        $transactions = CustomerTransaction::whereHas('customer.user', function ($query) {
            if (isset($_GET['user_id']) && $_GET['user_id'])
                $query->where('id', $_GET['user_id']);
            else
                $query;
        })->where('verified', $req->verified)->with(['customer.user'])->limit(2000)->orderBy('id', 'desc');

        return view('customer.customersDepositList', [
            'transactions' => $transactions->get()->keyBy('id'),
            'users' => User::where('role', '<>', 'user')->where('verified', true)->get()->keyBy('id'),
            'verified' => $req->verified,
            'user_id' => $req->user_id,
            'get' => http_build_query($_GET) . '&',
        ]);
    }

    public function approveDeposit($id)
    {
        Helper::access('counter');
        $trans = CustomerTransaction::findOrFail($id);
        if ($trans->verified == 'approved')
            return $trans->verified;
        echo $trans->customer->update([
            'balance' => $trans->customer->balance + $trans->amount,
        ]);
        $trans->update([
            'verified' => 'approved',
        ]);
        return $trans->verified;
    }

    public function rejectDeposit($id, Request $req)
    {
        DB::beginTransaction();
        Helper::access('counter');
        $trans = CustomerTransaction::findOrFail($id);
        if ($trans->verified == 'rejected')
            return $trans->verified;
        if ($trans->verified == 'approved')
            $trans->customer->update([
                'balance' => $trans->customer->balance - $trans->amount,
            ]);
        $trans->update([
            'verified' => 'rejected',
            'description' => $trans->description . ' _ ' . $req->reason,
        ]);
        $trans->paymentLinks()->delete();
        DB::commit();
        return $trans->verified;
    }

    public function customersOrderList(Request $req)
    {
        Helper::access('counter');
        return view('customer.customersOrderList', [
            'orders' => Order::where('confirm', true)->whereNotNull('customer_id')
                ->where('state', false)->with(['user', 'paymentLinks.customerTransaction'])->get()->keyBy('id'),
            'users' => User::where('role', '<>', 'user')->where('verified', true)->select('id', 'name')->get(),
            'selectedUser' => (!$req->user || $req->user == 'all') ? 'all' : +$req->user,

        ]);
    }

    public function approveOrder($id)
    {
        Helper::access('counter');
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'approved')
            return;
        $order->counter = 'approved';

        app('Telegram')->sendOrderToBale($order, env('GroupId'));
        (new CommentController)->create($order, auth()->user(), 'تایید حسابداری');
        $order->save();
        DB::commit();
    }

    public function rejectOrder($id, Request $req)
    {
        Helper::access('counter');
        DB::beginTransaction();
        $order = Order::findOrFail($id);
        if ($order->counter == 'rejected' || $order->state)
            return;
        if ($order->counter == 'approved') {
            $this->deleteFromBale(env('GroupId'), $order->bale_id);
        }
        $order->counter = 'rejected';
        (new CommentController)->create($order, auth()->user(), 'عدم تایید حسابداری: ' . ($req->reason ?? ''));
        $order->save();
        DB::commit();
        (new OrderController())->cancelInvoice($id, $req);
    }

    public function customerSOA($id, Request $request)
    {
        $user = auth()->user();
        if ($user->meta(['allCustomers', 'editAllCustomers']))
            $customer = Customer::with(['orders', 'transactions'])->find($id);
        else
            $customer = $user->customers()->with(['orders', 'transactions'])->find($id);
        $orders = $customer->orders->where('total', '>', 0)->where('confirm', true);
        $transactions = $customer->transactions;
        $timeDescription = 'همه تراکنش ها';
        if ($request->timeFilter == 'specifiedTime') {
            $timeDescription = 'از ' . $request->from . ' تا ' . $request->to;
            $request->from = Verta::parse($request->from)->toCarbon();
            $request->to = Verta::parse($request->to)->addDay()->addSeconds(-1)->toCarbon();
            $transactions = $transactions->where('created_at', '>', $request->from)
                ->where('created_at', '<', $request->to);
            $orders = $orders->where('created_at', '>', $request->from)
                ->where('created_at', '<', $request->to);
        }

        $pdf = PDF::loadView('customer.customerSOA', [
                'customer' => $customer,
                'transactions' => $transactions,
                'orders' => $orders,
                'timeDescription' => $timeDescription,
                'withInvoice' => !!$request->allInvoice,
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
        $orders = $customer->orders->keyBy('id')->where('confirm', true)->where('total', '>', 0);
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
        $user = auth()->user();
        $payLink = PaymentLink::findOrfail($id);
        if ($payLink->order->user->id != $user->id)
            Helper::access('editAllCustomers');
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

    public function paymentTracking(Request $req)
    {

        $orders = Order::with(['user', 'paymentLinks'])
            ->where([
                ['counter', 'approved'],
                ['confirm', true],
                ['total', '>', 0],
                ['customer_id', '>', 0],
                ['state', '>=', 10],
            ])
            ->with(['user', 'paymentLinks'])
            ->where(function ($query) {
                $query->orWhereDate('payInDate', '<', today())->orWhere(function ($query) {
                    $query->whereNull('payInDate')->whereDate('sent_at', '<', today()->addWeeks(-2));
                });
            });
        if (!$req->noPostpone)
            $orders = $orders->where(function ($query) {
                $query->orWhereDate('postponeDate', '<', today())->orWhereNull('postponeDate');
            });
        if ($req->user)
            $orders = $orders->where('user_id', $req->user);

        if(!auth()->user()->meta('allCustomers'))
            $orders = $orders->where('user_id', auth()->user()->id);

        if ($req->paymethods)
            $orders = $orders->whereIn('paymentMethod', array_keys($req->paymethods));

        if ($req->from) {
            $orders = $orders->whereDate('sent_at', '>=', Verta::parse($req->from)->DateTime());
        }

        if ($req->to) {
            $orders = $orders->whereDate('sent_at', '<=', Verta::parse($req->to)->DateTime());
        }

        $orders = $orders->get()->keyBy('id');

        $orders = $orders->filter(fn($order) => $order->unpaid() > 0);

        return view('customer.paymentTracking', [
            'orders' => $orders,
            'users' => User::where('role', '<>', 'user')->get()->keyBy('id'),
        ]);
    }

    public function postponedDay($id, $days)
    {
        Helper::access('counter');
        $order = Order::findOrFail($id);
        $date = Carbon::now();
        $date->addDays(+$days);
        $order->postponeDate = $date;
        $order->save();
    }

    public function blockList()
    {
        Helper::access('blockList');
        $user = auth()->user();
        $customers = Customer::with(['orders', 'transactions'])->get()->keyBy('id');
        return view('customer.blockList', [
            'customers' => $customers,
        ]);
    }

    public function changeBlock($id)
    {
        Helper::access('usersEdit');
        $customer = Customer::findOrFail($id);
        $customer->block = !$customer->block;
        $customer->save();
        return $customer->block;
    }
}
