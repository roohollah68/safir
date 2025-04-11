<?php

namespace App\Http\Controllers;

use App\Models\Good;
use Illuminate\Http\Request;
use App\Models\Production;

class ProductionController extends Controller
{
    public function create()
    {
        $goods = Good::all();
        return view('production.addEdit', [
            'goods' => $goods,
            'edit' => false
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'good_id' => 'required|exists:goods,id',
            'requested_quantity' => 'required|numeric|min:1'
        ]);

        Production::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => 'pending'
        ]);

        return redirect()->route('productionList')
            ->with('success', 'Production request submitted successfully!');
    }

    public function index()
    {      
        $productions = Production::with(['good', 'user'])
            ->orderBy('status')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('production.productionList', compact('productions'));
    }

    public function update(Request $request, $id)
    {
        $production = Production::findOrFail($id);

        $validated = $request->validate([
            'requested_quantity' => 'required|numeric|min:1',
            'good_id' => 'required|exists:goods,id'
        ]);

        $production->update($validated);

        return redirect()->route('productionList')
            ->with('success', 'Production request updated!');
    }

    public function edit($id)
    {
        $production = Production::findOrFail($id);
        $goods = Good::all();

        return view('production.addEdit', [
            'production' => $production,
            'goods' => $goods,
            'edit' => true
        ]);
    }

    public function updateQuantity(Request $request, $id)
    {
        $production = Production::findOrFail($id);

        $validated = $request->validate([
            'produced_quantity' => 'required|numeric|min:0'
        ]);

        $producedQuantity = (float) $validated['produced_quantity'];
        $requestedQuantity = (float) $production->requested_quantity;

        if ($producedQuantity >= $requestedQuantity) {
            $production->status = 'completed';
        } elseif ($producedQuantity > 0) {
            $production->status = 'in_production';
        } else {
            $production->status = 'pending';
        }

        $production->produced_quantity = $producedQuantity;
        $production->save();

        return response()->json([
            'message' => 'تعداد تولید شده با موفقیت به‌روزرسانی شد.',
            'production' => $production
        ]);
    }

    public function delete($id)
    {
        $production = Production::findOrFail($id);
        $production->delete();

        return response()->json([
            'message' => 'درخواست تولید با موفقیت حذف شد.'
        ]);
    }
}