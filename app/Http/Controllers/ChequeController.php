<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class ChequeController extends Controller
{
    public function cheque()
    {
        $cheque = new Cheque();
        $receivedCheque = $cheque->receivedCheque();
        $givenCheque = $cheque->givenCheque();
        return view('cheque.cheque', compact('receivedCheque', 'givenCheque'));
    }

    public function view($id)
    {
        $cheque = new Cheque();
        $viewCheque = $cheque->viewGivenCheque($id);
        return view('cheque.givenView', compact('viewCheque'))->render();
    }

    public function recievedView($id)
    {
        $cheque = new Cheque();
        $viewCheque = $cheque->viewReceivedCheque($id);
        return view('cheque.receivedView', compact('viewCheque'))->render();
    }

    public function passCheque(Request $request)
    {
        $cheque = new Cheque();
        $cheque->passCheque($request->cheque_id, $request->type);
        return response()->json(['success' => true]);
    }
}
