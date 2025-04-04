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
        $keysungoods = Keysungood::all()->keyBy('good_id');
        $goods = $goods->filter(fn($good)=>!isset($keysungoods[$good->id]));
        return view('keysun.good', compact('goods'));
    }

    public function excelData(Request $request)
    {
        $orders = Helper::Order(false)
            ->whereIn('id', $request->ids)
            ->with(['orderProducts', 'customer'])
            ->get()->keyBy('id');
        foreach ($orders as $order) {
            $order->customer_type = 1;
            $order->national_id_code = '';
            $order->economic_code = '';
            if ($order->customer) {
                $order->economic_code = $order->customer->economic_code ?: '';
                $order->customer_type = $order->customer->customer_type == 'Individual' ? 1 : 2;
                if ($order->customer_type == 1)
                    $order->national_id_code = $order->customer->national_code;
                else
                    $order->national_id_code = $order->customer->national_id;
            }
        }
        return [
            view('keysun.invoice1', compact('orders'))->render(),
            view('keysun.invoice2', compact('orders'))->render()
        ];
    }
}
