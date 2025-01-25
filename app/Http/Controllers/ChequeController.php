<?php

namespace App\Http\Controllers;

use App\Models\Cheque;

class ChequeController extends Controller
{
    public function list()
    {
        $cheque = new Cheque();
        $receivedCheque = $cheque->receivedCheque();
        $givenCheque = $cheque->givenCheque();

        return view('cheque.cheque', compact('receivedCheque', 'givenCheque'));
    }

    public function view($id)
    {
    $viewCheque = \DB::table('withdrawals')
        ->select('id', 'cheque_date', 'cheque_id', 'amount', 'account_name', 'user_file', 
                 'expense', 'location', 'user_desc', 'pay_method', 'expense_type', 
                 'expense_desc', 'official', 'vat', 'bank_id')
        ->where('pay_method', 'cheque')
        ->where('id', $id) 
        ->first();  

    return view('cheque.chequeView', compact('viewCheque'));
    }

}
