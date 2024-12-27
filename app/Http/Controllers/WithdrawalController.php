<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function new()
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        return view('withdrawal.addEdit', [
            'withdrawal' => new Withdrawal(),
        ]);
    }

    public function insert(Request $req)
    {
        $user = auth()->user();
        Helper::access(['withdrawal', 'allWithdrawal']);
        request()->validate([
            'user_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'amount' => 'required',
            'expense' => 'required',
            'account_name' => 'required',
            'expense_type' => 'required',
            'expense_desc' => 'required',
        ]);

        $withdrawal = $user->withdrawals()->make([
            'amount' => +str_replace(",", "", $req->amount),
            'expense' => $req->expense,
            'user_desc' => $req->user_desc,
            'account_number' => $req->account_number,
            'account_name' => $req->account_name,
            'pay_method' => $req->pay_method,
            'cheque_date' => $req->cheque_date_hide,
            'cheque_id' => $req->cheque_id,
            'expense_type' => $req->expense_type,
            'expense_desc' => $req->expense_desc,
        ]);
        if ($req->file("user_file")) {
            $withdrawal->user_file = $req->file("user_file")->store("", 'withdrawal');
        }
        $withdrawal->save();
        return redirect(route('WithdrawalList'));
    }

    public function edit($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        if ($user->meta('allWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        if ($withdrawal->manager_confirm != 1)
            return view('withdrawal.addEdit', [
                'withdrawal' => $withdrawal,
            ]);
        return redirect(route('WithdrawalList'));
    }

    public function delete($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        if ($user->meta('allWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        if($withdrawal->manager_confirm != 1)
            $withdrawal->delete();
        return redirect(route('WithdrawalList'));
    }

    public function update($id, Request $req)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        request()->validate([
            'user_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'amount' => 'required',
            'expense' => 'required',
            'account_name' => 'required',
            'expense_type' => 'required',
            'expense_desc' => 'required',
        ]);
        $user = auth()->user();
        if ($user->meta('allWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        if ($withdrawal->manager_confirm != 1){
            $withdrawal->update([
                'amount' => +str_replace(",", "", $req->amount),
                'expense' => $req->expense,
                'user_desc' => $req->user_desc,
                'account_number' => $req->account_number,
                'account_name' => $req->account_name,
                'pay_method' => $req->pay_method,
                'cheque_date' => $req->cheque_date_hide,
                'cheque_id' => $req->cheque_id,
                'expense_type' => $req->expense_type,
                'expense_desc' => $req->expense_desc,
                'counter_confirm' => 0,
                'manager_confirm' => 0,
                'payment_confirm' => 0,
            ]);
            if ($req->file("user_file")) {
                $withdrawal->user_file = $req->file("user_file")->store("", 'withdrawal');
            }elseif (!$req->old_user_file) {
                $withdrawal->user_file = null;
            }
            $withdrawal->save();
        }
        return redirect(route('WithdrawalList'));
    }

    public function list(Request $req)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        $withdrawals = Withdrawal::where('id' , '>' , 0);
        if (!$user->meta('allWithdrawal'))
            $withdrawals = $withdrawals->where('user_id' , $user->id);
        if($req->filter == 'counter'){
            $withdrawals = $withdrawals->where('counter_confirm' , '<>' , 1);
        }
        if($req->filter == 'manager'){
            $withdrawals = $withdrawals->where('manager_confirm' , '<>' , 1)->where('counter_confirm' , 1);
        }
        if($req->filter == 'payment'){
            $withdrawals = $withdrawals->where('payment_confirm' , '<>' , 1)->where('manager_confirm' , 1);
        }
        if($req->filter == 'paid'){
            $withdrawals = $withdrawals->where('payment_confirm' , 1);
        }
        return view('withdrawal.list', [
            'withdrawals' => $withdrawals->get()->keyBy('id'),
            'user' => $user,
            'filter' => $req->filter,
        ]);
    }

    public function counter($id , Request $req)
    {
        Helper::access('counter');
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->counter_confirm = $req->counter_confirm;
        $withdrawal->counter_desc = $req->counter_desc;
        $withdrawal->bank = $req->bank;
        $withdrawal->save();
        return redirect(route('WithdrawalList'));
    }


    public function manager($id , Request $req)
    {
        $user = auth()->user();
        if($user->id != 122)
            abort(401);
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->manager_confirm = $req->manager_confirm;
        $withdrawal->manager_desc = $req->manager_desc;
        $withdrawal->save();
        return redirect(route('WithdrawalList'));
    }

    public function payment($id , Request $req)
    {
        Helper::access('counter');
        request()->validate([
            'user_file' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
        ]);
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->payment_confirm = $req->payment_confirm;
        $withdrawal->payment_desc = $req->payment_desc;
        if($req->file('payment_file'))
            $withdrawal->payment_file = $req->file("payment_file")->store("", 'withdrawal');
        $withdrawal->save();
        return redirect(route('WithdrawalList'));
    }

    public function view($id)
    {
        Helper::access(['withdrawal', 'allWithdrawal']);
        $user = auth()->user();
        if ($user->meta('allWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        return view('withdrawal.view', [
            'withdrawal' => $withdrawal,
        ]);
    }
}
