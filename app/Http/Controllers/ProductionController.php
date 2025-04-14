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
        $requests = ProductionRequest::with(['good', 'productions'])->where('isCompleted', false)->get();
        $productionHistory = Production::with(['good'])->orderBy('created_at')->get();
        
        return view('production.addProduction', compact('requests', 'productionHistory'));
    }

    public function addProduction(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:production_requests,id',
            'amount' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $productionRequest = ProductionRequest::findOrFail($validated['request_id']);
            $production = Production::create([
                'request_id' => $validated['request_id'],
                'user_id' => Auth::id(),
                'good_id' => $productionRequest->good_id,
                'amount' => $validated['amount']
            ]);

            $formulations = Formulation::where('good_id', $productionRequest->good_id)->get();

            if($formulations->isNotEmpty()) {
                foreach($formulations as $formulation) {
                    $rawProduct = Product::where('good_id', $formulation->rawGood_id)
                        ->where('warehouse_id', 3)
                        ->firstOrFail();

                    $requiredRaw = $formulation->amount * $validated['amount'];
                    
                    if($rawProduct->quantity < $requiredRaw) {
                        throw new \Exception("مواد اولیه ناکافی برای: {$rawProduct->good->name}");
                    }

                    $rawProduct->decrement('quantity', $requiredRaw);

                    ProductChange::create([
                        'product_id' => $rawProduct->id,
                        'change' => -$requiredRaw,
                        'quantity' => $rawProduct->quantity,
                        'desc' => null
                    ]);
                }
            }

            $finalProduct = Product::firstOrCreate(
                ['good_id' => $productionRequest->good_id, 'warehouse_id' => 3],
                ['quantity' => 0, 'alarm' => 0, 'high_alarm' => 0]
            );

            $finalProduct->increment('quantity', $validated['amount']);

            ProductChange::create([
                'product_id' => $finalProduct->id,
                'change' => $validated['amount'],
                'quantity' => $finalProduct->quantity,
                'desc' => 'اضافه کردن موجودی به اندازه ' . $validated['amount'] . ' عدد'
            ]);

            $totalProduced = Production::where('request_id', $validated['request_id'])
                ->sum('amount');
                
            if($totalProduced >= $productionRequest->amount) {
                $productionRequest->update([
                    'isCompleted' => true,
                    'amount' => 0
                ]);
            }
        });

        return redirect()->route('productionList')
            ->with('success', 'تولید با موفقیت ثبت شد.');
    }
}