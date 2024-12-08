<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function new()
    {
        Helper::access('addWithdrawal');
        return view('withdrawal.addEdit', [
            'withdrawal' => new Withdrawal(),

        ]);
    }

    public function insert(Request $req)
    {
        Helper::access('addWithdrawal');
        request()->validate([
            'file1' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'amount' => 'required',
            'description' => 'required',
        ]);
        $user = auth()->user();
        $withdrawal = $user->withdrawals()->make([
            'amount' => +str_replace(",", "", $req->amount),
            'description' => $req->description,
        ]);
        if ($req->file("file1")) {
            $withdrawal->file1 = $req->file("file1")->store("", 'withdrawal');
        }
        $withdrawal->save();
        return redirect(route('WithdrawalList'));
    }

    public function edit($id)
    {
        Helper::access(['addWithdrawal','confirmWithdrawal']);
        $user = auth()->user();
        if($user->meta('confirmWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        return view('withdrawal.addEdit', [
            'withdrawal' => $withdrawal,
        ]);
    }

     public function delete($id)
    {
        Helper::access(['addWithdrawal','confirmWithdrawal']);
        $user = auth()->user();
        if($user->meta('confirmWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        if($withdrawal->pay)
            return;
        $withdrawal->delete();
        return redirect(route('WithdrawalList'));
    }

    public function update($id ,Request $req)
    {
        Helper::access(['addWithdrawal','confirmWithdrawal']);
        request()->validate([
            'file1' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'amount' => 'required',
            'description' => 'required',
        ]);
        $user = auth()->user();
        if($user->meta('confirmWithdrawal'))
            $withdrawal = Withdrawal::findOrFail($id);
        else
            $withdrawal = $user->withdrawals()->findOrFail($id);
        if($withdrawal->pay)
            return;
        $withdrawal->amount = +str_replace(",", "", $req->amount);
        $withdrawal->description = $req->description;
        if ($req->file("file1")) {
            $withdrawal->file1 = $req->file("file1")->store("", 'withdrawal');
        }elseif (!$req->oldFile1){
            $withdrawal->file1 = null;
        }
        $withdrawal->save();
        return redirect(route('WithdrawalList'));
    }

    public function list()
    {
        Helper::access(['addWithdrawal','confirmWithdrawal','payWithdrawal']);
        $user = auth()->user();
        if($user->meta('confirmWithdrawal') || $user->meta('payWithdrawal'))
            $withdrawals = Withdrawal::all()->keyBy('id');
        else
            $withdrawals = $user->withdrawals->keyBy('id');
        return view('withdrawal.list',[
            'withdrawals' => $withdrawals,
            'user' => $user,
        ]);
    }

    public function confirm()
    {

    }

    public function pay()
    {

    }
}
