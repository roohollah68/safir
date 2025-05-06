<?php

namespace App\Http\Controllers;

use App\Models\FixedCost;
use Illuminate\Http\Request;

class FixedCostController extends Controller
{
    public function index()
    {
        $categoryMap = [
            '0' => 'حقوق تولید',
            '1' => 'حقوق فروش',
            '2' => 'اجاره',
            '3' => 'بیمه',
            '4' => 'بودجه‌ی ماهیانه‌ی تبلیغات',
        ];
        $fixedCosts = FixedCost::all();
        $totalAmount = $fixedCosts->sum('amount');
        return view('fixedCosts.list', compact('fixedCosts', 'categoryMap', 'totalAmount'));
    }

    public function create()
    {
        return view('fixedCosts.form');
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
        ]);

        $validated['amount'] = +str_replace(",", "", $request->amount);

        if ($id) {
            $fixedCost = FixedCost::findOrFail($id);
            $fixedCost->update($validated);
            return redirect()->route('fixed-costs.index')->with('success', 'هزینه ثابت با موفقیت ویرایش شد.');
        } else {
            FixedCost::create($validated);
            return redirect()->route('fixed-costs.index')->with('success', 'هزینه ثابت با موفقیت اضافه شد.');
        }
    }

    public function edit($id)
    {
        $fixedCost = FixedCost::findOrFail($id);
        return view('fixedCosts.form', compact('fixedCost'));
    }
}
