<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    public function receivedCheque()
    {
        return \DB::table('customer_transactions')
            ->select('id', 'pay_method', 'cheque_date', 'cheque_name', 'cheque_code', 'customer_id', 'amount')
            ->where('pay_method', 'cheque')
            ->where('verified','approved')
            ->get();
    }

    public function givenCheque()
    {
        return \DB::table('withdrawals')
            ->select('id','cheque_date', 'cheque_id', 'amount','account_name')
            ->where('pay_method', 'cheque')
            ->where('payment_confirm', 1)
            ->get();
    }

     public function viewGivenCheque($id)
    {
        return \DB::table('withdrawals')
            ->select('id', 'cheque_date', 'cheque_id', 'amount','account_name','user_file',
            'expense', 'location','user_desc','pay_method','expense_type', 'expense_desc',
            'official','vat','bank_id')
            ->where('id', $id)
            ->first();
    }

    public function viewReceivedCheque($id)
    {
        return \DB::table('customer_transactions')
            ->select('id', 'pay_method', 'cheque_date', 'cheque_name', 'cheque_code', 'customer_id',
            'amount','description','created_at','updated_at')
            ->where('id', $id)
            ->first();
    }
}
