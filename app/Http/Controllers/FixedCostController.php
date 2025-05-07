<?php

namespace App\Http\Controllers;

use App\Models\FixedCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Events\FixedCostEvent;

class FixedCostController extends Controller
{
    public function index()
    {
        $expenseTypes = Config::get('expense_type.current');
        $fixedCosts = FixedCost::all();
        $totalAmount = $fixedCosts->sum('amount');
        return view('fixedCosts.list', compact('fixedCosts', 'totalAmount', 'expenseTypes'));
    }

    public function create()
    {
        $expenseTypes = Config::get('expense_type.current');
        $banks = \App\Models\Bank::all(); 
        return view('fixedCosts.form', compact('expenseTypes', 'banks'));
    }

    public function store(Request $request, $id = null)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|string',
            'account_owner' => 'required|string',
            'desc' => 'nullable|string',
            'iban' => 'required',
            'due_day' => 'required|integer|min:1|max:31',
            'official' => 'required|boolean',
            'vat' => 'nullable|boolean',
            'bank_id' => 'nullable|integer|exists:banks,id',
        ]);
        $validated['user_id'] = auth()->id();
        $validated['amount'] = +str_replace(",", "", $request->amount);

        if ($id) {
            $fixedCost = FixedCost::findOrFail($id);
            $fixedCost->update($validated);
            event(new FixedCostEvent($fixedCost));
            return redirect()->route('fixed-costs.index')->with('success', 'هزینه ثابت با موفقیت ویرایش شد.');
        } else {
            $fixedCost = FixedCost::create($validated);
            event(new FixedCostEvent($fixedCost));
            return redirect()->route('fixed-costs.index')->with('success', 'هزینه ثابت با موفقیت اضافه شد.');
        }
    }

    public function edit($id)
    {
        $expenseTypes = Config::get('expense_type.current');
        $banks = \App\Models\Bank::all(); 
        $fixedCost = FixedCost::with('bank')->findOrFail($id);
        return view('fixedCosts.form', compact('fixedCost', 'expenseTypes', 'banks'));
    }
}
