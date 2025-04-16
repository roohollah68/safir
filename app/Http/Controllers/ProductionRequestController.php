<?php

namespace App\Http\Controllers;

use App\Models\Good;
use Illuminate\Http\Request;
use App\Models\ProductionRequest;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductionRequestController extends Controller
{
    public function create()
    {
        $goods = Good::where('category', 'final')->get();
        
        $products = Product::from('products as warehouse1')
            ->withoutGlobalScopes()
            ->whereNull('warehouse1.deleted_at')
            ->join('products as warehouse3', function ($join) {
                $join->on('warehouse1.good_id', '=', 'warehouse3.good_id')
                    ->where('warehouse1.warehouse_id', 1)
                    ->where('warehouse3.warehouse_id', 3)
                    ->whereNull('warehouse3.deleted_at');
            })
            ->join('goods', 'warehouse1.good_id', '=', 'goods.id')
            ->where('goods.category', 'final')
            ->whereRaw('(warehouse1.quantity + warehouse3.quantity) < warehouse1.alarm')
            ->selectRaw('warehouse1.*, 
                goods.name as good_name, 
                (warehouse1.high_alarm - (warehouse1.quantity + warehouse3.quantity)) as required_quantity,
                (warehouse1.quantity + warehouse3.quantity) as quantity')
            ->get();

        $productionHistory = ProductionRequest::with(['good', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('production.addEditRequest', [
            'goods' => $goods,
            'edit' => false,
            'products' => $products,
            'productionHistory' => $productionHistory
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'good_id' => 'required|exists:goods,id',
            'amount' => 'required|numeric|min:1'
        ]);

        ProductionRequest::create([
            'good_id' => $validated['good_id'],
            'amount' => $validated['amount'],
            'user_id' => auth()->id()
        ]);

        return redirect()->route('productionList')
            ->with('success', 'Production request submitted successfully!');
    }

    public function update(Request $request, $id)
    {
        $production = ProductionRequest::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'good_id' => 'required|exists:goods,id'
        ]);

        $production->update($validated);

        return redirect()->route('productionList')
            ->with('success', 'Production request updated!');
    }

    public function list()
    {      
        $productions = ProductionRequest::with(['good', 'user', 'productions'])
            ->orderByDesc('created_at')
            ->get();

        return view('production.productionList', compact('productions'));
    }

    public function edit($id)
    {
        $production = ProductionRequest::with('good')->findOrFail($id);
        $goods = Good::where('category', 'final')->get();
        
        $products = Product::from('products as warehouse1')
            ->withoutGlobalScopes()
            ->whereNull('warehouse1.deleted_at')
            ->join('products as warehouse3', function ($join) {
                $join->on('warehouse1.good_id', '=', 'warehouse3.good_id')
                    ->where('warehouse1.warehouse_id', 1)
                    ->where('warehouse3.warehouse_id', 3)
                    ->whereNull('warehouse3.deleted_at');
            })
            ->join('goods', 'warehouse1.good_id', '=', 'goods.id')
            ->where('goods.category', 'final')
            ->whereRaw('(warehouse1.quantity + warehouse3.quantity) < warehouse1.alarm')
            ->selectRaw('warehouse1.*, 
                goods.name as good_name, 
                (warehouse1.high_alarm - (warehouse1.quantity + warehouse3.quantity)) as required_quantity,
                (warehouse1.quantity + warehouse3.quantity) as quantity')
            ->get();

        $productionHistory = ProductionRequest::with(['good', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('production.addEditRequest', [
            'goods' => $goods,
            'edit' => true,
            'products' => $products,
            'productionHistory' => $productionHistory,
            'production' => $production
        ]);
    }

    public function delete($id)
    {
        $production = ProductionRequest::findOrFail($id);
        $production->delete();

        return response()->json([
            'message' => 'درخواست تولید با موفقیت حذف شد.'
        ]);
    }
}