<?php

namespace App\Http\Controllers;

use App\Models\CustomerTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;

class ChequeController extends Controller
{
    public function cheque()
    {
        $receivedCheque = CustomerTransaction::where('pay_method', 'cheque')
            ->where('verified', 'approved')
            ->get();
        $givenCheque = Withdrawal::where('pay_method', 'cheque')
            ->where('payment_confirm', 1)
            ->get();

        return view('cheque.cheque', compact('receivedCheque', 'givenCheque'));
    }

    public function view($id)
    {
        $viewCheque = Withdrawal::with('user', 'bank')
            ->where('id', $id)
            ->first();
        return view('cheque.givenView', compact('viewCheque'))->render();
    }

    public function recievedView($id)
    {
        $viewCheque = CustomerTransaction::with('customer.user')
            ->where('id', $id)
            ->first();
        return view('cheque.receivedView', compact('viewCheque'))->render();
    }

    public function passCheque(Request $request)
    {
        if ($request->type == 'received') {
            $cheque = CustomerTransaction::find($request->cheque_id);
        } else {
            $cheque = Withdrawal::find($request->cheque_id);
        }
        $cheque->cheque_pass = 1;
        $cheque->save();

        return response()->json(['success' => true]);
    }

    // ------------------------ FILTERS ------------------------ //

    public function filterChequeDate(Request $request)
    {
        $startDate = $request->input('from');
        $endDate = $request->input('to');
        $state = $request->input('state');

        $startDate = Verta::parse($startDate)->DateTime()->format('Y-m-d');
        $endDate = Verta::parse($endDate)->DateTime()->format('Y-m-d');
        
        $receivedChequeQuery = CustomerTransaction::query();
        $givenChequeQuery = Withdrawal::query();

        if ($startDate) {
            $receivedChequeQuery->whereDate('cheque_date', '>=', $startDate) 
            ->where('pay_method', 'cheque')
            ->where('verified', 'approved');
            $givenChequeQuery->whereDate('cheque_date', '>=', $startDate)
            ->where('pay_method', 'cheque')
            ->where('payment_confirm', 1);
        }

        if ($endDate) {
            $receivedChequeQuery->whereDate('cheque_date', '<=', $endDate)
            ->where('pay_method', 'cheque')
            ->where('verified', 'approved');
            $givenChequeQuery->whereDate('cheque_date', '<=', $endDate)
            ->where('pay_method', 'cheque')
            ->where('payment_confirm', 1);
        }

        if ($state !== '') {
            $receivedChequeQuery->where('cheque_pass', $state)
             ->where('pay_method', 'cheque')
            ->where('verified', 'approved');
            $givenChequeQuery->where('cheque_pass', $state)
            ->where('pay_method', 'cheque')
            ->where('payment_confirm', 1);
        }

        $receivedCheque = $receivedChequeQuery->get();
        $givenCheque = $givenChequeQuery->get();

        return view('cheque.cheque', [
            'receivedCheque' => $receivedCheque,
            'givenCheque' => $givenCheque,
            'get' => http_build_query($request->except(['from', 'to', 'state'])) . '&',
            'from' => $startDate,
            'to' => $endDate,
        ]);
    }
}
