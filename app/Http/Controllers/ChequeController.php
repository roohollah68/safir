<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\CustomerTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;

class ChequeController extends Controller
{
    public function cheque(Request $request)
    {
        Helper::access('cheque');
        $receivedCheque = CustomerTransaction::where('pay_method', 'cheque')->where('verified', 'approved');
        $givenCheque = Withdrawal::where('pay_method', 'cheque')->where('payment_confirm', 1);

        if ($request->state == '0') {
            $receivedCheque = $receivedCheque->where('cheque_pass', false);
            $givenCheque = $givenCheque->where('cheque_pass', false);
        } elseif ($request->state == '1') {
            $receivedCheque = $receivedCheque->where('cheque_pass', true);
            $givenCheque = $givenCheque->where('cheque_pass', true);
        }

        if ($request->from) {
            $receivedCheque = $receivedCheque->whereDate('cheque_date', '>=', Verta::parse($request->from)->DateTime());
            $givenCheque = $givenCheque->whereDate('cheque_date', '>=', Verta::parse($request->from)->DateTime());
        }

        if ($request->to) {
            $receivedCheque = $receivedCheque->whereDate('cheque_date', '<=', Verta::parse($request->to)->DateTime());
            $givenCheque = $givenCheque->whereDate('cheque_date', '<=', Verta::parse($request->to)->DateTime());
        }

        $receivedCheque = $receivedCheque->get();
        $givenCheque = $givenCheque->get();

        return view('cheque.cheque', [
        'receivedCheque' => $receivedCheque,
        'givenCheque' => $givenCheque,
        'from' => $request->from,
        'to' => $request->to,
        ]);
    }

    public function view($id)
    {
        Helper::access('cheque');
        $viewCheque = Withdrawal::with('user', 'bank')
            ->where('id', $id)
            ->first();
        return view('cheque.givenView', compact('viewCheque'))->render();
    }

    public function receivedView(Request $request, $id)
    {
        Helper::access('cheque');
        $viewCheque = CustomerTransaction::with('customer.user')
            ->where('id', $id)
            ->first();

        if ($request->isMethod('post')) {

            $request->validate([
            'cheque_registration' => 'required|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            ]);

            if ($request->hasFile('cheque_registration')) {
            $path = $request->file('cheque_registration')->store("", 'deposit');
            $viewCheque->cheque_registration = $path;
            $viewCheque->save();
            return redirect()->back();
            }
        }

        return view('cheque.receivedView', compact('viewCheque'))->render();
    }

    public function passCheque(Request $request)
    {
        Helper::access('cheque');
        if ($request->type == 'received') {
            $cheque = CustomerTransaction::find($request->cheque_id);
        } else {
            $cheque = Withdrawal::find($request->cheque_id);
        }
        $cheque->cheque_pass = 1;
        $cheque->save();

        return response()->json(['success' => true]);
    }
}
