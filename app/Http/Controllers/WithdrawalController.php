<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Bank;
use App\Models\Supplier;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Tag\B;

class WithdrawalController extends Controller
{
    public function new()
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        return view('withdrawal.addEdit', [
            'suppliers' => Supplier::all()->keyBy('name'),
            'withdrawal' => new Withdrawal(),
            'edit' =>false,
        ]);
    }

    public function insertOrUpdate(Request $req, $id = null)
    {
        DB::beginTransaction();
        $user = auth()->user();
        Helper::access(['withdrawal', 'allWithdrawal']);
        request()->validate([
            'user_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
        ], [
            'user_file.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'user_file.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
        ]);
        $req->merge(['amount' => +str_replace(",", "", $req->amount)]);
        $withdrawal = $user->withdrawals()->where('manager_confirm', '<>', 1)->updateOrCreate([
            'id' => $id
        ], $req->merge([
            'counter_confirm' => 0,
            'manager_confirm' => 0,
            'payment_confirm' => 0
        ])->all());
        $supplier = Supplier::updateOrCreate(['name' => $req->account_name],[
           'account' => $req->account_number,
           'code' => $req->cheque_id,
        ]);
        $withdrawal->update(['supplier_id' => $supplier->id]);
        if ($req->file("user_file")) {
            $withdrawal->user_file = $req->file("user_file")->store("", 'withdrawal');
        } elseif (!$req->old_user_file) {
            $withdrawal->user_file = null;
        }
        $withdrawal->save();
        $this->bale($withdrawal->id);
        DB::commit();
        return redirect(route('WithdrawalList'));
    }

    public function edit($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        $withdrawal = Withdrawal::where('manager_confirm', '<', 1);
        if (!$user->meta('allWithdrawal'))
            $withdrawal = $withdrawal->where('user_id', $user->id);
        $withdrawal = $withdrawal->findOrNew($id);
        return view('withdrawal.addEdit', [
            'suppliers' => Supplier::all()->keyBy('name'),
            'withdrawal' => $withdrawal,
            'edit' => true,
        ]);
    }

    public function delete($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        if ($user->meta('allWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        if ($withdrawal->manager_confirm != 1)
            $withdrawal->delete();
        return redirect(route('WithdrawalList'));
    }

    public function list(Request $req)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        $withdrawals = Withdrawal::where('id', '>', 0);
        if (!$user->meta('allWithdrawal'))
            $withdrawals = $withdrawals->where('user_id', $user->id);
        if ($req->filter == 'counter')
            $withdrawals = $withdrawals->where('counter_confirm', '<>', 1);
        if ($req->filter == 'manager')
            $withdrawals = $withdrawals->where('manager_confirm', '<>', 1)->where('counter_confirm', 1);
        if ($req->filter == 'payment')
            $withdrawals = $withdrawals->where('payment_confirm', '<>', 1)->where('manager_confirm', 1);
        if ($req->filter == 'paid')
            $withdrawals = $withdrawals->where('payment_confirm', 1);
        if ($req->filter == 'recipient')
            $withdrawals = $withdrawals->where('recipient_confirm', '<>', 1)->where('payment_confirm', 1);
        if ($req->filter == 'complete')
            $withdrawals = $withdrawals->where('recipient_confirm', 1);
        if ($req->filter == 'tankhah')
            $withdrawals = $withdrawals->where('tankhah', 1);
        if (isset($req->official))
            $withdrawals = $withdrawals->where('official', $req->official);
        if (isset($req->Location))
            $withdrawals = $withdrawals->where('location', $req->Location);
        if(isset($req->Supplier))
            $withdrawals = $withdrawals->where('supplier_id', $req->Supplier);
        if(isset($req->payMethod))
            $withdrawals = $withdrawals->where('pay_method', $req->payMethod);
        if(isset($req->from))
            $withdrawals = $withdrawals->whereDate('created_at','>=', $req->from);
        if(isset($req->to))
            $withdrawals = $withdrawals->whereDate('created_at','<=', $req->to);
        return view('withdrawal.list', [
            'withdrawals' => $withdrawals->with('user')->get()->keyBy('id'),
            'suppliers' => Supplier::all()->keyBy('id')->sortBy('name'),
            'banks' => Bank::where('enable' , true)->get()->keyBy('id'),
            'get' => http_build_query($_GET).'&',
            'filter' => $req->filter,
            'official' => $req->official,
            'Location' => $req->Location,
            'Supplier' => $req->Supplier,
            'payMethod' => $req->payMethod,
            'from' => $req->from,
            'to' => $req->to,
        ]);
    }

    public function counter($id, Request $req)
    {
        Helper::access('counter');
        request()->validate([
            'expense_desc' => 'required',
        ], [
            'expense_desc.required' => 'نوع هزینه باید مشخص شود!'
        ]);
        Withdrawal::findOrFail($id)->update($req->merge([
            'manager_confirm' => 0
        ])->all());
        $this->bale($id);
        return redirect()->back();
    }

    public function manager($id, Request $req)
    {
        $user = auth()->user();
        if ($user->id != 122)
            abort(401);
        $withdrawal = Withdrawal::findOrFail($id);
        if ($withdrawal->counter_confirm != 1)
            return redirect()->back();
        $withdrawal->manager_confirm = $req->manager_confirm;
        $withdrawal->payment_confirm = 0;
        $withdrawal->manager_desc = $req->manager_desc;
        $withdrawal->save();
        $this->bale($withdrawal->id);
        return redirect()->back();
    }

    public function payment($id, Request $req)
    {
        Helper::access('withdrawalPay');
        request()->validate([
            'payment_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'payment_file2' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'payment_file3' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
        ]);
        $withdrawal = Withdrawal::findOrFail($id);
        if ($withdrawal->manager_confirm != 1)
            return redirect()->back();
        $withdrawal->payment_confirm = $req->payment_confirm;
        $withdrawal->recipient_confirm = 0;
        $withdrawal->payment_desc = $req->payment_desc;
        if ($req->file('payment_file'))
            $withdrawal->payment_file = $req->file("payment_file")->store("", 'withdrawal');
        if ($req->file('payment_file2'))
            $withdrawal->payment_file2 = $req->file("payment_file2")->store("", 'withdrawal');
        if ($req->file('payment_file3'))
            $withdrawal->payment_file3 = $req->file("payment_file3")->store("", 'withdrawal');
        $withdrawal->save();
        $this->bale($withdrawal->id);
        return redirect()->back();
    }

    public function recipient($id, Request $req)
    {
        Helper::access('withdrawalRecipient');
        request()->validate([
            'recipient_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
        ]);
        $withdrawal = Withdrawal::findOrFail($id);
        if ($withdrawal->payment_confirm != 1)
            return redirect()->back();
        $withdrawal->recipient_confirm = ($req->recipient_confirm || $withdrawal->tankhah);
        $withdrawal->recipient_desc = $req->recipient_desc;
        if ($req->file('recipient_file'))
            $withdrawal->recipient_file = $req->file("recipient_file")->store("", 'withdrawal');
        $withdrawal->save();
        return redirect()->back();
    }

    public function view($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        $withdrawal = Withdrawal::with('bank');
        if (!$user->meta('allWithdrawal'))
            $withdrawal = $withdrawal->where('user_id' , $user->id);
            $withdrawal = $withdrawal->findOrFail($id);
        return view('withdrawal.view', [
            'withdrawal' => $withdrawal,
        ]);
    }

    public function addTankhah()
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        return view('withdrawal.addEditTankhah', [
            'suppliers' => Supplier::all()->keyBy('name'),
            'withdrawal' => new Withdrawal(),
            'banks' => Bank::all()->keyBy('id'),
        ]);
    }

    public function editTankhah($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        $withdrawal = Withdrawal::where('tankhah', 1);
        if (!$user->meta('allWithdrawal'))
            $withdrawal = $withdrawal->where('user_id', $user->id);
        $withdrawal = $withdrawal->findOrNew($id);
        return view('withdrawal.addEditTankhah', [
            'suppliers' => Supplier::all()->keyBy('name'),
            'withdrawal' => $withdrawal,
            'banks' => Bank::all()->keyBy('id'),
        ]);
    }

    public function addEditTankhah(Request $req , $id = null)
    {
        DB::beginTransaction();
        $user = auth()->user();
        Helper::access(['withdrawal', 'allWithdrawal']);
        request()->validate([
            'user_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'expense_desc' => 'required',
        ], [
            'user_file.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'user_file.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
            'expense_desc.required' => 'نوع هزینه باید مشخص شود!'
        ]);
        $req->merge(['amount' => +str_replace(",", "", $req->amount)]);
        $withdrawal = $user->withdrawals()->updateOrCreate(['id' => $req->id], $req->merge([
            'tankhah' => 1,
            'counter_confirm' => 1,
            'manager_confirm' => 1,
            'payment_confirm' => 1,
            'recipient_confirm' => 1,
        ])->all());
        $supplier = Supplier::updateOrCreate(['name' => $req->account_name],[
            'account' => $req->account_number,
            'code' => $req->cheque_id,
        ]);
        $withdrawal->update(['supplier_id' => $supplier->id]);
        if ($req->file("user_file")) {
            $withdrawal->user_file = $req->file("user_file")->store("", 'withdrawal');
        } elseif (!$req->old_user_file) {
            $withdrawal->user_file = null;
        }
        $withdrawal->save();
        $this->bale($withdrawal->id);
        DB::commit();
        return redirect(route('WithdrawalList'));
    }

    public function bale($id)
    {
        $withdrawal = Withdrawal::find($id);

        $array = [
            'text' => view('withdrawal.bale',compact('withdrawal')),
            'reply_to_message_id' => $withdrawal->bale_id
        ];
        $bale_id = $this->sendMessageToBale($array, 5032678768)->result->message_id;
        $withdrawal->update([
            'bale_id' => $bale_id,
        ]);
    }

}
