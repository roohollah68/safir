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
        Helper::access('warehouse');
        $formulations = Formulation::with(['good' , 'rawGood.unit'])->get()->groupBy('good_id');
        echo view('formulation/formulationList', compact('formulations'));
    }

    public function add()
    {
        Helper::access('warehouse');
        $goods = Good::all();
        $finals = $goods->keyBy('name')->filter(fn($good) => $good->category == 'final')->map(fn($good) => $good->id);
        $raws = $goods->keyBy('name')->filter(fn($good) => $good->category != 'final')->map(fn($good) => $good->id);
        $edit = false;
        return view('formulation/addEditFormulation', compact('goods', 'edit', 'finals', 'raws'));
    }

    public function edit($id)
    {
        Helper::access('warehouse');
        $good = Good::with('formulations')->findOrFail($id);
        $goods = Good::all();
        $finals = $goods->keyBy('name')->filter(fn($good) => $good->category == 'final')->map(fn($good) => $good->id);
        $raws = $goods->keyBy('name')->filter(fn($good) => $good->category != 'final')->map(fn($good) => $good->id);
        $edit = true;
        return view('formulation/addEditFormulation', compact('goods', 'edit', 'finals', 'raws', 'good'));
    }

    public function addEditRow(Request $req, $id = null)
    {
        Helper::access('editWarehouse');
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
        Helper::access('editWarehouse');
        $formulations = Formulation::where('good_id' , $id)->delete();
        return redirect('/formulation/list');
    }

    public function deleteRow($id)
    {
        Helper::access('editWarehouse');
        $formulation = Formulation::findOrFail($id);
        $formulation->delete();
        return $this->getRawGoods($formulation->good_id);
    }

    public function getRawGoods($id)
    {
        $good = Good::with('formulations.rawGood')->findOrFail($id);
        return view('formulation/rawGoods', compact('good'));
    }
}
