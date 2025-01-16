<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function new()
    {
        Helper::access('withdrawal');
        $supplier = new Supplier();
        return view('withdrawal.suppliersAddEdit' , ['supplier' => $supplier]);
    }
    public function edit($id)
    {
        Helper::access('withdrawal');
        $supplier = Supplier::findOrFail($id);
        return view('withdrawal.suppliersAddEdit' , ['supplier' => $supplier]);
    }
    public function insertOrUpdate( Request $req)
    {
        Helper::access('withdrawal');
        Supplier::updateOrCreate([
            'id' => $req->id,
        ],$req->all());
        return redirect()->back();
    }

    public function list()
    {
        Helper::access('withdrawal');
        $suppliers = Supplier::with('withdrawals')->get()->keyBy('id');
        echo view('withdrawal.suppliersList' , ['suppliers' => $suppliers]);
        echo $b;
        return $b;
    }
}
