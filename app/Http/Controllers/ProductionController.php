<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionRequest;
use App\Models\Production;
use App\Models\Formulation;
use App\Models\Product;
use App\Models\ProductChange;
use App\Models\Good;

class ProductionController extends Controller
{
    public function addProductionForm()
    {
        $requests = ProductionRequest::with(['good'])
            ->get();
            
        foreach ($requests as $request) {
            $request->remaining_requests = $request->good->remainingRequests();
        }
        $productionHistory = Production::with(['good'])->get();
        $goods = Good::where('category', 'final')->get();
        
        return view('production.addProduction', compact('requests', 'productionHistory', 'goods'));
    }

    public function addProduction(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'good_id' => 'required|numeric|exists:goods,id',
        ]);

        DB::transaction(function () use ($validated) {
            $production = Production::create([
                'user_id' => Auth::id(),
                'good_id' => $validated['good_id'],
                'amount' => $validated['amount']
            ]);

            $formulations = Formulation::where('good_id', $validated['good_id'])->get();

            if($formulations->isNotEmpty()) {
                foreach($formulations as $formulation) {
                    $rawProduct = Product::firstOrCreate(
                        [
                            'good_id' => $formulation->rawGood_id,
                            'warehouse_id' => 3
                        ],
                        [
                            'quantity' => 0,
                            'available' => 1
                        ]
                    );

                $requiredRaw = $formulation->amount * $validated['amount'];

                $rawProduct->decrement('quantity', $requiredRaw);

                ProductChange::create([
                    'product_id' => $rawProduct->id,
                    'change' => -$requiredRaw,
                    'quantity' => $rawProduct->quantity,
                    'desc' => 'تولید محصول ' . $formulation->good->name,
                ]);
            }}

            $finalProduct = Product::withTrashed()->firstOrCreate(
                ['good_id' => $validated['good_id'], 'warehouse_id' => 3]
            );
            if ($finalProduct->trashed()) {
                $finalProduct->restore();
            }
            $finalProduct->increment('quantity', $validated['amount']);

            ProductChange::create([
                'product_id' => $finalProduct->id,
                'change' => $validated['amount'],
                'quantity' => $finalProduct->quantity,
                'desc' => 'اضافه کردن موجودی به اندازه ' . $validated['amount'] . ' عدد'
            ]);
        });

        return redirect()->route('productionList')
            ->with('success', 'تولید با موفقیت ثبت شد.');
    }
}