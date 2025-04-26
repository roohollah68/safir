<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Formulation;
use App\Models\Good;
use Illuminate\Http\Request;

class FormulationController extends Controller
{
    public function list()
    {
        Helper::access('formulation');
        $formulations = Formulation::with(['good' , 'rawGood.unit'])->get()->groupBy('good_id');

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
        $goods = Good::all();
        $finals = $goods->keyBy('name')->filter(fn($good) => $good->category == 'final')->map(fn($good) => $good->id);
        $raws = $goods->keyBy('name')->filter(fn($good) => $good->category != 'final')->map(fn($good) => $good->id);
        $edit = false;
        return view('formulation/addEditFormulation', compact('goods', 'edit', 'finals', 'raws'));
    }

    public function edit($id)
    {
        Helper::access('formulation');
        $good = Good::with('formulations')->findOrFail($id);
        $goods = Good::all();
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
}
