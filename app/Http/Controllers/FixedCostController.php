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
        $withdrawalLocations = Config::get('withdrawalLocation');
        return view('fixedCosts.list', compact('fixedCosts', 'totalAmount', 'expenseTypes', 'withdrawalLocations'));
    }

    public function create()
    {
        $suppliers = \App\Models\Supplier::all();
        $expenseTypes = Config::get('expense_type.current');
        $banks = \App\Models\Bank::all(); 
        $withdrawalLocations = Config::get('withdrawalLocation');
        return view('fixedCosts.form', compact('suppliers', 'expenseTypes', 'banks', 'withdrawalLocations'));
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
            'vat' => 'required_if:official,1|boolean',
            'bank_id' => 'nullable|integer|exists:banks,id',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'location' => 'required|string',
        ]);
         if ($validated['official'] == 0) {
            $validated['vat'] = 0;
        }
        $validated['user_id'] = auth()->id();
        $validated['amount'] = +str_replace(",", "", $request->amount);

        if ($id) {
            $fixedCost = FixedCost::findOrFail($id);
            $fixedCost->update($validated);
            return redirect()->route('fixed-costs.index')->with('success', 'هزینه ثابت با موفقیت ویرایش شد.');
        } else {
            $fixedCost = FixedCost::create($validated);
            return redirect()->route('fixed-costs.index')->with('success', 'هزینه ثابت با موفقیت اضافه شد.');
        }
    }

    public function edit($id)
    {
        $expenseTypes = Config::get('expense_type.current');
        $banks = \App\Models\Bank::all(); 
        $fixedCost = FixedCost::with('bank')->findOrFail($id);
        $suppliers = \App\Models\Supplier::all();
        $withdrawalLocations = Config::get('withdrawalLocation');
        return view('fixedCosts.form', compact('fixedCost', 'expenseTypes', 'banks', 'suppliers', 'withdrawalLocations'));
    }
}
