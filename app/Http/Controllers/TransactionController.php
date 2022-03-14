<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function show(){
        $user = auth()->user();
        $transactions = $user->transactions()->get();
        return view('transactions' , ['transactions'=>$transactions]);
    }
}
