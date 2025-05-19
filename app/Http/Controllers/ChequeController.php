<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\CustomerTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use App\Models\ChequeLog;
use Illuminate\Support\Facades\Auth;

class ChequeController extends Controller
{
    public function cheque(Request $request)
    {
        Helper::access('cheque');

        CustomerTransaction::where('pay_method', 'cheque')
            ->where('cheque_status', 2)
            ->whereDate('cheque_date', '<', now())
            ->update(['cheque_status' => 1]);

        Withdrawal::where('pay_method', 'cheque')
            ->where('cheque_status', 2)
            ->whereDate('cheque_date', '<', now())
            ->update(['cheque_status' => 1]);

        $receivedCheque = CustomerTransaction::where('pay_method', 'cheque')->where('verified', 'approved');
        $givenCheque = Withdrawal::where('pay_method', 'cheque')->where('payment_confirm', 1);

        if ($request->state !== null && $request->state !== '') {
            $receivedCheque = $receivedCheque->where('cheque_status', $request->state);
            $givenCheque = $givenCheque->where('cheque_status', $request->state);
        }

        if ($request->type === 'official') {
            $receivedCheque = $receivedCheque->where('official', 1);
            $givenCheque = $givenCheque->where('official', 1);
        } elseif ($request->type === 'unofficial') {
            $receivedCheque = $receivedCheque->where('official', 0);
            $givenCheque = $givenCheque->where('official', 0);
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

        $receivedTotal = $receivedCheque->sum('amount');
        $givenTotal = $givenCheque->sum('amount');

        return view('cheque.cheque', [
        'receivedCheque' => $receivedCheque,
        'givenCheque' => $givenCheque,
        'receivedTotal' => $receivedTotal,
        'givenTotal' => $givenTotal,
        'from' => $request->from,
        'to' => $request->to,
        ]);
    }

    public function view(Request $request, $id)
    {
        Helper::access('cheque');
        $viewCheque = Withdrawal::with('user', 'bank')
            ->where('id', $id)
            ->first();

        if ($request->isMethod('post')) {

            $request->validate([
                'cheque_registration' => 'required|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            ]);

            if ($request->hasFile('cheque_registration')) {
                $path = $request->file('cheque_registration')->store("", 'withdrawal');
                $viewCheque->cheque_registration = $path;
                $viewCheque->save();
                return redirect()->back();
            }
        }

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
        $validStatuses = array_keys(config('chequeStatus.statuses'));
        
        $request->validate([
            'cheque_status' => 'required|in:'.implode(',', $validStatuses)
        ]);

        if ($request->type == 'received') {
            $cheque = CustomerTransaction::find($request->cheque_id);
        } else {
            $cheque = Withdrawal::find($request->cheque_id);
        }

        ChequeLog::create([
            'cheque_id' => $request->cheque_id,
            'cheque_type' => $request->type,
            'old_status' => $cheque->cheque_status,
            'new_status' => $request->cheque_status,
            'changed_by' => Auth::id()
        ]);
        
        $cheque->cheque_status = $request->cheque_status;
        $cheque->save();
        return response()->json(['success' => true]);
    }

    public function getHistory($id, $type)
    {
        Helper::access('cheque');
        $history = ChequeLog::with('changer:id,name')
            ->where('cheque_id', $id)
            ->where('cheque_type', $type)
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('cheque.chequeLog', ['history' => $history]);
    }
}
