<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Good;
use App\Models\Keysungood;
use Illuminate\Http\Request;

class KeysunController extends Controller
{
    public function good()
    {
        $goods = Good::where('tag', '>', pow(10, 12))->get()->keyBy('id');
        $keysungoods = Keysungood::all()->keyBy('id');
        $goods = $goods->filter(fn($good)=>!isset($keysungoods[$good->id]));
        return view('keysun.good', compact('goods'));
    }

    public function excelData(Request $request)
    {
        $orders = Helper::Order(false)
            ->whereIn('id', $request->ids)
            ->with(['orderProducts.product.good.keysungood', 'customer'])
            ->get()->keyBy('id');


        return [
            view('keysun.invoice1', compact('orders'))->render(),
            view('keysun.invoice2', compact('orders'))->render()
        ];
    }
}
