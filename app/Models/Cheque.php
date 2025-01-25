<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    public function receivedCheque()
    {
        return \DB::table('customer_transactions')
            ->select('id', 'pay_method', 'cheque_date', 'cheque_name', 'cheque_code', 'customer_id', 'amount') // Add 'id'
            ->where('pay_method', 'cheque')
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

     public function viewCheque()
    {
        return \DB::table('withdrawals')
            ->select('id', 'cheque_date', 'cheque_id', 'amount','account_name','user_file',
            'expense', 'location','user_desc','pay_method','expense_type', 'expense_desc',
            'official','vat','bank_id')
            ->where('id', $id)
            ->get();        
    }
}
