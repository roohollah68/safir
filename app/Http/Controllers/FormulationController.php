<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Formulation;
use App\Models\Good;
use App\Models\ProductionRequest;
use Illuminate\Http\Request;

class FormulationController extends Controller
{
    public function list()
    {
        Helper::access('formulation');
        $formulations = Formulation::with(['good' , 'rawGood.unit'])
            ->whereHas('rawGood')
            ->get()
            ->groupBy('good_id');

        $productionPrices = [];
        foreach ($formulations as $goodId => $formulationGroup) {
            $total = 0;
            foreach ($formulationGroup as $formule) {
                $total += $formule->rawGood->price * $formule->amount;
            }
            $productionPrices[$goodId] = $total;
        }
        
        echo view('formulation/formulationList', compact('formulations', 'productionPrices'));
    }

    public function add()
    {
        Helper::access('formulation');
        $goods = Good::with(['products' => function ($query) {
                $query->where('warehouse_id', 3)
                    ->where('available', 1)
                    ->whereNull('deleted_at');
            }])
            ->whereHas('products')
            ->get();
        $finals = $goods->keyBy('name')->filter(fn($good) => $good->category == 'final')->map(fn($good) => $good->id);
        $raws = $goods->keyBy('name')->filter(fn($good) => $good->category != 'final')->map(fn($good) => $good->id);
        $edit = false;
        return view('formulation/addEditFormulation', compact('goods', 'edit', 'finals', 'raws'));
    }

    public function edit($id)
    {
        Helper::access('formulation');
        $good = Good::with(['formulations', 'products' => function ($query) {
            $query->where('warehouse_id', 3)
                  ->where('available', 1)
                  ->whereNull('deleted_at');
        }])
        ->findOrFail($id);
        $goods = Good::whereHas('products')->get();
        $finals = $goods->keyBy('name')->filter(fn($good) => $good->category == 'final')->map(fn($good) => $good->id);
        $raws = $goods->keyBy('name')->filter(fn($good) => $good->category != 'final')->map(fn($good) => $good->id);
        $edit = true;
        return view('formulation/addEditFormulation', compact('goods', 'edit', 'finals', 'raws', 'good'));
    }

    public function addEditRow(Request $req, $id = null)
    {
        Helper::access('formulation');
        if ($id) {
            $formulation = Formulation::findOrFail($id);
            $formulation->update(['amount'=>$req->amount]);
            $formulation->rawGood->update(['unit_id'=>$req->unit_id]);
        } else {
            $formulation = Formulation::create($req->all());
        }
        return $this->getRawGoods($formulation->good_id);
    }

    public function deleteAll($id)
    {
        Helper::access('formulation');
        $formulations = Formulation::where('good_id' , $id)->delete();
        return redirect('/formulation/list');
    }

    public function deleteRow($id)
    {
        Helper::access('formulation');
        $formulation = Formulation::findOrFail($id);
        $formulation->delete();
        return $this->getRawGoods($formulation->good_id);
    }

    public function getRawGoods($id)
    {
        Helper::access('formulation');
        $good = Good::with('formulations.rawGood')->findOrFail($id);
        return view('formulation/rawGoods', compact('good'));
    }

    public function rawUsage()
    {
        Helper::access('formulation');

        $formulations = Formulation::with(['good', 'rawGood.unit'])
            ->whereHas('rawGood', fn($query) => $query->whereIn('category', ['raw', 'pack']))
            ->get()
            ->groupBy('rawGood_id');

        $rawMaterial = $formulations->map(function ($entries) {
            $total = $entries->sum(fn($formule) => (float)$formule->amount);
            
            return (object)[
                'material' => $entries->first()->rawGood,
                'usage' => $entries->map(fn($formule) => (object)[
                    'final_product' => $formule->good->name,
                    'amount' => (float)$formule->amount
                ]),
                'total' => (float)$total 
            ];
        })->sortBy('total')->values();

        return view('formulation.rawUsage', compact('rawMaterial'));
    }

    public function productionReport()
    {
        Helper::access('formulation');
        $productions = ProductionRequest::selectRaw('
                production_requests.good_id,
                SUM(production_requests.amount) - COALESCE((
                    SELECT SUM(productions.amount) 
                    FROM productions 
                    WHERE productions.good_id = production_requests.good_id
                ), 0) as remaining_requests
            ')
            ->groupBy('production_requests.good_id')
            ->having('remaining_requests', '>', 0)
            ->get();

        $formulations = Formulation::with(['good', 'rawGood.unit'])
            ->whereHas('rawGood', fn($q) => $q->whereIn('category', ['raw', 'pack']))
            ->whereIn('good_id', $productions->pluck('good_id'))
            ->get()
            ->groupBy('good_id');

        $reportData = [];
        foreach ($formulations as $goodId => $items) {
            $production = $productions->firstWhere('good_id', $goodId);
            $multiplier = $production->remaining_requests;

            $reportData[$goodId] = [
                'good' => $items->first()->good,
                'remaining_requests' => $multiplier,
                'raws' => $items->map(function($item) use ($multiplier) {
                    return [
                        'name' => $item->rawGood->name,
                        'amount' => $item->amount * $multiplier,
                        'unit' => $item->rawGood->unit->name ?? '-'
                    ];
                })
            ];
        }
        return view('production.formulationPDF', compact('reportData'));
    }
}
