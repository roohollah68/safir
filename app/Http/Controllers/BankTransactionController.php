<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransactionController extends Controller
{
    public function new()
    {
        Helper::access('counter');
        return view('bank.addEditTransaction', [
            'bankTransaction' => new BankTransaction(),
            'banks' => Bank::where('enable', true)->get()->keyBy('id'),
            'edit' => false,
        ]);
    }

    public function insertOrUpdate(Request $req, $id = null)
    {
        DB::beginTransaction();
        $user = auth()->user();
        Helper::access('counter');
        request()->validate([
            'recipt' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'recipt2' => 'mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'bank_id' => 'required'
        ], [
            'recipt.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'recipt.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
            'recipt2.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'recipt2.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
        ]);
        $req->merge(['amount' => +str_replace(",", "", $req->amount)]);
        $bankTransaction = $user->bankTransactions()->updateOrCreate([
            'id' => $id
        ], $req->all());

        $bankTransaction->receipt = $req->old_receipt;
        if ($req->receipt)
            $bankTransaction->receipt = $req->file("receipt")->store("", 'withdrawal');
        $bankTransaction->receipt2 = $req->old_receipt2;
        if ($req->receipt2)
            $bankTransaction->receipt2 = $req->file("receipt2")->store("", 'withdrawal');
        $bankTransaction->save();
        DB::commit();
        return redirect(route('BankTransactionList'));
    }

    public function edit($id)
    {
        Helper::access('counter');
        return view('bank.addEditTransaction', [
            'bankTransaction' => BankTransaction::findOrFail($id),
            'banks' => Bank::where('enable', true)->get()->keyBy('id'),
            'edit' => true,
        ]);
    }

    public function delete($id)
    {

    }

    public function list(Request $req,)
    {
        return view('bank.transactionList', [
            'bankTransactions' => BankTransaction::with(['user', 'bank'])->get()->keyBy('id'),
        ]);
    }

    public function view($id)
    {
        return view('bank.view',[
            'bankTransaction' => BankTransaction::with(['user', 'bank','bankSource'])->findOrFail($id),
        ]);
    }
}
